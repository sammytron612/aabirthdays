<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use App\Models\Invitation;
use App\Enums\UserRole;
use Livewire\WithPagination;

class ManageUsers extends Component
{
    use WithPagination;

    public $showDeleteModal = false;
    public $userToDelete = null;

    public function toggleUserStatus($userId)
    {
        try {
            $user = User::find($userId);

            // Prevent self-action
            if ($user && $user->id === auth()->id()) {
                session()->flash('error', 'You cannot disable/enable your own account.');
                return;
            }

            if (!$user) {
                session()->flash('error', 'User not found.');
                return;
            }

            // Toggle between disabled and active status
            if ($user->role === UserRole::Disabled) {
                // When enabling, set to Admin role (most common case)
                // In a more complex app, you might want to store previous role
                $user->update(['role' => UserRole::Admin]);
                session()->flash('success', "User '{$user->name}' has been enabled and set to Admin role.");
            } else {
                // Disable the user
                $user->update(['role' => UserRole::Disabled]);
                session()->flash('success', "User '{$user->name}' has been disabled.");
            }
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while updating the user status.');
        }
    }

    public function confirmDelete($userId)
    {
        try {
            $user = User::find($userId);

            // Prevent self-deletion
            if ($user && $user->id === auth()->id()) {
                session()->flash('error', 'You cannot delete your own account.');
                return;
            }

            if (!$user) {
                session()->flash('error', 'User not found.');
                return;
            }

            // Check for related data that will be affected
            $invitationCount = Invitation::where('invited_by', $user->id)->count();

            if ($invitationCount > 0) {
                session()->flash('warning', "This user has {$invitationCount} invitation(s) that will also be deleted.");
            }

            $this->userToDelete = $user;
            $this->showDeleteModal = true;
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while preparing to delete the user.');
        }
    }

    public function deleteUser()
    {
        if (!$this->userToDelete) {
            return;
        }

        // Double check to prevent self-deletion
        if ($this->userToDelete->id === auth()->id()) {
            session()->flash('error', 'You cannot delete your own account.');
            $this->cancelDelete();
            return;
        }

        try {
            $userName = $this->userToDelete->name;

            // Delete related invitations first to avoid foreign key constraint violation
            $deletedInvitations = Invitation::where('invited_by', $this->userToDelete->id)->count();
            Invitation::where('invited_by', $this->userToDelete->id)->delete();

            // Now delete the user
            $this->userToDelete->delete();

            $message = "User '{$userName}' has been deleted successfully.";
            if ($deletedInvitations > 0) {
                $message .= " {$deletedInvitations} related invitation(s) were also deleted.";
            }

            session()->flash('success', $message);
        } catch (\Exception $e) {
            // Log the actual error for debugging
            \Log::error('User deletion failed: ' . $e->getMessage());

            session()->flash('error', 'Failed to delete user. Please try again or contact support if the problem persists.');
        }

        $this->cancelDelete();
    }    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->userToDelete = null;
    }

    public function render()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.admin.manage-users', [
            'users' => $users
        ]);
    }
}
