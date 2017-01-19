<?php
namespace app\index\model;
use think\Model;
use think\Db;
use app\index\lib\Utility;

class Raise_model extends Model{

    // 设置当前模型对应的完整数据表名称
    protected $table = 'yyd_raise';
    // 设置数据表主键
    protected $pk    = 'id';


    function IsExist($val)
    {
        if (isset($val)) {
            return $val;
        } else {
            return false;
        }
    }
    public function db_fetch_array($sql){

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
    public function db_fetch_arrays($sql)
    {
        // echo $sql;
        $query = Db::query($sql);
        $i = 0;
        $_res = array();
        $res = $query;
        if (empty($query)) {
            die(Db::_error_message());
        }

        foreach($res as $row){
            foreach ($row as $key =>$value){
                $_res[$i][$key] = $value;
            }
            $i++;
        }
        $query->free_result();
        return $_res;
    }


    public function getRaiseList($data = array())
    {

        if (isset($data['query_id'])&&$this->IsExist($data['query_id']) != false){
            $_sql = "where p1.id=".$data['query_id'];
        }
        else
        {
            $_sql = "where 1=1 ";
        }
        //搜索借款名称
        if (isset($data['raise_name'])&&$this->IsExist($data['raise_name']) != false){
            $_sql .= " and p1.`raise_name` like '%".urldecode($data['raise_name'])."%'";
        }
        //判断添加时间开始
        if (isset($data['dotime1'])&&$this->IsExist($data['dotime1']) != false){
            $dotime1 = ($data['dotime1']=="request")?$_REQUEST['dotime1']:$data['dotime1'];
            if ($dotime1!=""){
                $_sql .= " and p1.addtime > ".get_mktime($dotime1);
            }
        }
        //判断添加时间结束
        if (isset($data['dotime2'])&&$this->IsExist($data['dotime2'])!=false){
            $dotime2 = ($data['dotime2']=="request")?$_REQUEST['dotime2']:$data['dotime2'];
            if ($dotime2!=""){
                $_sql .= " and p1.addtime < ".get_mktime($dotime2);
            }
        }
        //判断借款状态
        if (isset($data['status'])&&$this->IsExist($data['status'])!= false){
            $_sql .= " and p1.status = '{$data['status']}' ";
        }

        //判断是否伪删除
        if(isset($data['is_hide']) && $data['is_hide'] == "0"){
            $_sql .= " and p1.is_hide = 0";
        }


        //判断借款状态
        if (isset($data['raise_type'])&&$this->IsExist($data['raise_type'])!= false){
            $_sql .= " and p1.raise_type = '{$data['raise_type']}' ";
        }

        //判断借款状态
        if (!empty($data['mapping_id'])){
            $_sql .= " and p1.mapping_id = '{$data['mapping_id']}' ";
        }


        $_select = "p1.*,p3.fileurl,datediff(FROM_UNIXTIME(p1.end_time, '%Y-%m-%d'),now()) as end_day";
        $sql = "select SELECT from yyd_raise as p1
		         left join yyd_users_upfiles as p3 on p3.id=p1.litpic
				 SQL ORDER LIMIT";

        //判断总的条数
        $newsql=str_replace(array('SELECT', 'SQL', 'ORDER', 'LIMIT'), array('count(1) as num', $_sql,'', ''), $sql);
        //echo $newsql;
//        $row = Db::getNum($newsql);
        $ret =Db::query($newsql);

        $temp= $ret;
        $total = intval($temp["0"]["num"]);
//        $total = intval($row['num']);
        //$_order=$data["order"];
        $_order = " order by p1.`addtime` desc ";

        //分页返回结果
        $data['page'] = empty($data['page'])?1:$data['page'];
        $data['epage'] = empty($data['epage'])?10:$data['epage'];

        $total_page = ceil($total / $data['page']);
        $_limit = " limit ".($data['epage'] * ($data['page'] - 1)).", {$data['epage']}";

        $sql1=str_replace(array('SELECT', 'SQL','ORDER', 'LIMIT'), array($_select,$_sql,$_order, $_limit), $sql);
        //echo $sql1;
        $tmp =Db::query($sql1);

        $res =$tmp;
        $i=0;
        $utility = new Utility();
        $list = array();
        foreach ($res as $rows => $row){

            $list[$i]['i_id']= $row['id'];
            $list[$i]['i_tit']=$row['raise_name'];
            //3.raise_account
            $list[$i]['i_amount']= $row['raise_account'];
            $status= $utility->getRaiseStatus($row['status'], $row['end_day']);
            $list[$i]["i_status"] = $status;
            $list[$i]['invest_amount_yes']= $row['raise_account_yes'];
            //raise_period
            $list[$i]['i_period']=$row['raise_period']."天";
            $list[$i]['i_progress']=$row['raise_account_scale'];
            $list[$i]['i_zannum']=$row['tender_times'];
            if(intval($row['end_day'])>=0 )
                $tmp = $row['end_day']."天";
            else
                $tmp = "已结束";

            $list[$i]['i_endday']=$tmp;
            $list[$i]['i_img']='http://www.91toufang.com'.$row['fileurl'];
            $list[$i]['typecase']="raise";

            $i++;
        }
        $result = array('list' => $list?$list:array(),'total' => $total,'page' =>  intval($data['page']),'epage' => intval ($data['epage']),'total_page' => $total_page);
        return $result;
    }



    public  function getRaiseOne($data = array()){

        if (!self::IsExist($data['query_id'])) return "articles_id_empty";
        if ($data['hits_status']==1){
            $sql ="update yyd_raise set hits =hits+1 where id={$data['id']}";
            Db::query($sql);
        }
        $_sql = " where p1.id={$data['query_id']}";
        if ($data['user_id']!=""){
            $_sql .= " and p1.user_id='{$data['user_id']}'";
        }
        $sql = "select p1.*,p2.username,p3.fileurl,datediff(FROM_UNIXTIME(p1.end_time, '%Y-%m-%d'),now()) as end_day from yyd_raise as p1
				left join yyd_users as p2 on p2.user_id=p1.user_id
				left join yyd_users_upfiles as p3 on p3.id=p1.litpic
				{$_sql}";

        $result = $this->db_fetch_array($sql);
        if ($result==false) return "articles_not_exist";
        return $result;
    }
    public function getRaiseTenderList($data = array())
    {
        $_sql = "where 1=1 ";
        //搜索借款名称
        if (self::IsExist($data['query_id']) != false){
            $_sql .= " and p1.`raise_id` = '".$data['query_id']."'";
        }
        //搜索借款名称
        if (self::IsExist($data['user_id']) != false){
            $_sql .= " and p1.`user_id` = '".$data['user_id']."'";
        }
        if (self::IsExist($data['username']) != false){
            $_sql .= " and p2.`username` like '%".urldecode($data['username'])."%'";
        }
        if (self::IsExist($data['raise_name']) != false){
            $_sql .= " and p3.`raise_name` like '%".urldecode($data['raise_name'])."%'";
        }
        //判断添加时间开始
        if (self::IsExist($data['dotime1']) != false){
            $dotime1 = ($data['dotime1']=="request")?$_REQUEST['dotime1']:$data['dotime1'];
            if ($dotime1!=""){
                $_sql .= " and p1.addtime > ".get_mktime($dotime1);
            }
        }
        //判断添加时间结束
        if (self::IsExist($data['dotime2'])!=false){
            $dotime2 = ($data['dotime2']=="request")?$_REQUEST['dotime2']:$data['dotime2'];
            if ($dotime2!=""){
                $_sql .= " and p1.addtime < ".get_mktime($dotime2);
            }
        }
        //判断借款状态
        if (self::IsExist($data['status'])!=""){
            $_sql .= " and p1.status = '{$data['status']}' ";
        }

        $_order = " order by p1.`addtime` desc ";

        $_select = "p1.*,p2.username,p3.raise_name,p3.raise_account,p3.raise_account_yes,p3.raise_period,p3.raise_type,p3.status as raise_status,datediff(FROM_UNIXTIME(p3.end_time, '%Y-%m-%d'),now()) as end_day";
        $sql = "select SELECT from yyd_raise_tender as p1
			    left join yyd_users as p2 on p2.user_id=p1.user_id
				left join yyd_raise as p3 on p3.id=p1.raise_id
				 SQL ORDER LIMIT
				";
        //是否显示全部的信息
        /*if (IsExist($data['limit'])!=false){
            if ($data['limit'] != "all"){ $_limit = "  limit ".$data['limit']; }
             $list =$mysql->db_fetch_arrays(str_replace(array('SELECT', 'SQL', 'ORDER', 'LIMIT'), array($_select, $_sql, $_order, $_limit), $sql));
            return	 $list;

        }*/
        //判断总的条数
        $newsql=str_replace(array('SELECT', 'SQL', 'ORDER', 'LIMIT'), array('count(1) as num', $_sql,'', ''), $sql);
        //$total = intval($row['num']);

        $temp =Db::query($newsql);
        $total = $temp->num_rows();

        //分页返回结果
        $data['page'] = !self::IsExist($data['page'])?1:$data['page'];
        $data['epage'] = !self::IsExist($data['epage'])?40:$data['epage'];

        $total_page = ceil($total / $data['epage']);
        $_limit = " limit ".($data['epage'] * ($data['page'] - 1)).", {$data['epage']}";
        $newsql=str_replace(array('SELECT', 'SQL','ORDER', 'LIMIT'), array($_select,$_sql,$_order, $_limit), $sql);
        $list = $this->db_fetch_arrays($newsql);

        return $list;
    }
    //购买债权
    function BuyRaise($data){
        //判断是否是用户的
        $sql = "select * from yyd_raise where  id='{$data['id']}'";
        $result = $this->db_fetch_array($sql);

        //echo "-----------1----------";
//var_dump($result);
        if ($result==false) return  iconv('GB2312', 'UTF-8',"项目支持失败");

        if ($result['status']!=0) return  iconv('GB2312', 'UTF-8',"项目支持失败");

        if($result['end_time']<time())  return  iconv('GB2312', 'UTF-8',"该项目已结束筹资!");

        if($result['raise_account_wait']<$data['account'])  return  iconv('GB2312', 'UTF-8',"您想支持的金额大于了剩余筹资金额！");
        $tender_result = $this->db_fetch_array($sql);

        $result['raise_name']="<a href=/zhongchou/a{$result['id']}.html>{$result['raise_name']}</a>";
        //判断支付密码是否正确
        $sql = "select 1 from yyd_users where user_id='{$data['user_id']}' and paypassword='".md5($data['paypassword'])."'";
        $_result = $this->db_fetch_array($sql);
        if ($_result==false) return  iconv('GB2312', 'UTF-8',"支付密码错误");

        //判断可用金额是否大于购买金额
        //
        $sql = "select * from yyd_account where user_id='{$data['user_id']}'";
        $account_result = $this->db_fetch_array($sql);
//        echo "-----------2---------";
//        var_dump($account_result);
        if ($account_result['balance']<$data['account']) return  iconv('GB2312', 'UTF-8',"余额不足，请充值！");

        if($result['raise_account_wait']==$data['account']) $_sql=",status=1";
        $sql = "update yyd_raise set raise_account_yes=raise_account_yes+{$data['account']},raise_account_scale = 100*(raise_account_yes/raise_account),tender_times=tender_times+1,raise_account_wait=raise_account_wait-{$data['account']} $_sql where id='{$data['id']}'";
        Db::query($sql);

        $sql = "insert into yyd_raise_tender set `addtime` = '".time()."',`addip` = '".self::ip_address()."',`tender_account` = '{$data['account']}',`user_id` = '{$data['user_id']}',`raise_id` = '{$data['id']}',`message` = '{$data['message']}',`status` = 0";
        Db::query($sql);

        $tender_id = Db::insert_id();

        $buyuser=self::GetUsers(array("user_id"=>$data['user_id']));


        $account=$data['account'];



        $log_info["user_id"] = $data['user_id'];//操作用户id
        $log_info["nid"] = "borrow_raise_buy_".$data['user_id']."_".$tender_id;//订单号
        $log_info["money"] = $account;//操作金额
        $log_info["income"] = 0;//收入
        $log_info["expend"] = $account;//支出
        $log_info["balance_cash"] = -$account;//可提现金额
        $log_info["balance_frost"] = 0;//不可提现金额
        $log_info["frost"] = 0;//冻结金额
        $log_info["await"] = 0;//待收金额
        $log_info["type"] = "borrow_raise_buy";//类型
        $log_info["to_userid"] = 0;//付给谁
        $log_info["remark"] = iconv('GB2312', 'UTF-8', "您对[") . $result['raise_name'] . iconv('GB2312', 'UTF-8'," ]项目的支持所付出金额");
        self::AddLog($log_info);

//        echo "-----------3---------";
//        var_dump($log_info);

        $user_log["user_id"] = $data['user_id'];
        $user_log["code"] = "borrow";
        $user_log["type"] = "borrow_raise";
        $user_log["operating"] = "borrow";
        $user_log["article_id"] = $tender_id;
        $user_log["result"] = 1;
        $user_log["content"] = iconv('GB2312', 'UTF-8',"您对[").$result['raise_name'] . iconv('GB2312', 'UTF-8',"]项目的支持成功");
        self::AddUsersLog($user_log);
//        echo "-----------4--------";
//        var_dump($user_log);


        $remind['nid'] = "borrow_raise_yes";
        $remind['receive_userid'] = $data['user_id'];
        $remind['code'] = "borrow";
        $remind['article_id'] =$tender_id;
        $remind['title'] = iconv('GB2312', 'UTF-8',"您对[")+$tender_result['raise_name'] . iconv('GB2312', 'UTF-8',"]项目支持成功");
        $remind['content'] =iconv('GB2312', 'UTF-8', "您在".date("Y-m-d",time())."成功支持[{$tender_result['raise_name']}") . iconv('GB2312', 'UTF-8',"]该项目");
        // var_dump($remind);
        self::sendRemind($remind);
//        echo "-----------5--------";
//        var_dump($remind);
        return $tender_id;
    }

    function AddUsersLog($data){

        $sql = "insert into yyd_users_log set  addtime='".time()."',addip='".self::ip_address()."'";
        foreach($data as $key => $value){
            $sql .= ",`$key` = '$value'";
        }
        Db::query($sql);
    }

    function ip_address()
    {
        if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
            $ip_address = $_SERVER["HTTP_CLIENT_IP"];
        } else if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip_address = array_pop(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
        } else if (!empty($_SERVER["REMOTE_ADDR"])) {
            $ip_address = $_SERVER["REMOTE_ADDR"];
        } else {
            $ip_address = '';
        }
        return $ip_address;
    }
    function GetUsers($data = array()){

        $_sql = " where 1=1 ";
        //判断用户id
        if (self::IsExist($data['user_id']) != false){
            $_sql .= " and p1.`user_id`  = '{$data['user_id']}'";
        }

        //判断是否搜索用户名
        elseif (self::IsExist($data['username']) != false){
            $_sql .= " and p1.`username` like '%{$data['username']}%'";
        }

        //判断是否搜索邮箱
        elseif (self::IsExist($data['email']) != false){
            $_sql .= " and p1.`email` like '%{$data['email']}%'";
        }

        $_select = "*";
        $sql = "select SELECT  from yyd_users as p1 SQL";
        return $this->db_fetch_array(str_replace(array('SELECT', 'SQL'), array($_select, $_sql), $sql));
        return $result;
    }


