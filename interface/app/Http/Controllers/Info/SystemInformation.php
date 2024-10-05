<?php

namespace App\Http\Controllers\Info;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SystemInformation extends Controller
{
    public function index()
    {
        return Inertia::render(
            'SystemInfo/Index',
            [
                'info' => [
                    'php' => [
                        'version' => phpversion(),
                        'extensions' => get_loaded_extensions(),
                    ],
                    'server' => [
                        'software' => $_SERVER['SERVER_SOFTWARE'],
                        'name' => php_uname('n'),
                        'os' => php_uname('s'),
                        'release' => php_uname('r'),
                        'machine' => php_uname('m'),
                    ],
                    'laravel' => [
                        'version' => app()->version(),
                        'env' => app()->environment(),
                    ],
                    'cpu' => [
                        'model' => shell_exec('cat /proc/cpuinfo | grep "model name" | uniq'),
                    ],
                    'memory' => [
                        'total' => shell_exec('cat /proc/meminfo | grep "MemTotal"'),
                        'free' => shell_exec('cat /proc/meminfo | grep "MemFree"'),
                        'used' => shell_exec('cat /proc/meminfo | grep "MemAvailable"'),
                    ]
                ]
            ]
        );
    }
}
