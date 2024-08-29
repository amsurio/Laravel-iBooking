<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Events\MessageSent;
use App\Models\User;

class ChatController extends Controller
{
    public function index()
    {
        $toUser = User::all();
        return view('chat.index', compact('toUser'));
    }

    public function fetchMessages($userId)
    {
        return Message::where(function ($query) use ($userId) {
            $query->where('user_id', auth()->id())
                ->where('receiver_id', $userId);
        })
            ->orWhere(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->where('receiver_id', auth()->id());
            })
            ->with('user')
            ->get();
    }

    public function sendMessage(Request $request)
    {
        $message = Message::create([
            'user_id' => auth()->id(),
            'receiver_id' => $request->to_user_id,
            'message' => $request->message
        ]);

        broadcast(new MessageSent($message))->toOthers();

        return response()->json(['status' => 'Message Sent!']);
    }
}
