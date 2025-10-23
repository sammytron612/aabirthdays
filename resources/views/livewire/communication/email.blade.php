<div class="space-y-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Email Management</h1>
        <p class="text-gray-600 dark:text-gray-300 mt-2">Send emails to members about anniversaries, events, and updates</p>
    </div>

    <!-- Success/Error Messages -->
    @if (session()->has('success'))
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-4">
            <div class="flex">
                <div class="text-green-800 dark:text-green-200">
                    ✅ {{ session('success') }}
                </div>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg p-4">
            <div class="flex">
                <div class="text-red-800 dark:text-red-200">
                    ⚠️ {{ session('error') }}
                </div>
            </div>
        </div>
    @endif

    @if (!$showPreview)
        <!-- Quick Templates Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Quick Templates</h2>
            <p class="text-gray-600 dark:text-gray-300 mb-4">Select a template to pre-fill your email</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <button wire:click="useTemplate('birthday')" class="bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg p-4 text-center transition-colors">
                    <div class="text-2xl mb-2">🎉</div>
                    <div class="text-sm font-medium text-gray-900 dark:text-white">Birthday Notifications</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Click to use</div>
                </button>

                <button wire:click="useTemplate('custom')" class="bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg p-4 text-center transition-colors">
                    <div class="text-2xl mb-2">📧</div>
                    <div class="text-sm font-medium text-gray-900 dark:text-white">Custom Message</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Click to use</div>
                </button>

                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 text-center opacity-50">
                    <div class="text-2xl mb-2">✉️</div>
                    <div class="text-sm font-medium text-gray-900 dark:text-white">Template 3</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Coming Soon</div>
                </div>
            </div>
        </div>
    @else
        <!-- Email Preview -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Email Preview</h2>
                <flux:button wire:click="hidePreview" variant="outline">
                    ← Back to Edit
                </flux:button>
            </div>

            <!-- Preview Content -->
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 mb-6">
                <div class="border-b border-gray-200 dark:border-gray-600 pb-4 mb-4">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                        <strong>To:</strong> {{ $selectedMembersData->pluck('email')->implode(', ') }} (Admin Users)
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                        <strong>BCC:</strong> All Members ({{ $members->count() }} recipients)
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                        <strong>Subject:</strong> {{ $subject }}
                    </div>
                </div>

                <!-- Party Date/Time Editor (only show if there are anniversary celebrations) -->
                @if(strpos($message, 'SPECIAL CELEBRATION') !== false)
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4 mb-4">
                        <div class="flex items-center mb-2">
                            <span class="text-yellow-800 dark:text-yellow-200 font-medium">🎊 Party Details</span>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                When (Date & Time):
                            </label>
                            <input
                                type="text"
                                wire:model.live="partyDateTime"
                                placeholder="e.g., Saturday, November 16th at 6:00 PM"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('partyDateTime') border-red-500 @enderror"
                            >
                            @error('partyDateTime')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                @endif

                <!-- Custom Message Editor (only show for custom messages) -->
                @if(strpos($subject, 'Message from AA Birthdays Team') !== false)
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4 mb-4">
                        <div class="flex items-center mb-3">
                            <span class="text-blue-800 dark:text-blue-200 font-medium">📝 Edit Your Message</span>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Subject:
                                </label>
                                <input
                                    type="text"
                                    wire:model.live="subject"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('subject') border-red-500 @enderror"
                                >
                                @error('subject')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Message:
                                </label>
                                <textarea
                                    wire:model.live="message"
                                    rows="8"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('message') border-red-500 @enderror"
                                    placeholder="Enter your message content here..."
                                ></textarea>
                                @error('message')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                @endif

                <div class="prose dark:prose-invert max-w-none">
                    <div class="whitespace-pre-wrap text-gray-900 dark:text-white">{{ $message }}</div>
                </div>
            </div>
            <!-- Recipients Summary -->
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">
                    Primary Recipients - Admin Users ({{ count($selectedMembersData) }})
                </h3>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                        @foreach($selectedMembersData as $admin)
                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                • {{ $admin->name }} ({{ $admin->email }})
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-4">
                    <h4 class="text-md font-medium text-gray-900 dark:text-white mb-2">
                        BCC Recipients - All Members ({{ $members->count() }})
                    </h4>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        All members will receive this email as BCC recipients (hidden from each other)
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3">
                <flux:button wire:click="hidePreview" variant="outline">
                    📝 Edit Email
                </flux:button>
                <flux:button wire:click="sendEmail" variant="primary">
                    🚀 Send Email
                </flux:button>
            </div>
        </div>
    @endif

    <!-- Anniversaries Modal -->
    @if($showAnniversariesModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-hidden">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Monthly Anniversaries - {{ \Carbon\Carbon::now()->format('F Y') }}
                        </h3>
                        <button wire:click="closeAnniversariesModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Content -->
                <div class="px-6 py-4 overflow-y-auto max-h-[calc(90vh-140px)]">
                    @if($anniversaries && count($anniversaries) > 0)
                        <div class="space-y-4">
                            <!-- Yearly Anniversaries -->
                            @php
                                $yearlyAnniversaries = $anniversaries->where('type', 'yearly');
                                $monthlyAnniversaries = $anniversaries->where('type', 'monthly');
                            @endphp

                            @if(count($yearlyAnniversaries) > 0)
                                <div>
                                    <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-3">🎉 Yearly Anniversaries</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        @foreach($yearlyAnniversaries as $anniversary)
                                            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-4">
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <div class="font-medium text-gray-900 dark:text-white">
                                                            {{ $anniversary['member']->name }}
                                                        </div>
                                                        <div class="text-sm text-gray-600 dark:text-gray-300">
                                                            {{ $anniversary['member']->email }}
                                                        </div>
                                                        <div class="text-sm text-green-700 dark:text-green-300 mt-1">
                                                            Started: {{ $anniversary['sobriety_date']->format('M j, Y') }}
                                                        </div>
                                                    </div>
                                                    <div class="text-right">
                                                        <div class="text-lg font-bold text-green-700 dark:text-green-300">
                                                            {{ $anniversary['milestone'] }}
                                                        </div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                                            {{ $anniversary['anniversary_date']->format('M j') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if(count($monthlyAnniversaries) > 0)
                                <div>
                                    <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-3">📅 Monthly Milestones</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        @foreach($monthlyAnniversaries as $anniversary)
                                            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <div class="font-medium text-gray-900 dark:text-white">
                                                            {{ $anniversary['member']->name }}
                                                        </div>
                                                        <div class="text-sm text-gray-600 dark:text-gray-300">
                                                            {{ $anniversary['member']->email }}
                                                        </div>
                                                        <div class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                                                            Started: {{ $anniversary['sobriety_date']->format('M j, Y') }}
                                                        </div>
                                                    </div>
                                                    <div class="text-right">
                                                        <div class="text-lg font-bold text-blue-700 dark:text-blue-300">
                                                            {{ $anniversary['milestone'] }}
                                                        </div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                                            {{ $anniversary['anniversary_date']->format('M j') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="text-gray-500 dark:text-gray-400 mb-2">
                                <div class="text-6xl mb-4">📅</div>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">No anniversaries this month</h3>
                            <p class="text-gray-500 dark:text-gray-400">
                                No sobriety anniversaries found for {{ \Carbon\Carbon::now()->format('F Y') }}.
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex justify-end space-x-3">
                        <flux:button wire:click="closeAnniversariesModal" variant="outline">
                            Cancel
                        </flux:button>
                        @if($anniversaries && count($anniversaries) > 0)
                            <flux:button wire:click="emailAnniversaries" variant="primary">
                                📧 Create Email
                            </flux:button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Custom Message Modal -->
    @if($showCustomMessageModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-hidden">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Custom Message Template
                        </h3>
                        <button wire:click="closeCustomMessageModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Content -->
                <div class="px-6 py-4">
                    <div class="text-center py-8">
                        <div class="text-6xl mb-4">📧</div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Create Custom Message</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">
                            Send a custom message to all members. You'll be able to edit the subject and content before sending.
                        </p>

                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4 mb-6">
                            <div class="text-sm text-blue-800 dark:text-blue-200">
                                <strong>Recipients:</strong> Admin users will receive the email directly, and all members will be BCC'd.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex justify-end space-x-3">
                        <flux:button wire:click="closeCustomMessageModal" variant="outline">
                            Cancel
                        </flux:button>
                        <flux:button wire:click="createCustomEmail" variant="primary">
                            📝 Create Email
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
