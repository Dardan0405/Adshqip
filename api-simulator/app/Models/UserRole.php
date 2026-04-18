<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    protected $table = 'aq_user_roles';

    protected $fillable = [
        'role_key',
        'role_name',
        'status',
        'permissions',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
        ];
    }

    public function hasPermission(string $permission): bool
    {
        $permissions = $this->permissions;

        if ($permissions === null) {
            return true;
        }

        if (! is_array($permissions)) {
            return false;
        }

        return in_array($permission, $permissions, true);
    }
}
