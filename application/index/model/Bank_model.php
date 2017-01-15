<?php
namespace app\index\model;

use think\Model;

use think\Db;
use app\index\model\Borrow_model;
use app\index\model\Sms as Sms_model;
use app\index\model\Email;
class Bank_model extends Model{

    public function db_fetch_array($sql)
    {
        $res = Db::query($sql);
        $_res = "";
        if(!empty($res)){
            if (is_array($res['0'])) {
                foreach ($res['0'] as $key => $value)
                    $_res[$key] = $value;
            }
        }

        return $_res;
    }

    /*
     * 查询  bank 记录表信息
     */
    public  function getBackRecordById($user){
        $sql = "select * from yyd_user_bankcard where user_id =$user";
        //$result =  Db::query($sql);
        return Db::query($sql);
    }

    /*
     * 插入绑卡信息
     */
    public  function insertBackRecord($data)
    {
        $sql = "insert into  yyd_user_bankcard set user_id='{$data['userid']}',real_name='{$data['real_name']}', id_type='{$data['cert_type']}',id_no='{$data['cert_no']}',
         bank_card_code='{$data['default_bank']}', bank_card_branch='',bank_card_no='{$data['bank_card_no']}',
         bind_mobile='{$data['card_bind_mobile_phone_no']}',remark=''";
         Db::query($sql);
        return  Db::query('select last_insert_id()');
    }

    /*
     * 解绑银行卡
     */
    public  function  removeBackRecordById($user){
        $sql = "delete from yyd_user_bankcard where user_id =$user";
        return Db::query($sql);
    }

    /*
     * 检查可用余额
     */
    public  function  checkUserMoney($user){
        $sql = "select * from yyd_account where user_id =$user";
        return Db::query($sql);
    }
}