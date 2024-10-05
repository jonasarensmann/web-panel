<?php

namespace App\Http\Controllers\Domains;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Process;

class EmailController extends Controller
{
    private string $BASE_PATH = "/var/mail/vhosts";

    private function getEmails(Domain $domain)
    {
        $content = file_get_contents($this->BASE_PATH . "/" . $domain->name . "/passwd");
        $lines = explode("\n", $content);

        $emails = [];
        foreach ($lines as $line) {
            $parts = explode("::", $line);
            if (count($parts) > 1) {
                $emails[] = $parts[0];
            }
        }

        return $emails;
    }

    public function index()
    {
        return Inertia::render('Email/Index', [
            'domains' => Domain::where('user_id', auth()->id())->get(),
        ]);
    }

    public function show(Domain $domain)
    {
        if ($domain->user_id !== auth()->id()) {
            return redirect()->route('emails.index');
        }

        return Inertia::render('Email/Show', [
            'domain' => $domain,
            'domains' => Domain::where('user_id', auth()->id())->get(),
            'emails' => $this->getEmails($domain)
        ]);
    }

    public function alias(Domain $domain)
    {
        if ($domain->user_id !== auth()->id()) {
            return redirect()->route('emails.index');
        }

        $content = file_get_contents("/etc/postfix/virtual_alias");

        $lines = explode("\n", $content);

        $aliases = [];
        foreach ($lines as $line) {
            $parts = explode(" ", $line);
            if (count($parts) > 1) {
                $aliases[] = $parts[0];
            }
        }

        return Inertia::render('Email/Alias', [
            'aliases' => $aliases,
            'domain' => $domain,
        ]);
    }

    public function store(Request $request)
    {
        $domain = Domain::where(['id' => $request->domain, 'user_id' => auth()->id()])->first();

        if (in_array($request->name . '@' . $domain->name, $this->getEmails($domain))) {
            return redirect()->route('emails.show', $domain)->with('error', 'Email already exists');
        }

        file_put_contents("/tmp/add-email.tmp", $request->name . '@' . $domain->name . " " . $request->password);

        Process::run("sudo /etc/web-panel/utilities/add-email.sh");

        return redirect()->route('emails.show', $domain);
    }

    public function destroy(Domain $domain, $email)
    {
        if ($domain->user_id !== auth()->id()) {
            return redirect()->route('emails.index');
        }

        file_put_contents("/tmp/remove-email.tmp", $email);

        Process::run("sudo /etc/web-panel/utilities/remove-email.sh");

        return redirect()->route('emails.show', $domain);
    }
}
