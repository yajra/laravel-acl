<?php

namespace Yajra\Acl\Traits;

trait AuthorizesResources
{
    /**
     * Permission resource ability mapping.
     *
     * @var array
     */
    protected $resourceAbilityMap = [
        'index'   => 'lists',
        'create'  => 'create',
        'store'   => 'create',
        'show'    => 'view',
        'edit'    => 'update',
        'update'  => 'update',
        'destroy' => 'delete',
    ];

    /**
     * Controller specific ability map.
     *
     * @var array
     */
    protected $customAbilityMap = [];

    /**
     * Authorize a resource action based on the incoming request.
     *
     * @param  string $model
     * @param  string|null $parameter
     * @param  array $options
     * @return void
     */
    public function authorizeResource($model, $parameter = null, array $options = [])
    {
        $parameter = $parameter ?: strtolower(class_basename($model));

        foreach ($this->resourceAbilityMap() as $method => $ability) {
            $modelName = in_array($method, ['index', 'create', 'store']) ? $model : $parameter;

            $this->middleware("can:{$modelName}.{$ability}", $options)->only($method);
        }
    }

    /**
     * Get the map of resource methods to ability names.
     *
     * @return array
     */
    protected function resourceAbilityMap()
    {
        return array_merge($this->resourceAbilityMap, $this->customAbilityMap);
    }
}