    function AddLog($data = array()){
        //第一步，查询是否有资金记录
        $sql = "select * from yyd_account_log where `nid` = '{$data['nid']}'";
        $result = $this -> db_fetch_array($sql);
        if (self::IsExist($result) && $result['nid']!="") return "account_log_nid_exiest";
        //第二步，查询原来的总资金
        $sql = "select * from yyd_account where user_id='{$data['user_id']}'";
        $result = $this->db_fetch_array($sql);
        if ($result==false){
            $sql = "insert into yyd_account set user_id='{$data['user_id']}',total=0";
            Db::query($sql);
            $sql = "select * from yyd_account where user_id='{$data['user_id']}'";
            $result = $this->db_fetch_array($sql);
        }

        //第三步，加入用户的财务记录
        $sql = "insert into yyd_account_log set ";
        $sql .= "nid='{$data['nid']}',";
        $sql .= "user_id='{$data['user_id']}',";
        $sql .= "type='{$data['type']}',";
        $sql .= "money='{$data['money']}',";
        $sql .= "remark='{$data['remark']}',";
        $sql .= "to_userid='{$data['to_userid']}',";

        $sql .= "balance_cash_new='{$data['balance_cash']}',";
        $sql .= "balance_cash_old='{$result['balance_cash']}',";
        $sql .= "balance_cash=balance_cash_new+balance_cash_old,";

        $sql .= "balance_frost_new='{$data['balance_frost']}',";
        $sql .= "balance_frost_old='{$result['balance_frost']}',";
        $sql .= "balance_frost=balance_frost_new+balance_frost_old,";

        $sql .= "balance_new=balance_cash_new+balance_frost_new,";
        $sql .= "balance_old='{$result['balance']}',";
        $sql .= "balance=balance_new+balance_old,";

        $sql .= "income_new='{$data['income']}',";
        $sql .= "income_old='{$result['income']}',";
        $sql .= "income=income_new+income_old,";

        $sql .= "expend_new='{$data['expend']}',";
        $sql .= "expend_old='{$result['expend']}',";
        $sql .= "expend=expend_new+expend_old,";

        $sql .= "frost_new='{$data['frost']}',";
        $sql .= "frost_old='{$result['frost']}',";
        $sql .= "frost=frost_new+frost_old,";

        $sql .= "await_new='{$data['await']}',";
        $sql .= "await_old='{$result['await']}',";
        $sql .= "await=await_new+await_old,";

        $sql .= "total_old='{$result['total']}',";
        $sql .= "total=balance+frost+await,";
        $sql .=" `addtime` = '".time()."',`addip` = '".self::ip_address()."'";
        Db::query($sql);
        $id = Db::insert_id();

        $sql = "select * from yyd_account_log where user_id='{$data['user_id']}' and id='{$id}'";
        $result = $this->db_fetch_array($sql);

        //第四步，更新用户表
        $sql = "update yyd_account set income={$result['income']},expend='{$result['expend']}',";
        $sql .= "balance_cash={$result['balance_cash']},balance_frost={$result['balance_frost']},";
        $sql .= "frost={$result['frost']},";
        $sql .= "await={$result['await']},";
        $sql .= "balance={$result['balance']},";
        $sql .= "total={$result['total']}";
        $sql .=" where user_id='{$data['user_id']}'";
        Db::query($sql);

        //第三步，加入网站的总费用
        $sql = "select * from yyd_account_balance where `nid` = '{$data['nid']}'";
        $result = $this -> db_fetch_array($sql);
        if ($result==false){
            //加入网站的财务表
            $sql = "select * from yyd_account_balance order by id desc";
            $result = $this -> db_fetch_array($sql);
            if ($result==false){
                $result['total'] = 0;
                $result['balance'] = 0;
            }
            $total = $result['total'] + $data['income'] + $data['expend'];
            $sql = "insert into yyd_account_balance set total='{$total}',balance={$result['balance']}+".$data['income']."-".$data['expend'].",income='{$data['income']}',expend='{$data['expend']}',type='{$data['type']}',`money`='{$data['money']}',user_id='{$data['user_id']}',nid='{$data['nid']}',remark='{$data['remark']}', `addtime` = '".time()."',`addip` = '".self::ip_address()."'";
            Db::query($sql);
        }

        //第三步，加入用户的总费用
        $sql = "select * from yyd_account_users where `nid` = '{$data['nid']}'";
        $result = $this-> db_fetch_array($sql);
        if ($result==false){
            //加入用户的财务表
            $sql = "select * from yyd_account_users where user_id='{$data['user_id']}' order by id desc ";
            $result = $this -> db_fetch_array($sql);
            if ($result==false){
                $result['total'] = 0;
                $result['balance'] = 0;
            }
            $total = $result['total'] + $data['income'] + $data['expend'];
            $sql = "insert into yyd_account_users set total='{$total}',balance={$result['balance']}+".$data['income']."-".$data['expend'].",income='{$data['income']}',expend='{$data['expend']}',type='{$data['type']}',`money`='{$data['money']}',user_id='{$data['user_id']}',nid='{$data['nid']}',remark='{$data['remark']}', `addtime` = '".time()."',`addip` = '".self::ip_address()."',await='{$data['await']}',frost='{$data['frost']}'";
            Db::query($sql);
        }
        return $data['nid'];
    }



