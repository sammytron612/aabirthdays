<?php

use App\Livewire\Members\AddMember;
use App\Livewire\Members\MembersList;
use App\Livewire\Analytics\Statistics;
use App\Livewire\Analytics\Reports;
use App\Livewire\Communication\Email;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\DataManagement;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('members', MembersList::class)->name('members.index');
    Route::get('members/add', AddMember::class)->name('members.add');
    Route::get('members/edit/{id}', AddMember::class)->name('members.edit');

    Route::get('analytics/statistics', Statistics::class)->name('analytics.statistics');
    Route::get('analytics/reports', Reports::class)->name('analytics.reports');

    Route::get('communication/email', Email::class)->name('communication.email');

    Route::get('settings/profile', Profile::class)->name('profile.edit');
    Route::get('settings/password', Password::class)->name('user-password.edit');
    Route::get('settings/appearance', Appearance::class)->name('appearance.edit');
    Route::get('settings/data-management', DataManagement::class)->name('settings.data-management');

    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});
