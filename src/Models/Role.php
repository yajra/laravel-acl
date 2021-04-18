<?php

namespace Yajra\Acl\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Yajra\Acl\Traits\HasPermission;
use Yajra\Acl\Traits\RefreshCache;

/**
 * @property string name
 * @property string slug
 * @property string description
 * @property bool system
 */
class Role extends Model
{
    use HasPermission, RefreshCache;

    /** @var string */
    protected $table = 'roles';

    /** @var array */
    protected $fillable = ['name', 'slug', 'description', 'system'];

    /** @var array */
    protected $casts = [
        'system' => 'bool',
    ];

    /**
     * Find a role by slug.
     *
     * @param  string  $slug
     * @return \Illuminate\Database\Eloquent\Model|static
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public static function findBySlug(string $slug)
    {
        return static::query()->where('slug', $slug)->firstOrFail();
    }

    /**
     * Roles can belong to many users.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(config('acl.user', config('auth.providers.users.model')))
            ->withTimestamps();
    }
}
