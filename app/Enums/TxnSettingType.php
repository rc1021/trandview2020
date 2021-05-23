<?php

namespace App\Enums;

use BenSampo\Enum\Enum;
use BenSampo\Enum\Contracts\LocalizedEnum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class TxnSettingType extends Enum implements LocalizedEnum
{
    const Spot = 1;
    const Margin = 2;
    const Feature = 3;
}
