<?php

namespace App\Http\Controllers\Info;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class LogFilesController extends Controller
{
    private function get_files()
    {
        return [
            "laravel.log" => storage_path() . "/logs/laravel.log",
            "caddy.log" => "/var/log/caddy/caddy.log",
        ];
    }

    public function index()
    {
        return Inertia::render(
            "LogFiles/Index",
            [
                "files" => $this->get_files(),
            ]
        );
    }

    public function show($file)
    {
        if (!auth()->user()->is_admin && (!isset(auth()->user()->permissions["view_logs"]) || !auth()->user()->permissions["view_logs"])) {
            return response()->json(["error" => "Unauthorized"], 401);
        }

        $files = $this->get_files();
        $file_path = $files[$file];

        if (!file_exists($file_path)) {
            return response()->json(["error" => "File not found"], 404);
        }

        return Inertia::render(
            "LogFiles/Show",
            [
                "title" => $file,
                "content" => file_get_contents($file_path),
            ]
        );
    }
}
