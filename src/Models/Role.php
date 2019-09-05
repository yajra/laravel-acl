<?php

namespace Yajra\Acl\Models;

use Yajra\Acl\Traits\RefreshCache;
use Yajra\Acl\Traits\HasPermission;
use Illuminate\Database\Eloquent\Model;

/**
 * @property \Yajra\Acl\Models\Permission permissions
 * @property bool system
 */
class Role extends Model
{
    use HasPermission, RefreshCache;

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
