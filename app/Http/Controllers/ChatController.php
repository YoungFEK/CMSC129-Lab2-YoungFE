<?php

namespace App\Http\Controllers;

use App\Services\ChatService;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(
        private readonly ChatService $chatService
    ) {}

    public function chat(Request $request)
    {
        $request->validate([
            'message'           => 'required|string|max:1000',
            'mode'              => 'nullable|string|in:chatbot,assistant',
            'history'           => 'nullable|array',
            'history.*.role'    => 'in:user,assistant',
            'history.*.content' => 'string',
        ]);

        $mode = $request->input('mode', 'chatbot');

        $result = $this->chatService->handleMessage(
            $request->message,
            $request->history ?? [],
            $mode
        );

        $statusCode = isset($result['error']) ? 503 : 200;

        return response()->json($result, $statusCode);
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'confirmations' => 'required|array',
            'mode'          => 'nullable|string|in:chatbot,assistant',
            'history'       => 'nullable|array',
        ]);

        $mode = $request->input('mode', 'chatbot');

        $result = $this->chatService->handleConfirmedActions(
            $request->confirmations,
            $request->history ?? [],
            $mode
        );

        return response()->json($result);
    }
}
