<?php
/**
 * Created by PhpStorm.
 * Date: 2021/12/31 11:52
 */

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Throwable;

/*
| 状态码 | 描述 |
| 404 | 未找到（请求资源不存在） |
| 401 | 未认证 (需要登录) |
| 403 | 没有权限 |
| 400 | 错误的请求（URL 或参数不正确） |
| 422 | 验证失败 |
| 500 | 服务器错误 |
*/

class AppException extends \Exception
{
    private $attachData;

    public function __construct($msg = '', $code = 400, $attachData = false)
    {
        parent::__construct($msg, $code);
        $this->attachData = $attachData;
    }

    /*
     * @param  \Illuminate\Http\Request  $request
     * */
    public function render($request)
    {
        return $request->expectsJson()
            ? $this->prepareJsonResponse($request)
            : $this->prepareResponse($request);
    }

    private function prepareJsonResponse($request): JsonResponse
    {
        return new JsonResponse(
            fail($this->getMessage()),
            $this->getCode(),
            [],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
    }

    private function prepareResponse($request)
    {
        return response()->make($this->getMessage());
//        return response()->view('errors.custom', ['msg' => $this->getMessage()], 500);
    }
}
