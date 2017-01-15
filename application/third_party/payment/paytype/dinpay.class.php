<?php

class dinpayPayment {
    
	var $name = '智付';//网银在线
    var $logo = 'dinpay';
    var $version = 3.0;
    var $description = "智付。";
    var $type = 1;//1->只能启动，2->可以添加
    var $charset = 'GB2312';
	
    var $orderby = 3;

    function ToSubmit($data){
	global $_G;

		//参数编码字符集(必选)
		$input_charset = "GBK";

		//接口版本(必选)固定值:V3.0
		$interface_version = "V3.0";

		//商家号（必填）
		$merchant_code = $data["MerchantID"];

		//后台通知地址(必填)
		$notify_url = $_G['system']['con_weburl']."modules/payment/dinpay_notify_url.php";

		//定单金额（必填）
		$order_amount = $data["money"];

		//商家定单号(必填)
		$order_no =  $data["trade_no"];

		//商家定单时间(必填)
		$order_time = date("Y-m-d H:i:s",time());

		//签名方式(必填)
		$sign_type = "MD5";

		//商品编号(选填)
		$product_code = "";

		//商品描述（选填）
		$product_desc = "";

		//商品名称（必填）
		$product_name = "chongzhi";

		//端口数量(选填)
		$product_num = "";

		//页面跳转同步通知地址(选填)
		$return_url =  $_G['system']['con_weburl']."modules/payment/dinpay_return_url.php";

		//业务类型(必填)
		$service_type = "direct_pay";

		//商品展示地址(选填)
		$show_url = "";

		//公用业务扩展参数（选填）
		$extend_param = "";

		//公用业务回传参数（选填）
		$extra_return_param = "";

		// 直联通道代码（选填）
		$bank_code = "";

		//客户端IP（选填）
		$client_ip = "";

	/* 注  new String(参数.getBytes("UTF-8"),"此页面编码格式"); 若为GBK编码 则替换UTF-8 为GBK*/
	if($product_name != "") {
	  $product_name = mb_convert_encoding($product_name, "GBK", "GBK");
	}
	if($product_desc != "") {
	  $product_desc = mb_convert_encoding($product_desc, "GBK", "GBK");
	}
	if($extend_param != "") {
	  $extend_param = mb_convert_encoding($extend_param, "GBK", "GBK");
	}
	if($extra_return_param != "") {
	  $extra_return_param = mb_convert_encoding($extra_return_param, "GBK", "GBK");
	}
	if($product_code != "") {
	  $product_code = mb_convert_encoding($product_code, "GBK", "GBK");
	}
	if($return_url != "") {
	  $return_url = mb_convert_encoding($return_url, "GBK", "GBK");
	}
	if($show_url != "") {
	  $show_url = mb_convert_encoding($show_url, "GBK", "GBK");
	}


	/*
	**
	 ** 签名顺序按照参数名a到z的顺序排序，若遇到相同首字母，则看第二个字母，以此类推，同时将商家支付密钥key放在最后参与签名，
	 ** 组成规则如下：
	 ** 参数名1=参数值1&参数名2=参数值2&……&参数名n=参数值n&key=key值
	 **/
	$signSrc= "";

	//组织订单信息
	if($bank_code != "") {
		$signSrc = $signSrc."bank_code=".$bank_code."&";
	}
	if($client_ip != "") {
                $signSrc = $signSrc."client_ip=".$client_ip."&";
	}
	if($extend_param != "") {
		$signSrc = $signSrc."extend_param=".$extend_param."&";
	}
	if($extra_return_param != "") {
		$signSrc = $signSrc."extra_return_param=".$extra_return_param."&";
	}
	if($input_charset != "") {
		$signSrc = $signSrc."input_charset=".$input_charset."&";
	}
	if($interface_version != "") {
		$signSrc = $signSrc."interface_version=".$interface_version."&";
	}
	if($merchant_code != "") {
		$signSrc = $signSrc."merchant_code=".$merchant_code."&";
	}
	if($notify_url != "") {
		$signSrc = $signSrc."notify_url=".$notify_url."&";
	}
	if($order_amount != "") {
		$signSrc = $signSrc."order_amount=".$order_amount."&";
	}
	if($order_no != "") {
		$signSrc = $signSrc."order_no=".$order_no."&";
	}
	if($order_time != "") {
		$signSrc = $signSrc."order_time=".$order_time."&";
	}
	if($product_code != "") {
		$signSrc = $signSrc."product_code=".$product_code."&";
	}
	if($product_desc != "") {
		$signSrc = $signSrc."product_desc=".$product_desc."&";
	}
	if($product_name != "") {
		$signSrc = $signSrc."product_name=".$product_name."&";
	}
	if($product_num != "") {
		$signSrc = $signSrc."product_num=".$product_num."&";
	}
	if($return_url != "") {
		$signSrc = $signSrc."return_url=".$return_url."&";
	}
	if($service_type != "") {
		$signSrc = $signSrc."service_type=".$service_type."&";
	}
	if($show_url != "") {
		$signSrc = $signSrc."show_url=".$show_url."&";
	}
        //设置密钥
	$key = $data["VerficationCode"]; // <支付密钥> 注:此处密钥必须与商家后台里的密钥一致
	$signSrc = $signSrc."key=".$key;

	$singInfo = $signSrc;
	//echo "singInfo=".$singInfo."<br>";

	//签名
	$sign = md5($singInfo);
	//echo "sign=".$sign."<br>";
	//echo $singInfo;
	
		$url=$_G['system']['con_weburl']."modules/payment/dinpay_order.php?input_charset=GBK";
		$url.="&sign={$sign}";
		$url.="&merchant_code={$merchant_code}";
		$url.="&bank_code={$bank_code}";
		$url.="&order_no={$order_no}";
		$url.="&order_amount={$order_amount}";
		$url.="&service_type={$service_type}";
		$url.="&input_charset={$input_charset}";
		$url.="&notify_url={$notify_url}";
		$url.="&interface_version={$interface_version}";
		$url.="&sign_type={$sign_type}";
		$url.="&order_time={$order_time}";
		$url.="&product_name={$product_name}";
		$url.="&client_ip={$client_ip}";
		$url.="&extend_param={$extend_param}";
		$url.="&extra_return_param={$extra_return_param}";
		$url.="&product_code={$product_code}";
		$url.="&product_desc={$product_desc}";
		$url.="&product_num={$product_num}";
		$url.="&return_url={$return_url}";
		$url.="&show_url={$show_url}";
		$url.="&key={$key}";
		
		return $url;
    }
	
	

	function GetFields(){
        return array(
			'MerchantID'=>array(
					'label'=>'商户号',
					'type'=>'string'
				),
                'VerficationCode'=>array(
                        'label'=>'密钥',
                        'type'=>'string'
                )
		);
    }
}
?>