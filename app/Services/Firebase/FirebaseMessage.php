<?php
namespace App\Services\Firebase;

use Google_Client;
use Illuminate\Support\Facades\Http;

class FirebaseMessage
{
    public static function sendFCMMessage($message)
    {
        // Ambil path dari .env
        $serviceAccountPath = storage_path(env('FIREBASE_CREDENTIALS', 'app/firebase/service.json'));

        // Cek file ada dan bukan folder
        if (! file_exists($serviceAccountPath)) {
            throw new \Exception("Firebase service account file tidak ditemukan di: $serviceAccountPath");
        }
        if (is_dir($serviceAccountPath)) {
            throw new \Exception("Path seharusnya file tapi ini folder: $serviceAccountPath");
        }

        // Baca file JSON dan decode
        $serviceAccountContent = file_get_contents($serviceAccountPath);
        $serviceAccount        = json_decode($serviceAccountContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("JSON decode error: " . json_last_error_msg());
        }

        // Ambil project_id
        $projectId = $serviceAccount['project_id'];

        // Setup Google Client
        $client = new Google_Client();
        $client->setAuthConfig($serviceAccountPath);
        $client->addScope("https://www.googleapis.com/auth/firebase.messaging");

        // Ambil access token
        $token = $client->fetchAccessTokenWithAssertion()["access_token"];

        // Endpoint FCM v1 API
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        // Kirim request menggunakan Laravel HTTP client
        $response = Http::withToken($token)
            ->acceptJson()
            ->post($url, $message);

        if ($response->successful()) {
            return response()->json([
                'message'  => 'Pesan berhasil dikirim',
                'response' => $response->json(),
            ]);
        } else {
            return response()->json([
                'message'  => 'Gagal mengirim pesan',
                'status'   => $response->status(),
                'response' => $response->body(),
            ], $response->status());
        }
    }

    public static function sendTopicBroadcast(string $topic, string $title, string $body, array $data = [])
    {
        // Path ke file service account JSON (letakkan di storage/app/firebase)
        $serviceAccountPath = storage_path(env('FIREBASE_CREDENTIALS'));

        // Baca file JSON dan ambil project_id
        $serviceAccount = json_decode(file_get_contents($serviceAccountPath), true);
        $projectId      = $serviceAccount['project_id'];

        // Setup Google Client
        $client = new Google_Client();
        $client->setAuthConfig($serviceAccountPath);
        $client->addScope("https://www.googleapis.com/auth/firebase.messaging");

        // Ambil access token
        $accessToken = $client->fetchAccessTokenWithAssertion()['access_token'];

        // Endpoint FCM v1 API
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        // Payload untuk broadcast ke topic
        $message = [
            'message' => [
                'topic'        => $topic,
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                ],
            ],
        ];

        // Kirim request
        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->post($url, $message);

        // Hasil respons
        if ($response->successful()) {
            return [
                'success'  => true,
                'message'  => 'Broadcast berhasil dikirim ke topic: ' . $topic,
                'response' => $response->json(),
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Gagal mengirim broadcast',
                'status'  => $response->status(),
                'error'   => $response->body(),
            ];
        }
    }
}
