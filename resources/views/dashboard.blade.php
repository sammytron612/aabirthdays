<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <!-- Welcome Section -->
        <div class="bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-lg p-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">
                {{ __('Welcome back!') }}
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                {{ __('Here\'s what\'s happening with your sobriety community.') }}
            </p>
        </div>

        <!-- Upcoming Anniversaries Component -->
        <div class="bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-lg p-6">
            <livewire:dashboard.upcoming-anniversaries />
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ __('Quick Actions') }}</h3>
                <div class="space-y-3">
                    <flux:button :href="route('members.add')" variant="primary" class="w-full" wire:navigate>
                        {{ __('Add New Member') }}
                    </flux:button>
                    <flux:button :href="route('members.index')" variant="ghost" class="w-full" wire:navigate>
                        {{ __('View All Members') }}
                    </flux:button>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ __('Statistics') }}</h3>
                <div class="space-y-3">
                    @php
                        $totalMembers = App\Models\Member::count();
                        $thisMonthAnniversaries = App\Models\Member::with('sobrietyDates')
                            ->whereHas('sobrietyDates', function($query) {
                                $query->whereMonth('sobriety_date', now()->month);
                            })->count();
                    @endphp
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('Total Members') }}</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $totalMembers }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('This Month Anniversaries') }}</span>
                        <span class="text-sm font-semibold text-green-600 dark:text-green-400">{{ $thisMonthAnniversaries }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
