<?php

namespace Yajra\Acl\Models;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Yajra\Acl\Traits\InteractsWithRole;
use Yajra\Acl\Traits\RefreshPermissionsCache;

/**
 * @property string $resource
 * @property string $name
 * @property string $slug
 * @property bool $system
 */
class Permission extends Model
{
    use InteractsWithRole, RefreshPermissionsCache;

    /** @var string */
    protected $table = 'permissions';

    /** @var string[] */
    protected $fillable = ['name', 'slug', 'resource', 'system'];

    /** @var array<string, string> */
    protected $casts = ['system' => 'bool'];

    /**
     * Find a permission by slug.
     *
     * @return \Illuminate\Database\Eloquent\Model|static
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException<\Illuminate\Database\Eloquent\Model>
     */
    public static function findBySlug(string $slug)
    {
        return static::query()->where('slug', $slug)->firstOrFail();
    }

    /**
     * Create a permissions for a resource.
     */
    public static function createResource(string $resource, bool $system = false): Collection
    {
        $group = Str::title($resource);
        $slug = Str::slug($group);
        $permissions = [
            [
                'slug' => 'viewAny-'.$slug,
                'resource' => $group,
                'name' => 'View Any '.$group,
                'system' => $system,
            ],
            [
                'slug' => 'view-'.$slug,
                'resource' => $group,
                'name' => 'View '.$group,
                'system' => $system,
            ],
            [
                'slug' => 'create-'.$slug,
                'resource' => $group,
                'name' => 'Create '.$group,
                'system' => $system,
            ],
            [
                'slug' => 'update-'.$slug,
                'resource' => $group,
                'name' => 'Update '.$group,
                'system' => $system,
            ],
            [
                'slug' => 'delete-'.$slug,
                'resource' => $group,
                'name' => 'Delete '.$group,
                'system' => $system,
            ],
        ];

        $collection = new Collection;
        foreach ($permissions as $permission) {
            try {
                $collection->push(static::create($permission));
            } catch (Exception $e) {
                // permission already exists.
            }
        }

        return $collection;
    }

    /**
     * Permission can belong to many users.
     */
    public function users(): BelongsToMany
    {
        /** @var class-string $model */
        $model = config('acl.user', config('auth.providers.users.model'));

        return $this->belongsToMany($model)->withTimestamps();
    }
}
