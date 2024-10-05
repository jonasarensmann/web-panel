<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CreateTokenRequest;
use App\Http\Requests\Auth\DeleteTokensRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TokenController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Auth/ApiKeys', [
            'tokens' => $request->user()->tokens,
        ]);
    }

    public function store(CreateTokenRequest $request)
    {
        $expires_at = $request->expires_at ? Carbon::parse($request->expires_at)->toDateTime() : null;

        $token = null;

        if ($expires_at) {
            $token = $request->user()->createToken($request->name, ['*'], $expires_at);
        } else {
            $token = $request->user()->createToken($request->name);
        }

        return Inertia::render('Auth/ApiKeys', [
            'tokens' => $request->user()->tokens,
            'token'=> explode('|', $token->plainTextToken)[1],
        ]);
    }

    public function destroy(DeleteTokensRequest $request)
    {
        $request->user()->tokens()->whereIn('id', $request->input('tokens'))->delete();

        return redirect()->back();
    }
}
