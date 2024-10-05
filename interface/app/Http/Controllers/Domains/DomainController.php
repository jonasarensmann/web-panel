<?php

namespace App\Http\Controllers\Domains;

use App\Http\Controllers\Controller;
use App\Http\Requests\Domains\CreateDomainRequest;
use App\Http\Requests\Domains\DeleteDomainRequest;
use App\Http\Requests\Domains\UpdateDomainRequest;
use App\Models\Domain;
use App\Models\Subdomain;
use Inertia\Inertia;

class DomainController extends Controller
{

    public function index()
    {
        return Inertia::render('Domain/Index', [
            'domains' => Domain::where('user_id', auth()->id())->get(),
            'subdomains' => Subdomain::where('user_id', auth()->id())->get()
        ]);
    }

    public function api_index()
    {
        return Domain::where('user_id', auth()->id())->get();
    }

    public function show(Domain $domain)
    {
        $caddyconfig = $domain->caddyconfig;

        foreach ($caddyconfig as $key => $value) {
            if (str_contains($key, '2020')) {
                unset($caddyconfig[$key]);
            }
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    if (str_contains($k, '2020') || str_contains($k, 'webmail') || str_contains($k, '__term')) {
                        unset($caddyconfig['*.' . $domain->name][$k]);
                    }
                }
            }
        }

        $domain->caddyconfig = [
            ...$caddyconfig,
        ];

        return Inertia::render('Domain/Show', [
            'domain' => $domain,
            'username' => auth()->user()->username,
        ]);
    }

    public function api_show(Domain $domain)
    {
        if ($domain->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $domain;
    }

    public function store(CreateDomainRequest $request)
    {
        Domain::factory()->create(['name' => $request->domain, 'user_id' => auth()->id()]);

        return redirect()->route('domains.index');
    }

    public function update(UpdateDomainRequest $request, Domain $domain)
    {
        $domain->name = $request->name ?? $domain->name;
        $domain->locked = $request->locked ?? $domain->locked;
        $domain->save();

        return redirect()->route('domains.index');
    }

    public function api_update(UpdateDomainRequest $request, Domain $domain)
    {
        $domain->name = $request->name ?? $domain->name;
        $domain->locked = $request->locked ?? $domain->locked;
        $domain->save();

        return $domain;
    }

    public function destroy(DeleteDomainRequest $request, Domain $domain)
    {
        Domain::delete_dns_file($domain);
        Domain::delete_caddyfile($domain);

        // remove the zone from named.conf
        $namedConf = "/etc/bind/named.conf";
        if (app()->environment(('local'))) {
            $namedConf = "/tmp/named.conf";
        }

        if (!file_exists($namedConf)) {
            file_put_contents($namedConf, "");
        }

        $namedConfContents = file_get_contents($namedConf);
        $namedConfContents = str_replace("zone \"{$domain->name}\" { type master; file \"/etc/bind/{$domain->name}.db\"; };", "", $namedConfContents);

        file_put_contents($namedConf, $namedConfContents);

        $domain->delete();

        return redirect()->route('domains.index');
    }
}
