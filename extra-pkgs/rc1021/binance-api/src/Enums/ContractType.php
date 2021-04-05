<?php

namespace BinanceApi\Enums;

use BenSampo\Enum\Enum;
use BenSampo\Enum\Contracts\LocalizedEnum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class ContractType extends Enum implements LocalizedEnum
{
    const PERPETUAL = 1; // 永续合约
    const CURRENT_MONTH = 2; // 当月交割合约
    const NEXT_MONTH = 3; // 次月交割合约
    const CURRENT_MONTH_DELIVERING = 4; // 交割中的无效类型
    const NEXT_MONTH_DELIVERING = 5; // 交割中的无效类型
}
