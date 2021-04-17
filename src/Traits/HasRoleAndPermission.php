<?php

namespace Yajra\Acl\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Yajra\Acl\Models\Permission;

trait HasRoleAndPermission
{
    use HasRole {
        HasRole::getPermissions as getRolePermissions;
    }

    use InteractsWithPermission;

    /**
     * Get all user permissions slug.
     *
     * @return array|null
     */
    public function getPermissions(): array
    {
        $rolePermissions = $this->getRolePermissions();
        $userPermissions = $this->permissions->pluck('slug')->toArray();

        return collect($userPermissions)->merge($rolePermissions)->unique()->toArray();
    }
}
