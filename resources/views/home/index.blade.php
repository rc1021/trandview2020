<div class="box box-solid">
    <div class="box-body">
        <h2>2021/03/25</h2>
        <ul>
            <li>修正<code>做空止損單</code>下單失敗問題</li>
        </ul>
        <h2>2021/03/23</h2>
        <ul>
            <li>設定 Tradingview webhook 位址 <a href="{{ route('signal') }}">{{ route('signal') }}</a></li>
            <li>新增限制 IP Address 白名單
              <ul>
                <li>個人帳戶白名單
                    <code>122.116.144.195</code>
                    <code>180.177.29.184</code>
                </li>
                <li>Tradingview 訊號來源
                    <code>52.89.214.238</code>
                    <code>34.212.75.30</code>
                    <code>54.218.53.128</code>
                    <code>52.32.178.7</code>
                </li>
              </ul>
            </li>
            <li>新增交易紀錄</li>
            <li>新增帳戶交易設定
              <ul>
                <li>記得去<a href="https://www.binance.com/zh-TW/my/settings/api-management">Binance API管理</a>取得金鑰後，將資料上傳到<a href="{{ route('admin.keysecret') }}">設定金鑰</a>功能才能有權限進行自動交易</li>
                <li>到<a href="https://www.binance.com/zh-TW/my/settings/api-management">機器人交易設定</a>設定以下資料
                    <code>初始可交易總資金%</code>
                    <code>槓桿開關</code>
                    <code>初始資金風險</code>
                    <code>標的幣借款利息(24h)</code>
                    <code>計價幣借款利息(24h)</code>
                </li>
              </ul>
            </li>
        </ul>
    </div>
</div>
