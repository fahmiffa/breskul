<?php
namespace App\Services\Midtrans;

use App\Models\Bill;
use App\Services\Firebase\FirebaseMessage;
use Illuminate\Support\Facades\Http;
use Midtrans\Config;
use Midtrans\Snap;

class Transaction
{

    public static function create($params)
    {

        Config::$serverKey    = env('MIDTRANS_SERVER_KEY');
        Config::$clientKey    = env('MIDTRANS_CLIENT_KEY');
        Config::$isProduction = env('MODE_MIDTRANS');
        Config::$isSanitized  = true;
        Config::$is3ds        = true;

        try {
            $snap        = Snap::createTransaction($params);
            $redirectUrl = $snap->redirect_url;

            return response()->json([
                'redirect_url' => $redirectUrl,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal membuat transaksi: ' . $e->getMessage(),
            ], 500);
        }
    }

    public static function hook($data)
    {
        try {
            $serverKey    = config('midtrans.server_key');
            $signatureKey = hash('sha512',
                $data['order_id'] .
                $data['status_code'] .
                $data['gross_amount'] .
                $serverKey
            );

            if ($signatureKey !== $data['signature_key']) {
                return response()->json(['message' => 'Invalid signature'], 403);
            }
            $order = Bill::where('mid', $data['order_id'])->first();
            if (! $order) {
                return response()->json(['message' => 'Order not found'], 404);
            }

        if ($data['transaction_status'] === 'expire') {
            if ($order) {
                $order->status = 3;
                $order->save();
            }
        }

            // Handle status pending
            if ($data['transaction_status'] === 'pending') {

                if ($data['payment_type'] === "bank_transfer") {
                    $order->via    = json_encode($data['va_numbers']);
                    $order->status = 2;
                }

                if ($data['payment_type'] === "echannel") {
                    $order->via = json_encode([
                        'bank'      => "Mandiri",
                        'va_number' => $data['bill_key'],
                        'code'      => $data['biller_code'],
                    ]);
                    $order->status = 2;
                }

                $order->save();
            }

            // Handle status settlement
            if ($data['transaction_status'] === 'settlement') {
                $order->status = 1;
                $order->save();

                $fcm = optional($order->head->murid->users)->fcm; 

                if ($fcm) {
                    $message = [
                        "message" => [
                            "token"        => $fcm,
                            "notification" => [
                                "title" => "Pembayaran",
                                "body"  => "Tagihan Anda " . $order->payment->name . " sudah terbayar",
                            ],
                        ],
                    ];
                    FirebaseMessage::sendFCMMessage($message);
                }
            }

            return response()->json(['message' => 'Notification handled'], 200);
        } catch (\Exception $e) {
            // Log error agar mudah ditelusuri
            \Log::error('Midtrans Hook Error: ' . $e->getMessage(), [
                'data'  => $data,
                'trace' => $e->getTraceAsString(),
            ]);

            $response = Http::asForm()->post(env('URL_TEL'), [
                'chat_id' => env('CHAT_TEL'),
                'text'    => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Internal Server Error',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

}
