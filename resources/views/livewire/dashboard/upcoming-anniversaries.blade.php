<div class="space-y-4">
    <!-- Header with month navigation -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ $this->getCurrentMonthName() }} Anniversaries
            </h2>

            <!-- Month Navigation - Better Styling -->
            <div class="flex items-center space-x-2">
                @php
                    $prevMonth = \Carbon\Carbon::create($currentYear, $currentMonth, 1)->subMonth();
                    $nextMonth = \Carbon\Carbon::create($currentYear, $currentMonth, 1)->addMonth();
                @endphp

                <flux:button
                    wire:click="previousMonth"
                    variant="outline"
                    size="sm"
                    class="flex items-center space-x-1 cursor-pointer">
                    <span>{{ $prevMonth->format('M') }}</span>
                </flux:button>

                <div class="text-sm text-gray-600 dark:text-gray-400 px-3 py-2 bg-gray-100 dark:bg-zinc-700 rounded-md">
                    {{ \Carbon\Carbon::create($currentYear, $currentMonth, 1)->format('Y') }}
                </div>

                <flux:button
                    wire:click="nextMonth"
                    variant="outline"
                    size="sm"
                    class="flex items-center space-x-1 cursor-pointer">
                    <span>{{ $nextMonth->format('M') }}</span>
                </flux:button>
            </div>
        </div>

        <!-- Filter Buttons -->
                <!-- Filter Buttons -->
        <div class="flex flex-wrap gap-2">
            <flux:button
                wire:click="setPeriod('1')"
                variant="{{ $selectedPeriod === '1' ? 'primary' : 'outline' }}"
                size="sm"
                class="cursor-pointer">
                1 Month
            </flux:button>
            <flux:button
                wire:click="setPeriod('2')"
                variant="{{ $selectedPeriod === '2' ? 'primary' : 'outline' }}"
                size="sm"
                class="cursor-pointer">
                2 Months
            </flux:button>
            <flux:button
                wire:click="setPeriod('3')"
                variant="{{ $selectedPeriod === '3' ? 'primary' : 'outline' }}"
                size="sm"
                class="cursor-pointer">
                3 Months
            </flux:button>
            <flux:button
                wire:click="setPeriod('6')"
                variant="{{ $selectedPeriod === '6' ? 'primary' : 'outline' }}"
                size="sm"
                class="cursor-pointer">
                6 Months
            </flux:button>
            <flux:button
                wire:click="setPeriod('9')"
                variant="{{ $selectedPeriod === '9' ? 'primary' : 'outline' }}"
                size="sm"
                class="cursor-pointer">
                9 Months
            </flux:button>
            <flux:button
                wire:click="setPeriod('yearly')"
                variant="{{ $selectedPeriod === 'yearly' ? 'primary' : 'outline' }}"
                size="sm"
                class="cursor-pointer">
                Yearly Only
            </flux:button>
            <flux:button
                wire:click="setPeriod('all')"
                variant="{{ $selectedPeriod === 'all' ? 'primary' : 'outline' }}"
                size="sm"
                class="cursor-pointer">
                All Anniversaries
            </flux:button>
        </div>
    </div>    <!-- Legend -->
    <div class="bg-gray-50 dark:bg-zinc-700 border border-gray-200 dark:border-zinc-600 rounded-lg p-4">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">{{ __('Filter Explanation') }}</h3>
        <div class="space-y-2">
            <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-gradient-to-br from-green-500 to-green-700 rounded-full"></div>
                <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('Monthly Milestones: Members reaching exactly X months in ') }}{{ \Carbon\Carbon::create($currentYear, $currentMonth, 1)->format('F') }}</span>
            </div>
            <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-gradient-to-br from-red-500 to-red-700 rounded-full"></div>
                <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('Yearly Anniversaries: Members with whole year milestones in ') }}{{ \Carbon\Carbon::create($currentYear, $currentMonth, 1)->format('F') }}</span>
            </div>
            <div class="flex items-center space-x-2">
                <span class="text-lg">�</span>
                <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('All Anniversaries: Shows both monthly (1-11 months) and yearly milestones') }}</span>
            </div>
        </div>
    </div>

    <!-- Anniversaries Grid -->
    @if($anniversaries->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($anniversaries as $anniversary)
                <div class="bg-white dark:bg-zinc-800 border-l-4 {{ $anniversary['type'] === 'yearly' ? 'border-l-red-500' : 'border-l-green-500' }} border-r border-t border-b border-gray-200 dark:border-r-zinc-700 dark:border-t-zinc-700 dark:border-b-zinc-700 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <!-- Member Info -->
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 {{ $anniversary['type'] === 'yearly' ? 'bg-gradient-to-br from-red-500 to-red-700' : 'bg-gradient-to-br from-green-500 to-green-700' }} rounded-full flex items-center justify-center">
                                <span class="text-white font-semibold text-sm">
                                    {{ substr($anniversary['member']->name, 0, 1) }}{{ substr(explode(' ', $anniversary['member']->name)[1] ?? '', 0, 1) }}
                                </span>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                {{ $anniversary['member']->name }}
                                @if($anniversary['is_special'])
                                    <span class="text-lg ml-1">🎉</span>
                                @endif
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                {{ $anniversary['member']->email }}
                            </p>
                        </div>
                    </div>

                    <!-- Anniversary Details -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('Celebrating') }}</span>
                            <span class="text-sm font-semibold {{ $anniversary['type'] === 'yearly' ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                {{ $anniversary['milestone'] }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('Anniversary Date') }}</span>
                            <span class="text-sm text-gray-900 dark:text-white">
                                {{ \Carbon\Carbon::parse($anniversary['anniversary_date'])->format('M j, Y') }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('Sobriety date') }}</span>
                            <span class="text-sm text-gray-900 dark:text-white">
                                {{ \Carbon\Carbon::parse($anniversary['sobriety_date'])->format('M j, Y') }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Summary -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <div class="flex items-center space-x-2">
                <div class="text-blue-600 dark:text-blue-400">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-blue-800 dark:text-blue-300">
                        {{ round($anniversaries->count()) }}
                        @if($selectedPeriod === 'all')
                            total anniversaries in {{ \Carbon\Carbon::create($currentYear, $currentMonth, 1)->format('F') }}
                        @elseif($selectedPeriod === 'yearly')
                            yearly anniversaries in {{ \Carbon\Carbon::create($currentYear, $currentMonth, 1)->format('F') }}
                        @else
                            members reaching {{ $selectedPeriod }} {{ $selectedPeriod == 1 ? 'month' : 'months' }} in {{ \Carbon\Carbon::create($currentYear, $currentMonth, 1)->format('F') }}
                        @endif
                    </p>
                    <p class="text-xs text-blue-600 dark:text-blue-400">
                        {{ round($anniversaries->where('type', 'yearly')->count()) }} yearly • {{ round($anniversaries->where('type', 'monthly')->count()) }} monthly milestones
                    </p>
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-12">
            <div class="text-gray-500 dark:text-gray-400 mb-2">

            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">No anniversaries this month</h3>
            <p class="text-gray-500 dark:text-gray-400">
                @if($selectedPeriod === 'all')
                    No anniversaries in {{ \Carbon\Carbon::create($currentYear, $currentMonth, 1)->format('F Y') }}.
                @elseif($selectedPeriod === 'yearly')
                    No yearly anniversaries in {{ \Carbon\Carbon::create($currentYear, $currentMonth, 1)->format('F Y') }}.
                @else
                    No members reaching {{ $selectedPeriod }} {{ $selectedPeriod == 1 ? 'month' : 'months' }} in {{ \Carbon\Carbon::create($currentYear, $currentMonth, 1)->format('F Y') }}.
                @endif
            </p>
                </div>
    @endif
</div>
