<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Data Management')" :subheading="__('Manage your application data and remove test data')">
        <!-- Success/Error Messages -->
        @if (session()->has('message'))
            <div class="mb-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                <div class="text-sm text-green-800 dark:text-green-200">
                    {{ session('message') }}
                </div>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <div class="text-sm text-red-800 dark:text-red-200">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        <!-- Data Overview -->
        <div class="mb-6 p-4 bg-gray-50 dark:bg-zinc-700 rounded-lg">
            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">{{ __('Current Data Overview') }}</h3>
            <div class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                <div>{{ __('Members:') }} <span class="font-medium">{{ $memberCount }}</span></div>
                <div>{{ __('Sobriety Dates:') }} <span class="font-medium">{{ $sobrietyDateCount }}</span></div>
            </div>
        </div>

        <!-- Remove Data Section -->
        <div class="border border-red-200 dark:border-red-800 rounded-lg p-4 bg-red-50 dark:bg-red-900/20">
            <h3 class="text-sm font-medium text-red-800 dark:text-red-200 mb-2">{{ __('Remove All Data') }}</h3>
            <p class="text-sm text-red-700 dark:text-red-300 mb-4">
                {{ __('This will permanently delete all members and their sobriety dates from the database. This action cannot be undone.') }}
            </p>

            <flux:button
                wire:click="openConfirmModal"
                variant="danger"
                size="sm"
                :disabled="$memberCount === 0"
            >
                {{ __('Remove All Data') }}
            </flux:button>
        </div>
    </x-settings.layout>

    <!-- Confirmation Modal -->
    @if($showConfirmModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-hidden">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-red-800 dark:text-red-200">
                            {{ __('Confirm Data Removal') }}
                        </h3>
                        <button wire:click="closeConfirmModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Content -->
                <div class="p-6">
                    <div class="mb-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                                    {{ __('This action cannot be undone') }}
                                </h3>
                                <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                    <p>{{ __('You are about to permanently delete:') }}</p>
                                    <ul class="mt-1 list-disc list-inside">
                                        <li>{{ $memberCount }} {{ __('members') }}</li>
                                        <li>{{ $sobrietyDateCount }} {{ __('sobriety date records') }}</li>
                                    </ul>
                                    <p class="mt-2 font-medium">{{ __('Are you absolutely sure you want to proceed?') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-3">
                        <flux:button
                            wire:click="closeConfirmModal"
                            variant="ghost"
                            type="button"
                        >
                            {{ __('Cancel') }}
                        </flux:button>
                        <flux:button
                            wire:click="removeDummyData"
                            variant="danger"
                            type="button"
                        >
                            {{ __('Yes, Remove All Data') }}
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</section>
