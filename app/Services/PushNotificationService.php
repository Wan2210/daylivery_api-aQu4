<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;

class PushNotificationService
{
    public function sendToUser(User $user, array $payload): void
    {
        $tokens = $user->pushTokens()
            ->where('is_active', true)
            ->whereNotNull('token')
            ->pluck('token')
            ->filter(fn (string $token): bool => $token !== '')
            ->all();

        if ($tokens === []) {
            return;
        }

        $serverKey = env('FCM_SERVER_KEY');

        if (blank($serverKey)) {
            return;
        }

        Http::asJson()
            ->withHeaders([
                'Authorization' => 'key='.$serverKey,
                'Content-Type' => 'application/json',
            ])
            ->post('https://fcm.googleapis.com/fcm/send', [
                'registration_ids' => $tokens,
                'notification' => [
                    'title' => $payload['title'] ?? 'Pesan baru',
                    'body' => $payload['body'] ?? '',
                ],
                'data' => array_merge([
                    'type' => $payload['type'] ?? 'general',
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ], $payload['data'] ?? []),
            ]);
    }
}
