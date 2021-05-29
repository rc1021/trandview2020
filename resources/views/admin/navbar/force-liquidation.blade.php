<div class="btn-group" data-toggle="buttons">
    <a href="{{ route('admin.txn.forceLiquidation', ['pair' => $pair]) }}"
        rel="nofollow"
        class="btn btn-danger btn-xs"
        style="color: #fff;"
        data-confirm="是否確定要強制平倉?"
        data-disable-with="執行中..."
        data-method="post"
        data-remote="true"
        data-refresh="500"
        data-alter>
        <i class="fa fa-exclamation-triangle"></i>&nbsp;強制平倉
    </a>
</div>
