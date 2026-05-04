<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AiService;
use Illuminate\Support\Str;

class AiController extends Controller
{
    private AiService $aiService;

    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Handle chat messages from the frontend
     */
    public function chat(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'mode' => 'required|in:inquiry,crud',
        ]);

        // Get or create session ID for the specific mode
        $sessionId = session("chat_session_id_{$validated['mode']}");
        if (!$sessionId) {
            $sessionId = Str::uuid()->toString();
            session(["chat_session_id_{$validated['mode']}" => $sessionId]);
        }

        try {
            $result = $this->aiService->chat(
                $validated['message'],
                $sessionId,
                $validated['mode']
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your request.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get chat history for the current session
     */
    public function getHistory(Request $request)
    {
        $mode = $request->get('mode', 'inquiry'); // Default to inquiry if not specified

        $sessionId = session("chat_session_id_{$mode}");

        if (!$sessionId) {
            return response()->json(['history' => []]);
        }

        $history = $this->aiService->getChatHistory($sessionId, 20);

        return response()->json(['history' => $history]);
    }

    /**
     * Clear chat history
     */
    public function clearHistory(Request $request)
    {
        $mode = $request->get('mode', 'inquiry'); // Default to inquiry if not specified

        $sessionId = session("chat_session_id_{$mode}");

        if (!$sessionId) {
            return response()->json([
                'success' => false,
                'message' => 'No chat session found.',
            ]);
        }

        $this->aiService->clearChatHistory($sessionId);

        return response()->json([
            'success' => true,
            'message' => 'Chat history cleared.',
        ]);
    }
}
