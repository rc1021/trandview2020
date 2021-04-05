<?php

namespace BinanceApi\Enums;

use BenSampo\Enum\Enum;
use BenSampo\Enum\Contracts\LocalizedEnum;

/**
 *
 */
final class TimeInForce extends Enum implements LocalizedEnum
{
    const GTC = 1; // 成交为止, 订单会一直有效，直到被成交或者取消。
    const IOC = 2; // 无法立即成交的部分就撤销, 订单在失效前会尽量多的成交。
    const FOK = 3; // 无法全部立即成交就撤销, 如果无法全部成交，订单会失效。

    //合約新增
    const GTX = 4; // Good Till Crossing 无法成为挂单方就撤销
}
