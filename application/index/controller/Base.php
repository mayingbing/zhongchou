<?php
namespace app\index\controller;
use think\Controller;

use think\Session;

class Base extends Controller{

    public function __construct()
    {
        // 先调用父类的构造函数
        parent::__construct();
        if(Session::get('userid')==''){
            $url="/index/login/login";
            $this->redirect($url);
        }
    }

}