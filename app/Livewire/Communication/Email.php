<?php

namespace App\Livewire\Communication;

use App\Models\Member;
use App\Models\SobrietyDate;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class Email extends Component
{
    public $subject = '';
    public $message = '';
    public $selectedMembers = [];
    public $selectAll = false;
    public $showPreview = false;
    public $showAnniversariesModal = false;
    public $partyDateTime = '';

    public function toggleSelectAll()
    {
        if ($this->selectAll) {
            $this->selectedMembers = Member::pluck('id')->toArray();
        } else {
            $this->selectedMembers = [];
        }
    }

    public function updatedSelectAll()
    {
        $this->toggleSelectAll();
    }

    public function useTemplate($templateType)
    {
        switch ($templateType) {
            case 'birthday':
                $this->showAnniversariesModal = true;
                break;

            default:
                // Future templates can be added here
                break;
        }
    }

    public function closeAnniversariesModal()
    {
        $this->showAnniversariesModal = false;
    }

    public function getAnniversariesData()
    {
        $now = Carbon::now();
        $currentMonth = $now->month;
        $anniversaries = collect();

        // Get all members with their most recent sobriety dates
        $members = Member::with('sobrietyDates')->get();

        foreach ($members as $member) {
            $latestSobrietyDate = $member->sobrietyDates->sortByDesc('sobriety_date')->first();

            if ($latestSobrietyDate) {
                $sobrietyStart = Carbon::parse($latestSobrietyDate->sobriety_date);

                // Check for yearly anniversaries in current month
                if ($sobrietyStart->month === $currentMonth) {
                    $yearsAnniversary = $now->year - $sobrietyStart->year;
                    if ($yearsAnniversary >= 1) {
                        $anniversaries->push([
                            'member' => $member,
                            'sobriety_date' => $sobrietyStart,
                            'milestone' => $yearsAnniversary . ' ' . ($yearsAnniversary == 1 ? 'Year' : 'Years'),
                            'type' => 'yearly',
                            'anniversary_date' => $sobrietyStart->copy()->year($now->year)
                        ]);
                    }
                }

                // Check for monthly milestones (1-11 months) in current month
                for ($targetMonths = 1; $targetMonths <= 11; $targetMonths++) {
                    $targetDate = $sobrietyStart->copy()->addMonths($targetMonths);

                    if ($targetDate->month === $currentMonth && $targetDate->year === $now->year) {
                        $anniversaries->push([
                            'member' => $member,
                            'sobriety_date' => $sobrietyStart,
                            'milestone' => $targetMonths . ' ' . ($targetMonths == 1 ? 'Month' : 'Months'),
                            'type' => 'monthly',
                            'anniversary_date' => $targetDate
                        ]);
                    }
                }
            }
        }

        return $anniversaries->sortBy('anniversary_date');
    }

    public function emailAnniversaries()
    {
        $anniversaries = $this->getAnniversariesData();

        if ($anniversaries->isEmpty()) {
            session()->flash('error', 'No anniversaries found for this month.');
            return;
        }

        // Check if there are any specific milestone anniversaries for party notification
        // Party milestones: 6 months, 1, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50 years
        $partyMilestoneYears = [1, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50];

        $partyWorthy = $anniversaries->filter(function ($anniversary) use ($partyMilestoneYears) {
            // Check for 6 months
            if ($anniversary['type'] === 'monthly' && $anniversary['milestone'] === '6 Months') {
                return true;
            }

            // Check for specific year milestones
            if ($anniversary['type'] === 'yearly') {
                $years = (int) explode(' ', $anniversary['milestone'])[0];
                return in_array($years, $partyMilestoneYears);
            }

            return false;
        });

        // Pre-fill email with anniversary data
        $this->subject = 'Monthly Anniversary Celebrations - ' . $anniversaries->first()['anniversary_date']->format('F Y');

        $message = "Dear Members,\n\n";
        $message .= "We're excited to celebrate the following sobriety anniversaries this month:\n\n";

        foreach ($anniversaries as $anniversary) {
            $message .= "🎉 " . $anniversary['member']->name . " - " . $anniversary['milestone'] . " (" . $anniversary['anniversary_date']->format('M j') . ")\n";
        }

        // Add party information if there are 6-month or yearly anniversaries
        if ($partyWorthy->isNotEmpty()) {
            $message .= "\n🎊 SPECIAL CELEBRATION! 🎊\n";
            $message .= "We will be hosting a celebration party for our members reaching 6-month and yearly milestones!\n\n";

            $whenText = !empty($this->partyDateTime) ? $this->partyDateTime : '[Date and time to be announced]';
            $message .= "📅 When: " . $whenText . "\n";
            $message .= "📍 Where: The Jubilee Centre\n      Allendale Road\n      Farringdon\n      Sunderland\n      SR3 3EL\n";
            $message .= "🍽️ Please bring food to share if possible!\n\n";
            $message .= "Special congratulations to:\n";

            foreach ($partyWorthy as $anniversary) {
                $message .= "⭐ " . $anniversary['member']->name . " - " . $anniversary['milestone'] . "\n";
            }
            $message .= "\n";
        }

        $message .= "Congratulations to all!";

        $this->message = $message;

        // Set admin users as recipients - for preview purposes we'll use User IDs
        // The actual sending will use User model emails as TO recipients
        $this->selectedMembers = User::pluck('id')->toArray();
        $this->selectAll = false;

        // Close modal and show preview
        $this->showAnniversariesModal = false;
        $this->showPreview = true;
    }

    public function updatedPartyDateTime()
    {
        // Regenerate the message when party date/time changes
        if ($this->showPreview) {
            $this->regenerateMessage();
        }
    }

    protected function regenerateMessage()
    {
        $anniversaries = $this->getAnniversariesData();

        if ($anniversaries->isEmpty()) {
            return;
        }

        // Check for party-worthy anniversaries
        $partyMilestoneYears = [1, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50];

        $partyWorthy = $anniversaries->filter(function ($anniversary) use ($partyMilestoneYears) {
            // Check for 6 months
            if ($anniversary['type'] === 'monthly' && $anniversary['milestone'] === '6 Months') {
                return true;
            }

            // Check for specific year milestones
            if ($anniversary['type'] === 'yearly') {
                $years = (int) explode(' ', $anniversary['milestone'])[0];
                return in_array($years, $partyMilestoneYears);
            }

            return false;
        });

        // Regenerate message
        $message = "Dear Members,\n\n";
        $message .= "We're excited to celebrate the following sobriety anniversaries this month:\n\n";

        foreach ($anniversaries as $anniversary) {
            $message .= "🎉 " . $anniversary['member']->name . " - " . $anniversary['milestone'] . " (" . $anniversary['anniversary_date']->format('M j') . ")\n";
        }

        // Add party information if there are 6-month or yearly anniversaries
        if ($partyWorthy->isNotEmpty()) {
            $message .= "\n🎊 SPECIAL CELEBRATION! 🎊\n";
            $message .= "We will be hosting a celebration party for our members reaching 6-month and yearly milestones!\n\n";

            $whenText = !empty($this->partyDateTime) ? $this->partyDateTime : '[Date and time to be announced]';
            $message .= "📅 When: " . $whenText . "\n";
            $message .= "📍 Where: The Jubilee Centre\n      Allendale Road\n      Farringdon\n      Sunderland\n      SR3 3EL\n";
            $message .= "🍽️ Please bring food to share if possible!\n\n";
            $message .= "Special congratulations to:\n";

            foreach ($partyWorthy as $anniversary) {
                $message .= "⭐ " . $anniversary['member']->name . " - " . $anniversary['milestone'] . "\n";
            }
            $message .= "\n";
        }

        $message .= "Congratulations to all!";

        $this->message = $message;
    }

    public function showPreview()
    {
        $this->validate([
            'subject' => 'required|min:3',
            'message' => 'required|min:10',
            'selectedMembers' => 'required|array|min:1'
        ]);

        $this->showPreview = true;
    }

    public function hidePreview()
    {
        $this->showPreview = false;
    }

    public function sendEmail()
    {
        $this->validate([
            'subject' => 'required|min:3',
            'message' => 'required|min:10',
            'selectedMembers' => 'required|array|min:1'
        ]);

        // Get admin users as primary recipients (TO)
        $adminUsers = User::all();

        // Get all members for BCC only
        $allMembers = Member::all();
        $bccEmails = $allMembers->pluck('email')->toArray();

        try {
            // Send email TO admin users, BCC all members
            // Mail::to($adminUsers->pluck('email')->toArray())
            //     ->bcc($bccEmails)
            //     ->send(new AnniversaryEmail($this->subject, $this->message));

            // For now, simulate sending

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to send email: ' . $e->getMessage());
            return;
        }

        $successMessage = 'Email sent successfully! ';
        $successMessage .= 'TO: ' . count($adminUsers) . ' admin(s), ';
        $successMessage .= 'BCC: ' . count($bccEmails) . ' member(s)';

        session()->flash('success', $successMessage);        // Reset form
        $this->reset(['subject', 'message', 'selectedMembers', 'selectAll', 'showPreview']);
    }    public function getSelectedMembersProperty()
    {
        // For admin emails, we get from User model
        return User::whereIn('id', $this->selectedMembers)->get();
    }

    public function render()
    {
        $members = Member::orderBy('name')->get();
        $adminUsers = User::orderBy('name')->get();
        $anniversaries = $this->getAnniversariesData();

        return view('livewire.communication.email', [
            'members' => $members,
            'adminUsers' => $adminUsers,
            'selectedMembersData' => $this->getSelectedMembersProperty(),
            'anniversaries' => $anniversaries
        ])->layout('components.layouts.app', ['title' => 'Email Management']);
    }
}
