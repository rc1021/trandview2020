<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use GuzzleHttp\Client;

class SignalPodcast implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $webhook, $path, $content;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $webhook, string $path, string $content)
    {
        $this->webhook = $webhook;
        $this->path = $path;
        $this->content = $content;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = new Client([ 'base_uri' => $this->webhook, ]);
        $client->post($this->path, [ 'body' => $this->content ]);
    }
}
