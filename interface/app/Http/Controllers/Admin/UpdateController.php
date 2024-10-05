<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PanelConfig;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Inertia\Inertia;

class UpdateController extends Controller
{
    private string $githubEndpoint = "api.github.com/repos/jonasarensmann/web-panel/commits";

    public function index()
    {
        $updates = Http::get($this->url())->json();

        $formatedUpdates = [];

        foreach ($updates as $update) {
            $formatedUpdates[$update['sha']] = [
                'id' => $update['sha'],
                'message' => $update['commit']['message'],
                'author' => $update['author']['login'],
                'date' => $update['commit']['author']['date'],
            ];
        }

        return Inertia::render(
            'Admin/Update/Index',
            [
                'updates' => $formatedUpdates,
            ]
        );
    }

    private function url()
    {
        $key = env('GITHUB_KEY', '');

        if (empty($key)) {
            $this->githubEndpoint = 'https://' . $this->githubEndpoint;
        } else {
            $this->githubEndpoint = 'https://' . $key . '@' . $this->githubEndpoint;
        }

        $panelConfig = new PanelConfig();
        $lastUpdated = $panelConfig->get_key('last_updated');

        $url = $this->githubEndpoint . '?since=' . $lastUpdated;

        return $url;
    }

    public function check()
    {
        $panelConfig = new PanelConfig();
        $lastChecked = $panelConfig->get_key('last_checked_for_updates');

        if (!empty($lastChecked) && Carbon::parse($lastChecked)->diffInHours(now()) < 1) {
            return;
        }

        $response = Http::get($this->url());

        if ($response->status() !== 200) {
            Log::warning('Failed to check for updates: \n' . $response->body());
            return false;
        }

        $panelConfig->set_key('last_checked_for_updates', now()->toIso8601ZuluString());

        if (empty($response->json())) {
            return false;
        }
        return true;
    }

    public function update()
    {
        Log::info('Updating panel');

        $key = env('GITHUB_KEY', '');

        $repo = "github.com/jonasarensmann/web-panel.git";

        if (empty($key)) {
            $repo = 'https://' . $repo;
        } else {
            $repo = 'https://' . $key . '@' . $repo;
        }

        if (app()->environment('local')) {
            Process::run("bash -c 'cd /tmp && git clone $repo'");

            $panelConfig = new PanelConfig();
            $panelConfig->set_key('last_updated', Carbon::now()->toIso8601ZuluString());
            return;
        }

        $command = "bash -c 'export REPO=$repo && sudo /etc/web-panel/utilities/update.sh'";

        $process = Process::run($command);

        if ($process->failed()) {
            Log::error($process->errorOutput());
            return;
        }

        if ($process->successful()) {
            $panelConfig = new PanelConfig();
            $panelConfig->set_key('last_updated', Carbon::now()->toIso8601ZuluString());
        }
    }
}
