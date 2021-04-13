<?php

if (! function_exists('ceil_dec')) {
    //無條件進位
    function ceil_dec($v, $precision) : float{
        $c = pow(10, $precision);
        return ceil($v*$c)/$c;
    }
}
if (! function_exists('floor_dec')) {
    //無條件捨去
    function floor_dec($v, $precision) : float{
        $c = pow(10, $precision);
        return floor($v*$c)/$c;
    }
}
