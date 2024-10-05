<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Message;

class MessageController extends Controller
{
    public function index()
    {
        $messages = Message::where(['user_id' => auth()->user()->id])->orderBy("created_at", "desc")->get();

        if (auth()->user()->is_admin) {
            $adminMessages = Message::where(['show_admins' => true])->orderBy("created_at", "desc")->get();
            $messages = $messages->merge($adminMessages);
        }

        return Inertia::render(
            "User/Messages/Index",
            [
                "messages" => $messages,
            ]
        );
    }

    public function check()
    {
        return Message::where(["user_id" => auth()->user()->id, "read" => false])->count();
    }

    public function show(Message $message)
    {
        if ($message->user_id !== auth()->user()->id) {
            return abort(403);
        }

        $message->read = true;
        $message->save();

        return Inertia::render(
            "User/Messages/Show",
            [
                "message" => $message,
            ]
        );
    }

    public function destroy(Message $message)
    {
        if ($message->user_id !== auth()->user()->id) {
            return abort(403);
        }

        $message->delete();

        return redirect()->route("messages.index");
    }
}
