<?php

namespace App\Services;

use Carbon\Carbon;

class PanelConfig
{
    private string $fileLocation;

    public function __construct()
    {
        $this->fileLocation = app()->environment("local") ? "/tmp/panel.json" : "/etc/web-panel/config.json";

        if (!$this->check_if_file_exists()) {
            $this->create_file();
        }
    }

    public function check_if_file_exists(): bool
    {
        return file_exists($this->fileLocation);
    }

    public function create_file()
    {
        if ($this->check_if_file_exists()) {
            return false;
        }

        $now = Carbon::now()->toIso8601ZuluString();

        file_put_contents($this->fileLocation, json_encode([
            'last_updated' => $now,
            'last_checked_for_updates' => $now,
        ]));

        return true;
    }

    public function set_key(string $key, $value): bool
    {
        if (!$this->check_if_file_exists()) {
            return false;
        }

        $data = json_decode(file_get_contents($this->fileLocation), true);
        unset($data[$key]);
        $data[$key] = $value;

        file_put_contents($this->fileLocation, json_encode($data));

        return true;
    }

    public function get_key(string $key)
    {
        if (!$this->check_if_file_exists()) {
            return false;
        }

        return json_decode(file_get_contents($this->fileLocation), true)[$key];
    }
}
