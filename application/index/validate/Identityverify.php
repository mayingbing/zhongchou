<?php
namespace app\index\validate;

use think\Validate;

class Identityverify extends Validate
{

    // 验证规则
    protected $rule = [
        'username' => 'require|min:1|max:50',
        'icnumber' => 'require|min:15|max:18',
    ];


}
