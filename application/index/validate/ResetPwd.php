<?php
namespace app\index\validate;

use think\Validate;

class ResetPwd extends Validate
{

    // 验证规则
    protected $rule = [
        'oldpassword' => 'require|min:6|alphaNum',
        'newpassword' => 'require|min:6|alphaNum',
        'newpasswordcmf' => 'require|min:6|alphaNum',
        'verify' => 'require',
    ];


}
