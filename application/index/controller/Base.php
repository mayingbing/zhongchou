<?php
namespace app\index\controller;
use think\Controller;

use think\Session;

class Base extends Controller{

    public function __construct()
    {
        // �ȵ��ø���Ĺ��캯��
        parent::__construct();
        if(Session::get('userid')==''){
            $url="/index/login/login";
            $this->redirect($url);
        }
    }

}