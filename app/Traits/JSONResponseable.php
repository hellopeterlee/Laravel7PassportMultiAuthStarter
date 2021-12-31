<?php
/**
 * Created by PhpStorm.
 * Date: 2021/12/30 09:55
 */

namespace App\Traits;

trait JSONResponseable
{
    function ok($data = [], $msg = '操作成功'): \Illuminate\Http\JsonResponse
    {
        return response()->json(ok($data, $msg), 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    function fail($msg = '操作失败', $code = -1): \Illuminate\Http\JsonResponse
    {
        return response()->json(fail($msg, $code), 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
