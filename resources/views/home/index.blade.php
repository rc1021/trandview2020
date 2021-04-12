<div class="box box-solid">
    <div class="box-body">
        <h2>2021/04/12</h2>
        <ul>
            <li>持倉中收到進場訊號(略過本次訊息)</li>
            <li>沒有足夠的餘額可借就降10%<br>
                <img class="img-responsive" src="/logs/沒有足夠的餘額可借就降10趴.jpg" />
            </li>
        </ul>
        <h2>2021/04/11</h2>
        <ul>
            <li>槓桿交易加入&nbsp;<a href="{{ route('admin.txn.forceLiquidation') }}" rel="nofollow" class="btn btn-danger btn-xs" style="color: #fff;" data-confirm="是否確定要強制平倉?" data-disable-with="執行中..." data-method="post" data-remote="true" data-refresh="500" data-alter="">
                <i class="fa fa-exclamation-triangle"></i>&nbsp;強制平倉
            </a>&nbsp;功能</li>
        </ul>
        <h2>2021/04/04</h2>
        <ul>
            <li>更新命名空間和路由，調整了 Menu / 網址 的位置</li>
        </ul>
        <h2>2021/04/03</h2>
        <ul>
            <li>新的登入頁面</li>
            <li>幫現在的主題加料</li>
            <li>新增每日營收通知訊息</li>
            <li>換應用程式Logo<br>
                for collapse&nbsp;&nbsp;<img src="/logo_lg.png" height="50" /><br>
                for mini&nbsp;&nbsp;<img src="/logo_mini.png" height="50" /></li>
        </ul>
        <h2>2021/04/02</h2>
        <ul>
            <li>介面穿新衣，但還不完整，欲修改介面 style 請到 <a href="https://github.com/rc1021/AdminLTE">https://github.com/rc1021/AdminLTE</a> 下載最新版型</li>
            <li>修正出場時找不到 <code>order</code> 變數的問題</li>
        </ul>
        <h2>2021/04/01</h2>
        <ul>
            <li>下止損單時發現標的幣的數量為 0 時，將記錄當下資產詳情以便未來除錯</li>
            <li>恢復止損單下單時機，等待 1 秒之後才執行</li>
            <li>當遇到 <code>-3045, The system does not have enough asset now.</code> 幣安當下無法借貸時，先重新下單幾次，如果一直無法借貸則立刻使用原有資產下單</li>
        </ul>
        <h2>2021/03/31</h2>
        <ul>
            <li>重新裝潢有 Logo 了</li>
            <li>修改一些文字呈現的內容</li>
            <li>調整槓桿逐倉交易記錄欄位列表，以及錯誤呈現方式</li>
            <li>修正出場訊號來臨時，標的幣數量為 0 發生的錯誤</li>
            <li>修正下單狀態字碼轉換錯誤的問題</li>
            <li>如果是市價賣出, 就用 <code>cummulativeQuoteQty / executedQty</code> 算回當初的賣價</li>
            <li>修正 <code>做多</code> 時，下止損單的時機
                <ul>
                    <li>取消先等待 1 秒再製作止損單的機制</li>
                    <li>改成下止損單前, 先查看總資產有多少數量的標的幣</li>
                </ul>
            </li>
        </ul>
        <h2>2021/03/30</h2>
        <ul>
            <li>新增 Line notify 服務，欲收到持平倉訊息，請到 <a href="{{ route('admin.setting') }}">設定</a>  完成 Line 帳號連動</li>
        </ul>
        <h2>2021/03/29</h2>
        <ul>
            <li>修正下止損單的時機
                <ul>
                  <li><code>做多/做空</code>進場後，先等待 1 秒再製作止損單</li>
                </ul>
            </li>
            <li>修正止損單資產(asset)數量不足問題導致的下單失敗問題，應該用無條件捨去(ex: 0.0012345 -> 0.00123 而不是 0.00124)</li>
            <li>處理訊號過程(包含做多做空下單和止損單)中，發生錯誤時記錄詳細資訊，而不是只有 binance 告訴我們的代碼和訊息而已</li>
            <li>檢查放空止損單如果被觸發，即還清不足的借款</li>
            <li>修正更新公式表記錄錯誤的人員名稱</li>
        </ul>
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
                <li>記得去<a href="https://www.binance.com/zh-TW/my/settings/api-management">Binance API管理</a>取得金鑰後，將資料上傳到<a href="{{ route('admin.txn.keysecret') }}">設定金鑰</a>功能才能有權限進行自動交易</li>
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
