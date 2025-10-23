<?php

namespace App\Livewire\Analytics;

use App\Models\Member;
use App\Models\SobrietyDate;
use Livewire\Component;
use Carbon\Carbon;

class Statistics extends Component
{
    public function getSobrietyDatesByMonth()
    {
        $sobrietyDates = SobrietyDate::all();
        $monthlyData = [];

        // Initialize all months with 0
        for ($i = 1; $i <= 12; $i++) {
            $monthlyData[Carbon::create()->month($i)->format('M')] = 0;
        }

        // Count sobriety dates by month
        foreach ($sobrietyDates as $sobrietyDate) {
            $month = Carbon::parse($sobrietyDate->sobriety_date)->format('M');
            $monthlyData[$month]++;
        }

        return $monthlyData;
    }    public function getLongestSobriety()
    {
        $members = Member::with('sobrietyDates')->get();
        $sobrietyData = [];

        foreach ($members as $member) {
            $mostRecentDate = $member->mostRecentSobrietyDate;
            if ($mostRecentDate) {
                $daysSober = $mostRecentDate->daysSober();
                $yearsSober = round($daysSober / 365.25, 2); // More accurate with leap years
                $sobrietyData[] = [
                    'name' => $member->name,
                    'years' => $yearsSober,
                    'days' => $daysSober,
                    'formatted' => $mostRecentDate->formattedTimeSober()
                ];
            }
        }

        // Sort by days sober (descending) for accuracy
        usort($sobrietyData, function($a, $b) {
            return $b['days'] - $a['days'];
        });

        return array_slice($sobrietyData, 0, 10); // Top 10
    }

    public function getMembersWithRelapses()
    {
        $members = Member::withCount('sobrietyDates')->get();
        $relapseData = [];

        foreach ($members as $member) {
            if ($member->sobriety_dates_count > 1) {
                $relapseData[] = [
                    'name' => $member->name,
                    'count' => $member->sobriety_dates_count
                ];
            }
        }

        // Sort by number of sobriety dates (descending)
        usort($relapseData, function($a, $b) {
            return $b['count'] - $a['count'];
        });

        return $relapseData;
    }

    public function render()
    {
        return view('livewire.analytics.statistics', [
            'sobrietyDatesByMonth' => $this->getSobrietyDatesByMonth(),
            'longestSobriety' => $this->getLongestSobriety(),
            'membersWithRelapses' => $this->getMembersWithRelapses()
        ])->layout('components.layouts.app', ['title' => 'Analytics & Statistics']);
    }
}