    public  function GetNidOne($data = array()){
        $nid = $data['nid'];
        if($nid == "") return "remind_error_nid_empty";
        $sql = "select * from yyd_remind where nid='$nid'";
        return $this->db_fetch_array($sql);
    }
    function AddMessage($data = array()){
        //判断名称是否存在
//        echo "----------34----------";
//        var_dump($data);
        $receive_user = $data['receive_user'];
        $receive_userid = $data['receive_userid'];
        $receive_users = $data['receive_users'];
        $receive_user_type = $data['receive_user_type'];
        $receive_admin_type = $data['receive_admin_type'];
        unset($data['receive_user']);
        unset($data['receive_userid']);
        unset($data['receive_users']);
        unset($data['receive_user_type']);
        unset($data['receive_admin_type']);
        if (!self::IsExist($data['name'])) {
            return "message_name_empty";
        }
        if (!self::IsExist($data['contents'])) {
            return "message_contents_empty";
        }

        //判断标识名是否存在
        if (!self::IsExist($data['type'])) {
            return "message_type_empty";
        }


        if ($data['type']=="user"){
            if($receive_user=="" && $receive_userid=="")
                return "message_receive_user_empty";
            if ($receive_user!=""){
                $sql = "select user_id from yyd_users where username ='{$receive_user}'";
                $result = $this->db_fetch_array($sql);
                if ($result == false)
                    return "message_receive_username_not_exiest";
                $data['receive_value'] = $result['user_id'];
            }elseif ($receive_userid!=""){
                $sql = "select user_id from yyd_users where user_id ='{$receive_userid}'";
                $result = $this->db_fetch_array($sql);
                if ($result == false)
                    return "message_receive_username_not_exiest";
                $data['receive_value'] = $result['user_id'];
            }
            $data['status'] = 1;
            if ($data['status']==""){
                $_data['send_status'] = 2;
            }else{
                $_data['send_status'] = 1;
            }

        }elseif ($data['type']=="users"){
            if($receive_users=="")
                return "message_receive_users_empty";
            $data['receive_value'] = $receive_users;
        }elseif ($data['type']=="user_type"){
            if($receive_user_type=="")
                return "message_receive_user_type_empty";
            $data['receive_value'] = $receive_user_type;
        }elseif ($data['type']=="admin_type"){
            if($receive_admin_type=="")
                return "message_receive_admin_type_empty";
            $data['receive_value'] = $receive_admin_type;
        }

        $sql = "insert into yyd_message set addtime='".time()."',addip='".self::ip_address()."',";
        foreach($data as $key => $value){
            $_sql[] = "`$key` = '$value'";
        }
        Db::query($sql.join(",",$_sql));
        $send_id =  Db::insert_id();

        if ($data['status']==1){
            $_data['send_id'] = $send_id;
            $_data['send_status'] = $data['status'];
            $result = self::SendMessage($_data);
        }
        return 1;
    }
    function SendMessage($data = array()){
        //判断标识名是否存在
        if (!self::IsExist($data['send_id'])) {
            return "message_id_empty";
        }

        //发送状态
        $send_status = $data['send_status'];
        unset($data['send_status']);

        $sql = "select * from yyd_message where id='{$data['send_id']}'";
        $result = $this->db_fetch_array($sql);
        if ($result==false) return "message_empty";
        $receive_value = $result['receive_value'];
        $data['contents'] = $result['contents'];
        $data['name'] = $result['name'];
        $data['type'] = $result['type'];
        $data['send_userid'] = $result['user_id'];
        //判断标识名是否存在
        if (!self::IsExist($data['contents'])) {
            return "message_contents_empty";
        }
        if ($data['type']=="all"){
            $data['user_id'] = 0;
        }
        elseif ($data['type']=="users"){
            $receive_value = explode(",",$receive_value);
            foreach ($receive_value as $key => $value){
                $_receive_value[] = "'".$value."'";
            }
            $receive_value = join(",",$_receive_value);
            $sql = "select user_id,username from yyd_users where username in ({$receive_value})";
            $result = $this->db_fetch_arrays($sql);
            if ($result !=false){
                foreach ($result as $key => $value){
                    $_result[] = $value['user_id'];
                    $_result_username[] = $value['username'];
                }
                $data['receive_id'] = join(",",$_result);
                $data['receive_value'] = join(",",$_result_username);
            }
            $data['user_id'] = 0;
        }
        elseif ($data['type']=="user_type"){
            $data['user_id'] = 0;
            $data['receive_id'] = $receive_value;
        }elseif ($data['type']=="admin_type"){
            $data['user_id'] = 0;
            $data['receive_id'] = $receive_value;
        }elseif ($data['type']=="user"){
            $data['user_id'] = $receive_value;
            $data['receive_id'] = $receive_value;
        }
        //更新短消息
        $sql = "update yyd_message set status='{$send_status}' where id='{$data['send_id']}'";
        Db::query($sql);

        $sql = "insert into yyd_message_receive set addtime='".time()."',addip='".self::ip_address()."',";
        foreach($data as $key => $value){
            $_sql[] = "`$key` = '$value'";
        }
        Db::query($sql.join(",",$_sql));

        return 1;
    }


