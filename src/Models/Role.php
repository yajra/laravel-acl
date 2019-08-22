<?php

namespace Yajra\Acl\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Acl\Traits\HasPermission;

/**
 * @property \Yajra\Acl\Models\Permission permissions
 * @property bool system
 */
class Role extends Model
{
    use HasPermission;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'roles';

    /**
     * Fillable fields.
     *
     * @var array
     */
    protected $fillable = ['name', 'slug', 'description', 'system'];

    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            app('cache.store')->forget('permissions.policies');
        });

        static::deleted(function () {
            app('cache.store')->forget('permissions.policies');
        });
    }

    /**
     * Roles can belong to many users.
     *
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(config('acl.user', config('auth.providers.users.model')))
                    ->withTimestamps();
    }
}
