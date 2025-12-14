@extends('40-shared/layouts/app')

@section('title', 'Notification Preferences')

@section('content')
<div class="py-8 sm:py-12 bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Notification Preferences</h1>
            <p class="mt-2 text-base text-gray-600">Manage how and when you receive notifications</p>
        </div>

        @if (session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <div class="space-y-6">
            <!-- Notification Types Preferences -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900">Notification Types</h2>
                    <p class="mt-1 text-sm text-gray-600">Choose how you want to receive notifications for each type</p>
                </div>
                <div class="p-6">
                    <form id="preferences-form" method="POST" action="{{ route('notifications.preferences.bulk-update') }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="space-y-4">
                            @foreach($notificationTypes as $type => $label)
                                @php
                                    $pref = $preferences[$type] ?? null;
                                    $inApp = $pref ? $pref->in_app : true;
                                    $email = $pref ? $pref->email : true;
                                    $push = $pref ? $pref->push : false;
                                @endphp
                                <div class="border border-gray-200 rounded-lg p-4 hover:border-gray-300 transition-colors">
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex-1">
                                            <h3 class="text-sm font-semibold text-gray-900">{{ $label }}</h3>
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-3">
                                        @php $index = $loop->index; @endphp
                                        <!-- In-App -->
                                        <div class="flex items-center justify-between">
                                            <label class="text-sm text-gray-700">In-App</label>
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="hidden" name="preferences[{{ $index }}][type]" value="{{ $type }}">
                                                <input type="hidden" name="preferences[{{ $index }}][in_app]" value="0">
                                                <input type="checkbox"
                                                       name="preferences[{{ $index }}][in_app]"
                                                       value="1"
                                                       class="sr-only peer"
                                                       {{ $inApp ? 'checked' : '' }}>
                                                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-500 peer-checked:bg-blue-600 transition-colors duration-200"></div>
                                                <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200 peer-checked:translate-x-5"></div>
                                            </label>
                                        </div>

                                        <!-- Email -->
                                        <div class="flex items-center justify-between">
                                            <label class="text-sm text-gray-700">Email</label>
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="hidden" name="preferences[{{ $index }}][email]" value="0">
                                                <input type="checkbox"
                                                       name="preferences[{{ $index }}][email]"
                                                       value="1"
                                                       class="sr-only peer"
                                                       {{ $email ? 'checked' : '' }}>
                                                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-500 peer-checked:bg-blue-600 transition-colors duration-200"></div>
                                                <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200 peer-checked:translate-x-5"></div>
                                            </label>
                                        </div>

                                        <!-- Push -->
                                        <div class="flex items-center justify-between">
                                            <label class="text-sm text-gray-700">Push</label>
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="hidden" name="preferences[{{ $index }}][push]" value="0">
                                                <input type="checkbox"
                                                       name="preferences[{{ $index }}][push]"
                                                       value="1"
                                                       class="sr-only peer"
                                                       {{ $push ? 'checked' : '' }}>
                                                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-500 peer-checked:bg-blue-600 transition-colors duration-200"></div>
                                                <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200 peer-checked:translate-x-5"></div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex items-center justify-end pt-4 border-t border-gray-200">
                            <button type="submit"
                                    class="inline-flex items-center px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                Save Preferences
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Quiet Hours -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900">Quiet Hours</h2>
                    <p class="mt-1 text-sm text-gray-600">Set times when you don't want to receive notifications</p>
                </div>
                <div class="p-6">
                    <form id="quiet-hours-form" method="POST" action="{{ route('notifications.quiet-hours.update') }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <label class="text-sm font-medium text-gray-900">Enable Quiet Hours</label>
                                <p class="text-xs text-gray-500 mt-1">Notifications will be saved but not sent during quiet hours</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="enabled" value="0">
                                <input type="checkbox"
                                       name="enabled"
                                       value="1"
                                       class="sr-only peer"
                                       {{ $quietHours['enabled'] ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-500 peer-checked:bg-blue-600 transition-colors duration-200"></div>
                                <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200 peer-checked:translate-x-5"></div>
                            </label>
                        </div>

                        <div id="quiet-hours-fields" class="grid grid-cols-1 sm:grid-cols-3 gap-4 {{ !$quietHours['enabled'] ? 'opacity-50 pointer-events-none' : '' }}">
                            <div>
                                <label for="start" class="block text-sm font-medium text-gray-700 mb-2">Start Time</label>
                                <input type="time"
                                       name="start"
                                       id="start"
                                       value="{{ $quietHours['start'] ? \Carbon\Carbon::parse($quietHours['start'])->format('H:i') : '22:00' }}"
                                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                            </div>

                            <div>
                                <label for="end" class="block text-sm font-medium text-gray-700 mb-2">End Time</label>
                                <input type="time"
                                       name="end"
                                       id="end"
                                       value="{{ $quietHours['end'] ? \Carbon\Carbon::parse($quietHours['end'])->format('H:i') : '08:00' }}"
                                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                            </div>

                            <div>
                                <label for="timezone" class="block text-sm font-medium text-gray-700 mb-2">Timezone</label>
                                <select name="timezone"
                                        id="timezone"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                                    @foreach(timezone_identifiers_list() as $tz)
                                        <option value="{{ $tz }}" {{ $quietHours['timezone'] === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center justify-end pt-4 border-t border-gray-200">
                            <button type="submit"
                                    class="inline-flex items-center px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                Save Quiet Hours
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Email Digest -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900">Email Digest</h2>
                    <p class="mt-1 text-sm text-gray-600">Receive a summary of your notifications via email</p>
                </div>
                <div class="p-6">
                    <form id="email-digest-form" method="POST" action="{{ route('notifications.email-digest.update') }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="frequency" class="block text-sm font-medium text-gray-700 mb-2">Frequency</label>
                            <select name="frequency"
                                    id="frequency"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                                <option value="none" {{ $emailDigest['frequency'] === 'none' ? 'selected' : '' }}>None</option>
                                <option value="daily" {{ $emailDigest['frequency'] === 'daily' ? 'selected' : '' }}>Daily</option>
                                <option value="weekly" {{ $emailDigest['frequency'] === 'weekly' ? 'selected' : '' }}>Weekly</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Choose how often you want to receive email digests</p>
                        </div>

                        <div id="digest-time-fields" class="grid grid-cols-1 sm:grid-cols-2 gap-4 {{ $emailDigest['frequency'] === 'none' ? 'opacity-50 pointer-events-none' : '' }}">
                            <div>
                                <label for="digest-time" class="block text-sm font-medium text-gray-700 mb-2">Preferred Time</label>
                                <input type="time"
                                       name="time"
                                       id="digest-time"
                                       value="{{ $emailDigest['time'] ? \Carbon\Carbon::parse($emailDigest['time'])->format('H:i') : '09:00' }}"
                                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                            </div>

                            <div>
                                <label for="digest-timezone" class="block text-sm font-medium text-gray-700 mb-2">Timezone</label>
                                <select name="timezone"
                                        id="digest-timezone"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                                    @foreach(timezone_identifiers_list() as $tz)
                                        <option value="{{ $tz }}" {{ $emailDigest['timezone'] === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center justify-end pt-4 border-t border-gray-200">
                            <button type="submit"
                                    class="inline-flex items-center px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                Save Email Digest Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle quiet hours fields
    const quietHoursCheckbox = document.querySelector('#quiet-hours-form input[name="enabled"]');
    const quietHoursFields = document.getElementById('quiet-hours-fields');
    
    if (quietHoursCheckbox) {
        quietHoursCheckbox.addEventListener('change', function() {
            if (this.checked) {
                quietHoursFields.classList.remove('opacity-50', 'pointer-events-none');
            } else {
                quietHoursFields.classList.add('opacity-50', 'pointer-events-none');
            }
        });
    }

    // Toggle email digest time fields
    const frequencySelect = document.getElementById('frequency');
    const digestTimeFields = document.getElementById('digest-time-fields');
    
    if (frequencySelect) {
        frequencySelect.addEventListener('change', function() {
            if (this.value === 'none') {
                digestTimeFields.classList.add('opacity-50', 'pointer-events-none');
            } else {
                digestTimeFields.classList.remove('opacity-50', 'pointer-events-none');
            }
        });
    }

    // Handle form submissions with AJAX
    const forms = ['preferences-form', 'quiet-hours-form', 'email-digest-form'];
    
    forms.forEach(formId => {
        const form = document.getElementById(formId);
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const submitButton = this.querySelector('button[type="submit"]');
                const originalText = submitButton.textContent;
                
                submitButton.disabled = true;
                submitButton.textContent = 'Saving...';
                
                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        const successDiv = document.createElement('div');
                        successDiv.className = 'mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg';
                        successDiv.textContent = data.message || 'Settings saved successfully!';
                        document.querySelector('.max-w-4xl').insertBefore(successDiv, document.querySelector('.space-y-6'));
                        
                        // Remove success message after 5 seconds
                        setTimeout(() => {
                            successDiv.remove();
                        }, 5000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while saving. Please try again.');
                })
                .finally(() => {
                    submitButton.disabled = false;
                    submitButton.textContent = originalText;
                });
            });
        }
    });
});
</script>
@endpush
@endsection


