<?php

namespace BinanceApi\Enums;

use BenSampo\Enum\Enum;
use BenSampo\Enum\Contracts\LocalizedEnum;

/**
 *
 */
final class RateLimitType extends Enum implements LocalizedEnum
{
    const REQUEST_WEIGHT = 1; // 单位时间请求权重之和上限
    const ORDERS = 2; // 单位时间下单次数限制
    const RAW_REQUESTS = 3; // 单位时间请求次数上限
}
