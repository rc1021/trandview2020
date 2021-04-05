<?php

namespace BinanceApi\Enums;

use BenSampo\Enum\Enum;
use BenSampo\Enum\Contracts\LocalizedEnum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class ContractStatusType extends Enum implements LocalizedEnum
{
    const PENDING_TRADING = 1; // 待上市
    const TRADING = 2; // 交易中
    const PRE_DELIVERING = 3; // 预交割
    const DELIVERING = 4; // 交割中
    const DELIVERED = 5; // 已交割
    const PRE_SETTLE = 6; // 预结算
    const SETTLING = 7; // 结算中
    const CLOSE = 8; // 已下架
}
