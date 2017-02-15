<?php
namespace app\index\controller;

use think\Controller;
use think\Db;
use app\index\model\Bank_model as Bank_model;
use app\index\model\Borrow_model;
use think\Session;
use app\index\model\Raise_model;
class Index extends Controller
{

    public function base(){
        if(Session::get('userid')==''){
            $url="/index/login/login";
            $this->redirect($url);
        }
    }

    public function test(){

        $a = NULL;

        if(isset($a)){
            echo 11;
        }else{
            echo 22;
        }

    }
    public function index()
    {


        $time = time()-3600*24*50;

        $img = '/public/zhongchou/index/image/roomimg.png';
        $all_borrow = Db::query('select id ,addtime,borrow_account_scale,upimg,name,borrow_account_yes from yyd_borrow where borrow_end_time >="'.$time.'"and status=3 order by addtime desc');


        $this->assign('all_borrow',$all_borrow);
        $this->assign('img',$img);

        return $this->fetch();
    }
    public function self()
    {
        self::base();
        $userid = Session::get('userid');
        $borrow_model = new Borrow_model();
        $input['user_id']=$userid;
        $result=$borrow_model->GetAccountInfo($input);
        //账户总额
        $_list["a_total"]=$borrow_model->IsExist($result["total"])?$result["total"]:0;
        //可用余额
        $_list["a_remain"]=$borrow_model->IsExist($result["balance"])?$result["balance"]:0;
        //冻结余额
        $_list["a_freeze"]=$borrow_model->IsExist($result["frost"])?$result["frost"]:0;
        //待收金额
        $_list["a_wait"]=$borrow_model->IsExist($result["await"])?$result["await"]:0;





        $this->assign('_list',$_list);
        return $this->fetch();
    }
    public function option()
    {
        return $this->fetch();
    }
    public function tender()
    {
        self::base();
        $userid = Session::get('userid');
        $res = Db::query('select a.account,a.addtime,b.name from yyd_borrow_tender a inner join yyd_borrow b on a.borrow_nid = b.borrow_nid  AND a.user_id = "'.$userid.'" order by a.borrow_nid DESC');
//var_dump($res);
//        if(!empty($res)&& is_array($res)){
//            $res = $res['0'];
//        };

        $this->assign("res",$res);
        return $this->fetch();
    }

    public function acount()
    {
        return $this->fetch();
    }
    public function helpcenter()
    {
        return $this->fetch();
    }
    public function contact_us()
    {
        return $this->fetch();
    }
    public function wd()
    {
        return $this->fetch();
    }