    function sendRemind($data){
        //是否禁止提醒模块
        //if ($_G['system']['remind_status']==0) return "";
//        echo '---------7---------';
//        var_dump($data);
        $remind_user = array();
        if ($data['receive_user']!=""){
            $data['receive_userid'] = $data['receive_user'];
        }
        //echo '---------8---------';
        $sql = "select remind from yyd_remind_user where user_id={$data['receive_userid']}";
        $result = $this->db_fetch_array($sql);
        if ($result !=false){
            $remind_user = unserialize ($result['remind']);
        }

        $remind_result = self::GetNidOne(array("nid"=>$data['nid']));

        $message_status = isset($remind_user[$data['nid']]['message'])?$remind_user[$data['nid']]['message']:$remind_result['message'];
        $email_status = isset($remind_user[$data['nid']]['email'])?$remind_user[$data['nid']]['email']:$remind_result['email'];
        if ($data['phone_status']==""){
            $phone_status = isset($remind_user[$data['nid']]['phone'])?$remind_user[$data['nid']]['phone']:$remind_result['phone'];
        }else{
            $phone_status = $data['phone_status'];
        }
        $email = isset($data['email'])?$data['email']:$result['email'];
        $phone = isset($data['phone'])?$data['phone']:$result['phone'];
        $_result = array();

        $message['send_userid'] = "0";
        $message['user_id'] = $data['receive_userid'];
        $message['name'] = $data['title'];
        $message['contents'] = $data['content'];
        $message['type'] = 'user';
        $message['status'] = $data['status'];
        $_result['message_result'] = self::AddMessage($message);
//        echo '------9--------';
//        var_dump($message);
//        echo '------91--------';
//        var_dump($_result['message_result']);
        if ($email_status==1 || $email_status==3){
//            echo '------10--------';
            $remail['user_id'] = $data['receive_userid'];
            $remail['title'] = $data['title'];
            if ($data['email_content']==""){
                $remail['msg'] =  "91投房-房地产众筹领导者提示：".$data['content'];
            }else{
                $remail['msg'] =$data['email_content'];
            }
            $this->load->model("User_model");

            $_result['email_result'] = $this->User_model->SendEmail($remail);
//            var_dump($_result['email_result']);
        }
        if ($phone_status==1 || $phone_status==3){
//            echo '------11--------';
            $send_sms['status'] = 1;
            $send_sms['type'] = $data['type'];
            if ($data['phone_content']==""){
                $send_sms['contents'] =  $data['content']."[91投房-房地产众筹领导者]";
            }else{
                $send_sms['contents'] =$data['phone_content'];
            }
            $send_sms['phone'] = $data['phone'];
            $send_sms['user_id'] = $data['receive_userid'];
            $this->load->model("Raise_model");
            $_result['phone_result'] = $this->Raise_model->SendSMS($send_sms);
//            var_dump($_result['phone_result']);
        }
        return $_result;
    }
    function GetAccountInfo($data = array()){

        $_sql = "where 1=1 ";

        if (self::IsExist($data['user_id'])!=false) {
            $_sql .= " and p1.user_id = {$data['user_id']}";
        }

        $_select = "p1.*,(select sum(total) from yyd_account_cash where user_id=p1.user_id and status=1) as cash_total,(select sum(money) from yyd_account_recharge where user_id=p1.user_id and status=1) as recharge_total";
        $sql = "select $_select from yyd_account as p1 $_sql";
        //echo $sql;
        $result = $this->db_fetch_array($sql);
        // var_dump($result);
        if ($result==false){
            $sql = "insert into yyd_account set user_id='{$data['user_id']}',total=0";
            // echo $sql;
            Db::query($sql);
            $sql = "select $_select from yyd_account as p1 $_sql";
            $result = $this->db_fetch_array($sql);
        }
        return $result;
    }

}