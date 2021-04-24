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
            $type = $this->signal->trading_platform_type;
            AdminUser::whereHas('keysecrets', function (Builder $query) use ($type) {
                $query->where('type', $type);
            })->chunk(200, function ($users) use ($type) {
                foreach ($users as $user)
                {
                    if($type->is(TradingPlatformType::BINANCE)) {
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
