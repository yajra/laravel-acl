<?php

namespace Yajra\Acl\Models;

use Illuminate\Support\Str;
use Yajra\Acl\Traits\HasRole;
use Yajra\Acl\Traits\RefreshCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

/**
 * @property string resource
 * @property string name
 * @property string slug
 * @property bool system
 */
class Permission extends Model
{
    use HasRole, RefreshCache;

    /** @var string */
    protected $table = 'permissions';

    /** @var array */
    protected $fillable = ['name', 'slug', 'resource', 'system'];

    /** @var array */
    protected $casts = ['system' => 'bool'];

    /**
     * Create a permissions for a resource.
     *
     * @param $resource
     * @param bool $system
     * @return \Illuminate\Support\Collection
     */
    public static function createResource($resource, $system = false)
    {
        $group       = Str::title($resource);
        $slug        = Str::slug($group);
        $permissions = [
            [
                'slug'     => $slug . '.viewAny',
                'resource' => $group,
                'name'     => 'View Any ' . $group,
                'system'   => $system,
            ],
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
