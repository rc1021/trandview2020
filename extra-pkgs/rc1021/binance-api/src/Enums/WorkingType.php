<?php

namespace BinanceApi\Enums;

use BenSampo\Enum\Enum;
use BenSampo\Enum\Contracts\LocalizedEnum;

/**
 * 条件价格触发类型
 */
final class WorkingType extends Enum implements LocalizedEnum
{
    const MARK_PRICE = 1;
    const CONTRACT_PRICE = 2;
}
