<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Unsubscribe from Emails</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css']); ?>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-lg shadow-md">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Unsubscribe from Emails
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    We're sorry to see you go. Please let us know why you're unsubscribing.
                </p>
            </div>
            
            <form class="mt-8 space-y-6" method="POST" action="<?php echo e(route('email.unsubscribe.post', $unsubscribe->token)); ?>">
                <?php echo csrf_field(); ?>
                
                <div>
                    <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                        Reason for unsubscribing (optional)
                    </label>
                    <select name="reason" id="reason" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Select a reason...</option>
                        <option value="too_many_emails">Receiving too many emails</option>
                        <option value="not_relevant">Content not relevant</option>
                        <option value="no_longer_interested">No longer interested</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div>
                    <label for="feedback" class="block text-sm font-medium text-gray-700 mb-2">
                        Feedback (optional)
                    </label>
                    <textarea 
                        name="feedback" 
                        id="feedback" 
                        rows="4" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Tell us how we can improve..."
                    ></textarea>
                </div>
                
                <div>
                    <button 
                        type="submit" 
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                    >
                        Unsubscribe
                    </button>
                </div>
                
                <div class="text-center">
                    <a href="<?php echo e(route('marketplace.index')); ?>" class="text-sm text-indigo-600 hover:text-indigo-500">
                        Cancel and return to marketplace
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

<?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\emails\unsubscribe.blade.php ENDPATH**/ ?>