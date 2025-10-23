<?php

namespace App\Livewire\Dashboard;

use App\Models\Member;
use Carbon\Carbon;
use Livewire\Component;

class UpcomingAnniversaries extends Component
{
    public $selectedPeriod = 'all'; // all, yearly, 1, 2, 3, 6, 9
    public $currentMonth;
    public $currentYear;

    public function mount()
    {
        $this->currentMonth = now()->month;
        $this->currentYear = now()->year;
    }

    public function setPeriod($period)
    {
        $this->selectedPeriod = $period;
    }

    public function previousMonth()
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }

    public function nextMonth()
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }

    public function getCurrentMonthName()
    {
        return Carbon::create($this->currentYear, $this->currentMonth, 1)->format('F Y');
    }

    private function getAnniversariesForPeriod($period)
    {
        $now = Carbon::now();
        $currentMonth = $this->currentMonth;
        $currentYear = $this->currentYear;
        $anniversaries = collect();

        // Get all members with sobriety dates
        $members = Member::with('sobrietyDates')->get();

        foreach ($members as $member) {
            // Get the most recent sobriety date for this member
            $latestSobrietyDate = $member->sobrietyDates->sortByDesc('sobriety_date')->first();

            if ($latestSobrietyDate) {
                $sobrietyStart = Carbon::parse($latestSobrietyDate->sobriety_date);

                if ($period === 'all' || $period === 'yearly') {
                    // Show yearly anniversaries (1+ years) happening in current month
                    if ($sobrietyStart->month === $currentMonth) {
                        // Calculate the anniversary date for this year
                        $anniversaryThisYear = $sobrietyStart->copy()->year($currentYear);

                        // Calculate which anniversary year this would be
                        $yearsAnniversary = $currentYear - $sobrietyStart->year;

                        // If anniversary already passed this year, show next year's anniversary
                        if ($anniversaryThisYear < $now) {
                            $anniversaryThisYear->addYear();
                            $yearsAnniversary += 1;
                        }

                        // Only show if it's 1+ years
                        if ($yearsAnniversary >= 1) {
                            $anniversaries->push([
                                'member' => $member,
                                'sobriety_date' => $sobrietyStart->toDateString(),
                                'anniversary_date' => $anniversaryThisYear->toDateString(),
                                'milestone' => $yearsAnniversary . ' ' . ($yearsAnniversary == 1 ? 'Year' : 'Years'),
                                'type' => 'yearly',
                                'is_special' => in_array($yearsAnniversary, [1, 5, 10, 15, 20, 25, 30])
                            ]);
                        }
                    }
                }

                if ($period === 'all' || is_numeric($period)) {
                    if ($period === 'all') {
                        // Show monthly milestones 1-11 months happening in current month
                        for ($targetMonths = 1; $targetMonths <= 11; $targetMonths++) {
                            $targetDate = $sobrietyStart->copy()->addMonths($targetMonths);

                            // Check if the target milestone date is in current month
                            if ($targetDate->month === $currentMonth && $targetDate->year === $currentYear) {
                                $anniversaries->push([
                                    'member' => $member,
                                    'sobriety_date' => $sobrietyStart->toDateString(),
                                    'anniversary_date' => $targetDate->toDateString(),
                                    'milestone' => $targetMonths . ' ' . ($targetMonths == 1 ? 'Month' : 'Months'),
                                    'type' => 'monthly',
                                    'is_special' => $targetMonths == 6
                                ]);
                            }
                        }
                    } else {
                        // Show members who will have exactly X months sober in current month
                        $targetMonths = (int)$period;
                        $targetDate = $sobrietyStart->copy()->addMonths($targetMonths);

                        // Check if the target milestone date is in current month
                        if ($targetDate->month === $currentMonth && $targetDate->year === $currentYear) {
                            $anniversaries->push([
                                'member' => $member,
                                'sobriety_date' => $sobrietyStart->toDateString(),
                                'anniversary_date' => $targetDate->toDateString(),
                                'milestone' => $targetMonths . ' ' . ($targetMonths == 1 ? 'Month' : 'Months'),
                                'type' => 'monthly',
                                'is_special' => $targetMonths == 6
                            ]);
                        }
                    }
                }
            }
        }

        return $anniversaries->sortBy('anniversary_date');
    }

    public function render()
    {
        $anniversaries = $this->getAnniversariesForPeriod($this->selectedPeriod);

        return view('livewire.dashboard.upcoming-anniversaries', [
            'anniversaries' => $anniversaries,
            'currentYear' => $this->currentYear,
            'currentMonth' => $this->currentMonth,
        ]);
    }
}
