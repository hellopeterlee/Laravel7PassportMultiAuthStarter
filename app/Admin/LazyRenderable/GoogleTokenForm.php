<?php

namespace App\Admin\LazyRenderable;

use App\Models\AdminUser;
use Arr;
use Dcat\Admin\Admin;
use Dcat\Admin\Widgets\Form;
use Google2FA;

class GoogleTokenForm extends Form
{
    private $payload;

    public function __construct($payload = [])
    {
        parent::__construct();
        $this->payload = $payload;
    }

// 处理表单提交请求
    public function handle(array $input)
    {
        $code = $input['code'];
        $key = $input['key'];
        if (Google2FA::verify($code, $key)) {
            $user = Admin::user();
            $adminUser = AdminUser::query()->find($user['id']);
            $adminUser['google_code'] = $key;
            if ($adminUser->save()) {
                return $this->success('绑定成功', admin_url('/'));
            }
        }
        return $this->error('绑定失败');
    }

    // 构建表单
    public function form()
    {
        $imgSrc = route('qrcode', ['text' => Arr::get($this->payload, 'QRCode')]);
        $this->display('qrcode', '扫码绑定')->with(function ($value) use ($imgSrc) {
            return "<img src='{$imgSrc}' />";
        });

        $this->text('key', '手动绑定码')->readOnly()->value(Arr::get($this->payload, 'key'));
        $this->text('code', '输入验证码');
    }

}
