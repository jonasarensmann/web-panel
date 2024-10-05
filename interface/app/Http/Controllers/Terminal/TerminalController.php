<?php

namespace App\Http\Controllers\Terminal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TerminalController extends Controller
{
    public function index()
    {
        return Inertia::render('Terminal/Index');
    }
}
