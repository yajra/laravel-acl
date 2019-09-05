<?php

namespace Yajra\Acl\Observers;

use Illuminate\Database\Eloquent\Model;

class AclModelObserver
{
    /**
     * Handle the User "saved" event.
     *
     * @return void
     */
    public function saved(Model $model)
    {
        $this->clearCache();
    }

    /**
     * Handle the User "deleted" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function deleted(User $user)
    {
        $this->clearCache();
    }

    /**
     * Clear ACL cache and reload current user roles.
     */
    protected function clearCache()
    {
        if (auth()->check()) {
            auth()->user()->load('roles');
        }

        app('cache.store')->forget(config('acl.cache.key', 'permissions.policies'));
    }
}
