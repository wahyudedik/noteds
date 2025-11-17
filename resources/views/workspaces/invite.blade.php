@extends('layouts.app')

@section('title', __('messages.invite_team') . ' - ' . $workspace->name)

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Invite Team Members</h1>
                    <p class="text-gray-600 mt-1">Workspace: <strong>{{ $workspace->name }}</strong></p>
                </div>
                <a href="{{ route('workspaces.show', $workspace) }}" class="text-gray-600 hover:text-gray-900">
                    ← Back to Workspace
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                        @if(session('invite_link'))
                            <div class="mt-3 p-3 bg-white rounded border border-green-200">
                                <p class="text-xs text-gray-600 mb-2">Invitation link untuk <strong>{{ session('invited_email') }}</strong>:</p>
                                <div class="flex items-center gap-2">
                                    <input type="text" 
                                           value="{{ session('invite_link') }}" 
                                           readonly 
                                           class="flex-1 text-xs px-3 py-2 border border-gray-300 rounded bg-gray-50"
                                           id="invite-link-input">
                                    <button onclick="copyInviteLink()" 
                                            class="px-3 py-2 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">
                                        Copy
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Invite Form -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Send Invitation</h2>
            
            <form action="{{ route('workspaces.invite.store', $workspace) }}" method="POST">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email Address
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               required
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               placeholder="team@example.com">
                        <p class="mt-1 text-xs text-gray-500">
                            User akan menerima link invitation untuk bergabung sebagai Workspace User.
                        </p>
                    </div>
                    
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                            Role
                        </label>
                        <select id="role" 
                                name="role" 
                                required
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="member">Member</option>
                            <option value="admin">Admin</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            Admin dapat mengundang member lain.
                        </p>
                    </div>
                </div>
                
                <div class="mt-6">
                    <button type="submit" 
                            class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Send Invitation
                    </button>
                </div>
            </form>
        </div>

        <!-- Pending Invitations -->
        @if($pendingInvitations->count() > 0)
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Pending Invitations</h2>
                
                <div class="space-y-3">
                    @foreach($pendingInvitations as $invitation)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $invitation->email }}</p>
                                        <p class="text-sm text-gray-500">
                                            Invited by {{ $invitation->inviter->name }} • 
                                            Role: <span class="font-semibold">{{ ucfirst($invitation->role) }}</span> • 
                                            Expires: {{ $invitation->expires_at->format('d M Y') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('register', ['invite' => $invitation->token]) }}" 
                                   target="_blank"
                                   class="text-xs text-blue-600 hover:text-blue-700 font-medium">
                                    View Link
                                </a>
                                <form action="{{ route('workspaces.invite.cancel', [$workspace, $invitation]) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Yakin ingin membatalkan invitation ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-xs text-red-600 hover:text-red-700 font-medium">
                                        Cancel
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">No Pending Invitations</h3>
                <p class="mt-2 text-sm text-gray-500">Invite team members to collaborate on this workspace.</p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    function copyInviteLink() {
        const input = document.getElementById('invite-link-input');
        input.select();
        input.setSelectionRange(0, 99999); // For mobile devices
        document.execCommand('copy');
        
        // Show feedback
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = 'Copied!';
        button.classList.add('bg-green-600');
        button.classList.remove('bg-blue-600');
        
        setTimeout(() => {
            button.textContent = originalText;
            button.classList.remove('bg-green-600');
            button.classList.add('bg-blue-600');
        }, 2000);
    }
</script>
@endpush
@endsection

