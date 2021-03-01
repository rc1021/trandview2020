<?php

namespace BinanceApi\Enums;

use BenSampo\Enum\Enum;
use BenSampo\Enum\Contracts\LocalizedEnum;

/**
 *
 */
final class OrderTypeRespType extends Enum implements LocalizedEnum
{
    const ACK = 1; // 返回速度最快，不包含成交信息，信息量最少
    const RESULT = 2; // 返回速度居中，返回吃单成交的少量信息
    const FULL = 3; // 返回速度最慢，返回吃单成交的详细信息
}
