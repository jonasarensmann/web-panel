<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use app\Models\User;
use Illuminate\Http\Request;
use Process;

class UserController extends Controller
{
    public function index()
    {
        return Inertia::render(
            'Admin/User/Index',
            [
                "users" => User::all()
            ]
        );
    }

    public function store(Request $request)
    {
        User::factory()->create([
            "username" => $request->username,
            "password" => $request->password,
            "is_admin" => $request->is_admin,
        ]);

        file_put_contents("/tmp/add-user.tmp", $request->username . " " . $request->password);

        Process::run("sudo /etc/web-panel/utilities/add-user.sh");

        return redirect()->route("users.index");
    }

    public function update(Request $request, User $user)
    {
        $user->update(
            $request->only(
                "username",
                "is_admin",
            )
        );

        return redirect()->route("users.index");
    }

    public function destroy(User $user)
    {
        if ($user->user_id === 1) {
            return redirect()->route("users.index");
        }

        $user->delete();
        return redirect()->route("users.index");
    }
}
