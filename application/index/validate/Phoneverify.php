<?php
namespace app\index\validate;

use think\Validate;

class Phoneverify extends Validate
{

    // ��֤����
    protected $rule = [
        'phonenum' => 'require|length:11',
        'verify' => 'require|number',
    ];


}
