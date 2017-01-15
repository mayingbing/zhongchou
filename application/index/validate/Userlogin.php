<?php
namespace app\index\validate;

use think\Validate;

class Userlogin extends Validate
{

    // 验证规则
    protected $rule = [
        'username' => 'require|min:1|max:50',
        'password' => 'require|min:6|max:50|alphaNum',
    ];



}
