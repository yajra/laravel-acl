<?php

namespace Yajra\Acl\Models;

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
    protected $fillable = ['name', 'slug', 'description', 'system'];
}
