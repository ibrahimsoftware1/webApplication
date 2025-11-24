<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AssignDefaultRole
{
    public function handle(Registered $event): void
    {
        // Ensure 'user' role exists, create if it doesn't
        $userRole = Role::firstOrCreate(['name' => 'user']);
        
        // Ensure 'create conversations' permission exists, create if it doesn't
        $createConversationPermission = Permission::firstOrCreate(['name' => 'create conversations']);

        // Only assign if user doesn't already have the role
        if (!$event->user->hasRole('user')) {
            $event->user->assignRole($userRole);
        }
        
        // Only assign if user doesn't already have the permission
        if (!$event->user->hasPermissionTo('create conversations')) {
            $event->user->givePermissionTo($createConversationPermission);
        }
    }
}
