<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\TxnDirectType;
use BinanceApi\Enums\SymbolType;

class CreateAdminTxnEntryRecsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_txn_entry_recs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('signal_history_id');

            // == 預先設定的參數&接收到的數據 ==
            $table->dateTime('B30', 0);    // 應開倉日期時間
            $table->float('B31', 24, 8)->default(0);    // 交易前可交易總資金
            $table->string('B32')->default(TxnDirectType::SHORT);    // 交易方向(多/空)
            $table->float('B33', 24, 8)->default(0);    // 資金風險(%)
            $table->string('B34')->default(SymbolType::BTCUSDT);    // 交易配對
            $table->tinyInteger('B35')->default(0);     // 槓桿使用
            $table->tinyInteger('B36')->default(1);     // 預先扣除手續費
            $table->float('B37', 24, 8)->default(0);    // 交易手續費
            $table->float('B38', 24, 8)->nullable();    // 起始風險價位(止損價位)
            $table->float('B39', 24, 8)->default(0);    // 開倉價格容差(最高價位)
            $table->float('B40', 24, 8)->default(0);    // 開倉價格容差(最低價位)
            $table->float('B41', 24, 8)->default(0);    // Entry訊號價位(當時的價位)
            // == 計算起始風險&預備金應加碼&應借款計算 ==
            $table->float('B43', 24, 8)->default(0);    // 資金風險金額
            $table->float('B44', 24, 8)->nullable();    // 起始風險%(1R)
            $table->float('B45', 24, 8)->default(0);    // 可開倉的部位大小(美元)
            $table->float('B46', 24, 8)->default(0);    // 應加碼金額(美元)
            $table->float('B47', 24, 8)->default(0);    // 應動用預備金加碼(美元)
            $table->float('B48', 24, 8)->default(0);    // 應借款金額(美元)
            $table->float('B49', 24, 8)->default(0);    // 應總共動用帳戶內多少資金(美元)
            // == 計算槓桿風險率 ==
            $table->float('D30', 24, 8)->default(0);    // 借款利息%預估
            $table->float('D31', 24, 8)->default(0);    // 借款利息預估(貨幣單位依照多空)
            $table->float('D32', 24, 8)->default(0);    // 風險率(加上利息)
            $table->integer('D33')->default(0);         // 查表排序
            $table->float('D34', 24, 8)->default(0);    // 對應的級別
            $table->float('D35', 24, 8)->default(0);    // 對應的強平線
            $table->tinyInteger('D36')->default(0);     // 此筆交易可否使用槓桿執行 0:不可以; 1:可以
            // == 做多 ==
            $table->float('D38', 24, 8)->default(0);    // 應借款金額(美元)
            $table->float('D39', 24, 8)->default(0);    // 總共應動用帳戶內多少資金(美元)
            $table->float('D40', 24, 8)->default(0);    // 買入BTC總共應有多少成交額(美元)
            // == 做空 ==
            $table->float('D42', 24, 8)->default(0);    // 應借款金額(BTC)
            $table->float('D43', 24, 8)->default(0);    // 動用帳戶內多少資金(BTC)
            $table->float('D44', 24, 8)->default(0);    // 總共應借入且賣出多少BTC
            // == 止盈止損單 ==
            $table->float('D46', 24, 8)->default(0);    // 觸發價
            $table->float('D47', 24, 8)->default(0);    // 限價
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_txn_entry_recs');
    }
}
