<?php

namespace App\Http\Controllers\Domains;

use App\Http\Controllers\Controller;
use App\Http\Requests\Domains\CreateCaddyconfigRequest;
use App\Http\Requests\Domains\UpdateCaddyconfigRequest;
use App\Http\Requests\Domains\DeleteCaddyconfigRequest;
use App\Models\Domain;
use Inertia\Inertia;

class CaddyconfigController extends Controller
{
    public function update(UpdateCaddyconfigRequest $request, Domain $domain, string $index)
    {
        if ($request->port === 2020) {
            return Inertia::render('domains.show', [
                'domain' => $domain,
                'error' => 'Port 2020 is reserved for the admin panel',
            ]);
        }

        if ($request->file_server && $request->reverse_proxy && $request->static_response) {
            return response()->json(['message' => 'only one field can be set']);
        } elseif ($request->file_server && $request->static_response) {
            return response()->json(['message' => 'only one field can be set']);
        } elseif ($request->reverse_proxy && $request->static_response) {
            return response()->json(['message' => 'only one field can be set']);
        } elseif ($request->file_server && $request->reverse_proxy) {
            return response()->json(['message' => 'only one field can be set']);
        }

        $newCaddyconfig = [];

        $username = auth()->user()->username;
        if ($request->file_server) {
            $newCaddyconfig = [
                'file_server' => $request->browse ? 'browse' : true,
                'root' => ['*', "/home/{$username}/domains/{$domain->name}/public"],
            ];

            if ($request->php) {
                $newCaddyconfig['php_fastcgi'] = 'unix//run/php/php8.2-fpm.sock';
            }
        }

        if ($request->reverse_proxy) {
            $newCaddyconfig = [
                'reverse_proxy' => $request->reverse_proxy_location,
            ];
        }

        if ($request->static_response) {
            $request->static_response_text = str_replace('"', '&quot;', $request->static_response_text);

            $newCaddyconfig = [
                'respond' => '"' . $request->static_response_text . '"',
            ];
        }

        $caddyconfig = $domain->caddyconfig;

        if ($index === $domain->name) {
            unset($caddyconfig[$request->port === 0 ? $domain->name : "$domain->name:$request->port"]);
            $caddyconfig[$request->port === 0 ? $domain->name : "$domain->name:$request->port"] = $newCaddyconfig;
        } else {
            unset($caddyconfig["*.{$domain->name}"][explode('.', $index)[0]]);
            unset($caddyconfig["*.{$domain->name}"]["@" . explode('.', $index)[0]]);
            $nCaddyconfig = [
                "handle@" . explode('.', $index)[0] => $newCaddyconfig,
                "@" . explode('.', $index)[0] => ['host', $request->port !== 0 ? "{$index}:{$request->port}" : $index],
            ];
            $caddyconfig["*.{$domain->name}"] = [
                ...$caddyconfig["*.{$domain->name}"],
                ...$nCaddyconfig,
            ];
        }

        $domain->caddyconfig = $caddyconfig;
        $domain->save();
        Domain::create_caddyfile($domain);

        return redirect()->route('domains.show', ['domain' => $domain->id]);
    }

    public function store(CreateCaddyconfigRequest $request, Domain $domain)
    {
        if ($request->port === 2020) {
            return Inertia::render('domains.show', [
                'domain' => $domain,
                'error' => 'Port 2020 is reserved for the admin panel',
            ]);
        }

        $newCaddyconfig = ['respond' => "\"Hello World\""];

        $caddyconfig = $domain->caddyconfig;

        $caddyconfig = [
            ...$caddyconfig,
            "$domain->name:$request->port" => $newCaddyconfig,
        ];

        $domain->update(["caddyconfig" => $caddyconfig]);
        Domain::create_caddyfile($domain);

        return redirect()->route('domains.show', ['domain' => $domain->id]);
    }

    public function destroy(DeleteCaddyconfigRequest $request, Domain $domain)
    {
        if ($request->port === 2020) {
            return Inertia::render('domains.show', [
                'domain' => $domain,
                'error' => 'Port 2020 is reserved for the admin panel',
            ]);
        }

        $caddyconfig = $domain->caddyconfig;

        unset($caddyconfig["$domain->name"]);

        $domain->update(["caddyconfig" => $caddyconfig]);
        Domain::create_caddyfile($domain);

        return redirect()->route('domains.show', ['domain' => $domain->id]);
    }
}
