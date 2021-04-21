<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LineNotify implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $line_notify_token, $message;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $line_notify_token, string $message)
    {
        $this->line_notify_token = $line_notify_token;
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if(!empty($this->line_notify_token)) {
            $apiUrl = "https://notify-api.line.me/api/notify";
            $params = [
                'message' => $this->message,
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $this->line_notify_token
            ]);
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            $output = curl_exec($ch);
            curl_close($ch);
        }
    }
}
