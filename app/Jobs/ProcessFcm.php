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
use App\Services\Firebase\FirebaseMessage;

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
        FirebaseMessage::sendTopicBroadcast($this->message['topic'], $this->message['title'], $this->message['body']);
    }
}
