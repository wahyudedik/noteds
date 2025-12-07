<?php $__env->startSection('title', __('messages.admin_tutorials')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900"><?php echo e(__('messages.tutorial_management')); ?></h2>
            <div class="flex gap-4 items-center">
                <a href="<?php echo e(route('admin.tutorials.create')); ?>" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    + Tambah Tutorial
                </a>
                <a href="<?php echo e(route('admin.dashboard')); ?>" class="text-blue-600 hover:text-blue-800">← Back to Dashboard</a>
            </div>
        </div>

        <?php if(session('success')): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo e(session('error')); ?>

            </div>
        <?php endif; ?>

        <!-- Filter -->
        <div class="bg-white shadow-sm rounded-lg p-4 mb-6">
            <form method="GET" action="<?php echo e(route('admin.tutorials.index')); ?>" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search title, description..."
                    class="rounded-md border-gray-300 shadow-sm">
                <select name="category" class="rounded-md border-gray-300 shadow-sm">
                    <option value="">All Categories</option>
                    <option value="design" <?php echo e(request('category') === 'design' ? 'selected' : ''); ?>>Design</option>
                    <option value="web" <?php echo e(request('category') === 'web' ? 'selected' : ''); ?>>Web</option>
                    <option value="photo" <?php echo e(request('category') === 'photo' ? 'selected' : ''); ?>>Photo</option>
                    <option value="business" <?php echo e(request('category') === 'business' ? 'selected' : ''); ?>>Business</option>
                </select>
                <select name="status" class="rounded-md border-gray-300 shadow-sm">
                    <option value="">All Status</option>
                    <option value="published" <?php echo e(request('status') === 'published' ? 'selected' : ''); ?>>Published</option>
                    <option value="draft" <?php echo e(request('status') === 'draft' ? 'selected' : ''); ?>>Draft</option>
                </select>
                <select name="featured" class="rounded-md border-gray-300 shadow-sm">
                    <option value="">All</option>
                    <option value="1" <?php echo e(request('featured') === '1' ? 'selected' : ''); ?>>Featured</option>
                    <option value="0" <?php echo e(request('featured') === '0' ? 'selected' : ''); ?>>Not Featured</option>
                </select>
                <div class="flex gap-2">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Filter
                    </button>
                    <?php if(request()->hasAny(['search', 'category', 'status', 'featured'])): ?>
                        <a href="<?php echo e(route('admin.tutorials.index')); ?>" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Clear
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <?php if($tutorials->count() > 0): ?>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Author</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Featured</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Views</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__currentLoopData = $tutorials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tutorial): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        <?php echo e(Str::limit($tutorial->title, 50)); ?>

                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <?php echo e($tutorial->author->name); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            <?php echo e($tutorial->category === 'design' ? 'bg-purple-100 text-purple-800' : ''); ?>

                                            <?php echo e($tutorial->category === 'web' ? 'bg-blue-100 text-blue-800' : ''); ?>

                                            <?php echo e($tutorial->category === 'photo' ? 'bg-green-100 text-green-800' : ''); ?>

                                            <?php echo e($tutorial->category === 'business' ? 'bg-yellow-100 text-yellow-800' : ''); ?>">
                                            <?php echo e($tutorial->category_label); ?>

                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if($tutorial->status === 'published'): ?>
                                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Published</span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Draft</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <?php if($tutorial->featured): ?>
                                            <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">⭐ Featured</span>
                                        <?php else: ?>
                                            <span class="text-gray-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo e(number_format($tutorial->views_count)); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo e($tutorial->created_at->format('d M Y')); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex gap-2">
                                            <a href="<?php echo e(route('tuts.show', $tutorial->slug)); ?>" target="_blank" class="text-blue-600 hover:text-blue-800">View</a>
                                            <a href="<?php echo e(route('admin.tutorials.edit', $tutorial)); ?>" class="text-green-600 hover:text-green-800">Edit</a>
                                            <form method="POST" action="<?php echo e(route('admin.tutorials.destroy', $tutorial)); ?>" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tutorial ini?');">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 px-6 pb-6">
                    <?php echo e($tutorials->links()); ?>

                </div>
            </div>
        <?php else: ?>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                <p class="text-gray-600">Tidak ada tutorial ditemukan.</p>
                <a href="<?php echo e(route('admin.tutorials.create')); ?>" class="mt-4 inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Buat Tutorial Pertama
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\admin\tutorials\index.blade.php ENDPATH**/ ?>