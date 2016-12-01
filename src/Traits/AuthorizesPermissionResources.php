<?php

namespace Yajra\Acl\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;

trait AuthorizesPermissionResources
{
    /**
     * Permission resource ability mapping.
     *
     * @var array
     */
    protected $resourcePermissionMap = [
        'index'   => 'view',
        'create'  => 'create',
        'store'   => 'create',
        'show'    => 'view',
        'edit'    => 'update',
        'update'  => 'update',
        'destroy' => 'delete',
    ];

    /**
     * Controller specific permission ability map.
     *
     * @var array
     */
    protected $customPermissionMap = [];

    /**
     * Authorize a permission resource action based on the incoming request.
     *
     * @param  string $resource
     * @param  array $options
     * @return void
     */
    public function authorizePermissionResource($resource, array $options = [])
    {
        $permissions = $this->resourcePermissionMap();
        $collection  = new Collection;
        foreach ($permissions as $method => $ability) {
            $collection->push(new Fluent([
                'ability' => $ability,
                'method'  => $method,
            ]));
        }

        $collection->groupBy('ability')->each(function ($permission, $ability) use ($resource, $options) {
            $this->middleware("can:{$resource}.{$ability}")
                 ->only($permission->pluck('method')->toArray());
        });
    }

    /**
     * Get the map of permission resource methods to ability names.
     *
     * @return array
     */
    protected function resourcePermissionMap()
    {
        return array_merge($this->resourcePermissionMap, $this->customPermissionMap);
    }
}
