<?php

namespace Yajra\Acl\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Acl\Traits\HasRole;

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
