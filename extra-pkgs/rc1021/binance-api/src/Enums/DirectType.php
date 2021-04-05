<?php

namespace BinanceApi\Enums;

use BenSampo\Enum\Enum;
use BenSampo\Enum\Contracts\LocalizedEnum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class DirectType extends Enum implements LocalizedEnum
{
    const SHORT = 1;
    const LONG = 2;

    // 合約新增
    const BOTH = 3;
}
