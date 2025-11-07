@extends('layouts.app')

@section('title', __('messages.profile_settings'))

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.profile_settings') }}</h1>
            <p class="mt-2 text-base text-gray-600">{{ __('messages.manage_account_settings') }}</p>
        </div>

        <div class="space-y-6">
            <!-- Profile Information -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.profile_information') }}</h2>
                </div>
                <div class="p-6">
                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @method('patch')

                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.name') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 @error('name') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                            @error('name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.username') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500">@</span>
                                </div>
                                <input type="text" name="username" id="username" value="{{ old('username', $user->username) }}" required
                                    class="mt-1 block w-full pl-8 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 @error('username') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                                    placeholder="your-username">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">{{ __('messages.only_letters_numbers_underscores') }}</p>
                            @error('username')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.email') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 @error('email') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                            @error('email')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Avatar Upload Section -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.avatar') }}
                            </label>
                            
                            <!-- Current Avatar Preview -->
                            <div class="mb-4">
                                <div class="flex items-center gap-4">
                                    <div class="relative">
                                        <div class="w-24 h-24 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center shadow-lg overflow-hidden">
                                            @if($user->avatar)
                                                @if(str_starts_with($user->avatar, 'http'))
                                                    <img src="{{ $user->avatar }}" alt="{{ $user->name }}" id="avatar-preview" class="w-24 h-24 rounded-full object-cover">
                                                @else
                                                    <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}" id="avatar-preview" class="w-24 h-24 rounded-full object-cover">
                                                @endif
                                            @else
                                                <span class="text-4xl font-bold text-white" id="avatar-initial">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-600 mb-2">Current avatar preview</p>
                                        <label for="avatar_file" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 cursor-pointer transition-colors duration-200">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            Upload Photo
                                        </label>
                                        <input type="file" name="avatar_file" id="avatar_file" accept="image/*" class="hidden" onchange="previewAvatar(this)">
                                        <button type="button" onclick="clearAvatar()" class="ml-2 inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Remove
                                        </button>
                                    </div>
                                </div>
                                <p class="mt-2 text-xs text-gray-500">Upload a photo (JPG, PNG, GIF - Max 2MB) or use URL below</p>
                            </div>

                            <!-- Avatar URL Input (Alternative) -->
                            <div>
                                <label for="avatar" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('messages.avatar_url') }} <span class="text-xs text-gray-500">(Optional - if you prefer to use a URL)</span>
                                </label>
                                <input type="url" name="avatar" id="avatar" value="{{ old('avatar', $user->avatar && str_starts_with($user->avatar, 'http') ? $user->avatar : '') }}"
                                    placeholder="https://example.com/avatar.jpg"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 @error('avatar') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                                <p class="mt-1 text-xs text-gray-500">Or enter an avatar image URL</p>
                                @error('avatar')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                @error('avatar_file')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="bio" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.bio') }}
                            </label>
                            <textarea name="bio" id="bio" rows="4" maxlength="500"
                                :placeholder="__('messages.tell_about_yourself')"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 resize-y @error('bio') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">{{ old('bio', $user->bio) }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">{{ __('messages.max_characters') }}</p>
                            @error('bio')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="location" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.location') }}
                            </label>
                            <input type="text" name="location" id="location" value="{{ old('location', $user->location) }}"
                                :placeholder="__('messages.city_country')"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 @error('location') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                            @error('location')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Bank Account Information Section -->
                        <div class="pt-6 border-t border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.bank_account') }}</h3>
                            <p class="text-sm text-gray-600 mb-4">This information will be used for withdrawal requests.</p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('messages.bank_name') }}
                                    </label>
                                    <input type="text" name="bank_name" id="bank_name" value="{{ old('bank_name', $user->bank_name) }}"
                                        placeholder="e.g., BCA, Mandiri, BRI"
                                        maxlength="100"
                                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 @error('bank_name') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                                    @error('bank_name')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="bank_account_number" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('messages.account_number') }}
                                    </label>
                                    <input type="text" name="bank_account_number" id="bank_account_number" value="{{ old('bank_account_number', $user->bank_account_number) }}"
                                        placeholder="{{ __('messages.account_number') }}"
                                        maxlength="50"
                                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 @error('bank_account_number') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                                    @error('bank_account_number')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-6">
                                <label for="bank_account_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('messages.account_name') }}
                                </label>
                                <input type="text" name="bank_account_name" id="bank_account_name" value="{{ old('bank_account_name', $user->bank_account_name) }}"
                                    placeholder="{{ __('messages.name_as_appears_bank') }}"
                                    maxlength="100"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 @error('bank_account_name') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                                @error('bank_account_name')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
                            @if(session('status') === 'profile-updated')
                                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                                    class="text-sm text-green-600 flex items-center">
                                    <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    {{ __('messages.saved_successfully') }}
                                </p>
                            @endif
                            <button type="submit" class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm hover:shadow-md transition-all duration-200">
                                {{ __('messages.save_changes') }}
                            </button>
                        </div>
                    </form>
                    
                    @push('scripts')
                    <script>
                        function previewAvatar(input) {
                            if (input.files && input.files[0]) {
                                const reader = new FileReader();
                                const file = input.files[0];
                                
                                // Validate file size (2MB max)
                                if (file.size > 2 * 1024 * 1024) {
                                    if (typeof Swal !== 'undefined') {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'File Too Large',
                                            text: 'Please select an image smaller than 2MB.',
                                        });
                                    } else {
                                        alert('File too large. Please select an image smaller than 2MB.');
                                    }
                                    input.value = '';
                                    return;
                                }
                                
                                // Validate file type
                                if (!file.type.match('image.*')) {
                                    if (typeof Swal !== 'undefined') {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Invalid File Type',
                                            text: 'Please select an image file (JPG, PNG, GIF).',
                                        });
                                    } else {
                                        alert('Please select an image file.');
                                    }
                                    input.value = '';
                                    return;
                                }
                                
                                reader.onload = function(e) {
                                    const preview = document.getElementById('avatar-preview');
                                    const initial = document.getElementById('avatar-initial');
                                    
                                    if (preview) {
                                        preview.src = e.target.result;
                                        preview.style.display = 'block';
                                    } else {
                                        // Create preview if it doesn't exist
                                        const avatarContainer = document.querySelector('.relative .w-24');
                                        if (avatarContainer) {
                                            avatarContainer.innerHTML = '<img src="' + e.target.result + '" alt="Avatar Preview" id="avatar-preview" class="w-24 h-24 rounded-full object-cover">';
                                        }
                                    }
                                    
                                    if (initial) {
                                        initial.style.display = 'none';
                                    }
                                };
                                
                                reader.readAsDataURL(file);
                            }
                        }
                        
                        function clearAvatar() {
                            const input = document.getElementById('avatar_file');
                            const preview = document.getElementById('avatar-preview');
                            const initial = document.getElementById('avatar-initial');
                            const urlInput = document.getElementById('avatar');
                            
                            if (input) input.value = '';
                            if (urlInput) urlInput.value = '';
                            
                            if (preview) {
                                preview.style.display = 'none';
                            }
                            
                            if (initial) {
                                initial.style.display = 'block';
                            }
                        }
                    </script>
                    @endpush
                </div>
            </div>

            <!-- Update Password -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.update_password') }}</h2>
                </div>
                <div class="p-6">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <!-- Delete Account -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-red-200">
                <div class="px-6 py-4 border-b border-red-200 bg-red-50">
                    <h2 class="text-lg font-semibold text-red-900">{{ __('messages.delete_account') }}</h2>
                </div>
                <div class="p-6">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
