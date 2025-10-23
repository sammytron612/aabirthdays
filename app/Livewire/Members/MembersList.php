<?php

namespace App\Livewire\Members;

use App\Models\Member;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class MembersList extends Component
{
    use WithPagination;

    #[Title('Members List')]
    #[Layout('components.layouts.app')]

    public $search = '';
    public $sortBy = 'upcoming_anniversary';
    public $sortDirection = 'asc';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortBy' => ['except' => 'upcoming_anniversary'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function getUpcomingAnniversaryDate($sobrietyDate)
    {
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;
        $anniversaryThisYear = Carbon::createFromFormat('Y-m-d', $currentYear . '-' . $sobrietyDate->format('m-d'));

        // For current month, always show this year's anniversary (even if passed)
        if ($anniversaryThisYear->month == $currentMonth) {
            return $anniversaryThisYear;
        }

        // For other months, if this year's anniversary has passed, get next year's
        if ($anniversaryThisYear->isPast()) {
            $anniversaryThisYear = $anniversaryThisYear->addYear();
        }

        return $anniversaryThisYear;
    }

    public function render()
    {
        $query = Member::with('sobrietyDates')
            ->whereHas('sobrietyDates');

        // Apply search filter
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        // Apply sorting
        if ($this->sortBy === 'upcoming_anniversary') {
            // Custom sorting for upcoming anniversaries - current month first, then next month
            $members = $query->get()->sortBy(function ($member) {
                $sobrietyDate = $member->mostRecentSobrietyDate();
                if (!$sobrietyDate) return '9999-12-31'; // Put members without dates at the end

                $anniversaryDate = $this->getUpcomingAnniversaryDate($sobrietyDate->sobriety_date);
                $currentMonth = Carbon::now()->month;
                $currentYear = Carbon::now()->year;

                // Priority sorting: current month first, then chronological
                if ($anniversaryDate->month == $currentMonth && $anniversaryDate->year == $currentYear) {
                    // Current month - sort by day within month (prefix with 0)
                    return '0-' . $anniversaryDate->format('Y-m-d');
                } else {
                    // Other months - sort chronologically (prefix with 1)
                    return '1-' . $anniversaryDate->format('Y-m-d');
                }
            });

            if ($this->sortDirection === 'desc') {
                $members = $members->sortByDesc(function ($member) {
                    $sobrietyDate = $member->mostRecentSobrietyDate();
                    if (!$sobrietyDate) return '0000-01-01'; // Put members without dates at the end in desc

                    $anniversaryDate = $this->getUpcomingAnniversaryDate($sobrietyDate->sobriety_date);
                    $currentMonth = Carbon::now()->month;
                    $currentYear = Carbon::now()->year;

                    // Priority sorting: current month first, then chronological
                    if ($anniversaryDate->month == $currentMonth && $anniversaryDate->year == $currentYear) {
                        // Current month - sort by day within month (prefix with 0)
                        return '0-' . $anniversaryDate->format('Y-m-d');
                    } else {
                        // Other months - sort chronologically (prefix with 1)
                        return '1-' . $anniversaryDate->format('Y-m-d');
                    }
                });
            }

            // Paginate manually
            $perPage = 15;
            $currentPage = $this->getPage();
            $total = $members->count();
            $members = $members->slice(($currentPage - 1) * $perPage, $perPage)->values();

            $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
                $members,
                $total,
                $perPage,
                $currentPage,
                ['path' => request()->url(), 'pageName' => 'page']
            );
        } else {
            // Standard database sorting
            $query->join('sobriety_dates', 'members.id', '=', 'sobriety_dates.member_id');

            switch ($this->sortBy) {
                case 'name':
                    $query->orderBy('members.name', $this->sortDirection);
                    break;
                case 'email':
                    $query->orderBy('members.email', $this->sortDirection);
                    break;
                case 'sobriety_date':
                    $query->orderBy('sobriety_dates.sobriety_date', $this->sortDirection);
                    break;
                case 'days_sober':
                    $query->orderBy('sobriety_dates.sobriety_date', $this->sortDirection === 'asc' ? 'desc' : 'asc');
                    break;
                case 'current_month_sober':
                    // Sort by sobriety date for members with anniversaries in current month
                    $currentMonth = Carbon::now()->month;
                    $query->whereMonth('sobriety_dates.sobriety_date', $currentMonth)
                          ->orderBy('sobriety_dates.sobriety_date', $this->sortDirection);
                    break;
            }

            $paginator = $query->select('members.*')->paginate(15);
        }

        return view('livewire.members.members-list', [
            'members' => $paginator,
        ]);
    }
}
