<?php

namespace App\Policies;

use App\Models\User;

abstract class BasePolicy
{
    /**
     * The name of the resource (e.g., 'Article', 'Faq').
     * Used to dynamically check 'view {resource}' and 'manage {resource}'.
     *
     * @var string
     */
    protected string $resourceName;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo("view {$this->resourceName}") || 
               $user->hasPermissionTo("manage {$this->resourceName}");
    }

    public function view(User $user, $model): bool
    {
        return $user->hasPermissionTo("view {$this->resourceName}") || 
               $user->hasPermissionTo("manage {$this->resourceName}");
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo("manage {$this->resourceName}");
    }

    public function update(User $user, $model): bool
    {
        return $user->hasPermissionTo("manage {$this->resourceName}");
    }

    public function delete(User $user, $model): bool
    {
        return $user->hasPermissionTo("manage {$this->resourceName}");
    }

    public function restore(User $user, $model): bool
    {
        return $user->hasPermissionTo("manage {$this->resourceName}");
    }

    public function forceDelete(User $user, $model): bool
    {
        return $user->hasPermissionTo("manage {$this->resourceName}");
    }
}
