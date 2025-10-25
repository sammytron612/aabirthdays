<?php

namespace App\Livewire\Admin;

use App\Enums\UserRole;
use App\Models\Invitation;
use App\Models\User;
use App\Notifications\InvitationNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Component;
use Livewire\WithPagination;

class ManageInvites extends Component
{
    use WithPagination;

    public $email = '';
    public $name = '';
    public $role = 'admin';
    public $showInviteForm = false;

    protected $rules = [
        'email' => 'required|email|unique:users,email|unique:invitations,email',
        'name' => 'required|string|min:2|max:255',
        'role' => 'required|in:admin,birthday'
    ];

    protected $messages = [
        'email.required' => 'Email address is required.',
        'email.email' => 'Please enter a valid email address.',
        'email.unique' => 'This email address is already registered or has a pending invitation.',
        'name.required' => 'Full name is required.',
        'name.min' => 'Name must be at least 2 characters.',
        'role.required' => 'Please select a role.',
        'role.in' => 'Please select a valid role.'
    ];

    public function mount()
    {
        $this->role = UserRole::Admin->value;
    }

    public function showForm()
    {
        $this->showInviteForm = true;
        $this->resetForm();
    }

    public function hideForm()
    {
        $this->showInviteForm = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->email = '';
        $this->name = '';
        $this->role = UserRole::Admin->value;
        $this->resetErrorBag();
    }

    public function sendInvite()
    {
        $this->validate();

        try {
            // If inviting someone as Birthday Secretary, disable existing birthday secretaries
            if ($this->role === UserRole::Birthday->value) {
                $existingBirthdayUsers = User::where('role', UserRole::Birthday)->get();
                foreach ($existingBirthdayUsers as $user) {
                    $user->update(['role' => UserRole::Disabled]);
                }

                if ($existingBirthdayUsers->count() > 0) {
                    session()->flash('success',
                        'Invitation sent successfully to ' . $this->email . '. ' .
                        $existingBirthdayUsers->count() . ' existing Birthday Secretary(s) have been disabled.'
                    );
                } else {
                    session()->flash('success', 'Invitation sent successfully to ' . $this->email);
                }
            } else {
                session()->flash('success', 'Invitation sent successfully to ' . $this->email);
            }

            // Create the invitation
            $invitation = Invitation::createInvitation(
                $this->email,
                $this->name,
                UserRole::from($this->role),
                auth()->id()
            );

            // Send the invitation email
            Notification::route('mail', $this->email)->notify(new InvitationNotification($invitation));

            $this->resetForm();
            $this->hideForm();

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to send invitation: ' . $e->getMessage());
        }
    }

    public function deleteInvitation($invitationId)
    {
        try {
            $invitation = Invitation::findOrFail($invitationId);
            $invitation->delete();
            session()->flash('success', 'Invitation deleted successfully.');

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete invitation: ' . $e->getMessage());
        }
    }

    public function resendInvitation($invitationId)
    {
        try {
            $invitation = Invitation::findOrFail($invitationId);

            if (!$invitation->isValid()) {
                if ($invitation->isExpired()) {
                    session()->flash('error', 'Cannot resend expired invitation. Please create a new one.');
                    return;
                }
                if ($invitation->isAccepted()) {
                    session()->flash('error', 'This invitation has already been accepted.');
                    return;
                }
            }

            // Resend the invitation
            Notification::route('mail', $invitation->email)->notify(new InvitationNotification($invitation));
            session()->flash('success', 'Invitation resent to ' . $invitation->email);

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to resend invitation: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $invitations = Invitation::with('invitedBy')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.admin.manage-invites', [
            'invitations' => $invitations,
            'roleOptions' => UserRole::options()
        ])->layout('components.layouts.app', ['title' => 'Manage Invites']);
    }
}
