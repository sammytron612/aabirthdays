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
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <flux:button
                                    :href="route('members.edit', $member->id)"
                                    variant="ghost"
                                    size="sm"
                                    wire:navigate
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
</div>
