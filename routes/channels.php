<?php

use Illuminate\Support\Facades\Broadcast;

use App\Models\ChatSession;

Broadcast::channel('chat.{sessionId}', function ($user, $sessionId) {
    $session = ChatSession::find($sessionId);
    return $session && (int) $user->id === (int) $session->user_id;
});
