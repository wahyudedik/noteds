<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Growth Challenges') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Challenge Header -->
            <div class="mb-8 bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg shadow-lg p-8 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-3xl font-bold mb-2">🏆 {{ __('Complete Challenges') }}</h3>
                        <p class="text-purple-100">{{ __('Level up and earn exclusive rewards') }}</p>
                    </div>
                    <div class="text-center bg-white/10 rounded-lg p-4 backdrop-blur-sm">
                        <p class="text-purple-100 text-sm mb-1">{{ __('Challenges Completed') }}</p>
                        <p class="text-4xl font-bold" id="completed-count">0</p>
                    </div>
                </div>
            </div>

            <!-- Active Challenges -->
            <div class="mb-8">
                <h4 class="text-2xl font-bold text-gray-900 mb-4">🎯 {{ __('Active Challenges') }}</h4>
                <div id="active-challenges" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="text-center py-12 col-span-full">
                        <i class="fas fa-spinner fa-spin text-4xl text-gray-400 mb-4"></i>
                        <p class="text-gray-600">{{ __('Loading challenges...') }}</p>
                    </div>
                </div>
            </div>

            <!-- Challenge History -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h4 class="text-lg font-bold text-gray-900 mb-4">📜 {{ __('Challenge History') }}</h4>
                <div id="challenge-history">
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-clock text-3xl mb-2"></i>
                        <p>{{ __('No completed challenges yet') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let userChallenges = [];

        async function loadChallenges() {
            try {
                const response = await fetch('/api/growth/challenges');
                const data = await response.json();

                userChallenges = data.challenges || [];

                const completed = userChallenges.filter(c => c.user_progress?.completed).length;
                document.getElementById('completed-count').textContent = completed;

                displayActiveChallenges(userChallenges.filter(c => !c.user_progress?.completed));
                displayChallengeHistory(userChallenges.filter(c => c.user_progress?.completed));
            } catch (error) {
                console.error('Failed to load challenges:', error);
                document.getElementById('active-challenges').innerHTML = `
                    <div class="col-span-full text-center py-8 text-red-500">
                        <i class="fas fa-exclamation-circle text-3xl mb-2"></i>
                        <p>{{ __('Failed to load challenges') }}</p>
                    </div>
                `;
            }
        }

        function displayActiveChallenges(challenges) {
            const container = document.getElementById('active-challenges');

            if (challenges.length === 0) {
                container.innerHTML = `
                    <div class="col-span-full text-center py-12 text-gray-500">
                        <i class="fas fa-trophy text-5xl mb-4"></i>
                        <p class="text-lg font-semibold">{{ __('No active challenges') }}</p>
                        <p class="text-sm">{{ __('Check back later for new challenges') }}</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = challenges.map(challenge => {
                const progress = challenge.user_progress || {
                    current: 0,
                    joined: false
                };
                const progressPercentage = ((progress.current / challenge.target) * 100).toFixed(1);
                const isJoined = progress.joined;

                return `
                    <div class="bg-white rounded-lg shadow-sm border-2 border-gray-200 overflow-hidden hover:shadow-md transition">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <h5 class="text-xl font-bold text-gray-900 mb-1">${challenge.name}</h5>
                                    <p class="text-sm text-gray-600">${challenge.description}</p>
                                </div>
                                <span class="flex-shrink-0 text-3xl">${getChallengeIcon(challenge.type)}</span>
                            </div>
                            
                            <div class="mb-4">
                                <div class="flex items-center justify-between text-sm mb-2">
                                    <span class="text-gray-600">{{ __('Progress') }}</span>
                                    <span class="font-semibold text-gray-900">${progress.current} / ${challenge.target}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3">
                                    <div class="bg-gradient-to-r from-blue-500 to-purple-500 h-3 rounded-full transition-all" 
                                        style="width: ${Math.min(progressPercentage, 100)}%"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">${progressPercentage}% {{ __('complete') }}</p>
                            </div>
                            
                            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">{{ __('Reward') }}</p>
                                    <p class="font-semibold text-green-600">${challenge.reward_description || 'Exclusive Badge'}</p>
                                </div>
                                ${isJoined 
                                    ? '<span class="px-4 py-2 bg-gray-100 text-gray-600 font-medium rounded-lg text-sm">{{ __('Joined') }}</span>'
                                    : `<button onclick="joinChallenge(${challenge.id})" 
                                            class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition text-sm">
                                            {{ __('Join Challenge') }}
                                        </button>`
                                }
                            </div>
                            
                            ${challenge.end_date ? `
                                    <div class="mt-3 text-center">
                                        <p class="text-xs text-gray-500">
                                            {{ __('Ends') }}: ${new Date(challenge.end_date).toLocaleDateString()}
                                        </p>
                                    </div>
                                ` : ''}
                        </div>
                    </div>
                `;
            }).join('');
        }

        function displayChallengeHistory(challenges) {
            const container = document.getElementById('challenge-history');

            if (challenges.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-medal text-4xl mb-3"></i>
                        <p>{{ __('No completed challenges yet') }}</p>
                        <p class="text-sm">{{ __('Complete your first challenge to see it here') }}</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = `
                <div class="space-y-3">
                    ${challenges.map(challenge => `
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                                <div class="flex items-center gap-4">
                                    <div class="text-3xl">${getChallengeIcon(challenge.type)}</div>
                                    <div>
                                        <p class="font-semibold text-gray-900">${challenge.name}</p>
                                        <p class="text-sm text-gray-600">{{ __('Completed') }}: ${new Date(challenge.user_progress.completed_at).toLocaleDateString()}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-green-100 text-green-800 text-sm font-semibold">
                                        <i class="fas fa-check mr-1"></i>
                                        {{ __('Completed') }}
                                    </span>
                                </div>
                            </div>
                        `).join('')}
                </div>
            `;
        }

        function getChallengeIcon(type) {
            const icons = {
                'upload_notes': '📝',
                'make_sales': '💰',
                'get_reviews': '⭐',
                'reach_followers': '👥',
                'streak_days': '🔥',
                'complete_profile': '✅',
                'share_notes': '📤',
                'default': '🎯'
            };
            return icons[type] || icons.default;
        }

        async function joinChallenge(challengeId) {
            try {
                const response = await fetch(`/api/growth/challenges/${challengeId}/join`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    alert(data.message || '{{ __('Successfully joined challenge!') }}');
                    loadChallenges(); // Reload to show updated state
                } else {
                    const error = await response.json();
                    alert(error.message || '{{ __('Failed to join challenge') }}');
                }
            } catch (error) {
                console.error('Failed to join challenge:', error);
                alert('{{ __('An error occurred. Please try again.') }}');
            }
        }

        // Load challenges on page load
        document.addEventListener('DOMContentLoaded', loadChallenges);
    </script>
</x-app-layout>
