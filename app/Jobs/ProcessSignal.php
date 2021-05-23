<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Builder;
use App\Models\SignalHistory;
use App\Models\AdminUser;
use App\Enums\TradingPlatformType;
use App\Jobs\BinanceMarginTrandingWorker;
use Binance;

class ProcessSignal implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $signal;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(SignalHistory $signal)
    {
        $this->signal = $signal->withoutRelations();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if($this->signal->is_valid)
        {
            AdminUser::matchTypePair($this->signal->trading_platform_type, $this->signal->symbol_type)->orderBy('id', 'desc')->chunk(200, function ($users) {
                foreach ($users as $user)
                {
                    if($this->signal->trading_platform_type->is(TradingPlatformType::BINANCE)) {
                        BinanceMarginTrandingWorker::dispatch($user, $this->signal);
                    }
                    else {
                        // else if
                    }
                }
            });
        }
    }
}
