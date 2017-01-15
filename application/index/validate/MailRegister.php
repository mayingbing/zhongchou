<?php
namespace app\index\validate;

use think\Validate;

class mailRegister extends Validate
{

    // ��֤����
    protected $rule = [
        'email' => 'require|email',
        'username' => 'require|min:1|max:40',
        'password' => 'require|min:6|alphaNum',
        'verify' => 'require',
        'checkbox' => 'require',
    ];


}
