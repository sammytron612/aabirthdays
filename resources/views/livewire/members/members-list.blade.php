<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                {{ __('Members List') }}
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('View and manage all members and their sobriety milestones') }}
            </p>
        </div>
        <flux:button :href="route('members.add')" variant="primary" wire:navigate>
            {{ __('Add Member') }}
        </flux:button>
    </div>

    <!-- Success Message -->
    @if (session()->has('message'))
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
            <div class="text-sm text-green-800 dark:text-green-200">
                {{ session('message') }}
            </div>
        </div>
    @endif

    <!-- Search and Filters -->
    <div class="bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-lg p-4">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <flux:input
                    wire:model.live="search"
                    :label="__('Search Members')"
                    placeholder="{{ __('Search by name or email...') }}"
                />
            </div>
            <div class="flex gap-2 items-end">
                <flux:button
                    wire:click="sortBy('upcoming_anniversary')"
                    :variant="$sortBy === 'upcoming_anniversary' ? 'primary' : 'ghost'"
                    size="sm"
                >
                    {{ __('Upcoming Anniversaries') }}
                    @if($sortBy === 'upcoming_anniversary')
                        <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                    @endif
                </flux:button>
                <flux:button
                    wire:click="$set('showDisabled', {{ $showDisabled ? 'false' : 'true' }})"
                    :variant="$showDisabled ? 'ghost' : 'primary'"
                    size="sm"
                >
                    {{ $showDisabled ? __('Hide Disabled') : __('Show Disabled') }}
                </flux:button>
            </div>
        </div>
    </div>

    <!-- Members Table -->
    <div class="bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-zinc-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer"
                            wire:click="sortBy('name')">
                            {{ __('Name') }}
                            @if($sortBy === 'name')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer"
                            wire:click="sortBy('email')">
                            {{ __('Email') }}
                            @if($sortBy === 'email')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer"
                            wire:click="sortBy('sobriety_date')">
                            {{ __('Sobriety Date') }}
                            @if($sortBy === 'sobriety_date')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('Time Sober') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('Status') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('Actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-zinc-800 divide-y divide-gray-200 dark:divide-zinc-700">
                    @forelse($members as $member)
                        @php
                            $sobrietyDate = $member->mostRecentSobrietyDate();
                            $nextAnniversary = $sobrietyDate ? $this->getUpcomingAnniversaryDate($sobrietyDate->sobriety_date) : null;
                            $daysUntilAnniversary = $nextAnniversary ? round(now()->diffInDays($nextAnniversary)) : null;
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-zinc-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $member->name }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-600 dark:text-gray-300">
                                    {{ $member->email }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">
                                    {{ $sobrietyDate ? $sobrietyDate->sobriety_date->format('M j, Y') : 'N/A' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">
                                    {{ $sobrietyDate ? $sobrietyDate->formattedTimeSober() : 'N/A' }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $sobrietyDate ? number_format($sobrietyDate->daysSober()) . ' days' : '' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($member->disabled ?? false)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400">
                                        {{ __('Disabled') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">
                                        {{ __('Active') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <flux:button
                                    wire:click="openEditModal({{ $member->id }})"
                                    variant="ghost"
                                    size="sm"
                                >
                                    {{ __('Edit') }}
                                </flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="text-gray-500 dark:text-gray-400">
                                    @if($search)
                                        {{ __('No members found matching your search.') }}
                                    @else
                                        {{ __('No members found. Add your first member to get started.') }}
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($members->hasPages())
            <div class="bg-white dark:bg-zinc-800 px-4 py-3 border-t border-gray-200 dark:border-zinc-700">
                {{ $members->links() }}
            </div>
        @endif
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-lg p-4">
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Total Members') }}</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $members->total() }}</div>
        </div>
        <div class="bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-lg p-4">
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Anniversaries This Month') }}</div>
            <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                @php
                    $thisMonth = $members->filter(function($member) {
                        $sobrietyDate = $member->mostRecentSobrietyDate();
                        if (!$sobrietyDate) return false;
                        $anniversary = $this->getUpcomingAnniversaryDate($sobrietyDate->sobriety_date);
                        return $anniversary->month === now()->month && $anniversary->year === now()->year;
                    })->count();
                @endphp
                {{ $thisMonth }}
            </div>
        </div>
        <div class="bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-lg p-4">
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Upcoming (30 days)') }}</div>
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                @php
                    $upcoming = $members->filter(function($member) {
                        $sobrietyDate = $member->sobrietyDates->sortByDesc('sobriety_date')->first();
                        if (!$sobrietyDate) return false;
                        $anniversary = $this->getUpcomingAnniversaryDate($sobrietyDate->sobriety_date);
                        return round(now()->diffInDays($anniversary)) <= 30;
                    })->count();
                @endphp
                {{ $upcoming }}
            </div>
        </div>
    </div>

    <!-- Edit Member Modal -->
    @if($showEditModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-hidden">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('Edit Member') }}
                        </h3>
                        <button wire:click="closeEditModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Content -->
                <div class="p-6">
                    <form wire:submit.prevent="saveEditedMember" class="space-y-4">
                        <!-- Name -->
                        <div>
                            <flux:input
                                wire:model="name"
                                label="{{ __('Name') }}"
                                placeholder="{{ __('Enter member name') }}"
                                required
                            />
                            @error('name')
                                <div class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <flux:input
                                wire:model="email"
                                type="email"
                                label="{{ __('Email') }}"
                                placeholder="{{ __('Enter email address') }}"
                                required
                            />
                            @error('email')
                                <div class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Member Status -->
                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-zinc-700 rounded-lg">
                            <div>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ __('Member Status') }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $disabled ? __('Member is currently disabled') : __('Member is active') }}
                                </div>
                            </div>
                            <flux:button
                                wire:click="toggleMemberStatus"
                                :variant="$disabled ? 'primary' : 'danger'"
                                size="sm"
                                type="button"
                            >
                                {{ $disabled ? __('Enable Member') : __('Disable Member') }}
                            </flux:button>
                        </div>

                        <!-- Sobriety Dates Section -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">
                                {{ __('Sobriety Dates') }}
                            </h4>

                            <!-- Existing Sobriety Dates -->
                            @if(!empty($sobrietyDates))
                                <div class="space-y-3 mb-4">
                                    @foreach($sobrietyDates as $index => $sobrietyData)
                                        <div class="flex items-center space-x-3 p-3 bg-gray-50 dark:bg-zinc-700 rounded-lg">
                                            <input
                                                type="date"
                                                wire:model="sobrietyDates.{{ $index }}.sobriety_date"
                                                class="flex-1 rounded-md border-gray-300 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            />
                                            <button
                                                type="button"
                                                wire:click="removeSobrietyDate({{ $index }})"
                                                class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Add New Sobriety Date -->
                            <div class="flex items-center space-x-3">
                                <input
                                    type="date"
                                    wire:model="newSobrietyDate"
                                    placeholder="{{ __('Select new sobriety date') }}"
                                    class="flex-1 rounded-md border-gray-300 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <flux:button
                                    wire:click="addSobrietyDate"
                                    variant="primary"
                                    size="sm"
                                    type="button"
                                >
                                    {{ __('Add Date') }}
                                </flux:button>
                            </div>

                            @error('newSobrietyDate')
                                <div class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-end space-x-3 pt-4">
                            <flux:button
                                wire:click="closeEditModal"
                                variant="ghost"
                                type="button"
                            >
                                {{ __('Cancel') }}
                            </flux:button>
                            <flux:button
                                type="submit"
                                variant="primary"
                            >
                                {{ __('Save Changes') }}
                            </flux:button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