    public function tixian()
    {
        self::base();
        $userid = Session::get('userid');
        $bankmodel = new Bank_model();
        $bankCardResult = array();
        $bankCardResult = $bankmodel->getBackRecordById($userid);

        //如果有绑定的卡记录
        if (!empty($bankCardResult) && isset($bankCardResult)) {

            return $this->fetch();

        } //去绑卡
        else {

            $params['actionUrl'] =  urlencode("/index/topup/beiTopUpStep3SmsVerify");
            $params['status'] =  0;
            $params['type'] =  0;

            $json_arr = json_encode($params);
            $url = "/index/topup/out_money_detail_bei_page?value=$json_arr";
            $this->redirect($url);
        }

    }
    public function goto_bangka(){

        $params['actionUrl'] =  urlencode("/index/topup/beiTopUpStep3SmsVerify");
        $params['status'] =  0;
        $params['type'] =  0;

        $json_arr = json_encode($params);
        $url = "/index/topup/out_money_detail_bei_page?value=$json_arr";
        $this->redirect($url);

    }
    public function set()
    {
        self::base();
        $userid = Session::get('userid');
        $bankmodel = new Bank_model();
        $bankCardResult = array();
        $bankCardResult = $bankmodel->getBackRecordById($userid);


        $res = Db::query('select phone from yyd_users_info WHERE user_id = "'.$userid.'"');


        if(!empty($res)&& is_array($res)){
            $res= $res['0'];
        }
        $phone = $res['phone'];
        $is_card = 0;
        //如果有绑定的卡记录
        if (!empty($bankCardResult) && isset($bankCardResult)) {
            $is_card=1;
        }


        $this->assign('phone',$phone);
        $this->assign('is_card',$is_card);
        return $this->fetch();
    }
    public function shiming()
    {
        return $this->fetch();
    }
    public function bangka()
    {
        self::base();
        $userid = Session::get('userid');
        $bankmodel = new Bank_model();
        $bankCardResult = array();
        $bankCardResult = $bankmodel->getBackRecordById($userid);

        if(!empty($bankCardResult)){
            $bankCardResult = $bankCardResult['0'];
            $bankCardResult['bank_card_no'] = '****  ****  ****  ' . substr($bankCardResult['bank_card_no'], -4, 4);
        }



        $this->assign('bankCardResult',$bankCardResult);
        return $this->fetch();
    }
    public function tobangka()
    {
        $params['actionUrl'] =  urlencode("/index/topup/beiTopUpStep3SmsVerify");
        $params['status'] =  0;
        $params['type'] =  0;

        $json_arr = json_encode($params);
        $url = "/index/topup/out_money_detail_bei_page?value=$json_arr";
        $this->redirect($url);
    }
    public function select_city()
    {
        return $this->fetch();
    }
    public function select_bank()
    {
        return $this->fetch();
    }
    public function setpaypass()
    {
        self::base();
        $userid = Session::get('userid');

        $phone = input('phone');
        $verifycode = input('verifycode');
        $paypassword = input('paypassword');

        $res = Db::query('select * from yyd_approve_smslog where phone ="'.$phone.'"order by addtime DESC limit 1');
        if(empty($res)){
            $url = '/index/index/set_paypassword';
            $this->redirect($url);
        }

        if($res['0']['code']==$verifycode){
            Db::query('update yyd_users set paypassword = "'.md5($paypassword).'" where user_id = "'.$userid.'"');


            $url = '/index/index/self';
            $this->redirect($url);

        }


    }

    public function set_paypassword()
    {
        self::base();
        $userid = Session::get('userid');

        $res = Db::query('select bind_mobile from yyd_user_bankcard where user_id ='.$userid);

        $phone = '';

        if(empty($res)){

            $msg = iconv('gbk','utf-8','您还未绑卡!');
            echo "<script charset='utf-8' language='javascript' type='text/javascript' > alert('".$msg."');parent.location.href='/index/index/bangka'; </script>";
            return;

        }
        $phone = $res['0']['bind_mobile'];
        $this->assign('phone',$phone);
        return $this->fetch();
    }
    public function zhuce()
    {
        return $this->fetch();
    }
    public function my_yaoqing()
    {
        self::base();
        $userid = Session::get('userid');

        return $this->fetch();
    }

    public function login()
    {
        return $this->fetch();
    }
    public function xmxq()
    {

        $userid = Session::get('userid');
        $is_card = 0;
        if($userid){
            $bankmodel = new Bank_model();
            $bankCardResult = array();
            $bankCardResult = $bankmodel->getBackRecordById($userid);
            //如果有绑定的卡记录
            if (!empty($bankCardResult) && isset($bankCardResult)) {
                $is_card=1;
            }
        }







        $img = '/public/zhongchou/index/image/tutu_02.png';
        $from = input('f');
        $borrow_id = substr($from,6);

        $curr_borrow = Db::query('select * from yyd_borrow where id = '.$borrow_id);

        $curr_borrow = $curr_borrow['0'];
        $curr_borrow['borrow_other_time'] = $curr_borrow['borrow_end_time']-time();
        $curr_borrow['borrow_other_time']  = $curr_borrow['borrow_other_time'] /(24*3600);

        $this->assign('curr_borrow',$curr_borrow);
        $this->assign('img',$img);
        $this->assign('is_card',$is_card);
        return $this->fetch();
    }
    public function to_chouzi()
    {
        self::base();
        $from = input('f');
        $borrow_id = substr($from,6);
        $curr_borrow = Db::query('select * from yyd_borrow where id = '.$borrow_id);
        $curr_borrow = $curr_borrow['0'];

        $userid = Session::get('userid');
        $borrow_model = new Borrow_model();
        $input['user_id']=$userid;
        $result=$borrow_model->GetAccountInfo($input);
        //账户总额
        $_list["a_total"]=$borrow_model->IsExist($result["total"])?$result["total"]:0;
        //可用余额
        $_list["a_remain"]=$borrow_model->IsExist($result["balance"])?$result["balance"]:0;

        $borrow_nid = $curr_borrow['borrow_nid'];
        $this->assign('_list',$_list);

        $this->assign('borrow_id',$borrow_id);
        $this->assign('borrow_nid',$borrow_nid);
        $this->assign('curr_borrow',$curr_borrow);
        return $this->fetch();
    }

