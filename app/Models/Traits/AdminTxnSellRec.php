<?php

namespace App\Models\Traits;

use App\Models\AdminUser;
use App\Models\TxnMarginOrder;
use App\Models\AdminTxnExitRec;
use BinanceApi\BinanceApiManager;
use BinanceApi\Enums\SymbolType;

trait AdminTxnSellRec
{
    // 建立實際平倉紀錄
    public static function createRec(AdminTxnExitRec $exit, AdminUser $user)
    {
        $rec = new \App\Models\AdminTxnSellRec;
        $rec->user_id = $user->id;
        $rec->txn_exit_id = $exit->id;


        // 開始時間
        $start_at = time();
        $position_start_at = date("Y-m-d H:i:s", $start_at);

        // 取得資料庫所有止損單
        $arrStopLossLimit = $user->stopLossLimits;
        if(count($arrStopLossLimit) > 0)
        {
            $ks = $user->keysecret()->toArray();
            $api = new BinanceApiManager(data_get($ks, 'key', ''), data_get($ks, 'secret', ''));
            $symbol = SymbolType::coerce((int)$user->transactionSetting->transaction_matching);

            foreach ($arrStopLossLimit as $key => $stop)
            {
                list($err, $order) = $api->doLongExit($symbol, $stop->toArray());

                if(!is_null($err)) {
                    $stop->error = $err;
                    $stop->save();
                }
                else {
                    TxnMarginOrder::create([
                        'user_id' => $user->id,
                        'symbol' => $symbol->value,
                        'orderId' => $order['orderId'],
                        'type' => $order['type'],
                        'result' => json_encode($order)
                    ]);
                }

                $stop->delete();
            }
        }

        // 結束時間
        $done_at = time();
        $position_done_at = date("Y-m-d H:i:s", $done_at);
        $position_duration = $done_at - $start_at;

        $rec->J29 = $position_start_at;
        $rec->J30 = $position_done_at;
        $rec->J31 = $position_duration;

        $rec->save();
        return $rec;
    }
}
