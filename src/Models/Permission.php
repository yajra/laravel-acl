<?php

namespace Yajra\Acl\Models;

use Illuminate\Support\Collection;
use Yajra\Acl\Traits\HasRole;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string resource
 * @property string name
 * @property string slug
 * @property bool system
 */
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
     * @param bool $system
     * @return \Illuminate\Support\Collection
     */
    public static function createResource($resource, $system = false)
    {
        $group        = ucfirst($resource);
        $slug         = strtolower($group);
        $permissions  = [
            [
                'slug'     => $slug . '.view',
                'resource' => $group,
                'name'     => 'View ' . $group,
                'system'   => $system,
            ],
            [
                'slug'     => $slug . '.create',
                'resource' => $group,
                'name'     => 'Create ' . $group,
                'system'   => $system,
            ],
            [
                'slug'     => $slug . '.update',
                'resource' => $group,
                'name'     => 'Update ' . $group,
                'system'   => $system,
            ],
            [
                'slug'     => $slug . '.delete',
                'resource' => $group,
                'name'     => 'Delete ' . $group,
                'system'   => $system,
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
