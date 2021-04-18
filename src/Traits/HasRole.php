<?php

namespace Yajra\Acl\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Yajra\Acl\Models\Role;

trait HasRole
{
    use InteractsWithRole;

    /**
     * Check if user have access using any of the acl (permissions or roles slug).
     *
     * @param  string|array  $acl
     * @return boolean
     */
    public function canAccess($acl): bool
    {
        return $this->canAtLeast($acl) || $this->hasRole($acl);
    }

    /**
     * Check if user has at least one of the given permissions
     *
     * @param  string|array  $permissions
     * @return bool
     */
    public function canAtLeast($permissions): bool
    {
        $can = false;

        if (auth()->check()) {
            /** @var Role $role */
            foreach ($this->roles as $role) {
                if ($role->canAtLeast($permissions)) {
                    $can = true;
                }
            }
        } else {
            try {
                $guest = $this->findRoleBySlug('guest');

                return $guest->canAtLeast($permissions);
            } catch (ModelNotFoundException $exception) {
                //
            }
        }

        return $can;
    }


    /**
     * Get all user role permissions.
     *
     * @return array
     */
    public function getPermissions(): array
    {
        $permissions = [[], []];

        foreach ($this->roles as $role) {
            $permissions[] = $role->getPermissions();
        }

        return call_user_func_array('array_merge', $permissions);
    }

    /**
     * Check if the given entity/model is owned by the user.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $entity
     * @param  string  $relation
     * @return bool
     */
    public function owns(Model $entity, $relation = 'user_id'): bool
    {
        return $this->getKeyName() === $entity->{$relation};
    }
}
