<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

// Get all users with Spatie roles
echo "=== All Users with Spatie Roles ===\n";
$roles = DB::table('roles')->get();
foreach ($roles as $role) {
    echo "\nRole: {$role->name}\n";
    $users = DB::table('model_has_roles')
        ->where('role_id', $role->id)
        ->join('users', 'model_has_roles.model_id', '=', 'users.id')
        ->select('users.name', 'users.email', 'users.role as column_role')
        ->get();

    if ($users->isEmpty()) {
        echo "  - No users\n";
    } else {
        foreach ($users as $user) {
            echo "  - {$user->name} ({$user->email}) [column_role: {$user->column_role}]\n";
        }
    }
}

echo "\n\n=== All Users (Column Role) ===\n";
$allUsers = User::all();
foreach ($allUsers as $user) {
    $spaatiRoles = $user->getRoleNames()->implode(', ') ?: 'none';
    echo "- {$user->name} ({$user->email})\n";
    echo "  Column role: {$user->role}\n";
    echo "  Spatie roles: {$spaatiRoles}\n";
    echo "  Verification: {$user->verification_status}\n";
}
