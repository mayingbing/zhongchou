<?php
namespace app\index\validate;

use think\Validate;

class Mailverify extends Validate
{

    // ��֤����
    protected $rule = [
        'email' => 'require|email',
    ];


}
