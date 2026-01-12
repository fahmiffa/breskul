<?php

namespace App\Jobs;

use Google_Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use DB;

class ProcessFcm implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $message;

    public function __construct(array $message)
    {
        $this->message = $message;
    }

    public function handle(): void
    {
        try {
            $serviceAccountPath = storage_path(
                env('FIREBASE_CREDENTIALS', 'app/firebase/service.json')
            );

            $serviceAccount = json_decode(
                file_get_contents($serviceAccountPath),
                true
            );

            $projectId = $serviceAccount['project_id'];

            $client = new Google_Client();
            $client->setAuthConfig($serviceAccountPath);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

            $token = $client->fetchAccessTokenWithAssertion()['access_token'];

            $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

            $response = Http::withToken($token)
                ->acceptJson()
                ->post($url, $this->message);

            if ($response->successful()) {
                Log::info('FCM sent', [
                    'response' => $response->json(),
                ]);
                return;
            }

            // ⛔ HANDLE UNREGISTERED TOKEN
            if (
                $response->status() === 404 &&
                str_contains($response->body(), 'UNREGISTERED')
            ) {
                Log::warning('FCM token unregistered', [
                    'payload' => $this->message,
                ]);

                DB::table('users')->where('fcm',$token)->update(['fcm'=>null]);
                return;
            }

            Log::error('FCM HTTP Error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

        } catch (\Throwable $e) {
            Log::critical('ProcessFcm failed', [
                'error'   => $e->getMessage(),
                'payload' => $this->message,
            ]);

            throw $e; // biar queue retry sesuai config
        }
    }
}
