<?php

namespace App\Enums;

use BenSampo\Enum\Enum;
use BenSampo\Enum\Contracts\LocalizedEnum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class TxnDirectType extends Enum implements LocalizedEnum
{
    const SHORT = 1;
    const LONG = 2;
}
