<?php

namespace Yajra\Acl\Models;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Yajra\Acl\Traits\InteractsWithRole;
use Yajra\Acl\Traits\RefreshCache;

/**
 * @property string resource
 * @property string name
 * @property string slug
 * @property bool system
 */
class Permission extends Model
{
    use InteractsWithRole, RefreshCache;

    /** @var string */
    protected $table = 'permissions';

    /** @var array */
    protected $fillable = ['name', 'slug', 'resource', 'system'];

    /** @var array */
    protected $casts = ['system' => 'bool'];

    /**
     * Find a permission by slug.
     *
     * @param  string  $slug
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    public static function findBySlug(string $slug)
    {
        return static::query()->where('slug', $slug)->firstOrFail();
    }

    /**
     * Create a permissions for a resource.
     *
     * @param  string  $resource
     * @param  bool  $system
     * @return \Illuminate\Support\Collection
     */
    public static function createResource(string $resource, $system = false)
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
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(config('acl.user', config('auth.providers.users.model')))
            ->withTimestamps();
    }
}
