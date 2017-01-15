<?php
namespace app\index\validate;

use think\Validate;

class Phoneverify extends Validate
{

    // 验证规则
    protected $rule = [
        'phonenum' => 'require|length:11',
        'verify' => 'require|number',
    ];


}
