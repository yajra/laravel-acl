<?php

namespace Yajra\Acl\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Yajra\Acl\Traits\HasPermission;
use Yajra\Acl\Traits\RefreshPermissionsCache;

/**
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property bool $system
 */
class Role extends Model
{
    use HasPermission, RefreshPermissionsCache;

    protected $table = 'roles';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'system',
    ];

    protected $casts = [
        'system' => 'bool',
    ];

    /**
     * Find a role by slug.
     */
    public static function findBySlug(string $slug): Role
    {
        return static::query()->where('slug', $slug)->firstOrFail();
    }

    /**
     * Roles can belong to many users.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\Illuminate\Foundation\Auth\User>
     */
    public function users(): BelongsToMany
    {
        /** @var class-string<\Illuminate\Foundation\Auth\User> $model */
        $model = config('acl.user', config('auth.providers.users.model'));

        // @phpstan-ignore-next-line
        return $this->belongsToMany($model)->withTimestamps();
    }
}
