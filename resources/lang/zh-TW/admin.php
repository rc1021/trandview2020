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
            'title' => '設定金鑰',
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
        'order' => [
            'orderId' => '下單編號',
            'type' => '下單類型',
            'transactTime' => '執行時間',
            'created_at' => '建立時間',
            'symbol' => '交易配對',
            'price' => '單價',
            'origQty' => '數量',
            'executedQty' => '成交數量',
            'cummulativeQuoteQty' => '金額',
            'timeInForce' => '下單種類',
            'fills' => '細節',
            'marginBuyBorrowAsset' => '借款單位',
            'marginBuyBorrowAmount' => '借款金額',
            'side' => '方向',
            'status' => '狀態',
        ],
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
        'margin' => [
            'isolated' => [
                'formula' => [
                    'divider_1' => '帳戶VIP等級現況',
                    'divider_2' => '交易條件',
                    'divider_3' => 'Tradingview訊號內容',
                    'divider_4' => '幣安借款安全設置',
                    'divider_5' => '做多',
                    'divider_6' => '做空',
                    'divider_7' => '止盈止損單',
                    'divider_8' => '退場計時',
                    'file_content' => '檔案內容',
                    'title' => '公式表',
                    'id' => '版本號',
                    'user_id' => '修改人',
                    'file_path' => '檔案位置',
                    'file_preview' => '公式表預覽',
                    'created_at' => '創建日期',
                    'updated_at' => '最後修改日期',
                    'commit' => '本次修改註解',
                    'setcol1' => '1.當前總資金(計價幣)',
                    'setcol2' => '2.當前總資金(標的幣)',
                    'setcol3' => '3.交易手續費(maker)',
                    'setcol4' => '4.交易手續費(taker)',
                    'setcol5' => '5.標的幣借款利息(24h)',
                    'setcol6' => '6.計價幣借款利息(24h)',
                    'setcol7' => '7.交易配對',
                    'setcol8' => '8.每次初始可交易總資金(%)',
                    'setcol9' => '9.每次交易資金風險(%)',
                    'setcol10' => '10.槓桿使用',
                    'setcol11' => '11.應開倉日期時間',
                    'setcol12' => '12.交易方向(多/空)',
                    'setcol13' => '13.Entry訊號價位(當時的價位)',
                    'setcol14' => '14.起始風險價位(止損價位)',
                    'setcol15' => '15.開倉價格容差(最高價位)',
                    'setcol16' => '16.開倉價格容差(最低價位)',
                    'setcol17' => '17.指定位階',
                    'setcol18' => '18.做多追加保證金線%',
                    'setcol19' => '19.做空追加保證金線%',
                    'setcol20' => '20.應借入計價幣',
                    'setcol21' => '21.應動用帳戶內計價幣',
                    'setcol22' => '22.成交額(計價幣)',
                    'setcol23' => '23.應減碼(賣掉)多少標的幣',
                    'setcol24' => '24.應借入標的幣',
                    'setcol25' => '25.應賣出帳戶內標的幣',
                    'setcol26' => '26.總共要賣出標的幣',
                    'setcol27' => '27.觸發價',
                    'setcol28' => '28.限價',
                    'setcol29' => '29.生效時間',
                    'setcol30' => '30.最糟可撐幾小時的利息費用',
                ],
            ]
        ],
        'current_total_funds' => '當前總資金',
        'btc_daily_interest' => '標的幣借款利息(24h)',
        'usdt_daily_interest' => '計價幣借款利息(24h)',
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
    ],
    'rec' => [
        'signal' => [
            'txn_order' => [
                'count' => '有 :count 筆資料'
            ],
            'txn_orders' => '下單資料',
            'log' => '開倉紀錄',
            'detail' => '詳情',
            'created_at' => '發生時間',
            'clock' => '間距',
            'message' => '訊號內容',
            'is_valid' => '驗證',
            'error' => '錯誤訊息',
            'txn_type' => '交易執行類別',
            'txn_direct_type' => '交易執行類別',
            'txn_exchange_type' => '交易執行類別',
            'trading_platform_type' => '交易所',
            'symbol_type' => '交易配對',
            'position_at' => '執行日期時間',
            'current_price' => '現價',
            'entry_price' => '交易執行價格',
            'risk_start_price' => '起始風險價位',
            'hight_position_price' => '開倉價格容差(最高價位)',
            'low_position_price' => '開倉價格容差(最低價位)',
            'position_price' => '開倉價格',
            'auto_liquidation_at' => '自動平倉時間',
        ],
    ],
];
