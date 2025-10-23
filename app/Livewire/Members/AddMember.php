<?php

namespace App\Livewire\Members;

use App\Models\Member;
use App\Models\SobrietyDate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class AddMember extends Component
{
    #[Title('Add/Edit Member')]
    #[Layout('components.layouts.app')]

    public $editingId = null;
    public $name = '';
    public $email = '';
    public $sobriety_date = '';
    public $sobrietyDates = [];
    public $newSobrietyDate = '';

    public function mount($id = null)
    {
        if ($id) {
            $this->editingId = $id;
            $member = Member::findOrFail($id);
            $this->name = $member->name;
            $this->email = $member->email;

            // Load all sobriety dates ordered by date descending (most recent first)
            $this->sobrietyDates = $member->sobrietyDates()->orderByDesc('sobriety_date')->get()->map(function($date) {
                return [
                    'id' => $date->id,
                    'sobriety_date' => $date->sobriety_date->format('Y-m-d'),
                ];
            })->toArray();

            // Load the most recent sobriety date for backward compatibility
            $mostRecentSobrietyDate = $member->sobrietyDates()->orderByDesc('sobriety_date')->first();
            if ($mostRecentSobrietyDate) {
                $this->sobriety_date = $mostRecentSobrietyDate->sobriety_date->format('Y-m-d');
            }
        }
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:members,email,' . $this->editingId,
            'sobriety_date' => 'required|date',
        ]);

        if ($this->editingId) {
            $member = Member::findOrFail($this->editingId);
            $member->update([
                'name' => $this->name,
                'email' => $this->email,
            ]);

            // Handle multiple sobriety dates
            if (!empty($this->sobrietyDates)) {
                // Get existing sobriety date IDs
                $existingIds = collect($this->sobrietyDates)->pluck('id')->filter()->toArray();

                // Delete sobriety dates that are no longer in the list
                $member->sobrietyDates()->whereNotIn('id', $existingIds)->delete();

                // Update or create sobriety dates
                foreach ($this->sobrietyDates as $sobrietyData) {
                    if ($sobrietyData['id']) {
                        // Update existing
                        $member->sobrietyDates()->where('id', $sobrietyData['id'])->update([
                            'sobriety_date' => $sobrietyData['sobriety_date'],
                        ]);
                    } else {
                        // Create new
                        $member->sobrietyDates()->create([
                            'sobriety_date' => $sobrietyData['sobriety_date'],
                        ]);
                    }
                }
            } else {
                // Fallback to single sobriety date for backward compatibility
                $firstSobrietyDate = $member->sobrietyDates()->first();
                if ($firstSobrietyDate) {
                    $firstSobrietyDate->update([
                        'sobriety_date' => $this->sobriety_date,
                    ]);
                } else {
                    $member->sobrietyDates()->create([
                        'sobriety_date' => $this->sobriety_date,
                    ]);
                }
            }

            $message = 'Member updated successfully!';
        } else {
            $member = Member::create([
                'name' => $this->name,
                'email' => $this->email,
            ]);

            // Create sobriety dates
            if (!empty($this->sobrietyDates)) {
                foreach ($this->sobrietyDates as $sobrietyData) {
                    $member->sobrietyDates()->create([
                        'sobriety_date' => $sobrietyData['sobriety_date'],
                    ]);
                }
            } else {
                // Fallback to single sobriety date
                $member->sobrietyDates()->create([
                    'sobriety_date' => $this->sobriety_date,
                ]);
            }

            $message = 'Member added successfully!';
        }

        session()->flash('message', $message);

        return redirect()->route('members.index');
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

            // Sort by date
            usort($this->sobrietyDates, function($a, $b) {
                return strcmp($a['sobriety_date'], $b['sobriety_date']);
            });
        }
    }

    public function removeSobrietyDate($index)
    {
        unset($this->sobrietyDates[$index]);
        $this->sobrietyDates = array_values($this->sobrietyDates); // Re-index array
    }

    public function render()
    {
        return view('livewire.members.add-member');
    }
}
