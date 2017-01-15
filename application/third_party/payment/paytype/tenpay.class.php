<?php

class tenpayPayment  {

    var $name = '�Ƹ�ͨ';//֧�������ر��Ƽ�����
    var $logo = 'TENPAY';
    var $version = 20070902;
    var $description = "��Ѷ�Ƹ�ͨ��";
    var $type = 1;//1->ֻ��������2->�������
    var $charset = 'GB2312';
	
    var $submitUrl = 'https://www.tenpay.com/cgi-bin/v1.0/pay_gate.cgi'; //  
    var $orderby = 3;
 
    public static function ToSubmit($data){
		
		/* �̻��� */

require_once ("modules/payment/classes/tenpay/RequestHandler.class.php");

/* �̻��ţ�����ʱ��ؽ������̻����滻Ϊ��ʽ�̻��� */
$partner = $data['member_id'];

/* ��Կ */
$key = $data['PrivateKey'];



//4λ�����
$randNum = rand(1000, 9999);

//�����ţ��˴���ʱ�����������ɣ��̻������Լ����������ֻҪ����ȫ��Ψһ����
$out_trade_no = $data['trade_no'];


$data['money']=$data['money']*100;
/* ����֧��������� */
$reqHandler = new RequestHandler();
$reqHandler->init();
$reqHandler->setKey($key);
$reqHandler->setGateUrl("https://gw.tenpay.com/gateway/pay.htm");

//----------------------------------------
//����֧������ 
//----------------------------------------
$reqHandler->setParameter("total_fee", (int)$data['money']);  //�ܽ��
//�û�ip
$reqHandler->setParameter("spbill_create_ip", $_SERVER['REMOTE_ADDR']);//�ͻ���IP
$reqHandler->setParameter("return_url", "http://".$_SERVER['HTTP_HOST']."/modules/payment/payReturnUrl.php");//֧���ɹ��󷵻�
$reqHandler->setParameter("partner", $partner);
$reqHandler->setParameter("out_trade_no", $out_trade_no);
$reqHandler->setParameter("notify_url", "http://".$_SERVER['HTTP_HOST']."/modules/payment/payNotifyUrl.php");
$reqHandler->setParameter("body", $data['subject']);
$reqHandler->setParameter("bank_type", "DEFAULT");  	  //�������ͣ�Ĭ��Ϊ�Ƹ�ͨ
$reqHandler->setParameter("fee_type", "1");               //����
//ϵͳ��ѡ����
$reqHandler->setParameter("sign_type", "MD5");  	 	  //ǩ����ʽ��Ĭ��ΪMD5����ѡRSA
$reqHandler->setParameter("service_version", "1.0"); 	  //�ӿڰ汾��
$reqHandler->setParameter("input_charset", "GBK");   	  //�ַ���
$reqHandler->setParameter("sign_key_index", "1");    	  //��Կ���

//ҵ���ѡ����
$reqHandler->setParameter("attach", "");             	  //�������ݣ�ԭ�����ؾͿ�����
$reqHandler->setParameter("product_fee", (int)$data['money']);        	  //��Ʒ����
$reqHandler->setParameter("transport_fee", "0");      	  //��������
$reqHandler->setParameter("time_start", date("YmdHis"));  //��������ʱ��
$reqHandler->setParameter("time_expire", "");             //����ʧЧʱ��

$reqHandler->setParameter("buyer_id", "");                //�򷽲Ƹ�ͨ�ʺ�
$reqHandler->setParameter("goods_tag", "");               //��Ʒ���




//�����URL
$reqUrl = $reqHandler->getRequestURL();
/*$submit_url=$reqHandler->getGateUrl();
$params = $reqHandler->getAllParameters();
$url="?t=1";
foreach($params as $k => $v) {
$url.="&".$k."=".$v;
}*/
		return $reqUrl;
    }

   function GetFields(){
        return array(
                'member_id'=>array(
                        'label'=>'�ͻ���',
                        'type'=>'string'
                ),
                'PrivateKey'=>array(
                        'label'=>'˽Կ',
                        'type'=>'string'
                ),
                'authtype'=>array(
                    'label'=>'�̼�֧��ģʽ',
                    'type'=>'select',
                    'options'=>array('0'=>'�ײͰ����̼�','1'=>'����֧���̼�')
                )
            );
    }
}
?>
