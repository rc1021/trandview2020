<?php

if (! function_exists('replaceclass')) {
    function replaceclass($html, array $arr) {
        $data = preg_replace_callback('/class="([^"]+)"/i', function($m) use ($arr) {
            array_push($tmp, json_encode($m[1], 1));
            foreach ($arr as $key => $value) {
                if(strpos($m[1], $key) !== false) {
                    $m[0] = preg_replace(sprintf("/\b%s\b/", $key), $value, $m[0], 1);
                }
            }
            return $m[0];
        }, $html);
        return $data;
    }
}
