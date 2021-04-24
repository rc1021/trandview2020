<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\BinanceMarginStopLossLimitCheck;
use App\Models\TxnMarginOrder;
use App\Models\AdminUser;
use App\Jobs\DailySummary;

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
        // $schedule->job(new DailySummary)->cron('0 16 * * *');
        $schedule->job(new BinanceMarginStopLossLimitCheck)->cron('59 * * * *');

        $schedule->call(function () {
            // 取得所有尚未結束的止損單
            AdminUser::whereNotNull('line_notify_token')->chunk(200, function ($users) {
                foreach ($users as $user)
                {
                    $user->notify("嗨，該更新Binance金鑰囉!\n(為了該每次的交易更安全，每月第1天請記得更新金鑰)\n\n奉上網址\nhttps://www.binance.com/zh-TW/my/settings/api-management\n\n更換網址\nhttps://bosytradingbot.com/admin/auth/key-secrets");
                }
            });
        })->cron('0 0 1 * *');
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
