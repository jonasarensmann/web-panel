<?php

namespace App\Observers;

use App\Models\Domain;
use App\Models\Subdomain;
use App\Models\User;

class SubdomainObserver
{
    /**
     * Handle the Domain "created" event.
     */
    public function created(Subdomain $subdomain): void
    {
        $username = User::where(['id' => $subdomain->user_id])->first()->username;
        $domainName = Domain::where(['id' => $subdomain->domain_id])->first()->name;

        if (app()->environment('local')) {
            return;
        }
        if (!file_exists("/home/{$username}/domains/{$subdomain->name}")) {
            mkdir("/home/{$username}/domains/{$subdomain->name}.{$domainName}/public", 0760, true);
        }
    }
    /**
     * Handle the Domain "deleted" event.
     */
    public function deleted(Subdomain $subdomain): void
    {
        $username = User::where(['id' => $subdomain->user_id])->first()->username;
        $domainName = Domain::where(['id' => $subdomain->domain_id])->first()->name;

        if (app()->environment('local')) {
            return;
        }
        if (!file_exists("/home/{$username}/domains/{$subdomain->name}")) {
            rmdir("/home/{$username}/domains/{$subdomain->name}.{$domainName}");
        }
    }
}
