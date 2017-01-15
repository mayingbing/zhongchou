<?php
namespace app\index\validate;

use think\Validate;

class Smsverify extends Validate
{

    // ��֤����
    protected $rule = [
        'phonenum' => 'require|number|number',
        'username' => 'require|min:1|max:40',
        'password' => 'require|min:6|alphaNum',
        'smsverify' => 'require',
        'checkbox' => 'require',
    ];


}

