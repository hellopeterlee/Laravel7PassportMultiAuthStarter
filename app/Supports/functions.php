<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

if (!function_exists('ok')) {
    function ok($data = [], $msg = '操作成功')
    {
        return [
            "code" => 0,
            "msg" => $msg,
            "data" => $data,
        ];
    }
}

if (!function_exists('fail')) {
    function fail($msg = '操作失败', $code = -1)
    {
        return [
            "code" => $code,
            "msg" => $msg,
        ];
    }
}

if (!function_exists('appLog')) {
    function appLog($msg, $context = '')
    {
        Log::channel('app')->info($msg, Arr::wrap($context));
    }
}
