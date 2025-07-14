<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Spatie\Permission\Models\Role;

class AssignRoleOnFirstLogin
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        if ($user->roles()->count() === 0) {
            $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
            $student = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

            $user->assignRole($user->id === 1 ? $admin : $student);
        }

        $user->load('roles', 'permissions');
    }
}
