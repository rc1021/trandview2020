<?php

namespace BinanceApi\Enums;

use BenSampo\Enum\Enum;
use BenSampo\Enum\Contracts\LocalizedEnum;

/**
 *
 */
final class OrderStatusType extends Enum implements LocalizedEnum
{
    const NEW = 1; // 新订单
    const PARTIALLY_FILLED = 2; // 部分订单被成交
    const FILLED = 3; // 订单完全成交
    const CANCELED = 4; // 用户撤销了订单
    const PENDING_CANCEL = 5; // 撤销中(目前并未使用)
    const REJECTED = 6; // 订单没有被交易引擎接受，也没被处理
    const EXPIRED = 7; // 订单被交易引擎取消, 比如 LIMIT FOK 订单没有成交 市价单没有完全成交 强平期间被取消的订单 交易所维护期间被取消的订单
}
