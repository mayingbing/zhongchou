<?php
namespace app\index\model;

use think\Model;
use think\Db;
use app\index\lib\Utility;
class Borrow_model extends Model{

    // ���õ�ǰģ�Ͷ�Ӧ���������ݱ�����
    protected $table = 'yyd_borrow';
    // �������ݱ�����
    protected $pk    = 'id';




    function IsExist($val)
    {
        if (isset($val)) {
            return $val;
        } else {
            return false;
        }
    }

    public function getBorrowList($data = array())
    {

        if (isset($data['query_id'])&&self::IsExist($data['query_id']) != false){
            $_sql = "where p1.id=".$data['query_id'];
        }
        else
        {
            $_sql = "where 1=1 ";
        }

        //�ж��û�id
        if (isset($data['user_id'])&&self::IsExist($data['user_id']) != false){
            $_sql .= " and p1.user_id = {$data['user_id']}";
        }

        if(isset($data['is_flow']) && $data['is_flow']==1){
            $_sql .= " and p1.is_flow = '{$data['is_flow']}' ";
        }elseif($data['is_flow']!=2){
            $_sql .= " and p1.is_flow = '0'";

        }
        //�ѵ��û���
        if (isset($data['username'])&&self::IsExist($data['username']) != false){
            $_sql .= " and p2.username like '%{$data['username']}%'";
        }

        //�����������
        if (isset($data['borrow_name'])&&self::IsExist($data['borrow_name']) != false){
            $_sql .= " and p1.`name` like '%".urldecode($data['borrow_name'])."%'";
        }

        //�����������
        if (isset($data['borrow_nid'])&&self::IsExist($data['borrow_nid']) != false){
            $_sql .= " and p1.`borrow_nid` = '{$data['borrow_nid']}'";
        }

        if (isset($data['borrow_type'])&&self::IsExist($data['borrow_type']) != false){
            if ($data['borrow_type']=="credit"){
                $_sql .= " and p1.`vouchstatus`!=1 and `fast_status`!=1 and `is_flow`!=1 and `is_jin`!=1";
            }elseif($data['borrow_type']=="vouch"){
                $_sql .= " and p1.`vouchstatus`=1";
            }elseif($data['borrow_type']=="fast"){
                $_sql .= " and p1.`fast_status`=1";
            }elseif($data['borrow_type']=="flow"){
                $_sql .= " and p1.`is_flow`=1";
            }elseif($data['borrow_type']=="jin"){
                $_sql .= " and p1.`is_jin`=1";
            }
        }

        //�ж�����
        if (isset($data['query_type'])&&self::IsExist($data['query_type'])!=false){
            $type = $data['query_type'];
            //�ȴ����
            if ($type=="wait"){
                $_sql .= " and p1.status=0";
            }
            //�ɹ����
            elseif ($type=="success"){
                $_sql .= " and p1.status=1";
            }
            elseif ($type=="invest"){
                $_sql .= " and p1.status=1 and p1.verify_time >".time()." - p1.borrow_valid_time*60*60*24 and p1.account>p1.borrow_account_yes";
            }
            elseif ($type=="vouch"){
                $_sql .= " and p1.vouchstatus=1 and p1.verify_time >".time()." - p1.borrow_valid_time*60*60*24 and p1.status=1";
            }
            //����ʧ��
            elseif ($type=="false"){
                $_sql .= " and p1.status=2";
            }
            //��������
            elseif ($type=="full_check"){
                $_sql .= " and p1.status=1 and p1.account=p1.borrow_account_yes ";
            }

            //������˳ɹ�
            elseif ($type=="full_success"){
                $_sql .= " and  p1.status=3";
            }

            elseif ($type=="repay_yes"){
                $_sql .= " and (p1.status=3 or p1.is_flow=1) and p1.repay_account_wait='0.00'";
            }

            elseif ($type=="repay_no"){
                $_sql .= " and (p1.status=3 or p1.is_flow=1) and p1.repay_account_wait!='0.00'";
            }
            //�������ʧ��
            elseif ($type=="full_false"){
                $_sql .= " and p1.status=4";
            }
            //�û�����
            elseif ($type=="flow_stop"){
                $_sql .= " and p1.status!=5 ";
            }


            //���ڽ���
            elseif ($type=="tender_now"){
                $_sql .= " and ((p1.status=3 and p1.repay_account_wait!='0.00') or (p1.status=1 and p1.borrow_valid_time*60*60*24 + p1.verify_time >= ".time()."))";
            }

            //����
            elseif ($type=="first" && $data['is_flow']!=1){
                if (isset($data['status'])&&self::IsExist($data['status'])==""){
                    $_sql .= " and p1.status = 0 ";
                }elseif($data['status']==1){
                    $_sql .= " and p1.status=1 and p1.borrow_account_yes!=p1.account and p1.borrow_valid_time*60*60*24 + p1.verify_time >=".time();
                }elseif($data['status']==5){
                    $_sql .= " and p1.status = 5 ";
                }elseif($data['status']==6){
                    $data['status'] = 1;
                    $_sql .= " and  p1.borrow_valid_time*60*60*24 + p1.verify_time <".time();
                }else{
                    $_sql .= " and p1.status in (0,1,2) ";
                }
            }
            //����
            elseif ($type=="full"){
                if ($data['type']=="repay"){
                    $_sql .= " and p1.status = 3 and repay_account_wait>0";
                }elseif ($data['type']=="repayyes"){
                    $_sql .= " and p1.status = 3 and repay_account_wait=0";
                }elseif (isset($data['status'])&&self::IsExist($data['status'])==""){
                    $_sql .= " and p1.status = 1 and  p1.borrow_account_yes=p1.account ";
                }elseif (isset($data['status'])&&self::IsExist($data['status'])!=""){
                    $_sql .= " and p1.status = {$data['status']} ";
                }

            }
        }

        //�ж����ʱ�俪ʼ
        if (isset($data['dotime1'])&&self::IsExist($data['dotime1']) != false){
            $dotime1 = ($data['dotime1']=="request")?$_REQUEST['dotime1']:$data['dotime1'];
            if ($dotime1!=""){
                $_sql .= " and p1.addtime > ".get_mktime($dotime1);
            }
        }

        //�ж����ʱ�����
        if (isset($data['dotime2'])&&self::IsExist($data['dotime2'])!=false){
            $dotime2 = ($data['dotime2']=="request")?$_REQUEST['dotime2']:$data['dotime2'];
            if ($dotime2!=""){
                $_sql .= " and p1.addtime < ".get_mktime($dotime2);
            }
        }

        //�жϽ��״̬
        if (isset($data['status'])&&self::IsExist($data['status'])!= false){
            if ($data['status']==-1){
                $_sql .= " and p1.status = 1 and p1.borrow_valid_time*60*60*24 + p1.verify_time <".time();
            }else{
                if($data['is_flow']==2){
                    $_sql .= " and (p1.status in ({$data['status']}) or p1.is_flow=1)";
                }else{
                    $_sql .= " and p1.status in ({$data['status']})";
                }
            }
        }

        //�ж��Ƿ�αɾ��
        if(isset($data['is_hide']) && $data['is_hide'] == "0"){
            $_sql .= " and p1.is_hide = 0";
        }

        //�ж��Ƿ�����
        if (isset($data['late_display'])&&self::IsExist($data['late_display'])==1 ){
            $_sql .= " and p1.verify_time >".time()." - p1.borrow_valid_time*60*60*24";
        }

        //�ж��Ƿ񵣱����
        if (isset($data['vouch_status'])&&self::IsExist($data['vouch_status'])!= false){
            $_sql .= " and p1.vouch_status in ({$data['vouch_status']})";
        }


        //�ж��������
        if (isset($data['tiyan_status'])&&self::IsExist($data['tiyan_status'])!= false){
            $_sql .= " and p1.tiyan_status in ({$data['tiyan_status']})";
        }

        //�������
        if (isset($data['borrow_period'])&&self::IsExist($data['borrow_period'])!= false){

//            if ($data['borrow_period']=="1"){
//                $_sql .= " and p1.borrow_period <= 3";
//            }elseif ($data['borrow_period']=="2"){
//                $_sql .= " and p1.borrow_period >= 3 and p1.borrow_period <= 6";
//            }elseif ($data['borrow_period']=="3"){
//                $_sql .= " and p1.borrow_period >= 6 and p1.borrow_period <= 12";
//            }elseif ($data['borrow_period']=="4"){
//                $_sql .= " and p1.borrow_period >= 12 ";
//            }
            $_sql .= " and p1.borrow_period =".$data['borrow_period'];
        }
        //�ж���ת����
        if (isset($data['borrow_apr'])&&self::IsExist($data['borrow_apr'])!= false){
            $_sql .= " and p1.borrow_apr=".$data['borrow_apr'];
        }
        if(isset($data['check_period_valid'])&&self::IsExist($data['check_period_valid'])!= false ){

            $_sql .= " and p1.borrow_end_time >".time(). " and p1.borrow_star_time <".time();
        }

        if(isset($data['is_auto'])&&self::IsExist($data['is_auto'])!= false ){
            $_sql .= " and p1.is_auto =".$data['is_auto'];
        }




        //������
        if (isset($data['flag'])&&self::IsExist($data['flag'])!= false){
            $_sql .= " and p1.flag = {$data['flag']}";
        }

        //Ȧ�ӽ��
        if (isset($data['group_id'])&&self::IsExist($data['group_id'])!= false){
            if($data['group_id']!="all"){
                $_sql .= " and p1.group_status=1 and p1.group_id = {$data['group_id']}";
            }else{ // ���������� yyd_group_member
                $_sql .= " and p1.group_status=1 and p1.group_id in (select group_id from yyd_group_member where user_id='{$data['my_userid']}')";
            }
        }

        //�����;
        if (isset($data['borrow_use'])&&self::IsExist($data['borrow_use']) != false){
            $_sql .= " and p1.borrow_use in ('{$data['borrow_use']}')";
        }

        //����û�����
        if (isset($data['borrow_usertype'])&&self::IsExist($data['borrow_usertype']) != false){
            $_sql .= " and p1.borrow_usertype = '{$data['borrow_usertype']}'";
        }

        //�Ƿ���
        if (isset($data['award_status'])&&self::IsExist($data['award_status'])!= false){
            if($data['award_status']==1){
                $_sql .= " and p1.award_status >0";
            }else{
                $_sql .= " and p1.award_status = 0";
            }
        }

        //���
        if (isset($data['borrow_style'])&&self::IsExist($data['borrow_style']) && $data['borrow_style']!='all' ){
            $_sql .= " and p1.borrow_style in ({$data['borrow_style']})";
        }


        if (isset($data['account_status'])&&self::IsExist($data['account_status']!= false)){
            if ($data['account_status']==1){
                $_sql .= " and p1.account < 50000 ";
            }elseif($data['account_status']==2){
                $_sql .= " and p1.account >= 50000 and p1.account <= 100000";
            }elseif($data['account_status']==3){
                $_sql .= " and p1.account >= 100000 and p1.account <= 500000";
            }elseif($data['account_status']==4){
                $_sql .= " and p1.account >= 500000";
            }
        }

        //����
        $_order = " order by p1.`fast_status` desc,p1.`vouchstatus` desc,p1.`id` desc";
        if (isset($data['order'])&&self::IsExist($data['order'])!= false){
            $order = $data['order'];
            if ($order == "account_up"){
                $_order = " order by p1.`account` desc ";
            }else if ($order == "account_down"){
                $_order = " order by p1.`account` asc";
            }
            if ($order == "credit_up"){
                $_order = " order by p3.`credit` desc,p1.id desc ";
            }else if ($order == "credit_down"){
                $_order = " order by p3.`credit` asc,p1.id desc ";
            }
            if ($order == "apr_up"){
                $_order = " order by p1.`borrow_apr` desc,p1.id desc ";
            }else if ($order == "apr_down"){
                $_order = " order by p1.`borrow_apr` asc,p1.id desc ";
            }
            if ($order == "jindu_up"){
                $_order = " order by p1.`borrow_account_scale` desc,p1.id desc ";

            }else if ($order == "jindu_down"){
                $_order = " order by p1.`borrow_account_scale` asc,p1.id desc ";
            }

            if ($order == "period_up"){
                $_order = " order by p1.`borrow_period` desc,p1.id desc ";

            }else if ($order == "period_down"){
                $_order = " order by p1.`borrow_period` asc,p1.id desc ";
            }

            if ($order == "flag"){
                $_order = " order by p1.vouch_status desc,p1.`flag` desc,p1.id desc ";
            }
            if ($order == "index"){
                $_order = " order by p1.`borrow_account_scale` asc,p1.`recommend` desc,p1.`flag` desc,p1.id desc ";
            }
        }


        if (isset($data['jine'])&&$data['jine']==1){
            $_order = " order by p1.`account` desc";
        }
        if (isset($data['jine'])&&$data['jine']==2){
            $_order = " order by p1.`account` asc";
        }
        if (isset($data['jine'])&&$data['jine']==3){
            $_order = " order by p3.`credit` asc";
        }
        if (isset($data['jine'])&&$data['jine']==4){
            $_order = " order by p3.`credit` desc";
        }
        if (isset($data['jine'])&&$data['jine']==5){
            $_order = " order by p1.`borrow_end_time` asc";
        }
        if (isset($data['jine'])&&$data['jine']==6){
            $_order = " order by p1.`borrow_end_time` desc";
        }

        if (isset($data['jine'])&&$data['jine']==7){
            $_order = " order by p1.`borrow_account_scale` asc";
        }
        if (isset($data['jine'])&&$data['jine']==8){
            $_order = " order by p1.`borrow_account_scale` desc";
        }

        $_select = "(100-p1.borrow_account_scale) as borrow_account_scale_sy,(245*p1.borrow_account_scale/100) as borrow_account_scale_width, p1.*,p2.username,p3.credits";
        $sql = "select SELECT from yyd_borrow as p1
				 left join yyd_users as p2 on p1.user_id=p2.user_id
				 left join yyd_credit as p3 on p1.user_id=p3.user_id
				 SQL ORDER LIMIT
				";


        //�ж��ܵ�����
        $newsql=str_replace(array('SELECT', 'SQL', 'ORDER', 'LIMIT'), array('count(1) as num', $_sql,'', ''), $sql);
        //$row = Db::getNum($newsql);
        //TODO
        // $row = Db::getNum($newsql);
        //var_dump($newsql);
        //die;

        $ret =Db::query($newsql);
        $temp= $ret;
        $total = intval($temp["0"]["num"]);
        //TODO
        //var_dump($temp);
        //var_dump($total);
        //die;

        //��ҳ���ؽ��
        $data['page'] = !self::IsExist($data['page'])?1:$data['page'];
        $data['epage'] = !self::IsExist($data['epage'])?10:$data['epage'];

        $total_page = ceil($total / $data['epage']);
        $_limit = " limit ".($data['epage'] * ($data['page'] - 1)).", {$data['epage']}";
        $sql1 =str_replace(array('SELECT', 'SQL','ORDER', 'LIMIT'), array($_select,$_sql,$_order, $_limit), $sql);


        $tmp =Db::query($sql1);
        $res =$tmp;
        $i=0;

        //TODO
//        var_dump($sql1);
//        var_dump($temp);
//        var_dump($res);
//        die;

        $utility = new Utility();
        $list = array();
        foreach ($res as $row) {
            if ($data["getDetail"] == 0) {
                $list[$i]['i_id'] = $row["id"];
                $list[$i]['i_tit'] = $row["name"];
                $list[$i]['i_shouru'] = $row["borrow_apr"] . "%";
                $list[$i]['i_jiner'] = $row["account"];
                $list[$i]['i_jin'] = $row["borrow_account_scale"] . "%";
                $list[$i]['i_img'] = self::IsExist($row["upimg"]) ? 'http://www.91toufang.com' . $row["upimg"] : "images/project.jpg";

                $list[$i]['i_qixian'] = iconv('GB2312', 'UTF-8', $utility->getBorrowPeriod($row["borrow_period"]));
                $list[$i]['typecase'] = "borrow";
            } else if ($data["getDetail"] == 1) {
                if ($row["borrow_object"] == 0)
                    $list[$i]["t_face_type"] = iconv('GB2312', 'UTF-8', "������Ŀ");
                else
                    $list[$i]["t_face_type"] = iconv('GB2312', 'UTF-8', "��ҵ��Ŀ");


                //case 26:
                $list[$i]["t_invest_sum"] = $row["borrow_account_wait"]; //ʣ����
                //case 2:
                $list[$i]["t_query_id"] = $row["id"];
                //case 3: //user_id
                $list[$i]["user_id"] = $row["user_id"];
                //case 32://��������
                $str = preg_replace('#src="/#is', 'style=width:100%; src="http://www.91toufang.com/', $row['borrow_contents']);
                //var_dump($str);
                $list[$i]["t_borrow_content"] = $str;

                //break;
                //case 25://�ѳＯ�ʽ�//borrow_account_yes
                $list[$i]["t_borrow_yes"] = $row["borrow_account_yes"];
                // case 24: // borrow_nid;
                $list[$i]["borrow_nid"] = $row["borrow_nid"];
                // case 44: //"tender_times"Ͷ���˸���
                $list[$i]["t_tender_times"] = $row["tender_times"];
                //case 4://����
                $list[$i]["t_name"] = $row["name"];
                //case 17://���ʽ�
                $list[$i]["t_num"] = $row["account"];
                //case 31://�����ʣ�
                $list[$i]["t_rate"] = $row["borrow_apr"];
                //case 30://�������ޣ�
                $borrow_period = $utility->getBorrowPeriod($row["borrow_period"]);
                $list[$i]['t_period'] = $borrow_period;
                //case 29://���ʽ��// ��ط�ʽ
                $type = $utility->getReturntype($row["borrow_style"]);
                $list[$i]["t_return_type"] = $type;
                //case 5://ʣ��ʱ�䣺
                if ($row['borrow_star_time'] > time()) {//�ж������Ƿ����ԤԼʱ�䡣
                    $star_status = 1;
                } else {
                    $star_status = 0;
                }
                $status = $utility->getBorrowStatus($star_status, $row["status"], $row);
                $list[$i]['t_return_status'] = $status;


                // case 27://borrow_account_scale//Ͷ����ȣ�
                $list[$i]["t_return_percent"] = $row["borrow_account_scale"];
                //case 109://Ͷ��������
                $list[$i]["t_invester"] = iconv('GB2312', 'UTF-8', $row["username"]);
                $list[$i]["t_img_tuijian"] = "/91toufang/themes/rongzi/images/tuijian.jpg";
                $list[$i]["t_img_dbao"] = "/91toufang/themes/rongzi/images/ico_dbao.gif";
                $list[$i]['typecase'] = "borrow";
                $list[$i]['t_img'] = self::IsExist($row["upimg"]) ? 'http://www.91toufang.com' . $row["upimg"] : "images/project.jpg";
            }
            $i++;

        }
        $result = array('list' => $list?$list:array(),'total' => $total,'page' => intval( $data['page']),'epage' =>  intval($data['epage']),'total_page' => $total_page);
        //$result = array('total' => $total,);

        //TODO Debug
        //var_dump($list);
        //var_dump($result);
        //die;

        return $result;
    }





