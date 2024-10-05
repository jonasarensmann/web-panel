<?php

namespace App\Http\Controllers\FileManager;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Illuminate\Http\Request;

class FileController extends Controller
{
    private function path(string $requestPath): string
    {
        $username = app()->environment(('local')) ? trim(shell_exec("whoami")) : auth()->user()->username;

        $path = "";
        if ($requestPath === "") {
            $path = "/home/" . $username;
        } else {
            if (str_contains($requestPath, "/home/" . $username)) {
                $path = $requestPath;
            } else {
                $path = "/home/" . $username . "/" . $requestPath;
            }
        }

        return $path;
    }

    private function deleteDir(string $dirPath): void
    {
        // TODO: Create less dangerous way to delete directories
        shell_exec('rm -rf ' . $dirPath);
    }

    public function index(Request $request)
    {
        $files = [];

        $requestPath = $request->has('path') ? $request->input('path') : "";

        if (str_contains($requestPath, "../")) {
            return redirect()->route("file.index");
        }

        $path = $this->path($requestPath);

        if (is_file($path)) {
            $content = file_get_contents($path);

            return Inertia::render(
                "FileManager/Show",
                [
                    "path" => $path,
                    "name" => basename($path),
                    "content" => $content,
                ]
            );
        }

        foreach (scandir($path) as $file) {
            if ($file === "." || $file === "..") {
                continue;
            }

            try {
                $fileSize = filesize($path . "/" . $file);
            } catch (\Exception $e) {
                $fileSize = -1;
            }

            $files[] = [
                "name" => $file,
                "size" => $fileSize,
                "type" => is_dir($path . "/" . $file) ? "directory" : "file",
            ];
        }

        return Inertia::render(
            "FileManager/Index",
            [
                "path" => $path,
                "files" => $files,
            ]
        );
    }

    public function store(Request $request)
    {
        $requestPath = $request->has('path') ? $request->path : "";

        if (str_contains($requestPath, "../")) {
            return redirect()->route("file.index");
        }

        $path = $this->path($requestPath);

        file_put_contents($path, $request->file_content ?? "");

        return redirect()->route("files.index", ["path" => $requestPath]);
    }

    public function storedir(Request $request)
    {
        $requestPath = $request->has("path") ? $request->path : "";

        if (str_contains($requestPath, "../")) {
            return redirect()->route("files.index");
        }

        $path = $this->path($requestPath);

        mkdir($path . "/");
    }

    public function destroy(Request $request)
    {
        $requestPath = $request->has('path') ? $request->path : "";

        if (str_contains($requestPath, "../")) {
            return redirect()->route("files.index");
        }

        $path = $this->path($requestPath);

        if (is_dir($path)) {
            $this->deleteDir($path);
        }

        return redirect()->route("files.index");
    }
}
