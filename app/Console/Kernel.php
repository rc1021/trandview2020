<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\BinanceIsolatedStopLossLimitCheck;
use App\Models\TxnMarginOrder;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->exec('echo 5')->everyMinute();
        // $schedule->command('backup:clean')->daily()->at('01:00');
        // $schedule->command('backup:run')->daily()->at('01:30');
        $schedule->call(function () {
            // 取得所有尚未結束的止損單
            TxnMarginOrder::stopLossLimit()->statusNew()->chunk(200, function ($orders) {
                foreach ($orders as $order)
                {
                    BinanceIsolatedStopLossLimitCheck::dispatch($order->id);
                }
            });
        })->cron('59 * * * *');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
