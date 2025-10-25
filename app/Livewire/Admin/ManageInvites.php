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
            // Create the invitation
            $invitation = Invitation::createInvitation(
                $this->email,
                $this->name,
                UserRole::from($this->role),
                auth()->id()
            );

            // Send the invitation email
            Notification::route('mail', $this->email)->notify(new InvitationNotification($invitation));

            session()->flash('success', 'Invitation sent successfully to ' . $this->email);

            $this->resetForm();
            $this->hideForm();

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to send invitation: ' . $e->getMessage());
        }
    }

    public function deleteUser($userId)
    {
        try {
            $user = User::findOrFail($userId);

            if ($user->id === auth()->id()) {
                session()->flash('error', 'You cannot delete your own account.');
                return;
            }

            $user->delete();
            session()->flash('success', 'User deleted successfully.');

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }

    public function resendInvite($userId)
    {
        try {
            $user = User::findOrFail($userId);

            // Check if there's a pending invitation for this user
            $invitation = Invitation::where('email', $user->email)
                ->where('accepted_at', null)
                ->first();

            if ($invitation && $invitation->isValid()) {
                // Resend existing invitation
                Notification::route('mail', $user->email)->notify(new InvitationNotification($invitation));
            } else {
                // Create new invitation for existing user
                $newInvitation = Invitation::createInvitation(
                    $user->email,
                    $user->name,
                    $user->role,
                    auth()->id()
                );

                Notification::route('mail', $user->email)->notify(new InvitationNotification($newInvitation));
            }

            session()->flash('success', 'Invitation resent to ' . $user->email);

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to resend invitation: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.admin.manage-invites', [
            'users' => $users,
            'roleOptions' => UserRole::options()
        ])->layout('components.layouts.app', ['title' => 'Manage Invites']);
    }
}
