<?
ob_start();
require_once ('../../core/config.inc.php');
require_once (ROOT_PATH.'modules/account/account.class.php');
require_once (ROOT_PATH.'modules/payment/payment.class.php');
$result = paymentClass::GetOne(array("nid"=>"dinpay"));
//****************************************	//MD5密钥要跟订单提交页相同，如Send.asp里的 key = "test" ,修改""号内 test 为您的密钥
											//如果您还没有设置MD5密钥请登陆我们为您提供商户后台，地址：https://merchant3.chinabank.com.cn/
	$_Md5Key=$result['fields']['VerficationCode']['value'];							//登陆后在上面的导航栏里可能找到“B2C”，在二级导航栏里有“MD5密钥设置”
											//建议您设置一个16位以上的密钥或更高，密钥最多64位，但设置16位已经足够了
//****************************************
	
   $merchant_code	= $_POST["merchant_code"];

	//通知类型
	$notify_type = $_POST["notify_type"];

	//通知校验ID
	$notify_id = $_POST["notify_id"];

	//接口版本
	$interface_version = $_POST["interface_version"];

	//签名方式
	$sign_type = $_POST["sign_type"];

	//签名
	$dinpaySign = $_POST["sign"];

	//商家订单号
	$order_no = $_POST["order_no"];

	//商家订单时间
	$order_time = $_POST["order_time"];

	//商家订单金额
	$order_amount = $_POST["order_amount"];

	//回传参数
	$extra_return_param = $_POST["extra_return_param"];

	//智付交易定单号
	$trade_no = $_POST["trade_no"];

	//智付交易时间
	$trade_time = $_POST["trade_time"];

	//交易状态 SUCCESS 成功  FAILED 失败
	$trade_status = $_POST["trade_status"];

	//银行交易流水号
	$bank_seq_no = $_POST["bank_seq_no"];


	/**
	 *签名顺序按照参数名a到z的顺序排序，若遇到相同首字母，则看第二个字母，以此类推，
	*同时将商家支付密钥key放在最后参与签名，组成规则如下：
	*参数名1=参数值1&参数名2=参数值2&……&参数名n=参数值n&key=key值
	**/


	//组织订单信息
	$signStr = "";
	if($bank_seq_no != "") {
		$signStr = $signStr."bank_seq_no=".$bank_seq_no."&";
	}
	if($extra_return_param != "") {
	    $signStr = $signStr."extra_return_param=".$extra_return_param."&";
	}
	$signStr = $signStr."interface_version=V3.0&";
	$signStr = $signStr."merchant_code=".$merchant_code."&";
	if($notify_id != "") {
	    $signStr = $signStr."notify_id=".$notify_id."&notify_type=".$notify_type."&";
	}

        $signStr = $signStr."order_amount=".$order_amount."&";
        $signStr = $signStr."order_no=".$order_no."&";
        $signStr = $signStr."order_time=".$order_time."&";
        $signStr = $signStr."trade_no=".$trade_no."&";
        $signStr = $signStr."trade_status=".$trade_status."&";

	if($trade_time != "") {
	     $signStr = $signStr."trade_time=".$trade_time."&";
	}
	$key=$_Md5Key;
	$signStr = $signStr."key=".$key;
	$signInfo = $signStr;
	//将组装好的信息MD5签名
	$sign = md5($signInfo);
	//echo "sign=".$sign."<br>";

	//比较智付返回的签名串与商家这边组装的签名串是否一致
	if($dinpaySign==$sign) {
		//验签成功
		/**
		此处进行商户业务操作
		业务结束
		*/
		accountClass::OnlineReturn(array("trade_no"=>$order_no));
	
		echo "SUCCESS";
		exit;
	}else
        {
		//验签失败 业务结束
	}
?>