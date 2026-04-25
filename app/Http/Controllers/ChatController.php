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
            'history'           => 'nullable|array',
            'history.*.role'    => 'in:user,assistant',
            'history.*.content' => 'string',
        ]);

        $result = $this->chatService->handleMessage(
            $request->message,
            $request->history ?? []
        );

        $statusCode = isset($result['error']) ? 503 : 200;

        return response()->json($result, $statusCode);
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'confirmations' => 'required|array',
            'history'       => 'nullable|array',
        ]);

        $result = $this->chatService->handleConfirmedActions(
            $request->confirmations,
            $request->history ?? []
        );

        return response()->json($result);
    }
}
