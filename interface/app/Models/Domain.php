<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Process;

class Domain extends Model
{
    use HasFactory;

    protected $casts = [
        'dns' => 'array',
        'caddyconfig' => 'array',
    ];

    protected $fillable = [
        'dns',
        'caddyconfig',
        'locked'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subdomains()
    {
        return $this->hasMany(Subdomain::class);
    }

    public static function create_dns_template($domainName, $ipAddress)
    {
        $dns_template = [
            '$origin' => $domainName . '.',
            '$ttl' => 3600,
            'soa' => [
                'mname' => 'ns1.' . $domainName . '.',
                'rname' => 'hostmaster.' . $domainName . '.',
                'serial' => time(),
                'refresh' => 3600,
                'retry' => 600,
                'expire' => 604800,
                'minimum' => 86400
            ],
            'ns' => [
                ['host' => 'ns1.' . $domainName . '.'],
                ['host' => 'ns2.' . $domainName . '.']
            ],
            'mx' => [
                ['preference' => 10, 'host' => 'mail.' . $domainName . '.']
            ],
            'a' => [
                ['name' => '@', 'ip' => $ipAddress],
                ['name' => 'www', 'ip' => $ipAddress],
                ['name' => 'mail', 'ip' => $ipAddress],
                ['name' => 'ns1', 'ip' => $ipAddress],
                ['name' => 'ns2', 'ip' => $ipAddress],
                ['name' => 'webmail', 'ip' => $ipAddress],
            ],
            'txt' => [
                ['name' => '@', 'txt' => '"v=spf1 a mx ip4:' . $ipAddress . ' ~all"']
            ],
            'ptr' => [
                ['name' => '@', 'host' => 'mail.' . $domainName . '.']
            ],
        ];

        return $dns_template;
    }

    private static function restart_bind(): void
    {
        if (app()->environment(('local'))) {
            return;
        }

        $command = "sudo /bin/systemctl restart named";

        $result = Process::run($command);

        if ($result->exitCode() !== 0) {
            throw new \Exception("Failed to restart named: {$result->output()}");
        }
    }

    public static function delete_dns_file(Domain $domain): void
    {
        $zoneFile = "/etc/bind/{$domain->name}.db";
        if (app()->environment(('local'))) {
            $zoneFile = "/tmp/{$domain->name}.db";
        }

        if (file_exists($zoneFile)) {
            unlink($zoneFile);
        }
    }

    public static function create_dns_file(Domain $domain): void
    {
        $dnsJson = json_encode($domain->dns, JSON_PRETTY_PRINT);
        $tmpJsonFile = "/tmp/{$domain->name}.json";

        $zoneFile = "/etc/bind/{$domain->name}.db";
        if (app()->environment(('local'))) {
            $zoneFile = "/tmp/{$domain->name}.db";
        }

        $backupFile = null;
        if (file_exists($zoneFile)) {
            $backupFile = "/tmp/{$domain->name}.db.bak";
            file_put_contents($backupFile, file_get_contents($zoneFile));
        }

        file_put_contents($tmpJsonFile, $dnsJson);

        self::delete_dns_file($domain);

        $command = "zonefile -g {$tmpJsonFile} > {$zoneFile}";

        $result = Process::run($command);

        if ($result->exitCode() !== 0) {
            if (isset($backupFile)) {
                file_put_contents($zoneFile, file_get_contents($backupFile));
                unlink($backupFile);
            }

            throw new \Exception("Failed to regenerate zone file: {$result->errorOutput()}");
        } else {
            $namedConf = "/etc/bind/named.conf";
            if (app()->environment(('local'))) {
                $namedConf = "/tmp/named.conf";
            }
            if (!file_exists($namedConf)) {
                file_put_contents($namedConf, "");
            }

            $namedConfContents = file_get_contents($namedConf);

            if (strpos($namedConfContents, "zone \"{$domain->name}\" { type master; file \"/etc/bind/{$domain->name}.db\"; };") === false) {
                $namedConfContents .= "zone \"{$domain->name}\" { type master; file \"/etc/bind/{$domain->name}.db\"; };\n";
                file_put_contents($namedConf, $namedConfContents);
            }

            unlink($tmpJsonFile);
            if (isset($backupFile)) {
                unlink($backupFile);
            }
        }

        self::restart_bind();
    }

    public static function create_caddyconfig_template($domainName, $user_id)
    {
        $username = User::where(['id' => $user_id])->first()->username;

        $caddyfile = [
            "*.$domainName" => [
                "@webmail" => ["host", "webmail.$domainName"],
                "handle@webmail" => [
                    "root" => ["*", "/var/www/roundcube"],
                    "file_server" => true,
                    "encode" => ["zstd", "gzip"],
                    "php_fastcgi" => "unix//run/php/php8.2-fpm.sock",
                ],
            ],
            "$domainName:2020" => [
                "handle_path" => [
                    "super" => "/term/*",
                    "reverse_proxy" => "127.0.0.1:7681"
                ],
                "_handle_path" => [
                    "super" => "/filemanager/*",
                    "respond" => "404"
                ],
                "root" => ["*", "/etc/web-panel/interface/public"],
                "file_server" => true,
                "encode" => ["zstd", "gzip"],
                "php_fastcgi" => "unix//run/php/php8.2-fpm.sock",
            ],
            $domainName => [
                "root" => ["*", "/home/{$username}/domains/{$domainName}/public"],
                "file_server" => true,
                "encode" => ["zstd", "gzip"],
                "php_fastcgi" => "unix//run/php/php8.2-fpm.sock",
            ]
        ];

        self::create_caddyfile(new Domain([
            'name' => $domainName,
            'caddyconfig' => $caddyfile
        ]));

        return $caddyfile;
    }

    private static function restart_caddy(): void
    {
        if (app()->environment(('local'))) {
            return;
        }

        $command = "sudo /bin/systemctl reload caddy";

        $result = Process::run($command);

        if ($result->exitCode() !== 0 && !$result->exitCode() !== null) {
            throw new \Exception("Failed to reload caddy: {$result->output()}");
        }
    }

    public static function delete_caddyfile(Domain $domain): void
    {
        $caddyfile = "/etc/caddy/zones/{$domain->name}.caddy";
        if (app()->environment(('local'))) {
            $caddyfile = "/tmp/{$domain->name}.caddy";
        }

        if (file_exists($caddyfile)) {
            unlink($caddyfile);
        }
    }

    public static function create_caddyfile(Domain $domain): void
    {
        $caddyJson = json_encode($domain->caddyconfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $command = "/etc/web-panel/utilities/caddyfile-conv/main $(echo '$caddyJson')";
        if (app()->environment(('local'))) {
            $command = dirname(__DIR__, 3) . "/utilities/caddyfile-conv/main $(echo '$caddyJson')";
        }

        $result = Process::run($command);

        if ($result->exitCode() !== 0) {
            throw new \Exception("Failed to generate caddyfile: {$result->errorOutput()}");
        }

        $caddyfile = "/etc/caddy/zones/{$domain->name}.caddy";
        if (app()->environment(('local'))) {
            $caddyfile = "/tmp/{$domain->name}.caddy";
        }

        $backupFile = "/tmp/{$domain->name}.caddy.bak";
        if (file_exists($caddyfile)) {
            file_put_contents($backupFile, file_get_contents($caddyfile));
        }

        self::delete_caddyfile($domain);

        try {
            file_put_contents($caddyfile, $result->output());
        } catch (\Exception $e) {
            file_put_contents($caddyfile, file_get_contents($backupFile));
            if (file_exists($backupFile)) {
                unlink($backupFile);
            }
            throw new \Exception("Failed to write caddyfile: {$e->getMessage()}");
        }

        self::restart_caddy();

        if (file_exists($backupFile)) {
            unlink($backupFile);
        }
    }
}
