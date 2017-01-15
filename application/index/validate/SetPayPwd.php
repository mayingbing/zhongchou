<?php
namespace app\index\validate;

use think\Validate;

class SetPayPwd extends Validate
{

    // 验证规则
    protected $rule = [
        'newpasswordcmf' => 'require|min:6|alphaNum',
        'newpassword' => 'require|min:6|alphaNum',
        'verify' => 'require',
    ];


}
