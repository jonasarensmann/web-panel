<?php

use App\Http\Controllers\Admin\UpdateController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\TokenController;
use App\Http\Controllers\Domains\CaddyconfigController;
use App\Http\Controllers\Domains\DNSController;
use App\Http\Controllers\Domains\DomainController;
use App\Http\Controllers\Domains\EmailController;
use App\Http\Controllers\Domains\SubdomainController;
use App\Http\Controllers\FileManager\FileController;
use App\Http\Controllers\Info\LogFilesController;
use App\Http\Controllers\Info\SystemInformation;
use App\Http\Controllers\Terminal\TerminalController;
use App\Http\Controllers\User\MessageController;
use App\Http\Middleware\AdminOnly;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
    Route::get('/isadmin', function () {
        return response()->json(['isadmin' => auth()->user()->is_admin]);
    })->name('admin.check');
    Route::controller(DomainController::class)->group(function () {
        Route::get('/domains', 'index')
            ->name('domains.index');
        Route::get('/domains/{domain}', 'show')
            ->name('domains.show');
        Route::post('/domains', 'store')
            ->name('domains.store');
        Route::put('/domains/{domain}', 'update')
            ->name('domains.update');
        Route::delete('/domains/{domain}', 'destroy')
            ->name('domains.destroy');
    });
    Route::controller(DNSController::class)->group(function () {
        Route::get('/dns', 'index')
            ->name('dns.index');
        Route::get('/dns/{domain}', 'show')
            ->name('dns.show');
        Route::patch('/dns/{domain}', 'update')
            ->name('dns.update');
        Route::post('/dns/{domain}', 'store')
            ->name('dns.store');
        Route::delete('/dns/{domain}', 'destroy')
            ->name('dns.destroy');
    });
    Route::controller(SubdomainController::class)->group(function () {
        Route::get('/subdomains', 'index')
            ->name('subdomains.index');
        Route::post('/subdomains', 'store')
            ->name('subdomains.store');
        Route::delete('/subdomains/{subdomain}', 'destroy')
            ->name('subdomains.destroy');
    });
    Route::controller(TokenController::class)->group(function () {
        Route::get('/tokens', 'index')
            ->name('tokens.index');
        Route::post('/tokens', 'store')
            ->name('tokens.store');
        Route::delete('/tokens', 'destroy')
            ->name('tokens.destroy');
    });
    Route::controller(CaddyconfigController::class)->group(function () {
        Route::post('/caddyconfig/{domain}', 'store')
            ->name('caddyconfig.store');
        Route::patch('/caddyconfig/{domain}/{index}', 'update')
            ->name('caddyconfig.update');
        Route::delete('/caddyconfig/{domain}', 'destroy')
            ->name('caddyconfig.destroy');
    });
    Route::controller(SystemInformation::class)->group(function () {
        Route::get('/systeminfo', 'index')
            ->name('systeminfo.index');
    });
    Route::controller(LogFilesController::class)->group(function () {
        Route::get('/logs', 'index')
            ->name('logs.index');
        Route::get('/logs/{file}', 'show')
            ->name('logs.show');
    });
    Route::controller(MessageController::class)->group(function () {
        Route::get('/messages', 'index')
            ->name('messages.index');
        Route::get('/messages/check', 'check')
            ->name('messages.check');
        Route::get('/messages/{message}', 'show')
            ->name('messages.show');
        Route::delete('/messages/{message}', 'destroy')
            ->name('messages.destroy');
    });
    Route::controller(TerminalController::class)->group(function () {
        Route::get('/terminal', 'index')
            ->name('terminal.index');
    });
    Route::controller(EmailController::class)->group(function () {
        Route::get('/emails', 'index')
            ->name('emails.index');
        Route::get('/emails/{domain}', 'show')
            ->name('emails.show');
        Route::post('/emails', 'store')
            ->name('emails.store');
        Route::delete('/emails/{domain}/{email}', 'destroy')
            ->name('emails.destroy');
    });
    Route::controller(FileController::class)->group(function () {
        Route::get('/files', 'index')
            ->name('files.index');
        Route::post('/files', 'store')
            ->name('files.store');
        Route::post('/files/dir', 'storedir')
            ->name('files.storedir');
        Route::delete('/files', 'destroy')
            ->name('files.destroy');
    });

    Route::middleware(AdminOnly::class)->group(function () {
        Route::controller(UpdateController::class)->group(function () {
            Route::get('/updates', 'index')
                ->name('updates.index');
            Route::get('/updates/check', 'check')
                ->name('updates.check');
            Route::post('/updates', 'update')
                ->name('updates.update');
        });
        Route::controller(UserController::class)->group(function () {
            Route::get('/users', 'index')
                ->name('users.index');
            Route::post('users', 'store')
                ->name('users.store');
            Route::patch('users/{user}', 'update')
                ->name('users.update');
            Route::delete('users/{user}', 'destroy')
                ->name('users.destroy');
        });
    });
});

require __DIR__ . '/auth.php';
