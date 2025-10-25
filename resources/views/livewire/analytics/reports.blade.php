<div class="space-y-8">
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">Reports</h1>
        <p class="text-gray-600 dark:text-gray-300 mt-2">Generate and download sobriety anniversary reports by month</p>
    </div>

    <!-- Print Styles -->
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                font-size: 12px;
            }

            table {
                border-collapse: collapse;
                width: 100%;
            }

            th, td {
                border: 1px solid #000;
                padding: 8px;
                text-align: left;
            }

            th {
                background-color: #f5f5f5;
                font-weight: bold;
            }
        }
    </style>

    <!-- Report Generator -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 sm:p-6">
        <h2 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white mb-4">Anniversary Report Generator</h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4 text-sm sm:text-base">Select a month to see all members with sobriety anniversaries in that month. This includes yearly anniversaries (members who started sobriety in that month) and monthly milestones (members under 1 year celebrating monthly progress).</p>

        <div class="grid grid-cols-1 gap-4 mb-6">
            <!-- Month Selection -->
            <div>
                <flux:field>
                    <flux:label>Month</flux:label>
                    <flux:select wire:model="selectedMonth">
                        <option value="">Select Month</option>
                        <option value="all">All Members</option>
                        @foreach($monthOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-2">
                <flux:button wire:click="generateReport" variant="primary" class="w-full sm:w-auto">
                    Generate Report
                </flux:button>
                @if($showReport && !empty($reportData))
                    <flux:button wire:click="downloadReport" variant="outline" class="w-full sm:w-auto">
                        📥 Download CSV
                    </flux:button>
                @endif
            </div>
        </div>        @if (session()->has('error'))
            <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                <div class="flex items-center">
                    <div class="text-red-800 dark:text-red-200">
                        ⚠️ {{ session('error') }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Report Results -->
    @if($showReport)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4 mb-4">
                <h2 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">
                    @if($selectedMonth === 'all')
                        Anniversary & Monthly Milestone Report - All Members
                    @else
                        Anniversary & Monthly Milestone Report for {{ \Carbon\Carbon::create()->month((int)$selectedMonth)->format('F') }}
                    @endif
                </h2>
                <div class="flex flex-col sm:flex-row gap-2 sm:space-x-2">
                    <flux:button wire:click="downloadReport" variant="outline" size="sm" class="w-full sm:w-auto">
                        📥 Download
                    </flux:button>
                    <flux:button onclick="window.print()" variant="outline" size="sm" class="w-full sm:w-auto">
                        🖨️ Print
                    </flux:button>
                </div>
            </div>

            @if(count($reportData) > 0)
                <!-- Mobile-friendly table wrapper -->
                <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Member Name
                                    </th>
                                    <th class="hidden sm:table-cell px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Email
                                    </th>
                                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Sobriety Date
                                    </th>
                                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Time Sober
                                    </th>
                                    <th class="hidden lg:table-cell px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Years
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($reportData as $anniversaryData)
                                    <tr>
                                        <td class="px-4 sm:px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                            <div class="max-w-[150px] sm:max-w-none">
                                                <div class="truncate">{{ $anniversaryData['member']->name }}</div>
                                                <!-- Show email on mobile below name -->
                                                <div class="sm:hidden text-xs text-gray-500 dark:text-gray-400 truncate">
                                                    {{ $anniversaryData['member']->email }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="hidden sm:table-cell px-4 sm:px-6 py-4 text-sm text-gray-500 dark:text-gray-300">
                                            <div class="max-w-[200px] truncate">
                                                {{ $anniversaryData['member']->email }}
                                            </div>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            {{ \Carbon\Carbon::parse($anniversaryData['sobriety_date']->sobriety_date)->format('M j, Y') }}
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 text-sm text-gray-500 dark:text-gray-300">
                                            <div class="max-w-[120px] sm:max-w-none">
                                                {{ $anniversaryData['sobriety_date']->formattedTimeSober() }}
                                                <!-- Show years on mobile below time sober -->
                                                <div class="lg:hidden text-xs text-gray-400">
                                                    {{ round($anniversaryData['sobriety_date']->daysSober() / 365.25, 2) }} years
                                                </div>
                                            </div>
                                        </td>
                                        <td class="hidden lg:table-cell px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            {{ round($anniversaryData['sobriety_date']->daysSober() / 365.25, 2) }} years
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                    Total records: {{ $reportData->count() }}
                </div>
            @else
                <div class="text-center py-12">
                    <div class="text-gray-500 dark:text-gray-400 mb-2">
                        <div class="text-6xl mb-4">📅</div>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">No anniversaries found</h3>
                    <p class="text-gray-500 dark:text-gray-400">
                        No sobriety anniversaries found for {{ \Carbon\Carbon::create()->month((int)$selectedMonth)->format('F') }}.
                    </p>
                </div>
            @endif
        </div>
    @endif
</div>
