<?php

namespace App\Livewire\Analytics;

use App\Models\Member;
use App\Models\SobrietyDate;
use Livewire\Component;
use Carbon\Carbon;

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
            foreach ($member->sobrietyDates as $sobrietyDate) {
                $sobrietyStart = Carbon::parse($sobrietyDate->sobriety_date);

                // Only show members who have their sobriety anniversary in the selected month
                if ($sobrietyStart->month == (int)$this->selectedMonth) {
                    $now = Carbon::now();

                    // Calculate how long they've been sober as of today
                    $totalMonthsSober = floor($sobrietyStart->diffInMonths($now));
                    $totalYearsSober = intval($totalMonthsSober / 12);

                    if ($totalYearsSober >= 1) {
                        // They have at least 1 year of sobriety
                        $this->reportData->push([
                            'member' => $member,
                            'sobriety_date' => $sobrietyDate,
                            'anniversary_type' => 'yearly',
                            'anniversary_number' => $totalYearsSober,
                            'anniversary_display' => $totalYearsSober . ' ' . ($totalYearsSober == 1 ? 'Year' : 'Years')
                        ]);
                    } else {
                        // Less than 1 year of sobriety
                        $this->reportData->push([
                            'member' => $member,
                            'sobriety_date' => $sobrietyDate,
                            'anniversary_type' => 'monthly',
                            'anniversary_number' => $totalMonthsSober,
                            'anniversary_display' => $totalMonthsSober . ' ' . ($totalMonthsSober == 1 ? 'Month' : 'Months')
                        ]);
                    }
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

        $monthName = Carbon::create()->month((int)$this->selectedMonth)->format('F');
        $year = $this->selectedYear ?: Carbon::now()->year;
        $filename = "sobriety_anniversaries_{$monthName}_{$year}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, ['Member Name', 'Email', 'Sobriety Date', 'Days Sober', 'Years Sober', 'Anniversary Milestone']);

            foreach ($this->reportData as $anniversaryData) {
                $member = $anniversaryData['member'];
                $sobrietyDate = $anniversaryData['sobriety_date'];

                fputcsv($file, [
                    $member->name,
                    $member->email,
                    $sobrietyDate->sobriety_date,
                    $sobrietyDate->daysSober(),
                    round($sobrietyDate->daysSober() / 365.25, 2),
                    $anniversaryData['anniversary_display']
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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
