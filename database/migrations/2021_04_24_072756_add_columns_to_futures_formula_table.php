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
            $table->string('file_content')->nullable(); //檔案內容
            $table->string('title')->nullable(); //公式表
            $table->string('user_id'); //修改人
            $table->string('file_path')->nullable(); //檔案位置
            $table->string('file_preview')->nullable(); //公式表預覽
            $table->string('commit')->nullable(); //本次修改註解
            $table->string('setcol1')->nullable(); // 1.初始帳戶餘額
            $table->string('setcol2')->nullable(); // 2. 交易配對
            $table->string('setcol3')->nullable(); // 3. 每次初始可交易總資金(%)
            $table->string('setcol4')->nullable(); // 4. 每次交易資金風險(%)
            $table->string('setcol5')->nullable(); // 5. 應開倉日期時間
            $table->string('setcol6')->nullable(); // 6. 交易方向(多/空)
            $table->string('setcol7')->nullable(); // 7. Entry訊號價位(當時的價位)
            $table->string('setcol8')->nullable(); // 8. 起始風險價位(止損價位)
            $table->string('setcol9')->nullable(); // 9. 開倉價格容差(最高價位)
            $table->string('setcol10')->nullable(); // 10.開倉價格容差(最低價位)
            $table->string('setcol11')->nullable(); // 11.危及強平價格%
            // 參數設置
            $table->string('setcol12')->nullable(); // 12.危及維持保證金%
            $table->string('setcol13')->nullable(); // 13.做多觸發限價%
            $table->string('setcol14')->nullable(); // 14.回填實際借款%
            // 限價單
            $table->string('setcol15')->nullable(); // 15.槓桿倍數
            $table->string('setcol16')->nullable(); // 16.價格
            $table->string('setcol17')->nullable(); // 17.數量
            $table->string('setcol18')->nullable(); // 18.執行
            $table->string('setcol19')->nullable(); // 19.生效時間
            // 初始限價止損單
            $table->string('setcol20')->nullable(); // 20.觸發價格
            $table->string('setcol21')->nullable(); // 21.價格
            $table->string('setcol22')->nullable(); // 22.數量
            $table->string('setcol23')->nullable(); // 23.掛單執行
            $table->string('setcol24')->nullable(); // 24.觸發類型
            $table->string('setcol25')->nullable(); // 25.生效時間
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
