<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::where('email', 'dardanllugani@hotmail.com')->first();
$role = App\Models\UserRole::where('role_key', 'admin')->first();

echo json_encode([
    'user_found' => (bool) $user,
    'user_role' => $user?->role,
    'role_found' => (bool) $role,
    'role_key' => $role?->role_key,
    'permission_count' => is_array($role?->permissions) ? count($role->permissions) : null,
    'has_user_roles_permission' => $role?->hasPermission('admin_settings.user_roles'),
    'user_has_user_roles_permission' => $user?->hasRolePermission('admin_settings.user_roles'),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