    function timediff($begin_time,$end_time)
    {
        if($begin_time < $end_time){
            $starttime = $begin_time;
            $endtime = $end_time;
        }
        else{
            $starttime = $end_time;
            $endtime = $begin_time;
        }
        $timediff = $endtime-$starttime;
        $days = intval($timediff/86400);
        $remain = $timediff%86400;
        $hours = intval($remain/3600);
        $remain = $remain%3600;
        $mins = intval($remain/60);
        $secs = $remain%60;
        $res = array("day" => $days,"hour" => $hours,"min" => $mins,"sec" => $secs);
        return $res;
    }
    public function db_fetch_array($sql){
        $query = Db::query($sql);
        $_res = "";
        $res = $query;

        if(empty($res)){
            return $_res;
        }
        if (is_array($res[0])){
            foreach ($res[0] as $key =>$value)
                $_res[$key] =$value;
        }

        return $_res;
    }
    public function db_fetch_arrays($sql)
    {
        //echo $sql;
        $query = Db::query($sql);
        $i = 0;
        $_res = array();
        $res = $query;
        if (empty($res)) {
            return $_res;
        }

        foreach($res as $row){
            foreach ($row as $key =>$value){
                $_res[$i][$key] = $value;
            }
            $i++;
        }

        return $_res;
    }

