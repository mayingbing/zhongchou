<?php
namespace app\index\controller;
use think\Controller;

use think\Session;
use think\Cookie;
use think\Db;
use app\index\model\Sms as SmsModel;

class Login extends Controller{


    public function login()
    {

        if(Cookie::has('username')&&Cookie::has('password')) {
            $curr_username = Cookie::get('username');
            $curr_password = Cookie::get('password');
            $curr_user = Db::query('select password,user_id from yyd_users where username ="'.$curr_username.'"');


            if($curr_user) {
                $curr_user = $curr_user['0'];

                if ($curr_password == $curr_user['password']) {
                    Session::set('userid', $curr_user['user_id']);
                    Cookie::set('username', $curr_username, 3600 * 48);
                    Cookie::set('password', $curr_password, 3600 * 48);
                    $url = "/index/index/index";
                    $this->redirect($url);
                } else {
                    return $this->fetch();
                }
            }



        }
            return $this->fetch();
    }
    public function zhuce()
    {
        return $this->fetch();
    }
    public function set_password()
    {
        return $this->fetch();
    }
    public function dozuce()
    {
        $username = input('username');
        $phone = input('phone');
        $verifycode = input('verifycode');
        $password = input('password');

        $consistent_user = Db::query('select 1 from yyd_users where username ="'.$username.'"');
        $consistent_userinfo = Db::query('select 1 from yyd_users_info where phone ="'.$phone.'"');

        if(!empty($consistent_user)||!empty($consistent_userinfo)){
            echo "<script charset='utf-8' language='javascript' type='text/javascript' > alert('已注册可直接登录');parent.location.href='/index/login/login'; </script>";
            return;
        }

        $res = Db::query('select * from yyd_approve_smslog where phone ="'.$phone.'"order by addtime DESC limit 1');

        if(empty($res)){
            echo "<script charset='utf-8' language='javascript' type='text/javascript' > alert('注册失败');parent.location.href='/index/login/login'; </script>";
            return;
        }

        if($res['0']['code']==$verifycode){
            Db::query('insert into yyd_users (username,password) VALUE ("'.$username.'","'.md5($password).'")');
            $result = Db::query('select last_insert_id()');
            $user_id = $result['0']['last_insert_id()'];

            Db::query('insert into yyd_users_info (user_id,phone) VALUE ("'.$user_id.'","'.$phone.'")');

            Session::set('userid',$user_id);

            $url = '/index/index/index';
            $this->redirect($url);

        }

    }
    public function doreset(){

        $smsmodle = new SmsModel;

        $phonenum = input('phone');
        $captcha = input('verifycode');
        $user_password= input('password');


        $consistent_users = Db::query('SELECT * from yyd_users_info where phone ="'.$phonenum.'"');
        $consistent_users = $consistent_users['0'];
        $consistent_sms = $smsmodle::get(['phone' => $phonenum]);

            if($captcha==$consistent_sms['code']){
                if(!empty($consistent_users)){
                    $userid = $consistent_users['user_id'];
                    $passwordmd5 = md5($user_password);
                    $res = Db::query('update yyd_users set password ="'.$passwordmd5.'"where user_id ="'.$userid.'"');

                        echo "<script charset='utf-8' language='javascript' type='text/javascript' > alert('修改成功');parent.location.href='/index/login/login'; </script>";
                        return;

                }else{
                    echo "<script charset='utf-8' language='javascript' type='text/javascript' > alert('该手机还没注册');parent.location.href='/index/login/zhuce'; </script>";
                    return;
                }

            }else{
                echo "<script charset='utf-8' language='javascript' type='text/javascript' > alert('验证码错误');parent.location.href='/index/login/set_password'; </script>";
                return;
            }




    }
    public function dologin(){

        $username = input('username');

        $consistent_user = Db::query('select * from yyd_users where username ="'.$username.'"');

        if(!empty($consistent_user)){

            $consistent_user = $consistent_user['0'];

            if(md5(input('password'))==$consistent_user['password']) {
                Session::set('userid',$consistent_user['user_id']);
                Cookie::set('username',$consistent_user['username'],3600*48);
                Cookie::set('password',$consistent_user['password'],3600*48);
                $url = "/index/index/index";
                $this->redirect($url);
                return;
            }else{
                echo "<script charset='utf-8' language='javascript' type='text/javascript' > alert('密码错误');parent.location.href='/index/login/login'; </script>";
                return;
            }
        }

        echo "<script charset='utf-8' language='javascript' type='text/javascript' > alert('用户名未注册');parent.location.href='/index/login/login'; </script>";
        return;

    }
    public function isnewusername(){

        $isnewname = input('username');
        $consistent_user = Db::query('select 1 from yyd_users where username ="'.$isnewname.'"');
        if(empty($consistent_user)){
            echo json_encode(array("code" => 0, "msg" => '用户名可用'));
            die;
        }else{
            echo json_encode(array("code" => 1, "msg" => '用户名已注册'));
            die;
        }
    }

//发送短信
    public function sendsms()
    {
        $data['tel'] = input('tel');
        $sms = new SmsModel;

        if (Session::get('smscode_time') + 60 > strtotime(date("Y-m-d H:i:s")) && Session::get('smscode_phone') == $data['tel']) {
            echo json_encode(array("code" => 0, "msg" => '请过1分钟后再申请'));
            die;
        } else
            if (empty($data['tel'])) {
                echo json_encode(array("code" => 0, "msg" => '手机号不能为空'));
                die;
            } else {
                if ($sms->check_phonenum($data['tel'])) {
                    $smsdata = array();
                    Session::set('smscode_time', strtotime(date("Y-m-d H:i:s")));
                    Session::set('smscode_othertime', ('smscode_time') - strtotime(date("Y-m-d H:i:s")));
                    Session::set('smscode_phone', $data['tel']);
                    //发送信息给客户
                    $smsdata['phone'] = $data['tel'];
                    $smsdata['status'] = 1;
                    $smsdata['type'] = 1;
                    $smsdata['code'] = rand(100000, 999999);
                    $smsdata['contents'] = "验证码:" . $smsdata['code'] . "。您正在进行手机注册操作，请不要把验证码泄露给任何人。【91众筹】";
                    $smsresult = $sms->SendSMS($smsdata);
                    //
                    if ($smsresult > 0) {
                        echo json_encode(array("code" => 1, "msg" => '验证短信已发送，请查收！'));
                        $newsms['phone'] = $data['tel'];
                        $newsms['contents'] = $smsdata['contents'];
                        $newsms['addtime'] = time();
                        $newsms['code'] = $smsdata['code'];
                        //添加或更新该数据
                        $curr_sms = $sms->where('phone', $data['tel'])->find();

                        if (!empty($curr_sms)) {
                            $newsms['id'] = $curr_sms['id'];
                            $sms->update($newsms);
                        } else {
                            $sms->save($newsms);
                        }
                        return;
                    } else {
                        Session::set('smscode_username', "");
                        Session::set('smscode_time', Session::get('smscode_time') - 120);

                        echo json_encode(array("code" => 0, "msg" => '验证短信发送失败，请联系客服！'));
                        return;
                    }
                } else {
                    echo json_encode(array("code" => 0, "msg" => '手机号码格式不正确'));
                    die;
                }
            }
    }
    public function quit(){
        Session::set('userid','');
        Session::set('accountInfo','');
        Cookie::set('username','',60);
        Cookie::set('password','',60);
        $url = '/index/login/login';
        $this->redirect($url);
    }
}