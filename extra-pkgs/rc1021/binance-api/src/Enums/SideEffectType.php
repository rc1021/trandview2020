<?php

namespace BinanceApi\Enums;

use BenSampo\Enum\Enum;
use BenSampo\Enum\Contracts\LocalizedEnum;

/**
 *
 */
final class SideEffectType extends Enum implements LocalizedEnum
{
    const NO_SIDE_EFFECT = 1; // 普通交易订单
    const MARGIN_BUY = 2; // 自动借款交易订单
    const AUTO_REPAY = 3; // 自动还款交易订单
}
