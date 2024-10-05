<?php

namespace App\Http\Controllers\Domains;

use App\Http\Controllers\Controller;
use App\Http\Requests\Domains\CreateSubdomainRequest;
use App\Models\Domain;
use App\Models\Subdomain;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubdomainController extends Controller
{
    public function index()
    {
        return Inertia::render('Subdomain/Index', [
            'domains' => Domain::where('user_id', auth()->id())->get(),
            'subdomains' => Subdomain::where('user_id', auth()->id())->get(),
        ]);
    }

    public function store(CreateSubdomainRequest $request)
    {
        $domain = Domain::where(['name' => $request->domain, 'user_id' => auth()->user()->id])->first();
        if (!$domain) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $subdomain = Subdomain::factory()->create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'domain_id' => $domain->id,
        ]);

        // update the domain's caddyconfig and dns
        $newCaddyconfig = [];

        $newCaddyconfig["@" . $subdomain->name] = ['host', $subdomain->name . '.' . $domain->name];
        $newCaddyconfig["handle@" . $subdomain->name] = [
            'root' => ['*', "/home/{$domain->user->username}/domains/{$subdomain->name}.{$domain->name}/public"],
            'file_server' => true,
            'encode' => ['zstd', 'gzip'],
            'php_fastcgi' => 'unix//run/php/php8.2-fpm.sock',
        ];
        $newCaddyconfig["@" . $subdomain->name . ':2020'] = ['host', $subdomain->name . '.' . $domain->name . ':2020'];
        $newCaddyconfig["handle@" . $subdomain->name . ':2020'] = [
            'root' => ['*', "/etc/web-panel/interface/public"],
            'file_server' => true,
            'encode' => ['zstd', 'gzip'],
            'php_fastcgi' => 'unix//run/php/php8.2-fpm.sock',
        ];

        $caddyconfig = $domain->caddyconfig;
        $caddyconfig["*." . $domain->name] = array_merge($newCaddyconfig, $caddyconfig["*." . $domain->name]);

        $dns = $domain->dns;
        $dns = array_merge($dns, [
            'a' => [
                ...$dns['a'],
                ['name' => $subdomain->name, 'ip' => $domain->ip],
            ],
        ]);

        $domain->caddyconfig = $caddyconfig;
        $domain->dns = $dns;
        $domain->save();
        Domain::create_caddyfile($domain);
        Domain::create_dns_file($domain);

        return redirect()->route('subdomains.index');
    }

    public function destroy(Request $request, Subdomain $subdomain)
    {
        if ($subdomain->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $domain = Domain::find($subdomain->domain_id);
        $caddyconfig = $domain->caddyconfig;
        $dns = $domain->dns;
        unset($caddyconfig[$subdomain->name . '.' . $domain->name . ':2020']);
        unset($caddyconfig[$subdomain->name . '.' . $domain->name]);
        if (isset($dns['a'][$subdomain->name])) {
            unset($dns['a'][$subdomain->name]);
        }
        $domain->caddyconfig = $caddyconfig;
        $domain->dns = $dns;
        $domain->save();
        Domain::create_caddyfile($domain);
        Domain::create_dns_file($domain);

        $request->subdomain->delete();

        return redirect()->route('subdomains.index');
    }
}
