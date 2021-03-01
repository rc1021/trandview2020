<?php

namespace BinanceApi\Enums;

use BenSampo\Enum\Enum;
use BenSampo\Enum\Contracts\LocalizedEnum;

/**
 *
 */
final class IntervalType extends Enum implements LocalizedEnum
{
    const SECOND = 1; // 秒
    const MINUTE = 2; // 分
    const DAY = 3; // 日
}
