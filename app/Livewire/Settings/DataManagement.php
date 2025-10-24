<?php

namespace App\Livewire\Settings;

use App\Models\Member;
use App\Models\SobrietyDate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class DataManagement extends Component
{
    #[Title('Data Management')]
    #[Layout('components.layouts.app')]

    public $showConfirmModal = false;

    public function openConfirmModal()
    {
        $this->showConfirmModal = true;
    }

    public function closeConfirmModal()
    {
        $this->showConfirmModal = false;
    }

    public function removeDummyData()
    {
        try {
            // Delete all sobriety dates first (due to foreign key constraints)
            SobrietyDate::truncate();

            // Delete all members
            Member::truncate();

            session()->flash('message', 'All member and sobriety date data has been successfully removed.');

        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while removing data: ' . $e->getMessage());
        }

        $this->closeConfirmModal();
    }

    public function render()
    {
        $memberCount = Member::count();
        $sobrietyDateCount = SobrietyDate::count();

        return view('livewire.settings.data-management', [
            'memberCount' => $memberCount,
            'sobrietyDateCount' => $sobrietyDateCount,
        ]);
    }
}
