<?php

namespace App\Livewire\Analytics;

use App\Models\Member;
use App\Models\SobrietyDate;
use App\Models\User;
use App\Enums\UserRole;
use App\Mail\ReportEmail;
use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class Reports extends Component
{
    public $selectedMonth = '';
    public $selectedYear = '';
    public $reportData = [];
    public $showReport = false;

    public function mount()
    {
        $this->selectedMonth = Carbon::now()->month;
        $this->selectedYear = Carbon::now()->year;
    }

    public function generateReport()
    {
        if (!$this->selectedMonth || $this->selectedMonth === '') {
            session()->flash('error', 'Please select a month.');
            return;
        }

        // Get all members with their sobriety dates
        $allMembers = Member::with('sobrietyDates')->get();
        $this->reportData = collect();

        foreach ($allMembers as $member) {
            // Get the most recent sobriety date for this member
            $mostRecentSobrietyDate = $member->sobrietyDates()->orderByDesc('sobriety_date')->first();

            if ($mostRecentSobrietyDate) {
                $sobrietyStart = Carbon::parse($mostRecentSobrietyDate->sobriety_date);

                // Check if we should include this member
                $shouldInclude = false;

                if ($this->selectedMonth === 'all') {
                    // Include all members regardless of month
                    $shouldInclude = true;
                } else {
                    // Only show members who have their sobriety anniversary in the selected month
                    $shouldInclude = ($sobrietyStart->month == (int)$this->selectedMonth);
                }

                if ($shouldInclude) {
                    $this->reportData->push([
                        'member' => $member,
                        'sobriety_date' => $mostRecentSobrietyDate
                    ]);
                }
            }
        }

        // Sort by member name
        $this->reportData = $this->reportData->sortBy('member.name');

        $this->showReport = true;
    }    public function downloadReport()
    {
        if ($this->reportData->isEmpty()) {
            $this->generateReport();
        }

        if ($this->selectedMonth === 'all') {
            $monthName = 'All_Members';
        } else {
            $monthName = Carbon::create()->month((int)$this->selectedMonth)->format('F');
        }
        $year = $this->selectedYear ?: Carbon::now()->year;
        $filename = "sobriety_anniversaries_{$monthName}_{$year}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, ['Member Name', 'Email', 'Sobriety Date', 'Days Sober', 'Years Sober']);

            foreach ($this->reportData as $anniversaryData) {
                $member = $anniversaryData['member'];
                $sobrietyDate = $anniversaryData['sobriety_date'];

                fputcsv($file, [
                    $member->name,
                    $member->email,
                    $sobrietyDate->sobriety_date,
                    $sobrietyDate->daysSober(),
                    round($sobrietyDate->daysSober() / 365.25, 2)
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function emailReport()
    {
        if ($this->reportData->isEmpty()) {
            $this->generateReport();
        }

        if ($this->reportData->isEmpty()) {
            session()->flash('error', 'No data available to email.');
            return;
        }

        // Get users with admin and birthday roles (excluding disabled)
        $recipients = User::whereIn('role', [UserRole::Admin, UserRole::Birthday])->get();

        if ($recipients->isEmpty()) {
            session()->flash('error', 'No admin or birthday secretary users found to send the report to.');
            return;
        }

        try {
            // Generate report content
            $monthName = $this->selectedMonth === 'all' ? 'All Members' : Carbon::create()->month((int)$this->selectedMonth)->format('F');
            $year = $this->selectedYear ?: Carbon::now()->year;

            $subject = "Sobriety Anniversary Report - {$monthName} {$year}";
            $filename = "sobriety_anniversaries_{$monthName}_{$year}.csv";

            // Generate CSV data
            $csvData = $this->generateCsvData();

            // Get the birthday secretary name
            $birthdaySecretary = User::where('role', UserRole::Birthday)->first();
            $secretaryName = $birthdaySecretary ? $birthdaySecretary->name : 'Birthday Secretary';

            // Prepare email content
            $reportContent = [
                'title' => $subject,
                'message' => 'Please find the attached sobriety anniversary report in CSV format.',
                'period' => "{$monthName} {$year}",
                'totalMembers' => $this->reportData->count(),
                'secretaryName' => $secretaryName
            ];

            // Send email to each recipient
            foreach ($recipients as $recipient) {
                Mail::to($recipient->email)->queue(new ReportEmail($subject, $reportContent, $csvData, $filename));
            }

            session()->flash('success', "Report emailed successfully to " . $recipients->count() . " recipient(s) with CSV attachment.");

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to send email: ' . $e->getMessage());
        }
    }

    private function generateCsvData()
    {
        $output = fopen('php://temp', 'r+');

        // Add CSV headers
        fputcsv($output, ['Member Name', 'Email', 'Sobriety Date', 'Days Sober', 'Years Sober']);

        foreach ($this->reportData as $anniversaryData) {
            $member = $anniversaryData['member'];
            $sobrietyDate = $anniversaryData['sobriety_date'];

            fputcsv($output, [
                $member->name,
                $member->email,
                $sobrietyDate->sobriety_date,
                $sobrietyDate->daysSober(),
                round($sobrietyDate->daysSober() / 365.25, 2)
            ]);
        }

        rewind($output);
        $csvData = stream_get_contents($output);
        fclose($output);

        return $csvData;
    }

    public function getMonthOptions()
    {
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = Carbon::create()->month($i)->format('F');
        }
        return $months;
    }

    public function getYearOptions()
    {
        $currentYear = Carbon::now()->year;
        $years = [];

        // Get the earliest sobriety date to determine year range
        $earliestDate = SobrietyDate::orderBy('sobriety_date')->first();
        $startYear = $earliestDate ? Carbon::parse($earliestDate->sobriety_date)->year : $currentYear;

        for ($year = $startYear; $year <= $currentYear + 1; $year++) {
            $years[$year] = $year;
        }

        return $years;
    }

    public function render()
    {
        return view('livewire.analytics.reports', [
            'monthOptions' => $this->getMonthOptions(),
            'yearOptions' => $this->getYearOptions()
        ])->layout('components.layouts.app', ['title' => 'Reports']);
    }
}
