<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\Students;
use App\Jobs\ProcessFcm;
use Illuminate\Support\Facades\Log;

class PaymentWebhookService
{
    /**
     * Process the payment webhook data.
     *
     * @param array $data
     * @return bool
     */
    public function handle(array $data): bool
    {
        Log::channel('payment')->info($data);

        $title = $data['title'] ?? '';
        $text = $data['text'] ?? '';
        $content = $title . ' ' . $text;

        // Pattern to extract amount like Rp200.740
        if (preg_match('/Rp([\d\.]+)/', $content, $matches)) {
            $amountStr = str_replace('.', '', $matches[1]); // e.g. 200740
            $uniqueCode = substr($amountStr, -3); // e.g. 740

            // Find bill with this unique code
            $bill = Bill::where('unique_code', $uniqueCode)
                        ->where('status', 0)
                        ->latest()
                        ->first();

            if ($bill) {
                // Update status to Paid (1)
                $bill->status = 1;
                $bill->save();

                // Send FCM Notification
                $this->sendPaymentNotification($bill, (int) $amountStr);
                
                return true;
            }
        }

        return false;
    }

    /**
     * Send notification after successful payment.
     *
     * @param Bill $bill
     * @param int $amount
     * @return void
     */
    private function sendPaymentNotification(Bill $bill, int $amount): void
    {
         if ($bill->head && $bill->head->murid && $bill->head->murid->users) {
            $user = $bill->head->murid->users;
            
            // Topic convention: user_{id}
            $topic = 'user_' . $user->id; 
            $title = 'Pembayaran Berhasil';
            $nominal = number_format($bill->payment->nominal ?? 0, 0, ',', '.');
            $body = "Pembayaran " . $bill->payment->name . " sebesar Rp " . $nominal . " telah lunas.";

            $message = [
                "message" => [
                    "token" => $user->fcm,
                    "notification" => [
                        "title" => $title,
                        "body" => $body,
                    ],
                ],
            ];
            ProcessFcm::dispatch($message);
            
            FirebaseMessage::sendTopicBroadcast($topic, $title, $body);
                
        }
    }
}
