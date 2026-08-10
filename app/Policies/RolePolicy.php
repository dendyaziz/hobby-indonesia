<?php

namespace App\Policies;

use App\Models\User;

class RolePolicy extends BasePolicy
{
    protected string $resourceName = 'Role';

    public function update(User $user, $model): bool
    {
        if ($model->name === 'Super Admin') {
            return false;
        }

        return parent::update($user, $model);
    }

    public function delete(User $user, $model): bool
    {
        if ($model->name === 'Super Admin') {
            return false;
        }

        return parent::delete($user, $model);
    }
}
