<?php

namespace App\Policies;

class RolePolicy extends BasePolicy
{
    protected string $resourceName = 'Role';

    public function update(\App\Models\User $user, $model): bool
    {
        if ($model->name === 'Super Admin') {
            return false;
        }

        return parent::update($user, $model);
    }

    public function delete(\App\Models\User $user, $model): bool
    {
        if ($model->name === 'Super Admin') {
            return false;
        }

        return parent::delete($user, $model);
    }
}
