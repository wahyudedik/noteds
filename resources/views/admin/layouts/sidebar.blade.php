<!-- Admin Sidebar Navigation -->
<div class="bg-gray-900 text-white w-64 min-h-screen fixed left-0 top-0 overflow-y-auto">
    <!-- Logo/Header -->
    <div class="p-6 border-b border-gray-700">
        <h1 class="text-2xl font-bold">Noteds Admin</h1>
        <p class="text-sm text-gray-400 mt-2">Management Panel</p>
    </div>

    <!-- Navigation Menu -->
    <nav class="p-4 space-y-2">
        <!-- Dashboard -->
        <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600' : 'hover:bg-gray-800' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 0l-7-4 7-4 7 4m0 0a1 1 0 001-1V3a1 1 0 00-1-1h-1a1 1 0 00-1 1v2m7 8v10a1 1 0 01-1 1M5 9a1 1 0 001-1V3a1 1 0 00-1-1H4a1 1 0 00-1 1v5m0 0a1 1 0 001 1h1a1 1 0 001-1m-7 0v10a1 1 0 01-1 1m7-1h.01" />
            </svg>
            Dashboard
        </a>

        <!-- User Management -->
        <div class="pt-4 pb-2">
            <h3 class="px-4 text-xs uppercase font-semibold text-gray-400 tracking-wider">Users & Accounts</h3>
            <a href="{{ route('admin.users.index') }}" class="flex items-center px-4 py-2 text-sm rounded-lg {{ request()->routeIs('admin.users.*') ? 'bg-blue-600' : 'hover:bg-gray-800' }}">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 12H9m6 0a6 6 0 11-12 0 6 6 0 0112 0z" />
                </svg>
                Users
            </a>
            <a href="{{ route('admin.users.pending-verification') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-gray-800">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Pending Verification
            </a>
            <a href="{{ route('admin.accounts.moderation.index') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-gray-800">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 4v2M6.343 3.665c.886-.887 2.318-.887 3.203 0l.001.001c.884.884.884 2.319 0 3.203L9.35 9.12c.884.885.884 2.319 0 3.204l-.007.007a2.26 2.26 0 01-3.203 0l-.001-.001c-.885-.885-.885-2.319 0-3.204l.007-.007a2.26 2.26 0 013.203 0zm6.707-3.528c.886-.887 2.318-.887 3.203 0l.001.001c.884.884.884 2.319 0 3.203l-.007.007a2.26 2.26 0 01-3.203 0l-.001-.001c-.885-.885-.885-2.319 0-3.204l.007-.007a2.26 2.26 0 013.203 0zM9.172 15.172a2.26 2.26 0 013.203 0l.001.001c.884.884.884 2.319 0 3.203l-.007.007a2.26 2.26 0 01-3.203 0l-.001-.001c-.885-.885-.885-2.319 0-3.204l.007-.007zm6.364-.364a2.26 2.26 0 013.203 0l.001.001c.884.884.884 2.319 0 3.203l-.007.007a2.26 2.26 0 01-3.203 0l-.001-.001c-.885-.885-.885-2.319 0-3.204l.007-.007z" />
                </svg>
                Account Moderation
            </a>
        </div>

        <!-- Content Management -->
        <div class="pt-4 pb-2">
            <h3 class="px-4 text-xs uppercase font-semibold text-gray-400 tracking-wider">Content</h3>
            <a href="{{ route('admin.notes.index') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-gray-800">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Notes Management
            </a>
            <a href="{{ route('admin.notes.moderation.index') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-gray-800">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 4v2m0-14a9 9 0 110 18 9 9 0 010-18z" />
                </svg>
                Notes Moderation
            </a>
            <a href="{{ route('admin.forum.moderation.index') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-gray-800">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                Forum Moderation
            </a>
            <a href="{{ route('admin.featured-notes.index') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-gray-800">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Featured Notes
            </a>
        </div>

        <!-- Business Management -->
        <div class="pt-4 pb-2">
            <h3 class="px-4 text-xs uppercase font-semibold text-gray-400 tracking-wider">Business</h3>
            <a href="{{ route('admin.transactions.index') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-gray-800">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Transactions
            </a>
            <a href="{{ route('admin.withdraws.index') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-gray-800">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Withdrawals
            </a>
            <a href="{{ route('admin.refunds.index') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-gray-800">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                </svg>
                Refunds
            </a>
            <a href="{{ route('admin.disputes.index') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-gray-800">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Disputes
            </a>
        </div>

        <!-- Monetization & Rewards -->
        <div class="pt-4 pb-2">
            <h3 class="px-4 text-xs uppercase font-semibold text-gray-400 tracking-wider">Monetization</h3>
            <a href="{{ route('admin.affiliate.index') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-gray-800">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Affiliate
            </a>
            <a href="{{ route('admin.referral-transactions.index') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-gray-800">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.658 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
                Referral Commissions
            </a>
            <a href="{{ route('admin.commission-tiers.index') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-gray-800">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Commission Tiers
            </a>
            <a href="{{ route('admin.points.monitoring') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-gray-800">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Points Management
            </a>
        </div>

        <!-- Programs & Features -->
        <div class="pt-4 pb-2">
            <h3 class="px-4 text-xs uppercase font-semibold text-gray-400 tracking-wider">Programs</h3>
            <a href="{{ route('admin.certifications.index') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-gray-800">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                </svg>
                Certifications
            </a>
            <a href="{{ route('admin.badges.index') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-gray-800">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                </svg>
                Badges
            </a>
        </div>

        <!-- Studio & Orders -->
        <div class="pt-4 pb-2">
            <h3 class="px-4 text-xs uppercase font-semibold text-gray-400 tracking-wider">Studio</h3>
            <a href="{{ route('admin.order-verification.index') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-gray-800">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Order Verification
            </a>
            <a href="{{ route('admin.vendors.index') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-gray-800">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Vendors
            </a>
        </div>

        <!-- Content & Settings -->
        <div class="pt-4 pb-2">
            <h3 class="px-4 text-xs uppercase font-semibold text-gray-400 tracking-wider">Content & Settings</h3>
            <a href="{{ route('admin.faqs.index') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-gray-800">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                FAQ
            </a>
            <a href="{{ route('admin.cms-pages.index') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-gray-800">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                CMS Pages
            </a>
            <a href="{{ route('admin.settings.index') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-gray-800">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Settings
            </a>
        </div>

        <!-- Reports & Analytics -->
        <div class="pt-4 pb-2">
            <h3 class="px-4 text-xs uppercase font-semibold text-gray-400 tracking-wider">Reports</h3>
            <a href="{{ route('admin.repurchase-report') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-gray-800">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Revenue Report
            </a>
            <a href="{{ route('admin.system-health.index') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-gray-800">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                System Health
            </a>
        </div>

        <!-- User Profile -->
        <div class="pt-4 border-t border-gray-700">
            <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-3 rounded-lg hover:bg-gray-800">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                My Profile
            </a>
            <form method="POST" action="{{ route('logout') }}" class="px-4 py-3">
                @csrf
                <button type="submit" class="w-full text-left flex items-center text-red-400 hover:text-red-300">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    </nav>
</div>

<!-- Main Content Area (margin adjustment) -->
<div class="ml-64">
