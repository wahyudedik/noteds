<?php $__env->startSection('title', __('messages.forum_analytics')); ?>

<?php $__env->startPush('styles'); ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.min.css">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12 bg-gray-50 min-h-screen">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Forum Analytics</h1>
            <p class="mt-2 text-sm text-gray-600">Pantau performa post kamu dan ketahui apa yang paling menarik perhatian komunitas.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
            <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm">
                <p class="text-sm text-gray-500">Total Posts</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900"><?php echo e(number_format($summary['total_posts'])); ?></p>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm">
                <p class="text-sm text-gray-500">Total Views</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900"><?php echo e(number_format($summary['total_views'])); ?></p>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm">
                <p class="text-sm text-gray-500">Total Likes</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900"><?php echo e(number_format($summary['total_likes'])); ?></p>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm">
                <p class="text-sm text-gray-500">Total Comments</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900"><?php echo e(number_format($summary['total_comments'])); ?></p>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm">
                <p class="text-sm text-gray-500">Total Shares</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900"><?php echo e(number_format($summary['total_shares'])); ?></p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Views 30 Hari Terakhir</h2>
                    <span class="text-xs text-gray-500">Data harian</span>
                </div>
                <canvas id="viewsChart" height="220"></canvas>
                <?php if(array_sum($chartData) === 0): ?>
                    <p class="text-sm text-gray-500 mt-4 text-center">Belum ada views dalam 30 hari terakhir.</p>
                <?php endif; ?>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Top Posts (Views)</h2>
                    <span class="text-xs text-gray-500">Maks. 5 post</span>
                </div>
                <?php if($topPosts->isEmpty()): ?>
                    <p class="text-sm text-gray-500">Belum ada post yang bisa dianalisis.</p>
                <?php else: ?>
                    <canvas id="engagementChart" height="220"></canvas>
                <?php endif; ?>
            </div>
        </div>

        <?php if($topPosts->isNotEmpty()): ?>
            <div class="mt-8 bg-white border border-gray-200 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Detail Top Posts</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Post</th>
                                <th class="px-6 py-3 text-right font-medium text-gray-500 uppercase tracking-wider">Views</th>
                                <th class="px-6 py-3 text-right font-medium text-gray-500 uppercase tracking-wider">Likes</th>
                                <th class="px-6 py-3 text-right font-medium text-gray-500 uppercase tracking-wider">Comments</th>
                                <th class="px-6 py-3 text-right font-medium text-gray-500 uppercase tracking-wider">Shares</th>
                                <th class="px-6 py-3 text-right font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__currentLoopData = $topPosts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900"><?php echo e($post->title ?? 'Untitled Post'); ?></div>
                                        <div class="text-xs text-gray-500"><?php echo e($post->created_at?->diffForHumans()); ?></div>
                                    </td>
                                    <td class="px-6 py-4 text-right text-gray-900 font-semibold"><?php echo e(number_format($post->views_count)); ?></td>
                                    <td class="px-6 py-4 text-right text-gray-900"><?php echo e(number_format($post->likes_count)); ?></td>
                                    <td class="px-6 py-4 text-right text-gray-900"><?php echo e(number_format($post->comments_count)); ?></td>
                                    <td class="px-6 py-4 text-right text-gray-900"><?php echo e(number_format($post->shares_count)); ?></td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="<?php echo e(route('forum.show', $post)); ?>" class="text-sm text-blue-600 hover:text-blue-700">View Post</a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>
    <script>
        const chartLabels = <?php echo json_encode($chartLabels, 15, 512) ?>;
        const chartData = <?php echo json_encode($chartData, 15, 512) ?>;

        const viewsCtx = document.getElementById('viewsChart')?.getContext('2d');
        if (viewsCtx) {
            new Chart(viewsCtx, {
                type: 'line',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Views',
                        data: chartData,
                        tension: 0.4,
                        borderWidth: 3,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.15)',
                        fill: true,
                        pointRadius: 3,
                        pointBackgroundColor: '#2563eb',
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (context) => `${context.parsed.y.toLocaleString()} views`
                            }
                        }
                    }
                }
            });
        }

        const engagementData = <?php echo json_encode($engagementChart, 15, 512) ?>;
        const engagementCtx = document.getElementById('engagementChart')?.getContext('2d');
        if (engagementCtx) {
            new Chart(engagementCtx, {
                type: 'bar',
                data: {
                    labels: engagementData.labels,
                    datasets: [
                        {
                            label: 'Views',
                            data: engagementData.views,
                            backgroundColor: 'rgba(37, 99, 235, 0.75)',
                            stack: 'engagement'
                        },
                        {
                            label: 'Likes',
                            data: engagementData.likes,
                            backgroundColor: 'rgba(16, 185, 129, 0.75)',
                            stack: 'engagement'
                        },
                        {
                            label: 'Comments',
                            data: engagementData.comments,
                            backgroundColor: 'rgba(249, 115, 22, 0.75)',
                            stack: 'engagement'
                        },
                        {
                            label: 'Shares',
                            data: engagementData.shares,
                            backgroundColor: 'rgba(139, 92, 246, 0.75)',
                            stack: 'engagement'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: { stacked: true },
                        y: { stacked: true, beginAtZero: true, ticks: { precision: 0 } }
                    }
                }
            });
        }
    </script>
<?php $__env->stopPush(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\forum\analytics.blade.php ENDPATH**/ ?>