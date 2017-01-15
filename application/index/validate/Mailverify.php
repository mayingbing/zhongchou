<?php
namespace app\index\validate;

use think\Validate;

class Mailverify extends Validate
{

    // 验证规则
    protected $rule = [
        'email' => 'require|email',
    ];


}
