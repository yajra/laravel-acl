<?php

namespace Yajra\Acl\Models;

use Yajra\Acl\Traits\HasPermission;
use Illuminate\Database\Eloquent\Model;

/**
 * @property \Yajra\Acl\Permission permissions
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

    /**
     * Roles can belong to many users.
     *
     * @return Model
     */
    public function users()
    {
        return $this->belongsToMany(config('auth.providers.users.model'))
                    ->withTimestamps();
    }
}
