<div class="space-y-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Reports</h1>
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
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Anniversary Report Generator</h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Select a month to see all members with sobriety anniversaries in that month. This includes yearly anniversaries (members who started sobriety in that month) and monthly milestones (members under 1 year celebrating monthly progress).</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
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
            <div class="flex items-end space-x-2">
                <flux:button wire:click="generateReport" variant="primary">
                    Generate Report
                </flux:button>
                @if($showReport && !empty($reportData))
                    <flux:button wire:click="downloadReport" variant="outline">
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
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                    @if($selectedMonth === 'all')
                        Anniversary & Monthly Milestone Report - All Members
                    @else
                        Anniversary & Monthly Milestone Report for {{ \Carbon\Carbon::create()->month((int)$selectedMonth)->format('F') }}
                    @endif
                </h2>
                <div class="flex space-x-2">
                    <flux:button wire:click="downloadReport" variant="outline" size="sm">
                        📥 Download
                    </flux:button>
                    <flux:button onclick="window.print()" variant="outline" size="sm">
                        🖨️ Print
                    </flux:button>
                </div>
            </div>

            @if(count($reportData) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Member Name
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Email
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Sobriety Date
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Time Sober
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Years
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($reportData as $anniversaryData)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $anniversaryData['member']->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        {{ $anniversaryData['member']->email }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        {{ \Carbon\Carbon::parse($anniversaryData['sobriety_date']->sobriety_date)->format('M j, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        {{ $anniversaryData['sobriety_date']->formattedTimeSober() }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        {{ round($anniversaryData['sobriety_date']->daysSober() / 365.25, 2) }} years
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
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
