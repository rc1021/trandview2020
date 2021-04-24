<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToFuturesFormulaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('futures_formulas', function (Blueprint $table) {
            $table->string('file_content'); //檔案內容
            $table->string('title'); //公式表
            $table->string('user_id'); //修改人
            $table->string('file_path'); //檔案位置
            $table->string('file_preview'); //公式表預覽
            $table->string('commit'); //本次修改註解
            $table->string('setcol1'); // 1.初始帳戶餘額
            $table->string('setcol2'); // 2. 交易配對
            $table->string('setcol3'); // 3. 每次初始可交易總資金(%)
            $table->string('setcol4'); // 4. 每次交易資金風險(%)
            $table->string('setcol5'); // 5. 應開倉日期時間
            $table->string('setcol6'); // 6. 交易方向(多/空)
            $table->string('setcol7'); // 7. Entry訊號價位(當時的價位)
            $table->string('setcol8'); // 8. 起始風險價位(止損價位)
            $table->string('setcol9'); // 9. 開倉價格容差(最高價位)
            $table->string('setcol10'); // 10.開倉價格容差(最低價位)
            $table->string('setcol11'); // 11.危及強平價格%
            // 參數設置
            $table->string('setcol12'); // 12.危及維持保證金%
            $table->string('setcol13'); // 13.做多觸發限價%
            $table->string('setcol14'); // 14.回填實際借款%
            // 限價單
            $table->string('setcol15'); // 15.槓桿倍數
            $table->string('setcol16'); // 16.價格
            $table->string('setcol17'); // 17.數量
            $table->string('setcol18'); // 18.執行
            $table->string('setcol19'); // 19.生效時間
            // 初始限價止損單
            $table->string('setcol20'); // 20.觸發價格
            $table->string('setcol21'); // 21.價格
            $table->string('setcol22'); // 22.數量
            $table->string('setcol23'); // 23.掛單執行
            $table->string('setcol24'); // 24.觸發類型
            $table->string('setcol25'); // 25.生效時間
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('futures_formulas', function (Blueprint $table) {
            $table->dropColumn([
                'file_content',
                'title',
                'user_id',
                'file_path',
                'file_preview',
                'commit',
                'setcol1',
                'setcol2',
                'setcol3',
                'setcol4',
                'setcol5',
                'setcol6',
                'setcol7',
                'setcol8',
                'setcol9',
                'setcol10',
                'setcol11',
                'setcol12',
                'setcol13',
                'setcol14',
                'setcol15',
                'setcol16',
                'setcol17',
                'setcol18',
                'setcol19',
                'setcol20',
                'setcol21',
                'setcol22',
                'setcol23',
                'setcol24',
                'setcol25',
            ]);
        });
    }
}
