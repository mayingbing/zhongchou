<?
/******************************
 * $File: payment.php
 * $Description: ֧����ʽ
 * $Author: hummer 
 * $Time:2010-08-09
 * $Update:None 
 * $UpdateDate:None 
 * Copyright(c) 2013 by jichu.com. All rights reserved
******************************/

if (!defined('ROOT_PATH'))  /*die('���ܷ���')*/echo "<script>window.location.href='/404.htm';</script>";//��ֱֹ�ӷ���

$_A['list_purview']["payment"]["name"] = "֧������";
$_A['list_purview']["payment"]["result"]["payment_list"] = array("name"=>"֧������","url"=>"code/payment/list");

require_once("payment.class.php");

check_rank("payment_list");//���Ȩ��
/**
 * �������Ϊ�յĻ�����ʾ���е��ļ��б�
**/
if ($_A['query_type'] == "list"){
	
	//�޸�״̬
	if (isset($_REQUEST['id']) && isset($_REQUEST['status'])){
		$sql = "update {payment} set status='".$_REQUEST['status']."' where id = ".$_REQUEST['id'];
		$mysql->db_query($sql);	
	}
	
	$result = paymentClass::GetList();
	
	if (is_array($result)){
		$_A['payment_list'] = $result;
	}
}


/**
 * �������Ϊ�յĻ�����ʾ���е��ļ��б�
**/
elseif ($_A['query_type'] == "all"){
	//�޸�״̬
	
	$result = paymentClass::GetListAll();
	if (is_array($result)){
		$_A['payment_list'] = $result;
	}else{
		$msg = array($result);
	}
}
/**
 * ���
**/
elseif ($_A['query_type'] == "new"  || $_A['query_type'] == "edit" || $_A['query_type'] == "start" ){
	
	if (isset($_POST['name'])){
		$var = array("name","nid","order","status","description");
		$data = post_var($var);
		
		/*
		if ($_POST['clearlitpic']==1){
			$data['litpic'] = "";
		}else{
			$_G['upimg']['file'] = "litpic";
			$_G['upimg']['mask_status'] = 0;
			$pic_result = $upload->upfile($_G['upimg']);
			if (!empty($pic_result)){
				$data['litpic'] = $pic_result['filename'];
			}
		}
		*/
		$config = isset($_POST['config'])?$_POST['config']:"";
		$data['config'] = serialize($config);
		$data['type'] = $_A['query_type'];
		if ($_A['query_type'] == "edit"){
			$data['id'] = isset($_POST['id'])?$_POST['id']:"";
		}
		$result = paymentClass::Action($data);
		
		
		if ($result >0){
			$msg = array($MsgInfo['payment_action_success'],"",$_A['query_url']);
		}else{
			$msg = array($result);
		}
		
		//�������Ա������¼
		$admin_log["user_id"] = $_G['user_id'];
		$admin_log["code"] = "payment";
		$admin_log["type"] = "action";
		$admin_log["operating"] = $_A['query_type'];
		$admin_log["article_id"] = $data['id'];
		$admin_log["result"] = $result>0?1:0;
		$admin_log["content"] =  $msg[0];
		$admin_log["data"] =  join(",",$data);
		usersClass::AddAdminLog($admin_log);
	}
	
	elseif ($_A['query_type'] == "edit" || $_A['query_type'] == "new" || $_A['query_type'] == "start" ){
		$data['nid'] = $_REQUEST['nid'];
		$data['id'] = isset($_REQUEST['id'])?$_REQUEST['id']:"";
		$result = paymentClass::GetOne($data);
	
		if (is_array($result)){
			$result['nid'] = $data['nid'];
			$_A['payment_result'] = $result;
		}else{
			$msg = array($result);
		}
		
	}
	
}			

	
/**
 * ɾ��
**/
elseif ($_A['query_type'] == "del"){
	$data['id'] = $_REQUEST['id'];
	$result = paymentClass::Delete($data);
	if ($result >0){
		$msg = array($MsgInfo['payment_del_success'],"",$_A['query_url']);
	}else{
		$msg = array($MsgInfo[$result]);
	}
	//�������Ա������¼
	$admin_log["user_id"] = $_G['user_id'];
	$admin_log["code"] = "payment";
	$admin_log["type"] = "action";
	$admin_log["operating"] = "del";
	$admin_log["article_id"] = $data['id'];
	$admin_log["result"] = $result>0?1:0;
	$admin_log["content"] =  $msg[0];
	usersClass::AddAdminLog($admin_log);
}
	
?>