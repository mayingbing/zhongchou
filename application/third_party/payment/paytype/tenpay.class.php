<?php

class tenpayPayment  {

    var $name = '财付通';//支付宝（特别推荐！）
    var $logo = 'TENPAY';
    var $version = 20070902;
    var $description = "腾讯财付通。";
    var $type = 1;//1->只能启动，2->可以添加
    var $charset = 'GB2312';
	
    var $submitUrl = 'https://www.tenpay.com/cgi-bin/v1.0/pay_gate.cgi'; //  
    var $orderby = 3;
 
    public static function ToSubmit($data){
		
		/* 商户号 */

require_once ("modules/payment/classes/tenpay/RequestHandler.class.php");

/* 商户号，上线时务必将测试商户号替换为正式商户号 */
$partner = $data['member_id'];

/* 密钥 */
$key = $data['PrivateKey'];



//4位随机数
$randNum = rand(1000, 9999);

//订单号，此处用时间加随机数生成，商户根据自己情况调整，只要保持全局唯一就行
$out_trade_no = $data['trade_no'];


$data['money']=$data['money']*100;
/* 创建支付请求对象 */
$reqHandler = new RequestHandler();
$reqHandler->init();
$reqHandler->setKey($key);
$reqHandler->setGateUrl("https://gw.tenpay.com/gateway/pay.htm");

//----------------------------------------
//设置支付参数 
//----------------------------------------
$reqHandler->setParameter("total_fee", (int)$data['money']);  //总金额
//用户ip
$reqHandler->setParameter("spbill_create_ip", $_SERVER['REMOTE_ADDR']);//客户端IP
$reqHandler->setParameter("return_url", "http://".$_SERVER['HTTP_HOST']."/modules/payment/payReturnUrl.php");//支付成功后返回
$reqHandler->setParameter("partner", $partner);
$reqHandler->setParameter("out_trade_no", $out_trade_no);
$reqHandler->setParameter("notify_url", "http://".$_SERVER['HTTP_HOST']."/modules/payment/payNotifyUrl.php");
$reqHandler->setParameter("body", $data['subject']);
$reqHandler->setParameter("bank_type", "DEFAULT");  	  //银行类型，默认为财付通
$reqHandler->setParameter("fee_type", "1");               //币种
//系统可选参数
$reqHandler->setParameter("sign_type", "MD5");  	 	  //签名方式，默认为MD5，可选RSA
$reqHandler->setParameter("service_version", "1.0"); 	  //接口版本号
$reqHandler->setParameter("input_charset", "GBK");   	  //字符集
$reqHandler->setParameter("sign_key_index", "1");    	  //密钥序号

//业务可选参数
$reqHandler->setParameter("attach", "");             	  //附件数据，原样返回就可以了
$reqHandler->setParameter("product_fee", (int)$data['money']);        	  //商品费用
$reqHandler->setParameter("transport_fee", "0");      	  //物流费用
$reqHandler->setParameter("time_start", date("YmdHis"));  //订单生成时间
$reqHandler->setParameter("time_expire", "");             //订单失效时间

$reqHandler->setParameter("buyer_id", "");                //买方财付通帐号
$reqHandler->setParameter("goods_tag", "");               //商品标记




//请求的URL
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
                        'label'=>'客户号',
                        'type'=>'string'
                ),
                'PrivateKey'=>array(
                        'label'=>'私钥',
                        'type'=>'string'
                ),
                'authtype'=>array(
                    'label'=>'商家支付模式',
                    'type'=>'select',
                    'options'=>array('0'=>'套餐包量商家','1'=>'单笔支付商家')
                )
            );
    }
}
?>