    public function GetAttestationsCredit($data = array()){

        $_sql = " where 1=1 ";
        //�����û�id
        if ($data['user_id'] !== null && self::IsExist($data['user_id'])!=false) {
            $_sql .= " and p1.user_id ='{$data['user_id']}'";
        }
        //�����û�id
        if (isset($data['type_id']) && $data['type_id'] !== null && self::IsExist($data['type_id'])!=false) {
            $_sql .= " and p1.type_id ='{$data['type_id']}'";
        }

        $sql = "select p1.credit,p1.addtime,p2.validity from yyd_attestations  as p1 left join yyd_attestations_type as p2 on p1.type_id=p2.id {$_sql}";
        $result = $this->db_fetch_arrays($sql);
        $num = 0;
        foreach ($result as $key => $value){
            $_time = strtotime("{$value['validity']} month",$value['addtime']);
            if ($value['validity']==0 || $_time>time()){
                $num += $value['credit'];
            }
        }
        return $num;
    }
    public function GetBorrowCredit($data){
        if ($data['user_id']=="")
            return false;
        $_result = array();
        $attcredit = $this->GetAttestationsCredit(array("user_id"=>$data['user_id']));
        $sql = "select sum(credit) as creditnum from yyd_credit_log where user_id='{$data['user_id']}' and code='borrow'";
        $credit_log = $this->db_fetch_arrays($sql);
        $sql = "select sum(credit) as creditnum from yyd_credit_log where user_id='{$data['user_id']}' and code='approve'";
        $approve = $this->db_fetch_arrays($sql);
        $_result[1] = $attcredit;
        $_result[2] = $credit_log['0']['creditnum'];
        $_result[3] = $approve['0']['creditnum'];
        $result = array("credit_total"=>$_result[2]+$_result[1]+$_result[3],"borrow_credit"=>$_result[2],"approve_credit"=>$_result[3]+$_result[1]);
        return $result;
    }
    function GetBorrowCount($data){
        //��ȡ���ͳ��
        $latesql = "select count(1) as late_nums from yyd_account_log where user_id='{$data['user_id']}' and type='borrow_repay_late'";
        $late_nums = $this->db_fetch_arrays($latesql);
        $latemoneysql = "select sum(money) as latemoney from yyd_account_log where user_id='{$data['user_id']}' and type='borrow_repay_late'";
        $latemoney = $this->db_fetch_array($latemoneysql);
        //$sql = "select * from yyd_borrow_count where user_id='{$data['user_id']}'";

        $_result = self::GetBorrowCount_xin(array('user_id'=>$data['user_id']));

        //$_result = Db::db_fetch_array($sql);
        $_result['interest_scale'] = 0;
        if ($_result!=false && isset($_result['tender_capital_account'])&& $_result['tender_capital_account']>0){
            $_result['interest_scale'] = round($_result['tender_interest_account']/$_result['tender_capital_account']*100,2);
            $lixi="select sum(late_interest) as all_lixi from yyd_borrow_repay where user_id='{$data['user_id']}'";
            $lxre=$this->db_fetch_array($lixi);
            $all=$_result['weiyue']+$_result['borrow_repay_interest']+$lxre['all_lixi'];
        }

        if ($_result!=false &&isset($_result['borrow_account'])&& $_result['borrow_account']>0){
            $_result['borrow_interest_scale'] = round($all/$_result['borrow_account']*100,2);
        }
        //���˼���
        $sql = "select sum(recover_account) as num from yyd_borrow_recover where recover_status=0 and user_id='{$data['user_id']}' and recover_time<".(time()-60*60*24*90)." and recover_time<".time();
        $result = $this->db_fetch_array($sql);
        $_result['bad_recover_account'] = $result['num'];
        $_result['late_nums'] = $late_nums['0']['late_nums'];
        $_result['latemoney'] = $latemoney['latemoney'];

        //�����������
        $sql = "select recover_account,recover_time from yyd_borrow_recover where user_id='{$data['user_id']}' and recover_status=0 order by recover_time asc";
        $result = $this->db_fetch_array($sql);
        if(!empty($result))
        {
            $_result["recover_new_account"] = $result["recover_account"];
            $_result["recover_new_time"] = $result["recover_time"];
        }

        return $_result;
    }
    public function GetUserCount($data){
        //��ȡ���ͳ��
        $sql="select count(1) as all_times from yyd_borrow where user_id={$data['user_id']} ";
        $result=$this->db_fetch_arrays($sql);
        $latesql="select sum(p2.late_interest) as all_late_interest from yyd_borrow_tender as p1 left join yyd_borrow_recover as p2 on p1.id=p2.tender_id where (p1.user_id={$data['user_id']} and p1.change_status=0) or (p1.change_userid={$data['user_id']} and p1.change_status=1)";
        $late=$this->db_fetch_array($latesql);
        $_result = $this->GetBorrowCount(array("user_id"=>$data['user_id']));
        $_result['all_times']=$result[0]['all_times'];
        $_result['all_late_interest']=$late['all_late_interest'];

        $sql = " SELECT sum( money ) AS num FROM yyd_account_log WHERE user_id = '{$data['user_id']}' AND `type` = 'tender_award_add' ";
        $result = $this->db_fetch_array($sql);
        $_result['award_add'] = $result['num'];

        $sql = " SELECT sum( money ) AS num FROM yyd_account_log WHERE user_id = '{$data['user_id']}' AND `type` = 'borrow_award_lower' ";
        $result = $this->db_fetch_array($sql);
        $_result['award_lower'] = $result['num'];
        return $_result;
    }
    function GetBorrowCount_xin($data){
        if (!self::IsExist($data['user_id'])) return "";
        $_result=array();
        $sql = "select * from yyd_borrow_count where user_id='{$data['user_id']}'";
        $_result = $this->db_fetch_array($sql);
        $result_tender = $this->db_fetch_array("select count(*) as tender_times,count(*) as tender_success_times,sum(account) as tender_success_account,sum(recover_account_all) as tender_recover_account,sum(recover_account_yes) as tender_recover_yes,sum(recover_account_all) as tender_recover_wait,sum(account) as tender_capital_account ,sum(recover_account_interest) as tender_interest_account ,sum(recover_account_interest) as tender_interest_account ,sum(recover_account_interest) as tender_interest_wait,sum(recover_account_interest_yes) as tender_interest_yes from yyd_borrow_tender where user_id='{$data['user_id']}' ");
        $tender_recover_times_wait = $this->db_fetch_array("select count(*) as tender_recover_times_wait,sum(recover_account_wait) as tender_recover_wait from yyd_borrow_tender where user_id='{$data['user_id']}' and (`recover_account_all`!=`recover_account_yes`) ");
        $tender_recover_times_yes = $this->db_fetch_array("select count(*) as tender_recover_times_yes from yyd_borrow_tender where user_id='{$data['user_id']}' and `recover_account_all`=`recover_account_yes` ");
        foreach ($result_tender as $key => $value){
            $_result[$key]=$value;
        }
        $_result['tender_recover_times_wait']=$tender_recover_times_wait['tender_recover_times_wait'];
        $_result['tender_recover_wait']=$tender_recover_times_wait['tender_recover_wait'];
        $_result['tender_recover_times_yes']=$tender_recover_times_yes['tender_recover_times_yes'];

        return $_result;
    }
    function GetAmountUsers($data = array()){
        if (!self::IsExist($data['user_id'])) return "amount_user_id_empty";
        $borrow_first = 0;//��Աע���ʼ���,��2000��Ϊ0,hummer modify 201309060008
        if (isset($data['amount_result']) && $data['amount_result']!=""){
            $result = $data['amount_result'];
        }else{
            $sql = "select p1.* from yyd_borrow_amount as p1  where p1.user_id='{$data['user_id']}'";
            $result = $this->db_fetch_array($sql);
            if ($result==false) {
                $sql = "insert into yyd_borrow_amount set user_id='{$data['user_id']}'";
                Db::query($sql);
                $sql = "select p1.* from yyd_borrow_amount as p1  where p1.user_id='{$data['user_id']}'";
                $result = $this->db_fetch_array($sql);
            }
        }
        //��ȡ�û��ܻ��֣�(�ܻ���-60)*100Ϊ���Ӷ��
        $_result = $this->GetBorrowCredit(array("user_id"=>$data['user_id']));
        $borrow_credit = ($_result['approve_credit'])*0+$_result['borrow_credit'];//��ԭ����100Ԫÿ1�֣���Ϊ0Ԫÿ1��,hummer modify 201309060011
        $_data["borrow_amount"] = $borrow_first+$borrow_credit+$result['borrow'];
        $_data["borrow_amount_use"] = intval($borrow_first+$borrow_credit+$result['borrow_use']);
        $_data["borrow_amount_nouse"] =intval($_data["borrow_amount"] -$_data["borrow_amount_use"]);

        return $_data;
    }
    function GetRechargeCount_log($data=array()){

        if (self::IsExist($data['user_id'])!=false) {
            $_sql = " and p1.user_id = {$data['user_id']}";
        }
        $sql = "select sum(p1.money) as account,count(1) as num,p1.type from yyd_account_recharge as p1 where p1.status=1  $_sql group by  p1.type ";
        $result = $this->db_fetch_arrays($sql);
        $_result=array();
        if ($result!=false){
            foreach ($result as $key => $value){
                if ($value['type']==2){
                    $_result['recharge_all_down'] += $value['account'];
                }elseif ($value['type']==1){
                    $_result['recharge_all_up'] += $value['account'];
                }else{
                    $_result['recharge_all_other'] += $value['account'];
                }
                $_result['recharge_all'] += $value['account'];
            }
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
//        echo  $sql;
//        var_dump($result);
//        die;

        return $result;
    }
    public function GetInfoOne($data = array())
    {
        $_sql = "where 1=1";
        if (self::IsExist($data['id'])!=false) {
            $_sql .= " and p1.id = '{$data['id']}'";
        }
        if (self::IsExist($data['user_id'])!=false) {
            $_sql .= " and p1.user_id = '{$data['user_id']}'";
        }
        $sql = "select p1.*,p2.username from yyd_rating_info as p1  left join yyd_users as p2 on p1.user_id=p2.user_id $_sql";
        $result = Db::query($sql);
        $i=0;
        $temp = $result->result_array();
        //var_dump($temp);
        $this->load->library('utility');
        $res = $temp["0"] ;
        foreach ($res as $key => $value)
        {
            //�Ա�
            if ($res["sex"]==1)
                $list[$i]['t_sex']=iconv('GB2312', 'UTF-8', "��");
            else
                $list[$i]['t_sex']=iconv('GB2312', 'UTF-8', "Ů");
            //��������
            $list[$i]['t_birthday']=$res["birthday"];
            //�Ƿ���
            //var_dump($res["marry"]);
            $list[$i]['t_single']=iconv('GB2312', 'UTF-8',$this->utility->getMarried($res["marry"]));
            //����1������������黹û��			//��������
            $list[$i]['t_hashome']=$res["city"];
            //���޹���
            //echo "--2-------";
            //var_dump($res["house"]);
            $list[$i]['typecase']=iconv('GB2312', 'UTF-8',$this->utility->getHometype($res["house"]));
            //���޹�����
            if($res["is_car"]==1)
                $list[$i]['t_hascar']= iconv('GB2312', 'UTF-8', "��");
            else
                $list[$i]['t_hascar']= iconv('GB2312', 'UTF-8',"��");

            $list[$i]['t_hascar']=$res["is_car"];
            //��ҵѧУ
            $list[$i]['t_graduate']=$res["school"];
            $list[$i]['t_education']=iconv('GB2312', 'UTF-8',$this->utility->getEducation($res["edu"]));
        }

        if ($result==false) return "rating_info_empty";
        return $list[0];
    }
    function GetTenderList($data = array()){

        $_sql = "where 1=1 ";
        //�ж��û�id
        if (self::IsExist($data['user_id']) != false){
            $_sql .= " and p1.user_id = {$data['user_id']}";
        }
        //�жϽ���û�
        if (self::IsExist($data['borrow_userid']) != false){
            $_sql .= " and p3.user_id = {$data['borrow_userid']}";
        }
        //�ѵ��û���
        if (self::IsExist($data['username']) != false){
            $_sql .= " and p2.username like '%{$data['username']}%'";
        }
        //�����������
        if (self::IsExist($data['borrow_status']) != false){
            $_sql .= " and p3.`status` = '{$data['borrow_status']}'";
        }

        if ($data['change_status']!=""){
            $_sql .= " and p1.`change_status` = '{$data['change_status']}'";
        }
        //�����������
        if (self::IsExist($data['borrow_name']) != false){
            $_sql .= " and p3.`name` like '%".urldecode($data['borrow_name'])."%'";
        }
        //�����������
        if (self::IsExist($data['borrow_nid']) != false){
            $_sql .= " and p3.`borrow_nid` = '{$data['borrow_nid']}'";
        }
        //�ж����ʱ�俪ʼ
        if (self::IsExist($data['dotime1']) != false){
            $dotime1 = ($data['dotime1']=="request")?$_REQUEST['dotime1']:$data['dotime1'];
            if ($dotime1!=""){
                $_sql .= " and p1.addtime > ".get_mktime($dotime1);
            }
        }
        //�ж����ʱ�����
        if (self::IsExist($data['dotime2'])!=false){
            $dotime2 = ($data['dotime2']=="request")?$_REQUEST['dotime2']:$data['dotime2'];
            if ($dotime2!=""){
                $_sql .= " and p1.addtime < ".get_mktime($dotime2);
            }
        }
        //�жϽ��״̬
        if (self::IsExist($data['status'])!=""){
            $_sql .= " and p1.status in ({$data['status']})";
        }
        //�ж��Ƿ񵣱����
        if (self::IsExist($data['vouch_status'])!=""){
            $_sql .= " and p3.vouch_status in ({$data['vouch_status']})";
        }

        //�������
        if (self::IsExist($data['borrow_period'])!=""){
            $_sql .= " and p3.borrow_period = {$data['borrow_period']}";
        }

        //������
        if (self::IsExist($data['flag'])!=""){
            $_sql .= " and p3.flag = {$data['flag']}";
        }

        //�����;
        if (self::IsExist($data['borrow_use']) !=""){
            $_sql .= " and p3.borrow_use in ({$data['borrow_use']})";
        }

        //����û�����
        if (self::IsExist($data['borrow_usertype']) !=""){
            $_sql .= " and p3.borrow_usertype = '{$data['borrow_usertype']}'";
        }


        //���
        if (self::IsExist($data['borrow_style']) ){
            $_sql .= " and p3.borrow_style in ({$data['borrow_style']})";
        }

        //���Ȩ��
        if (self::IsExist($data['account1'])!=""){
            $_sql .= " and p1.account >= {$data['account1']}";
        }
        if (self::IsExist($data['account2'])!=""){
            $_sql .= " and p1.account <= {$data['account2']}";
        }

        //����
        $_order = " order by p1.id desc ";

        $_select = " p1.*,p2.username,p3.name as borrow_name,p3.account as borrow_account,p4.username as borrow_username,p3.repay_account_wait as borrow_account_wait_all,p3.repay_last_time,p3.repay_account_interest_wait as borrow_interest_wait_all,p4.user_id as borrow_userid,p3.borrow_apr,p3.borrow_period,p3.borrow_account_scale,p5.credits";
        $sql = "select SELECT from yyd_borrow_tender as p1
				 left join yyd_users as p2 on p1.user_id=p2.user_id
				 left join yyd_borrow as p3 on p1.borrow_nid=p3.borrow_nid
				 left join yyd_users as p4 on p4.user_id=p3.user_id
				 left join yyd_credit as p5 on p5.user_id=p3.user_id
				 left join yyd_borrow_change as p6 on p1.id=p6.tender_id
				 SQL ORDER LIMIT
				";
        //�Ƿ���ʾȫ������Ϣ
        /*if (IsExist($data['limit'])!=false){
            if ($data['limit'] != "all"){ $_limit = "  limit ".$data['limit']; }
            return Db::db_fetch_arrays(str_replace(array('SELECT', 'SQL', 'ORDER', 'LIMIT'), array($_select, $_sql, $_order, $_limit), $sql));
        }*/

        //�ж��ܵ�����
        $newsql=str_replace(array('SELECT', 'SQL', 'ORDER', 'LIMIT'), array('count(1) as num', $_sql,'', ''), $sql);


        //$row = Db::getNum($newsql);
        $temp =Db::query($newsql);
        $row = $temp->num_rows();

        $total = intval($row['num']);
        $account_tender=0;
        $recover_account_interest=0;
        //��ҳ���ؽ��
        $data['page'] = !self::IsExist($data['page'])?1:$data['page'];
        $data['epage'] = !self::IsExist($data['epage'])?10:$data['epage'];

        $total_page = ceil($total / $data['epage']);
        $_limit = " limit ".($data['epage'] * ($data['page'] - 1)).", {$data['epage']}";
        $sql1=str_replace(array('SELECT', 'SQL','ORDER', 'LIMIT'), array($_select,$_sql,$_order, $_limit), $sql);
        $result = Db::query($sql1);

        if (!$result) {
            die(Db::_error_message());
        }
        $i=0;
        $list=array();

        $res =$result ->result_array();


        foreach ($res as $tmp)
        {
            $row = $res[$i];
            foreach ($row as $key =>$value){
                $list[$i][$key]=$value;
            }

            $account_tender=$account_tender+$list[$i]['account_tender'];
            $recover_account_interest=$recover_account_interest+$list[$i]['recover_account_interest'];
            $repaysql="select * from yyd_borrow_repay where repay_time<".time()." and repay_status=0 and borrow_nid={$list[$i]['borrow_nid']}";
            $repayresult=$this->db_fetch_arrays($repaysql);
            if ($repayresult==true){
                $list[$i]['change_no']=1;
            }
            $latesql="select sum(late_interest) as all_interest from yyd_borrow_repay where borrow_nid={$list[$i]['borrow_nid']}";
            $late=$this->db_fetch_arrays($latesql);
            $list[$i]['late_interest'] = $late['all_interest'];
            $list[$i]["credit"] = $this->GetBorrowCredit(array("user_id"=>$list[$i]['user_id']));
            $recoversql="select count(1) as num from yyd_borrow_repay where borrow_nid={$list[$i]['borrow_nid']} and (repay_status=1 or repay_web=1)";
            $recoverresult=$this->db_fetch_arrays($recoversql);
            $list[$i]['norepay_num'] = $list[$i]['borrow_period'] - $recoverresult['num'];
            $list[$i]['repay_num'] = $recoverresult['num'];
            //$list[$i]['username'] = mb_substr($list[$i]['username'], 0, 1, 'gb2312')."***";
            $chsql="select status,buy_time from yyd_borrow_change where tender_id={$list[$i]['id']}";
            $chresult=$this->db_fetch_arrays($chsql);
            if ($chresult['status']==1){
                $recsql="select count(1) as count_all,
				sum(recover_account_yes) as recover_account_yes_all,
				sum(recover_interest_yes) as recover_interest_yes_all
				from yyd_borrow_recover where user_id={$list[$i]['user_id']} and borrow_nid={$list[$i]['borrow_nid']} and recover_yestime<{$chresult['buy_time']} and tender_id={$list[$i]['id']}";
                $recresult=$this->db_fetch_arrays($recsql);
                $list[$i]["recover_interest_yes_all"] = $recresult['recover_interest_yes_all'];
                $list[$i]["recover_account_yes_all"] = $recresult['recover_account_yes_all'];
                $list[$i]["count_all"] = $recresult['count_all'];

            }

            $list[$i]["username"]=$list[$i]["username"];
            $list[$i]["borrow_name"]=$list[$i]["borrow_name"];

            $i++;
        }

        return $list;
    }

    public function GetOne($data = array())
    {

        $_sql = "where 1=1 ";
        if (self::IsExist($data['user_id']) != "") {
            $_sql .= " and  p1.user_id = '{$data['user_id']}' ";
        }
        if (self::IsExist($data['id']) != "") {
            $_sql .= " and  p1.id = '{$data['id']}' ";
        }
        if (self::IsExist($data['borrow_nid']) != "") {
            $_sql .= " and  p1.borrow_nid = '{$data['borrow_nid']}' ";
        }
        $sql = "select p1.* ,p2.username,p3.username as verify_username,(p1.borrow_success_time+(p1.borrow_period*24*60*60*30)) as r_time_h from yyd_borrow as p1
				  left join yyd_users as p2 on p1.user_id=p2.user_id
				  left join yyd_users as p3 on p1.verify_userid = p3.user_id
				  $_sql
				";
        $result = $this->db_fetch_array($sql);
        if ($result == false) return "borrow_not_exist";
        return $result;
    }

    function GetAccountUsers($data = array())
    {

        $_sql = "where 1=1 ";
        //�ж�id�Ƿ����
        if (!self::IsExist($data['user_id'])) {
            return "account_bank_userid_empty";
        }
        if (self::IsExist($data['user_id']) != false) {
            $_sql .= " and p1.user_id = '{$data['user_id']}'";
        }
        $_select = "p1.*";
        $sql = "select $_select from yyd_account as p1 $_sql";
        $result = $this->db_fetch_array($sql);

        return $result;
    }
    function UseHongbao($data= array()){

        //�ж�hongbao�Ƿ�Ϊ��
        if(self::IsExist($data['hongbao']) ==""){
            return FALSE;
        }
        if(round($data['hongbao'])==0){

            return FALSE;
        }
        $sql = "select * from yyd_credit_log where type='hongbao' and nid='hongbao' and user_id='{$data['user_id']}'";
        $result = $this->db_fetch_array($sql);
        $remain = $result['value'];

        if(round($remain)<round($data['hongbao'])){
            return FALSE;
        }
        $sql = "update yyd_credit_log set `credit`=`credit`-{$data['hongbao']},`value`=`value`-{$data['hongbao']} where type='hongbao' and nid='hongbao' and user_id='{$data['user_id']}'";
        if(Db::query($sql)){
            $sql = "select sum(p1.credit) as num,p2.class_id from yyd_credit_log as p1 left join yyd_credit_type as p2 on p1.nid=p2.nid  where p1.user_id='{$data['user_id']}' group by p2.class_id order by p2.class_id desc";
            $result = $this->db_fetch_arrays($sql);
            $credits=serialize($result);
            if ($result!=false){
                $sql = "update yyd_credit set `credits`='{$credits}' where user_id='{$data['user_id']}'";
                Db::query($sql);
            }
            return TRUE;
        }
        return FALSE;
    }
    public function getSystemSetting()
    {
        $sql = "select nid, value from yyd_system ";
        $output = array();
        $result = $this->db_fetch_arrays($sql);
        foreach ($result as $key => $v) {
            $t = $v["nid"];
            $output[$t] = $v["value"];
        }
        return $output;
    }
    public function GetSecond($data = array())
    {
        $sql = "select * from yyd_borrow_tender where user_id='{$data['user_id']}' AND borrow_nid='{$data['borrow_nid']}'";
        $result = $this->db_fetch_array($sql);
        if ($result == false) return 0;
        return 1;
    }
    //��ȡ�û�����Ͷ�ʶ������ȫ���ģ�Ҳ���Ե�����ĳ����
    function GetUserTenderAccount($data)
    {

        $_sql = " where 1=1 ";
        if (self::IsExist($data['user_id']) != "") {
            $_sql .= " and user_id='{$data['user_id']}' ";
        }
        if (self::IsExist($data['borrow_nid']) != "") {
            $_sql .= " and borrow_nid='{$data['borrow_nid']}' ";
        }
        $sql = "select sum(account) as account_all from yyd_borrow_tender {$_sql}";
        $result = $this->db_fetch_array($sql);
        if ($result != false) {
            return $result["account_all"];
        }
        return 0;
    }
    function UpdateBorrowCount($data = array())
    {

        if ($data['user_id'] == "") return "";
        $user_id = $data['user_id'];
        $result = $this->db_fetch_array("select 1 from yyd_borrow_count where user_id='{$data['user_id']}'");
        if ($result == false) {
            $sql = "insert into yyd_borrow_count set user_id='{$data['user_id']}'";
            Db::query($sql);

        }
        $sql = "update yyd_borrow_count set user_id='{$data['user_id']}'";
        unset ($data['user_id']);
        foreach ($data as $key => $value) {
            $sql .= ",`{$key}`=`{$key}`+{$value}";
        }
        $sql .= " where user_id='{$user_id}'";

        Db::query($sql);


        $sql = "insert into yyd_borrow_count_log set user_id='{$data['user_id']}',addtime='" . time() . "'";
        foreach ($data as $key => $value) {
            $sql .= ",`{$key}`={$value}";
        }
        $sql .= " ";

        Db::query($sql);
        return "";
    }
    /**
     * ���Ͷ��
     *
     * @param Array $data
     * @return Boolen
     */
    public function AddTender($data = array())
    {
        $_G["system"] =self::getSystemSetting();
        //�ж�id�Ƿ�Ϊ��
        if (self::IsExist($data['borrow_nid']) == "") {
            return "idΪ��";
        }
        //�ж��Ƿ���ڽ���
        $borrow_result = self::GetOne(array("borrow_nid" => $data['borrow_nid']));

        if (!is_array($borrow_result)) {
            return $borrow_result;
        }
        if ($borrow_result["Second_limit_money"] < $data['account'] && $borrow_result["Second_limit_money"] != 0 && time() - $borrow_result['verify_time'] <= 1800) {
            return "��Ͷ�ʽ������˴˱������";
        }
        //���ֻ�ܱ�Ͷһ�θĳ�ȫ����ֻ��Ͷһ��
        //if ($borrow_result["is_Seconds"]==1){
        $dataS['borrow_nid'] = $data['borrow_nid'];
        $dataS['user_id'] = $data['user_id'];
        $is_Second = self::GetSecond($dataS);
        if ($is_Second == 1 && time() - $borrow_result['verify_time'] <= 1800 && $_G['system']['con_bid_limit'] == 1) {
            return "�˱����Ѿ�Ͷ�ʹ��ˣ����ܽ��ж���Ͷ�꣡";
        }
        if ($borrow_result["is_Seconds"] == 1 && $is_Second == 1) {
            return "�˱����Ѿ�Ͷ�ʹ��ˣ����ܽ��ж���Ͷ�꣡";
        }

        if ($is_Second == 1 && $borrow_result["is_Seconds"] != 1 && $_G['system']['con_is_Seconds_limit'] == 1) {
            return "�˱����Ѿ�Ͷ�ʹ��ˣ����ܽ��ж���Ͷ�꣡";
        }
        if ($borrow_result["user_id"] == $data['user_id']) {
            return "����Ͷ���Լ��ıꡣ";
        }
        //�ж��Ƿ��Ѿ�Ͷ������
        if ($borrow_result['borrow_account_yes'] >= $borrow_result['account']) {
            return "�Ѿ�Ͷ������";
        }
        //�ж��Ƿ��Ѿ����
        if ($borrow_result['verify_time'] == "" || $borrow_result['status'] != 1) {
            return "δ���";
        }
        //�ж��Ƿ��Ѿ�����
        if ($borrow_result['verify_time'] + $borrow_result['borrow_valid_time'] * 60 * 60 * 24 < time()) {
            return "�Ѿ�����";
        }
        //�жϽ���Ƿ���ȷ
        if (!is_numeric($data['account'])) {
            return "����ȷ";
        }
        //�ж��Ƿ�С��Ͷ�ʽ��
        if ($data['account'] < $borrow_result['tender_account_min']) {
            return "��С��Ͷ�ʽ���С��{$borrow_result['tender_account_min']}��";
        }
        //�ж��Ƿ����Ͷ�ʽ��
        if ($data['account'] > $borrow_result['Second_limit_money'] && $borrow_result['Second_limit_money'] > 0) {
            return "����Ͷ�ʽ��ܴ���{$borrow_result['Second_limit_money']}��";
        }
        //����ǵ����꣬���жϵ����Ƿ������
        if ($borrow_result['vouch_status'] == 1 && $borrow_result['vouch_account'] != $borrow_result['vouch_account_yes']) {
            return "������δ���";
        }
        //�ж�Ͷ�ʵ��ܽ��
        $tender_account_all = self::GetUserTenderAccount(array("user_id" => $data["user_id"], "borrow_nid" => $data['borrow_nid']));
        if ($tender_account_all + $data['account'] > $borrow_result['tender_account_max'] && $borrow_result['tender_account_max'] > 0) {
            $tender_account = $borrow_result['tender_account_max'] - $tender_account_all;
            return "���Ѿ�Ͷ����{$tender_account_all},���Ͷ���ܽ��ܴ���{$borrow_result['tender_account_max']}������໹��Ͷ��{$tender_account}";
        } else {
            $data['account_tender'] = $data['account'];
            //�ж�Ͷ�ʵĽ���Ƿ���ڴ���Ľ��
            if ($borrow_result['borrow_account_wait'] < $data['account']) {
                $data['account'] = $borrow_result['borrow_account_wait'];
                return "Ͷ�ʵĽ����ڴ���Ľ��";
            }
            //�жϽ���Ƿ���һ����
            $account_result = self::GetAccountUsers(array("user_id" => $data['user_id']));//��ȡ��ǰ�û������
            if ($account_result['balance'] < $data['account']) {
                return "��һ��";
            }
        }
        //�д��ղ���Ͷ�����
        if ($account_result['await'] <= $_G['system']['con_seconds_await_acc'] && $_G['system']['con_seconds_await'] == 1 && $borrow_result["is_Seconds"] == 1) {
            return "ֻ���д��ս�������һ�����ն�Ȳ���Ͷ����";
        }
        //�ж��Ƿ���������
        if ($borrow_result['tender_friends'] != "") {
            $_tender_friends = explode("|", $borrow_result['tender_friends']);
            $sql = "select username from yyd_users where user_id='{$data['user_id']}'";
            $result = $this->db_fetch_array($sql);
            if (!in_array($result['username'], $_tender_friends)) {
                return "�˱���������㲻����������Ľ���ˡ�����Ͷ��";
            }
        }
        if ($_G['system']['con_repay_no'] == 0) {
            $moresql = "select * from yyd_borrow where user_id={$data['user_id']} and repay_account_wait!=0";
            $more = $this->db_fetch_array($moresql);
            if ($more == true) {
                return "����Ͷ�ʽ�������δ������һ��";
            }
        } else {
            $acc = $data['account'] * 2;
            $moresql = "select sum(repay_account_wait) as account_all from yyd_borrow where user_id={$data['user_id']}";
            $more = $this->db_fetch_array($moresql);
            if ($more['account_all'] < $acc && $more['account_all'] != 0) {
                return "����Ͷ�ʽ�������δ������һ��";
            }
        }
        //���Ͷ�ʵĽ����Ϣ
        $sql = "insert into yyd_borrow_tender set `addtime` = '" . time() . "',`addip` = '" . self::ip_address() . "'";
        $flag_hongbao = false;

        foreach ($data as $key => $value) {
            $sql .= ",`$key` = '$value'";
            if ($key == "hongbao" && $value > 0) {
                $flag_hongbao = true;
            }
        }
        Db::query($sql);
        $insert_id = Db::insert_id();
        if ($insert_id > 0) {
            //���½�����Ϣ
            $sql = "update  yyd_borrow  set borrow_account_yes=borrow_account_yes+{$data['account']},borrow_account_wait=borrow_account_wait-{$data['account']},borrow_account_scale=(borrow_account_yes/account)*100,tender_times=tender_times+1  where borrow_nid='{$data['borrow_nid']}'";
            Db::query($sql);//�����Ѿ�Ͷ���Ǯ


            //Ͷ�����
            $borrow_url = "<a href=/invest/a{$data['borrow_nid']}.html target=_blank>{$borrow_result['name']}</a>";
            $log_info["user_id"] = $data["user_id"];//�����û�id
            $log_info["nid"] = "tender_frost_" . $data['user_id'] . "_" . time();
            $log_info["money"] = $data['account'];//�������
            $log_info["income"] = 0;//����
            $log_info["expend"] = 0;//֧��
            $log_info["balance_cash"] = 0;//�����ֽ��
            $log_info["balance_frost"] = -$data['account'];//�������ֽ��
            $log_info["frost"] = $data['account'];//������
            $log_info["await"] = 0;//���ս��
            $log_info["type"] = "tender";//����
            $log_info["to_userid"] = $borrow_result['user_id'];//����˭
            if ($data['auto_status'] == 1) {
                $log_info["remark"] = iconv('GB2312', 'UTF-8',"�Զ�Ͷ��[{$borrow_url}") .iconv('GB2312', 'UTF-8',"]�������ʽ�");//��ע
            } else {
                $log_info["remark"] = iconv('GB2312', 'UTF-8',"Ͷ��[{$borrow_url}") .iconv('GB2312', 'UTF-8',"]�������ʽ�");//��ע
            }
            self::AddLog($log_info);

            if ($borrow_result["is_flow"] != 1) {
                //����ͳ����Ϣ
                self::UpdateBorrowCount(array("user_id" => $data['user_id'], "tender_times" => 1, "tender_account" => $data['account'], "tender_frost_account" => $data['account']));
            }
            /*
                if($flag_hongbao){
                    $sql_update_hongbao = "update `{borrow_tender}` set `account` = `account` + `hongbao` where `id`={$insert_id}";
                    $mysql->db_query($sql_update_hongbao);//����
                }*/
        }

        return $insert_id;

        //�ж�Ͷ�ʵĽ���Ƿ���ڴ���Ľ��
        if ($borrow_result['borrow_account_wait'] <= $data['account']) {
            if ($borrow_result['is_Seconds'] == 1) {
                //$dataS = array();
                //echo $data['borrow_nid'];
                //$dataS['borrow_nid']=$data['borrow_nid'];
                //	$dataS['status']=3;
                //$dataS['reverify_remark']="����Զ�ͨ��";

                //$resultS = borrowClass::Reverify($dataS);
                ////echo $resultS;
                //	$dataP = array();
                //	$dataP['borrow_nid']=$data['borrow_nid'];
                //	$dataP['user_id']=$borrow_result['user_id'];
                // $BorrowRepayID=borrowClass::GetBorrowRepaytt($dataP);
                // //echo $BorrowRepayID;
                // $dataT = array();
                //	$dataT['borrow_nid'] = $data['borrow_nid'];
                //$dataT['id'] = $BorrowRepayID;
                //$dataT['user_id'] = $borrow_result['user_id'];
                //$resultT =  borrowClass::BorrowRepay($dataT);//��ȡ��ǰ�û������
            }
        }


    }



    //�û�Ͷ��
    function tenderNow($input = array())
    {

        $borrow_result = self::GetOne(array("borrow_nid" => $input['borrow_nid']));
        $sql = "select * from yyd_users_info where user_id = '{$input['user_id']}'";
        $result = $this->db_fetch_arrays($sql);
        $_G['islock'] = $result["status"];
        $sql = "select * from yyd_users where user_id = '{$input['user_id']}'";
        $result = $this->db_fetch_array($sql);

        $_G['paypassword'] = $result["paypassword"];

        if ($_G['islock'] == 1) {
            // echo "----01--------";
            $msg = iconv('GB2312', 'UTF-8',"���˺��Ѿ������������ܽ���Ͷ�꣬�������Ա��ϵ");
        }
        elseif (md5($input['paypassword']) != $_G['paypassword']) {
            //echo "----02--------";
//            echo md5($input['paypassword']);
//            echo "------";
//            echo  $_G['paypassword'];
            $msg = iconv('GB2312', 'UTF-8', "֧���������벻��ȷ");
        }
        elseif ($input['dxbPWD'] != $borrow_result['pwd'] && $borrow_result['isDXB'] == 1) {
            //         echo "----03--------";
            $msg = iconv('GB2312', 'UTF-8', "��������벻��ȷ");
        } elseif ($input['money'] == 0 || $input['money'] == '') {
//            echo "----04--------";
            $msg = iconv('GB2312', 'UTF-8', "Ͷ�ʽ���Ϊ�գ�");
        } elseif ($input['Second_limit_money'] < $input['money'] && $input['Second_limit_money'] != 0 && time() - $borrow_result['verify_time'] <= 1800) { //&& $_POST['is_Seconds']==1
//            echo "----05--------";
            $msg = iconv('GB2312', 'UTF-8', "��Ͷ�ʽ��������������ȣ�");
        }
        else {
            // ȥ���� ??liwnei -- check the new code
//            if($input['money']<50000||$input['money']>200000||$input['money']%5!=0){
//                $msg = array("Ͷ�ʽ�����5�����20��Ͷ�ʶ��Ϊ5��ı�����");
//                return iconv('GB2312', 'UTF-8',$msg[0]);
//            }
//echo "----1--------";
            //��������ӽ�ȥ
            $_tender['borrow_nid'] = $input['borrow_nid'];
            $_tender['user_id'] = $input['user_id'];
            $_tender['account'] = $input['money'];
            $_tender['contents'] = $input['contents'];
            $_tender['hongbao'] = $input['hongbao'];
            // hongbao come from  function UseHongbao($data= array()) _linwei_30/08/2015

            if ($borrow_result['is_flow'] == 1) {
                $_tender['flow_count'] = $input['flow_count'];
            }

            $_tender['status'] = 0;
            $_tender['nid'] = "tender_" . $_G['user_id'] . time() . rand(10, 99);//������
//echo "----2--------";
            $account_result = self::GetAccountUsers(array("user_id" => $_tender['user_id']));//��ȡ��ǰ�û������

//var_dump($account_result);
            if ($account_result['balance'] < $_tender['account']) {
                //$msg = array("tender_money_no");
                $msg = iconv('GB2312', 'UTF-8', "���㣡");
                return $msg;
            }
            // echo "----3--------";

            if (isset($input['use_hongbao']) && $input['use_hongbao'] == "Yes") {
                if (round($input['hongbao']) < 0) {
                    $msg = iconv('GB2312', 'UTF-8',"���Ƿ�ʹ�ú������<0����������ж�ʧ���������Ա��ϵ");
                    return $msg;
                }
                if (round($input['hongbao']) * 100 > round($input['money'])) {
                    $msg = iconv('GB2312', 'UTF-8',"���Ƿ�ʹ�ú�������������ж�ʧ���������Ա��ϵ");
                    return $msg;
                }
                if (round($input['hongbao']) > 0) {
                    $result = self::UseHongbao($_tender);
                    if ($result == FALSE) {
                        $msg = iconv('GB2312', 'UTF-8',"���Ƿ�ʹ�ú����������㣬��������ж�ʧ���������Ա��ϵ��");
                        return $msg;
                    }
                    $_tender['account'] = $_tender['account'] - round($_tender['hongbao']);
                }
            }
            $result = self::AddTender($_tender);

            //echo "--------result4-----------------";
            //var_dump($result);

            if ($borrow_result['is_flow'] == 1 && $result > 0) {

//                echo "----4--a-----";
                $sql = "update yyd_borrow_tender set status=1 where id={$result}";
                Db::query($sql);

                $sql = "select * from yyd_borrow_tender where id={$result}";
                $tender_result = $this->db_fetch_array($sql);
                //var_dump($tender_result);
                $tender_userid = $_tender['user_id'];
                $borrow_nid = $_tender['borrow_nid'];
                $tender_id = $result;
                $tender_account = $tender_result['account'];
                $flow_count = $_tender['flow_count'];
                $borrow_userid = $borrow_result['user_id'];
                $account = $tender_result['account'];
                $borrow_url = "<a href=/invest/a{$borrow_result['borrow_nid']}.html target=_blank>{$borrow_result['name']}</a>";

                //���Ͷ�ʵ��տ��¼
                $_equal["account"] = $tender_account;
                $_equal["period"] = $borrow_result["borrow_period"];
                $_equal["apr"] = $borrow_result["borrow_apr"];
                $_equal["style"] = 2;
                $_equal["type"] = "";
//                    echo "--------------66-------------";
//                    var_dump($_equal);
                // $util = Util::getIns()
                $this->load->library('utility');
                $equal_result = $this->utility->EqualInterest($_equal);
                //               echo "----5--------";
                //               var_dump($equal_result);
//                echo "--------result3-----------------";
                foreach ($equal_result as $period_key => $value) {
                    $repay_month_account = $value['account_all'];

                    $sql = "select 1 from yyd_borrow_repay  where user_id='{$borrow_userid}' and repay_period='0' and borrow_nid='{$borrow_nid}'";
                    $result = $this->db_fetch_array($sql);
//                    echo "----6--------";
                    //var_dump($result);
                    if ($result == false) {
                        $sql = "insert into yyd_borrow_repay set `addtime` = '" . time() . "',";
                        $sql .= "`addip` = '" . $this->ip_address() . "',user_id='{$borrow_userid}',status=1,`borrow_nid`='{$borrow_nid}',`repay_period`='0',";
                        $sql .= "`repay_time`='{$value['repay_time']}',`repay_account`='{$value['account_all']}',";
                        $sql .= "`repay_interest`='{$value['account_interest']}',`repay_capital`='{$value['account_capital']}'";
                        Db::query($sql);
                    } else {
                        $sql = "update yyd_borrow_repay set `addtime` = '" . time() . "',";
                        $sql .= "`addip` = '" . $this->ip_address() . "',user_id='{$borrow_userid}',status=1,`borrow_nid`='{$borrow_nid}',`repay_period`='0',";
                        $sql .= "`repay_time`='{$value['repay_time']}',`repay_account`=`repay_account`+'{$value['account_all']}',";
                        $sql .= "`repay_interest`=`repay_interest`+'{$value['account_interest']}',`repay_capital`=`repay_capital`+'{$value['account_capital']}'";
                        $sql .= " where user_id='{$borrow_userid}' and repay_period='0' and borrow_nid='{$borrow_nid}'";
                        Db::query($sql);
                    }

                    //��ֹ�ظ���ӻ�����Ϣ
                    $sql = "select 1 from yyd_borrow_recover where user_id='{$tender_userid}' and borrow_nid='{$borrow_nid}' and recover_period='$period_key' and tender_id='{$tender_id}'";
                    $result = $this->db_fetch_array($sql);
//                    echo "----7--------";
                    // var_dump($result);
                    if ($result == false) {

                        $sql = "insert into yyd_borrow_recover set `addtime` = '" . time() . "',";
                        $sql .= "`addip` = '" . $this->ip_address() . "',user_id='{$tender_userid}',status=1,`borrow_nid`='{$borrow_nid}',`borrow_userid`='{$borrow_userid}',`tender_id`='{$tender_id}',`recover_period`='{$period_key}',";
                        $sql .= "`recover_time`='{$value['repay_time']}',`recover_account`='{$value['account_all']}',";
                        $sql .= "`recover_interest`='{$value['account_interest']}',`recover_capital`='{$value['account_capital']}'";
                        Db::query($sql);

                    } else {
                        $sql = "update yyd_borrow_recover set `addtime` = '" . time() . "',";
                        $sql .= "`addip` = '" . $this->ip_address() . "',user_id='{$tender_userid}',status=1,`borrow_nid`='{$borrow_nid}',`borrow_userid`='{$borrow_userid}',`tender_id`='{$tender_id}',`recover_period`='{$period_key}',";
                        $sql .= "`recover_time`='{$value['repay_time']}',`recover_account`='{$value['account_all']}',";
                        $sql .= "`recover_interest`='{$value['account_interest']}',`recover_capital`='{$value['account_capital']}'";
                        $sql .= " where user_id='{$tender_userid}' and recover_period='{$period_key}' and borrow_nid='{$borrow_nid}' and tender_id='{$tender_id}'";
                        Db::query($sql);
                    }


                }

                $recover_times = count($equal_result);
                //���岽,����Ͷ�ʱ����Ϣ
                $_equal["type"] = "all";
                $equal_result = $this->utility->EqualInterest($_equal);
                $recover_all = $equal_result['account_total'];
                $recover_interest_all = $equal_result['interest_total'];
                $recover_capital_all = $equal_result['capital_total'];
                $sql = "update yyd_borrow_tender set recover_account_all='{$equal_result['account_total']}',recover_account_interest='{$equal_result['interest_total']}',recover_account_wait='{$equal_result['account_total']}',recover_account_interest_wait='{$equal_result['interest_total']}',recover_account_capital_wait='{$equal_result['capital_total']}'  where id='{$tender_id}'";
                Db::query($sql);

                $sql = "update yyd_borrow set repay_account_all=repay_account_all+'{$equal_result['account_total']}',repay_account_interest=repay_account_interest+'{$equal_result['interest_total']}',repay_account_capital=repay_account_capital+'{$equal_result['capital_total']}',repay_account_wait=repay_account_wait+'{$equal_result['account_total']}',repay_account_interest_wait=repay_account_interest_wait+'{$equal_result['interest_total']}',repay_account_capital_wait=repay_account_capital_wait+'{$equal_result['capital_total']}',flow_money=flow_money+'{$tender_account}',flow_count=flow_count+'{$flow_count}' where borrow_nid='{$borrow_nid}'";
                Db::query($sql);
                //               echo "--------result2-----------------";

                //������,�۳�Ͷ���˵��ʽ�
                $log_info["user_id"] = $tender_userid;//�����û�id
                $log_info["nid"] = "tender_success_" . $borrow_nid . $tender_userid . $tender_id . $period_key;//������
                $log_info["money"] = $tender_account;//�������
                $log_info["income"] = 0;//����
                $log_info["expend"] = $tender_account;//֧��
                $log_info["balance_cash"] = 0;//�����ֽ��
                $log_info["balance_frost"] = 0;//�������ֽ��
                $log_info["frost"] = -$tender_account;//������
                $log_info["await"] = 0;//���ս��
                $log_info["type"] = "tender_success";//����
                $log_info["to_userid"] = $borrow_userid;//����˭
                $log_info["remark"] = iconv('GB2312', 'UTF-8',"Ͷ��[") . $borrow_url .iconv('GB2312', 'UTF-8',"]�ɹ�Ͷ�ʽ��۳�");
                self::AddLog($log_info);

                //���߲�,��Ӵ��յĽ��
                $log_info["user_id"] = $tender_userid;//�����û�id
                $log_info["nid"] = "tender_success_frost_" . $borrow_nid . $tender_userid . $tender_id . $period_key;//������
                $log_info["money"] = $recover_all;//�������
                $log_info["income"] = 0;//����
                $log_info["expend"] = 0;//֧��
                $log_info["balance_cash"] = 0;//�����ֽ��
                $log_info["balance_frost"] = 0;//�������ֽ��
                $log_info["frost"] = 0;//������
                $log_info["await"] = $recover_all;//���ս��
                $log_info["type"] = "tender_success_frost";//����
                $log_info["to_userid"] = $borrow_userid;//����˭
                $log_info["remark"] =iconv('GB2312', 'UTF-8',"Ͷ��[").$borrow_url.iconv('GB2312', 'UTF-8',"]�ɹ����ս������");
                self::AddLog($log_info);

                //echo "--------result1-----------------";
                //�����û�������¼
                $user_log["user_id"] = $tender_userid;
                $user_log["code"] = "tender";
                $user_log["type"] = "tender_success";
                $user_log["operating"] = "tender";
                $user_log["article_id"] = $tender_userid;
                $user_log["result"] = 1;
                $user_log["content"] =iconv('GB2312', 'UTF-8', "Ͷ����ת�꣺[").$borrow_url.iconv('GB2312', 'UTF-8',"]�ɹ�");
                self::AddUsersLog($user_log);

                //��������ý��������б�ɹ�������ʧ��Ҳ����
                if ($borrow_result['award_status'] != 0) {
                    //echo "--------resu3-----------------";
                    //Ͷ�꽱���۳������ӡ�
                    if ($borrow_result['award_status'] == 1) {
                        $money = round(($tender_account / $borrow_result['account']) * $borrow_result['award_account'], 2);
                    } elseif ($borrow_result['award_status'] == 2) {
                        $money = round((($borrow_result['award_scale'] / 100) * $tender_account), 2);
                    }


                    $log_info["user_id"] = $tender_userid;//�����û�id
                    $log_info["nid"] = "tender_award_add_" . $tender_userid . "_" . $tender_id . $borrow_nid;//������
                    $log_info["money"] = $money;//�������
                    $log_info["income"] = $money;//����
                    $log_info["expend"] = 0;//֧��
                    $log_info["balance_cash"] = $money;//�����ֽ��
                    $log_info["balance_frost"] = 0;//�������ֽ��
                    $log_info["frost"] = 0;//������
                    $log_info["await"] = 0;//���ս��
                    $log_info["type"] = "tender_award_add";//����
                    $log_info["to_userid"] = 0;//����˭
                    $log_info["remark"] = iconv('GB2312', 'UTF-8',"���[").$borrow_url.iconv('GB2312', 'UTF-8',"]�Ľ���");
                    self::AddLog($log_info);
                }

                //����ͳ����Ϣ
                self::UpdateBorrowCount(array("user_id" => $tender_userid, "tender_times" => 1, "tender_account" => $tender_account, "tender_success_times" => 1, "tender_success_account" => $tender_account, "tender_recover_account" => $recover_all, "tender_recover_wait" => $recover_all, "tender_capital_account" => $recover_capital_all, "tender_capital_wait" => $recover_capital_all, "tender_interest_account" => $recover_interest_all, "tender_interest_wait" => $recover_interest_all, "tender_recover_times" => $recover_times, "tender_recover_times_wait" => $recover_times));
                $msg= iconv('GB2312', 'UTF-8',"success");
            }

            if ($borrow_result['is_flow'] == 1) {
                $url = "success&type=wait";
            } else {
                $url = "gettender";
            }
            if($result>0){
                $msg ="success";
                $this->AddAuto_V2(array('user_id'=>$input['user_id'] ,'timelimit_month'=>$borrow_result["borrow_period"],"apr"=> $borrow_result["borrow_apr"], "is_auto"=>$input['is_auto']));
            }else{
                $msg =iconv('GB2312', 'UTF-8',$result);
            }
        }
        return $msg;
    }


    //for Ͷ��1��2��3������
    //user_id �û�id
    private function  AddAuto_V2($data=array()){

//var_dump($data);
        $user_id = $data['user_id'];
        $timelimit_month=$data["timelimit_month"];
        $apr=$data["apr"];

        if (!isset($user_id)) return -1;//����û������ڣ��򷵻�


        $sql = "select count(*) as num from yyd_borrow_auto where user_id={$user_id} and is_auto=1 and timelimit_month_first={$timelimit_month} and timelimit_month_last={$timelimit_month} and apr_first={$apr} and apr_last={$apr}";
        $result = $this->db_fetch_array($sql);
        if ($result["num"] >= 1){
            return -2;
        }else{

            $sql = "insert into yyd_borrow_auto (user_id,tender_type,`status`,tender_account,tender_scale,`order`,timelimit_status,timelimit_month_first,timelimit_month_last,apr_status,apr_first,apr_last,is_auto,
                    account_min,first_date,last_date,account_min_status,date_status,account_use_status,account_use,video_status,realname_status,phone_status,my_friend,not_black,late_status,late_times,dianfu_status,dianfu_times,black_status,black_user,black_times,not_late_black,borrow_credit_status,borrow_credit_first,borrow_credit_last,tender_credit_status,tender_credit_first,tender_credit_last,user_rank,first_credit,last_credit,webpay_statis,webpay_times, borrow_style,timelimit_day_first,timelimit_day_last,award_status,award_first,award_last,vouch_status,tuijian_status,
                           updatetime)
                    values({$user_id},1,1,0,0,0,1,{$timelimit_month},{$timelimit_month},1,{$apr},{$apr},1,
                    0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,'".time()."')";


            $result = Db::query($sql);
            //var_dump($result);
            if ($result){
                return 1;
            }else{
                return -1;
            }
        }
    }

