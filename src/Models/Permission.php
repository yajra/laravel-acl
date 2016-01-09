<?php

namespace Yajra\Acl\Models;

use Illuminate\Support\Collection;
use Yajra\Acl\Traits\HasRole;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasRole;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'permissions';

    /**
     * @var array
     */
    protected $fillable = ['name', 'slug', 'resource', 'system'];

    /**
     * Create a permissions for a resource.
     *
     * @param $resource
     * @return \Illuminate\Support\Collection
     */
    public static function createResource($resource)
    {
        $group        = ucfirst($resource);
        $slug         = strtolower($group);
        $permissions  = [
            [
                'slug'     => $slug . '.lists',
                'resource' => $group,
                'name'     => 'Lists ' . $group,
                'system'   => true,
            ],
            [
                'slug'     => $slug . '.create',
                'resource' => $group,
                'name'     => 'Create ' . $group,
                'system'   => true,
            ],
            [
                'slug'     => $slug . '.view',
                'resource' => $group,
                'name'     => 'View ' . $group,
                'system'   => true,
            ],
            [
                'slug'     => $slug . '.update',
                'resource' => $group,
                'name'     => 'Update ' . $group,
                'system'   => true,
            ],
            [
                'slug'     => $slug . '.delete',
                'resource' => $group,
                'name'     => 'Delete ' . $group,
                'system'   => true,
            ],
        ];

        $collection = new Collection;
        foreach ($permissions as $permission) {
            try {
                $collection->push(static::create($permission));
            } catch (\Exception $e) {
                // permission already exists.
            }
        }

        return $collection;
    }
}
