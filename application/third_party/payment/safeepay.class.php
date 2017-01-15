<?php

class safeepayPayment {
    
	var $name = '闪付';//网银在线
    var $logo = 'safeepay';
    var $version = 4.0;
    var $description = "闪付。";
    var $type = 1;//1->只能启动，2->可以添加
    var $charset = 'GB2312';
	
    var $orderby = 1;

    function ToSubmit($data){
	global $_G;
		/*
		 * 下面5个变量为必填参数
		 * */
		$strSign = "";

		//业务类型
		$p0_Cmd = "Buy";

		//商户编号
		$p1_MerId = $data["p1_MerId"];

		//商户订单号
		$p2_Order =  $data["trade_no"];

		//支付金额
		$p3_Amt = $data["money"];

		//交易币种
		$p4_Cur = "CNY";

		//商品名称
		$p5_Pid = "ddddd";

		//商户接收支付成功数据的地址
		$p8_Url = $_G['system']['con_weburl']."/modules/payment/paytype/callback.php";
		
		$strSign = $p0_Cmd.$p1_MerId.$p2_Order.$p3_Amt.$p4_Cur.$p5_Pid.$p8_Url;
		$merchantKey = $data["merchantKey"];
		$hmac = HmacMd5($strSign,$merchantKey);
		
		$url="https://gateway.safeepay.com/interface.html";
		
		$url.="p0_Cmd={$p0_Cmd}";
		$url.="&p1_MerId={$p1_MerId}";
		$url.="&p2_Order={$p2_Order}";
		$url.="&p3_Amt={$p3_Amt}";
		$url.="&p4_Cur={$p4_Cur}";
		$url.="&p5_Pid={$p5_Pid}";
		$url.="&p8_Url={$p8_Url}";
		$url.="&hmac={$hmac}";
		
		return $url;
    }

  
	function GetFields(){
        return array(
			'p1_MerId'=>array(
					'label'=>'商户编号',
					'type'=>'string'
				),
                'merchantKey'=>array(
                        'label'=>'密钥',
                        'type'=>'string'
                )
		);
    }
}
?>