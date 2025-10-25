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
    public $showDisabled = true;

    // Edit modal properties
    public $showEditModal = false;
    public $editingMember = null;
    public $name = '';
    public $email = '';
    public $disabled = false;
    public $sobrietyDates = [];
    public $newSobrietyDate = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortBy' => ['except' => 'upcoming_anniversary'],
        'sortDirection' => ['except' => 'asc'],
        'showDisabled' => ['except' => true],
    ];    public function updatingSearch()
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

    public function openEditModal($memberId)
    {
        $this->editingMember = Member::findOrFail($memberId);
        $this->name = $this->editingMember->name;
        $this->email = $this->editingMember->email;
        $this->disabled = $this->editingMember->disabled ?? false;

        // Load all sobriety dates ordered by date descending (most recent first)
        $this->sobrietyDates = $this->editingMember->sobrietyDates()->orderByDesc('sobriety_date')->get()->map(function($date) {
            return [
                'id' => $date->id,
                'sobriety_date' => $date->sobriety_date->format('Y-m-d'),
            ];
        })->toArray();

        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editingMember = null;
        $this->name = '';
        $this->email = '';
        $this->disabled = false;
        $this->sobrietyDates = [];
        $this->newSobrietyDate = '';
    }

    public function saveEditedMember()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:members,email,' . $this->editingMember->id,
        ]);

        $this->editingMember->update([
            'name' => $this->name,
            'email' => $this->email,
            'disabled' => $this->disabled,
        ]);

        // Handle multiple sobriety dates
        if (!empty($this->sobrietyDates)) {
            // Get existing sobriety date IDs
            $existingIds = collect($this->sobrietyDates)->pluck('id')->filter()->toArray();

            // Delete sobriety dates that are no longer in the list
            $this->editingMember->sobrietyDates()->whereNotIn('id', $existingIds)->delete();

            // Update or create sobriety dates
            foreach ($this->sobrietyDates as $sobrietyData) {
                if ($sobrietyData['id']) {
                    // Update existing
                    $this->editingMember->sobrietyDates()->where('id', $sobrietyData['id'])->update([
                        'sobriety_date' => $sobrietyData['sobriety_date'],
                    ]);
                } else {
                    // Create new
                    $this->editingMember->sobrietyDates()->create([
                        'sobriety_date' => $sobrietyData['sobriety_date'],
                    ]);
                }
            }
        }

        session()->flash('message', 'Member updated successfully.');
        $this->closeEditModal();
    }

    public function toggleMemberStatus()
    {
        $this->disabled = !$this->disabled;
    }

    public function addSobrietyDate()
    {
        if (!empty($this->newSobrietyDate)) {
            // Add to the array for display
            $this->sobrietyDates[] = [
                'id' => null, // Will be null for new dates
                'sobriety_date' => $this->newSobrietyDate,
            ];

            // Clear the input
            $this->newSobrietyDate = '';

            // Sort by date descending (most recent first)
            usort($this->sobrietyDates, function($a, $b) {
                return strcmp($b['sobriety_date'], $a['sobriety_date']);
            });

            // Emit event to trigger scroll to bottom
            $this->dispatch('sobriety-date-added');
        }
    }

    public function removeSobrietyDate($index)
    {
        unset($this->sobrietyDates[$index]);
        $this->sobrietyDates = array_values($this->sobrietyDates); // Re-index array
    }

    public function render()
    {
        $query = Member::with('sobrietyDates')
            ->whereHas('sobrietyDates');

        // Apply disabled filter
        if (!$this->showDisabled) {
            $query->where(function($q) {
                $q->where('disabled', false)->orWhereNull('disabled');
            });
        }

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

    /**
     * Calculate and format the time sober from a given sobriety date.
     */
    public function getTimeSober($sobrietyDate)
    {
        if (!$sobrietyDate) {
            return 'N/A';
        }

        $days = \Carbon\Carbon::parse($sobrietyDate)->diffInDays(now());
        $years = floor($days / 365.25);
        $months = floor(($days % 365.25) / 30.44);
        $remainingDays = floor($days % 30.44);

        $parts = [];
        if ($years > 0) $parts[] = $years . ' year' . ($years > 1 ? 's' : '');
        if ($months > 0) $parts[] = $months . ' month' . ($months > 1 ? 's' : '');
        if ($remainingDays > 0 || empty($parts)) $parts[] = $remainingDays . ' day' . ($remainingDays != 1 ? 's' : '');

        return implode(', ', $parts);
    }
}
