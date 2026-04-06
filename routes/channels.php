<?php

use Illuminate\Support\Facades\Broadcast;

use App\Models\ChatSession;

Broadcast::channel('chat.{sessionId}', function ($user, $sessionId) {
    return (int) $user->id === (int) ChatSession::find($sessionId)->user_id;
});