    public function invest()
    {
        self::base();
        $userid = Session::get('userid');

        $input['user_id'] =  $userid;
        $input['money'] = input('tz_account');
        $input['paypassword'] = input('paypassword');

        $input['borrow_nid'] = input('borrow_id');

        $input['query_type'] = "tfgs_coutent";
        $input['is_auto'] = 1;

        $input['Second_limit_money'] ="";
        $input['flow_count']="";

        $bankmodel = new Bank_model();
        $bankCardResult = array();
        $bankCardResult = $bankmodel->getBackRecordById($userid);

        //如果有绑定的卡记录
        if (!empty($bankCardResult) && isset($bankCardResult)) {
            $bankCardResult = $bankCardResult['0'];
            //保存信息 到session
            Session::set('accountInfo', $bankCardResult);

            $borrowmodel = new Borrow_model;
            $result =  $borrowmodel->tenderNow($input);



            $data = array();
            if($result == 'success'){
                $data["status"] =$result;
                $data["url"]= "/qylwap/showmsg/index/title".urlencode("投资信息")."/status/".urlencode("恭喜你，你已投资成功");
                $msg = iconv('gbk','utf-8','恭喜你，你已投资成功');
                echo "<script charset='utf-8' language='javascript' type='text/javascript' > alert(".$msg.");parent.location.href='/index/index/self'; </script>";
                return;

            }else{
                $data["status"] ="fail";
                $data["reason"] =$result;

                $msg = iconv('gbk','utf-8','投资失败，原因:');
                echo "<script charset='utf-8' language='javascript' type='text/javascript' > alert('".$msg."');parent.location.href='/index/index/to_chouzi'; </script>";
                return;

            }




        } //去绑卡
        else {

            $params['actionUrl'] =  urlencode("/index/topup/beiTopUpStep3SmsVerify");
            $params['status'] =  0;
            $params['type'] =  0;

            $json_arr = json_encode($params);
            $url = "/index/topup/out_money_detail_bei_page?value=$json_arr";
            $this->redirect($url);
        }




    }


    public function invest2()
    {
        $userid = Session::get('userid');
        $tz_account = input('tz_account');
        $borrow_id = input('borrow_id');
        $bankmodel = new Bank_model();
        $bankCardResult = array();
        $bankCardResult = $bankmodel->getBackRecordById($userid);
        //如果有绑定的卡记录
        if (!empty($bankCardResult) && isset($bankCardResult)) {
            $bankCardResult = $bankCardResult['0'];
            //保存信息 到session
            Session::set('accountInfo', $bankCardResult);
            //显示绑卡信息
            $bankCardResult['bank_card_no'] = '****  ****  ****  ' . substr($bankCardResult['bank_card_no'], -4, 4);
            $url = "/index/index/acount";
            $this->redirect($url,$bankCardResult);
        } //去绑卡
        else {
            $params['actionUrl'] =  urlencode("/index/topup/beiTopUpStep3SmsVerify");
            $params['status'] =  0;
            $params['type'] =  0;
            $json_arr = json_encode($params);
            $url = "/index/topup/out_money_detail_bei_page?value=$json_arr";
            $this->redirect($url);
        }
    }



    public function faxian()
    {
        return $this->fetch();
    }
    public function jiaoyijilu()
    {
        self::base();
        $userid = Session::get('userid');

        $res = Db::query('select a.remark,a.addtime,a.money from yyd_account_recharge a where user_id = "'.$userid.'"'.'and status = 1 order by addtime desc limit 20');

        $this->assign('res',$res);

        return $this->fetch();
    }
    public function yaoqing()
    {
        return $this->fetch();
    }
    public function to_yaoqing()
    {
        return $this->fetch();
    }




}
