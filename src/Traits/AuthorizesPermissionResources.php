<?php

namespace Yajra\Acl\Traits;

trait AuthorizesPermissionResources
{
    /**
     * Permission resource ability mapping.
     *
     * @var array
     */
    protected $resourcePermissionMap = [
        'index'   => 'lists',
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
     * @param  string $model
     * @param  string|null $parameter
     * @param  array $options
     * @return void
     */
    public function authorizePermissionResource($model, $parameter = null, array $options = [])
    {
        $parameter = $parameter ?: strtolower(class_basename($model));

        foreach ($this->resourcePermissionMap() as $method => $ability) {
            $modelName = in_array($method, ['index', 'create', 'store']) ? $model : $parameter;

            $this->middleware("can:{$modelName}.{$ability}", $options)->only($method);
        }
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