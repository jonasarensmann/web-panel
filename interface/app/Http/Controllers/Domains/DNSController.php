<?php

namespace App\Http\Controllers\Domains;

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\Domains\DeleteDNSRecordRequest;
use App\Http\Requests\Domains\CreateDNSRecordRequest;
use App\Http\Requests\Domains\UpdateDNSRecordRequest;
use App\Models\Domain;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DNSController extends Controller
{
    public function index()
    {
        return Inertia::render('DNS/Index', [
            'domains' => Domain::where('user_id', auth()->id())->get(),
        ]);
    }

    public function api_index()
    {
        return Domain::where('user_id', auth()->id())->get();
    }

    public function show(Domain $domain)
    {
        return Inertia::render('DNS/Show', [
            'domain' => $domain,
        ]);
    }

    public function api_getByName(Request $request)
    {
        return Domain::where(['name' => $request->query('name')])->first()->id;
    }

    public function store(CreateDNSRecordRequest $request, Domain $domain)
    {
        $dns = $domain->dns;

        $record = [];

        if ($request->type === 'a' || $request->type === 'aaaa') {
            $record = [
                'name' => $request->name,
                'ip' => $request->value
            ];
        }
        if ($request->type === 'cname') {
            $record = [
                'name' => $request->name,
                'alias' => $request->value
            ];
        }
        if ($request->type === 'txt') {
            $record = [
                'name' => $request->name,
                'txt' => $request->value
            ];
        }
        if ($request->type === 'ns') {
            $record = [
                'host' => $request->name
            ];
        }
        if ($request->type === 'mx') {
            $record = [
                'preference' => explode(' ', $request->value)[0],
                'host' => explode(' ', $request->value)[1],
            ];
        }
        if ($request->type === 'ptr') {
            $record = [
                'name' => $request->name,
                'host' => $request->value
            ];
        }
        if (!isset($dns[$request->type])) {
            $dns[$request->type] = [];
        }

        $dns[$request->type][] = $record;
        $dns['soa']['serial'] = time();
        $domain->dns = $dns;
        $domain->save();
        Domain::create_dns_file($domain);

        return redirect()->route('dns.show', $domain);
    }

    public function api_store(CreateDNSRecordRequest $request, Domain $domain)
    {
        $dns = $domain->dns;

        $record = [];

        if ($request->type === 'a' || $request->type === 'aaaa') {
            $record = [
                'name' => $request->name,
                'ip' => $request->value
            ];
        }

        if ($request->type === 'cname') {
            $record = [
                'name' => $request->name,
                'alias' => $request->value
            ];
        }

        if ($request->type === 'txt') {
            $record = [
                'name' => $request->name,
                'txt' => $request->value
            ];
        }

        if ($request->type === 'ns') {
            $record = [
                'host' => $request->name
            ];
        }
        if ($request->type === 'mx') {
            $record = [
                'preference' => explode(' ', $request->value)[0],
                'host' => explode(' ', $request->value)[1],
            ];
        }
        if ($request->type === 'ptr') {
            $record = [
                'name' => $request->name,
                'host' => $request->value
            ];
        }

        if (!isset($dns[$request->type])) {
            $dns[$request->type] = [];
        }

        $dns[$request->type][] = $record;
        $dns['soa']['serial'] = time();
        $domain->dns = $dns;
        $domain->save();
        Domain::create_dns_file($domain);

        return $domain;
    }

    public function update(UpdateDNSRecordRequest $request, Domain $domain)
    {
        $dns = $domain->dns;

        $type = mb_convert_case($request->type, MB_CASE_LOWER);

        $record = [];

        if ($type === 'a' || $type === 'aaaa') {
            $record = [
                'name' => $request->name,
                'ip' => $request->value
            ];
        }

        if ($type === 'cname') {
            $record = [
                'name' => $request->name,
                'alias' => $request->value
            ];
        }

        if ($type === 'txt') {
            $record = [
                'name' => $request->name,
                'txt' => $request->value
            ];
        }

        if ($type === 'ns') {
            $record = [
                'host' => $request->name
            ];
        }

        if ($type === 'mx') {
            $record = [
                'preference' => explode(' ', $request->value)[0],
                'host' => explode(' ', $request->value)[1],
            ];
        }
        if ($request->type === 'ptr') {
            $record = [
                'name' => $request->name,
                'host' => $request->value
            ];
        }

        $updated = false;
        foreach ($dns[$type] as &$existingRecord) {
            if ($type === 'mx') {
                if ($existingRecord['host'] === explode(' ', $request->value)[1]) {
                    $existingRecord = $record;
                    $updated = true;
                }
            } elseif ($type === 'ns') {
                if ($existingRecord['host'] === $request->name) {
                    $existingRecord = $record;
                    $updated = true;
                }
            } else {
                if ($existingRecord['name'] === $request->name) {
                    $existingRecord = $record;
                    $updated = true;
                }
            }
        }

        if (!$updated) {
            $dns[$type][] = $record;
        }

        $dns['soa']['serial'] = time();
        $domain->dns = $dns;
        $domain->save();
        Domain::create_dns_file($domain);

        return redirect()->route('dns.show', $domain);
    }

    public function api_update(UpdateDNSRecordRequest $request, Domain $domain)
    {
        $dns = $domain->dns;

        $type = mb_convert_case($request->type, MB_CASE_LOWER);

        $record = [];

        if ($type === 'a' || $type === 'aaaa') {
            $record = [
                'name' => $request->name,
                'ip' => $request->value
            ];
        }

        if ($type === 'cname') {
            $record = [
                'name' => $request->name,
                'alias' => $request->value
            ];
        }

        if ($type === 'txt') {
            $record = [
                'name' => $request->name,
                'txt' => $request->value
            ];
        }

        if ($type === 'ns') {
            $record = [
                'host' => $request->name
            ];
        }

        if ($type === 'mx') {
            $record = [
                'preference' => explode(' ', $request->value)[0],
                'host' => explode(' ', $request->value)[1],
            ];
        }

        if ($request->type === 'ptr') {
            $record = [
                'name' => $request->name,
                'host' => $request->value
            ];
        }

        $updated = false;
        foreach ($dns[$type] as &$existingRecord) {
            if ($type === 'mx') {
                if ($existingRecord['host'] === explode(' ', $request->value)[1]) {
                    $existingRecord = $record;
                    $updated = true;
                }
            } elseif ($type === 'ns') {
                if ($existingRecord['host'] === $request->name) {
                    $existingRecord = $record;
                    $updated = true;
                }
            } else {
                if ($existingRecord['name'] === $request->name) {
                    $existingRecord = $record;
                    $updated = true;
                }
            }
        }

        if (!$updated) {
            $dns[$type][] = $record;
        }

        $dns['soa']['serial'] = time();
        $domain->dns = $dns;
        $domain->save();
        Domain::create_dns_file($domain);

        return $domain;
    }

    public function destroy(DeleteDNSRecordRequest $request, Domain $domain)
    {
        $dns = $domain->dns;

        foreach ($request->records as $record) {
            $type = explode('§', $record)[0];
            $name = explode('§', $record)[1];

            foreach ($dns[$type] as $key => $value) {
                if ($value['name'] === $name) {
                    unset($dns[$type][$key]);
                }
            }
        }

        $domain->dns = $dns;

        $domain->save();
        Domain::create_dns_file($domain);

        return redirect()->route('dns.show', $domain);
    }

    public function api_destroy(DeleteDNSRecordRequest $request, Domain $domain)
    {
        $dns = $domain->dns;

        foreach ($request->records as $record) {
            $type = explode('§', $record)[0];
            $name = explode('§', $record)[1];

            foreach ($dns[$type] as $key => $value) {
                if (
                    (isset($value['name']) && $value['name'] === $name) ||
                    (isset($value['host']) && $value['host'] === $name) ||
                    (isset($value['alias']) && $value['alias'] === $name) ||
                    (isset($value['txt']) && $value['txt'] === $name)
                ) {
                    Log::info('Deleting record: ' . (isset($value['name']) ? $value['name'] : 'unknown'));
                    unset($dns[$type][$key]);
                }
            }
        }

        $domain->dns = $dns;
        $domain->save();
        Domain::create_dns_file($domain);

        return $domain;
    }
}
