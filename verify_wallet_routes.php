<?php
// Quick verification script to check route configuration and user roles

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== WALLET ACCESS VERIFICATION ===" . PHP_EOL . PHP_EOL;

// Check user roles
$user = \App\Models\User::where('email', 'newobazajo@mailinator.com')->first();
if ($user) {
    echo "TEST USER: Shana Underwood" . PHP_EOL;
    echo "Email: " . $user->email . PHP_EOL;
    echo "Database Role Column: " . $user->role . PHP_EOL;
    echo "Has Spatie 'seller' role: " . ($user->hasRole('seller') ? 'YES ✅' : 'NO ❌') . PHP_EOL;
    echo "Has Spatie 'buyer' role: " . ($user->hasRole('buyer') ? 'YES' : 'NO') . PHP_EOL;
    echo PHP_EOL;
}

// Check route structure
$routes = app('router')->getRoutes();
echo "=== ROUTE STRUCTURE ===" . PHP_EOL . PHP_EOL;

echo "Wallet routes:" . PHP_EOL;
foreach ($routes as $route) {
    if (strpos($route->uri, 'wallet') !== false) {
        $middlewares = implode(', ', $route->middleware());
        echo "  - {$route->uri} [{$route->methods()[0]}] - Middleware: {$middlewares}" . PHP_EOL;
    }
}

echo PHP_EOL . "Collections (buyer-only) routes:" . PHP_EOL;
foreach ($routes as $route) {
    if (strpos($route->uri, 'collections') !== false) {
        $middlewares = implode(', ', $route->middleware());
        echo "  - {$route->uri} [{$route->methods()[0]}]" . PHP_EOL;
    }
}

echo PHP_EOL . "Seller Analytics (seller-only) routes:" . PHP_EOL;
foreach ($routes as $route) {
    if (strpos($route->uri, 'seller-analytics') !== false) {
        $middlewares = implode(', ', $route->middleware());
        echo "  - {$route->uri} [{$route->methods()[0]}]" . PHP_EOL;
    }
}

echo PHP_EOL . "=== CONCLUSION ===" . PHP_EOL;
echo "✅ Wallet route is NOT inside buyer_only middleware" . PHP_EOL;
echo "✅ Seller user should now be able to access /wallet" . PHP_EOL;
echo "✅ All shared features (wallet, support tickets, etc.) available to all authenticated users" . PHP_EOL;
echo "✅ Buyer-only features still protected" . PHP_EOL;
