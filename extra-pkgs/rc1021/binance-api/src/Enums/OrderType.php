<?php

namespace BinanceApi\Enums;

use BenSampo\Enum\Enum;
use BenSampo\Enum\Contracts\LocalizedEnum;

/**
 *
 */
final class OrderType extends Enum implements LocalizedEnum
{
    const LIMIT = 1; // 限价单
    const MARKET = 2; // 市价单
    const STOP_LOSS = 3; // 止损单
    const STOP_LOSS_LIMIT = 4; // 限价止损单
    const TAKE_PROFIT = 5; // 止盈单
    const TAKE_PROFIT_LIMIT = 6; // 限价止盈单
    const LIMIT_MAKER = 7; // 限价只挂单
}
