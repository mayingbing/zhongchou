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

    public function getcontents(){

        $ch = curl_init();

        $file = fopen('./test.txt','w');
        // 设置 url
        curl_setopt($ch,CURLOPT_URL,"http://php.net/manual/en/langref.php");
        // 返回结果，而不是输出它
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,0);
        curl_setopt($ch,CURLOPT_HEADER,0);

//        curl_setopt($ch,CURLOPT_FILE,$file);


        $res = curl_exec($ch);

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($res, 0, $header_size);
        $body = substr($res, $header_size);

        fclose($file);

        if ($res === FALSE) {
            echo "cURL Error: " . curl_error($ch);
        }



        curl_close($ch);
//var_dump($body);
    }



}