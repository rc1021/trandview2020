<?php

return [
    'online'                => '在線',
    'login'                 => '登錄',
    'logout'                => '登出',
    'setting'               => '設置',
    'name'                  => '名稱',
    'username'              => '用戶名',
    'password'              => '密碼',
    'password_confirmation' => '確認密碼',
    'remember_me'           => '記住我',
    'user_setting'          => '用戶設置',
    'avatar'                => '頭像',
    'list'                  => '列表',
    'new'                   => '新增',
    'create'                => '創建',
    'delete'                => '刪除',
    'remove'                => '移除',
    'edit'                  => '編輯',
    'view'                  => '查看',
    'continue_editing'      => '繼續編輯',
    'continue_creating'     => '繼續創建',
    'detail'                => '詳細',
    'browse'                => '瀏覽',
    'reset'                 => '重置',
    'export'                => '匯出',
    'batch_delete'          => '批次刪除',
    'save'                  => '儲存',
    'refresh'               => '重新整理',
    'order'                 => '排序',
    'expand'                => '展開',
    'collapse'              => '收起',
    'filter'                => '篩選',
    'search'                => '搜索',
    'close'                 => '關閉',
    'show'                  => '顯示',
    'entries'               => '條',
    'captcha'               => '驗證碼',
    'action'                => '操作',
    'title'                 => '標題',
    'description'           => '簡介',
    'back'                  => '返回',
    'back_to_list'          => '返回列表',
    'submit'                => '送出',
    'menu'                  => '目錄',
    'input'                 => '輸入',
    'succeeded'             => '成功',
    'failed'                => '失敗',
    'delete_confirm'        => '確認刪除？',
    'delete_succeeded'      => '刪除成功！',
    'delete_failed'         => '刪除失敗！',
    'update_succeeded'      => '更新成功！',
    'save_succeeded'        => '儲存成功！',
    'refresh_succeeded'     => '成功重新整理！',
    'login_successful'      => '成功登入！',
    'choose'                => '選擇',
    'choose_file'           => '選擇檔案',
    'choose_image'          => '選擇圖片',
    'more'                  => '更多',
    'deny'                  => '權限不足',
    'administrator'         => '管理員',
    'roles'                 => '角色',
    'permissions'           => '權限',
    'slug'                  => '標誌',
    'created_at'            => '建立時間',
    'updated_at'            => '更新時間',
    'alert'                 => '警告',
    'parent_id'             => '父目錄',
    'icon'                  => '圖示',
    'uri'                   => '路徑',
    'operation_log'         => '操作記錄',
    'parent_select_error'   => '父級選擇錯誤',
    'pagination'            => [
        'range' => '從 :first 到 :last ，總共 :total 條',
    ],
    'role'                  => '角色',
    'permission'            => '權限',
    'route'                 => '路由',
    'confirm'               => '確認',
    'cancel'                => '取消',
    'http'                  => [
        'method' => 'HTTP方法',
        'path'   => 'HTTP路徑',
    ],
    'all_methods_if_empty'  => '為空默認為所有方法',
    'all'                   => '全部',
    'current_page'          => '現在頁面',
    'selected_rows'         => '選擇的行',
    'upload'                => '上傳',
    'new_folder'            => '新建資料夾',
    'time'                  => '時間',
    'size'                  => '大小',
    'listbox'               => [
        'text_total'         => '總共 {0} 項',
        'text_empty'         => '空列表',
        'filtered'           => '{0} / {1}',
        'filter_clear'       => '顯示全部',
        'filter_placeholder' => '過濾',
    ],
    'menu_titles'            => [],
    'prev'                   => '上一步',
    'next'                   => '下一步',
    'quick_create'           => '快速創建',

    'auth' => [
        'keysecret' => [
            'title' => 'API金鑰',
            'type' => '金鑰來源',
            'alias' => '金鑰別名',
            'alias_unique' => '金鑰別名是唯一值',
            'apikey' => 'API Key',
            'secretkey' => 'Secret Key',
            'or_qrcode_upload' => '或者使用 QR Code 上傳',
            'or_qrcode_upload_helper' => '使用 QR Code 上傳時，不需要填寫 API Key 和 Secret Key； QR Code 圖片可以在 <a target="_blank" href="https://www.binance.com/zh-TW/my/settings/api-management">這裡</a> 找到'
        ],
    ],
    'txn' => [
        'switch' => [
            'on' => '開啟',
            'off' => '關閉',
        ],
        'status' => [
            'title' => '當前狀態',
            'liquidation' => '平倉中',
            'force_liquidation' => '強制平倉',
            'position' => '<span class="text-green">持倉中</span>'
        ],
        'current_total_funds' => '當前總資金',
        'initial_total_capital' => '初始總資金',
        'total_record' => '總交易紀錄',
        'total_transaction_times' => '總交易次數',
        'total_profit_times' => '總獲利次數',
        'PL' => '損益(%)',
        'total_number_of_short_times' => '總做空次數',
        'total_loss_times' => '總損失次數',
        'PF' => '獲利因子(PF)',
        'total_number_of_long_times' => '總做多次數',
        'total_profit' => '總獲利',
        'RM' => '報酬期望值(RM)',
        'use_leverage' => '使用槓桿率(%)',
        'total_loss' => '總損失',
        'WR' => '勝率(WR)',
        'average_capital_risk_during_the_period' => '期間平均資金風險(%)',
        'profit_and_loss' => '總損益',
        'KellyFormula' => '最優化風險建議',
        'initial_tradable_total_funds' => '初始可交易總資金',
        'transaction_matching' => '交易配對',
        'initial_capital_risk' => '初始資金風險',
        'lever_switch' => '槓桿開關',
        'transaction_fees' => '交易手續費%',
        'prededuct_handling_fee' => '預先扣除手續費(開/關)',
    ],
    'rec' => [
        'entry' => [
            'position_at' => '應開倉日期時間',
            'avaiable_total_funds' => '交易前可交易總資金',
            'tranding_long_short' => '交易方向(多/空)',
            'funds_risk' => '資金風險(%)',
            'transaction_matching' => '交易配對',
            'leverage' => '槓桿使用',
            'prededuct_handling_fee' => '預先扣除手續費',
            'transaction_fee' => '交易手續費',
            'risk_start_price' => '起始風險價位(止損價位)',
            'hight_position_price' => '開倉價格容差(最高價位)',
            'low_position_price' => '開倉價格容差(最低價位)',
            'entry_price' => 'Entry訊號價位(當時的價位)',
            'funds_risk_amount' => '資金風險金額',
            'risk_start' => '起始風險%(1R)',
            'position_price' => '應開倉部位大小(未加上槓桿量)',
            'leverage_power' => '應使用槓桿(倍數)',
            'leverage_price' => '應使用槓桿(金額)',
            'leverage_position_price' => '應開倉部位大小(加上槓桿量)',
            'position_few' => '應開倉(幾口)',
            'tranding_fee_amount' => '交易手續費',
            'position_few_amount' => '應開倉(預先扣除手續費)',
            'position_price_with_fee' => '應開倉部位大小(預先扣除手續費)',
            'leverage_price_with_fee' => '應使用槓桿(金額)(預先扣除手續費)',
            'leverage_power_with_fee' => '應使用槓桿(倍數)(預先扣除手續費)',
        ],
        'buy' => [
            'position_start_at' => '開倉交易起始日期時間',
            'position_done_at' => '開倉交易完成日期時間',
            'position_duration' => '開倉交易持續時間',
            'position_price' => '實際開倉部位大小',
            'position_price_avg' => '實際開倉價位(均價)', // 所以 若 我們要買進1個BTC 就會用37926.59的價位買了這個 0.1個BTC 然後剩下的0.9個BTC 就會要用更高的價位買進
            'position_quota' => '實際開倉(幾口)',
            'leverage_power' => '實際使用槓桿(倍數)',
            'leverage_price' => '實際使用槓桿(金額)',
            'risk_start' => '實際起始風險%(1R)',
            'transaction_fee' => '交易手續費',
            'funds_risk' => '實際資金風險(金額)',
            'funds_risk_less' => '剩餘資金風險(金額)',
            'target_rate' => '開倉目標達成率',
            'gap_position_at' => '差異項： 開倉交易完成日期時間',
            'gap_position_price' => '差異項： 實際開倉部位大小',
            'gap_position_price_avg' => '差異項： 實際開倉價位(均價)',
            'gap_position_quota' => '差異項： 實際開倉量',
            'gap_leverage_power' => '差異項： 實際使用槓桿(倍數)',
            'gap_leverage_price' => '差異項： 實際使用槓桿(金額)',
            'gap_risk_start' => '差異項： 實際起始風險%(1R)',
            'gap_transaction_fee' => '差異項： 交易手續費',
            'gap_funds_risk' => '差異項： 實際資金風險(金額)',
        ],
        'exit' => [
            'liquidation_at' => '應平倉日期時間',
            'liquidation_price' => 'Exit訊號價位(當時的價位)',
        ],
        'sell' => [
            'liquidation_start_at' => '平倉交易起始日期時間',
            'liquidation_done_at' => '平倉交易結束日期時間',
            'liquidation_duration' => '平倉交易持續時間',
            'liquidation_price_avg' => '平倉價位(均價)',
            'transaction_fee' => '交易手續費',
            'gain_funds' => '取回資金',
            'profit_and_loss' => '損益',
            'profit_and_loss_rate' => '損益率(%)',
            'r_value' => 'R值',
            'sell_total_funds' => '交易後可交易總資金',
            'gap_liquidation_at' => '差異項： 平倉交易結束日期時間',
            'gap_liquidation_price_avg' => '差異項： 平倉價位(均價)',
        ],
    ],
];