    /**
     * ��ֵ���
     *
     * @param Array $data =array("nid"=>"������","verify_remark"=>"��˱�ע","status"=>"���״̬")
     * @return Boolen
     */
    function AddLog($data = array())
    {
        //��һ������ѯ�Ƿ����ʽ��¼
        $sql = "select * from yyd_account_log where `nid` = '{$data['nid']}'";
        $result = $this->db_fetch_array($sql);
        if ($result['nid'] != "") return "account_log_nid_exiest";

        //�ڶ�������ѯԭ�������ʽ�
        $sql = "select * from yyd_account where user_id='{$data['user_id']}'";
        $result = $this->db_fetch_array($sql);
        if ($result == false) {
            $sql = "insert into yyd_account set user_id='{$data['user_id']}',total=0";
            Db::query($sql);
            $sql = "select * from yyd_account where user_id='{$data['user_id']}'";
            $result = $this->db_fetch_array($sql);
        }

        //�������������û��Ĳ����¼
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
        $sql .= " `addtime` = '" . time() . "',`addip` = '" . self::ip_address() . "'";
        Db::query($sql);
        $id = Db::insert_id();

        $sql = "select * from yyd_account_log where user_id='{$data['user_id']}' and id='{$id}'";
        $result = $this->db_fetch_array($sql);

        //���Ĳ��������û���
        $sql = "update yyd_account set income={$result['income']},expend='{$result['expend']}',";
        $sql .= "balance_cash={$result['balance_cash']},balance_frost={$result['balance_frost']},";
        $sql .= "frost={$result['frost']},";
        $sql .= "await={$result['await']},";
        $sql .= "balance={$result['balance']},";
        $sql .= "total={$result['total']}";
        $sql .= " where user_id='{$data['user_id']}'";
        Db::query($sql);

        //��������������վ���ܷ���
        $sql = "select * from yyd_account_balance where `nid` = '{$data['nid']}'";
        $result = $this->db_fetch_array($sql);
        if ($result == false) {
            //������վ�Ĳ����
            $sql = "select * from yyd_account_balance order by id desc";
            $result = $this->db_fetch_array($sql);
            if ($result == false) {
                $result['total'] = 0;
                $result['balance'] = 0;
            }
            $total = $result['total'] + $data['income'] + $data['expend'];
            $sql = "insert into yyd_account_balance set total='{$total}',balance={$result['balance']}+" . $data['income'] . "-" . $data['expend'] . ",income='{$data['income']}',expend='{$data['expend']}',type='{$data['type']}',`money`='{$data['money']}',user_id='{$data['user_id']}',nid='{$data['nid']}',remark='{$data['remark']}', `addtime` = '" . time() . "',`addip` = '" . self::ip_address() . "'";
            Db::query($sql);
        }

        //�������������û����ܷ���
        $sql = "select * from yyd_account_users where `nid` = '{$data['nid']}'";
        $result = $this->db_fetch_array($sql);
        if ($result == false) {
            //�����û��Ĳ����
            $sql = "select * from yyd_account_users where user_id='{$data['user_id']}' order by id desc ";
            $result = $this->db_fetch_array($sql);
            if ($result == false) {
                $result['total'] = 0;
                $result['balance'] = 0;
            }
            $total = $result['total'] + $data['income'] + $data['expend'];
            $sql = "insert into yyd_account_users set total='{$total}',balance={$result['balance']}+" . $data['income'] . "-" . $data['expend'] . ",income='{$data['income']}',expend='{$data['expend']}',type='{$data['type']}',`money`='{$data['money']}',user_id='{$data['user_id']}',nid='{$data['nid']}',remark='{$data['remark']}', `addtime` = '" . time() . "',`addip` = '" .$this->ip_address() . "',await='{$data['await']}',frost='{$data['frost']}'";
            Db::query($sql);
        }
        return $data['nid'];

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
    public function AddUsersLog($data)
    {

        $sql = "insert into yyd_users_log set addtime='" . time() . "',addip='" . $this->ip_address() . "'";
        foreach ($data as $key => $value) {
            $sql .= ",`$key` = '$value'";
        }
        Db::query($sql);
    }


}