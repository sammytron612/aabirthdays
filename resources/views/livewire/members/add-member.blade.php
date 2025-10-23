<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
            {{ $editingId ? __('Edit Member') : __('Add Member') }}
        </h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            {{ $editingId ? __('Update the member information below.') : __('Add a new member to the system.') }}
        </p>
    </div>

    <div class="bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-lg p-6 space-y-6">
        <form wire:submit="save" class="space-y-6">
            <div class="space-y-4">
                <div>
                    <flux:input
                        wire:model="name"
                        :label="__('Name')"
                        placeholder="{{ __('Enter the member\'s name') }}"
                        required
                    />
                    @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:input
                        wire:model="email"
                        :label="__('Email Address')"
                        type="email"
                        placeholder="{{ __('Enter the member\'s email address') }}"
                        required
                    />
                    @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:input
                        wire:model="sobriety_date"
                        :label="__('Sobriety Date')"
                        type="date"
                        required
                    />
                    @error('sobriety_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                @if($editingId)
                    <!-- Multiple Sobriety Dates Section -->
                    <div class="border-t border-gray-200 dark:border-zinc-700 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                            {{ __('Sobriety Dates') }}
                        </h3>

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
                            <button
                                type="button"
                                wire:click="addSobrietyDate"
                                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                            >
                                {{ __('Add Date') }}
                            </button>
                        </div>

                        @error('newSobrietyDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                @endif
            </div>

            <div class="flex items-center justify-between">
                <flux:button
                    variant="ghost"
                    :href="route('dashboard')"
                    wire:navigate
                >
                    {{ __('Cancel') }}
                </flux:button>

                <flux:button type="submit" variant="primary">
                    {{ $editingId ? __('Update Member') : __('Add Member') }}
                </flux:button>
            </div>
        </form>
    </div>
</div>
