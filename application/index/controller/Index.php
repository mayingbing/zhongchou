<?php
namespace app\index\controller;

use think\Controller;
use think\Db;
use app\index\controller\Base;
use app\index\model\Bank_model as Bank_model;
use app\index\model\Borrow_model;
use think\Session;

class Index extends Base
{



    public function index()
    {
        $all_borrow = Db::query('select id ,borrow_account_scale,upimg,name,borrow_account_yes from yyd_borrow ');
        $this->assign('all_borrow',$all_borrow);
        return $this->fetch();
    }
    public function self()
    {
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
    public function acount()
    {
        return $this->fetch();
    }
    public function tixian()
    {
        return $this->fetch();
    }
    public function set()
    {
        return $this->fetch();
    }
    public function shiming()
    {
        return $this->fetch();
    }
    public function bangka()
    {
        $userid = Session::get('userid');
        $bankmodel = new Bank_model();
        $bankCardResult = array();
        $bankCardResult = $bankmodel->getBackRecordById($userid);
        $bankCardResult = $bankCardResult['0'];
        $bankCardResult['bank_card_no'] = '****  ****  ****  ' . substr($bankCardResult['bank_card_no'], -4, 4);
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
    public function set_password()
    {
        return $this->fetch();
    }
    public function zhuce()
    {
        return $this->fetch();
    }
    public function login()
    {
        return $this->fetch();
    }
    public function xmxq()
    {
        $from = input('f');
        $borrow_id = substr($from,6);

        $curr_borrow = Db::query('select * from yyd_borrow where id = '.$borrow_id);

        $curr_borrow = $curr_borrow['0'];
        $curr_borrow['borrow_other_time'] = $curr_borrow['borrow_end_time']-time();
        $curr_borrow['borrow_other_time']  = $curr_borrow['borrow_other_time'] /(24*3600);

        $this->assign('curr_borrow',$curr_borrow);
        return $this->fetch();
    }
    public function to_chouzi()
    {

        $from = input('f');
        $borrow_id = substr($from,6);
        $curr_borrow = Db::query('select * from yyd_borrow where id = '.$borrow_id);
        $curr_borrow = $curr_borrow['0'];
        var_dump($curr_borrow);
        $this->assign('borrow_id',$borrow_id);
        $this->assign('curr_borrow',$curr_borrow);
        return $this->fetch();
    }

    public function invest()
    {
        $userid = Session::get('userid');

        $tz_account = input('tz_account');
        $zf_account = input('zf_account');
        $borrow_id = input('borrow_id');

        $bankmodel = new Bank_model();
        $bankCardResult = array();
        $bankCardResult = $bankmodel->getBackRecordById($userid);
        $bankCardResult = $bankCardResult['0'];
        //如果有绑定的卡记录
        if (!empty($bankCardResult) && isset($bankCardResult)) {
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
