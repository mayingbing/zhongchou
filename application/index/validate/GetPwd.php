<?php
namespace app\index\validate;

use think\Validate;

class GetPwd extends Validate
{

    // ��֤����
    protected $rule = [
        'email' => 'require|email',
        'username' => 'require|min:5|max:40',
    ];


}
