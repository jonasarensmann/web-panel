<?php

namespace App\Observers;

use App\Models\Domain;
use App\Models\User;

class DomainObserver
{
    /**
     * Handle the Domain "created" event.
     */
    public function created(Domain $domain): void
    {
        $username = User::where(['id' => $domain->user_id])->first()->username;

        if (app()->environment('local')) {
            return;
        }
        if (!file_exists("/home/{$username}/domains/{$domain->name}")) {
            mkdir("/home/{$username}/domains/{$domain->name}/public", 0760, true);
        }
    }

    /**
     * Handle the Domain "updated" event.
     */
    public function updated(Domain $domain): void
    {
        //
    }

    /**
     * Handle the Domain "deleted" event.
     */
    public function deleted(Domain $domain): void
    {
        $username = User::where(['id' => $domain->user_id])->first()->username;

        if (app()->environment('local')) {
            return;
        }
        if (file_exists("/home/{$username}/domains/{$domain->name}")) {
            rmdir("/home/{$username}/domains/{$domain->name}");
        }
    }

    /**
     * Handle the Domain "restored" event.
     */
    public function restored(Domain $domain): void
    {
        //
    }

    /**
     * Handle the Domain "force deleted" event.
     */
    public function forceDeleted(Domain $domain): void
    {
        //
    }
}
