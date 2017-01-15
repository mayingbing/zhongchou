<?
ob_start();
require_once ('../../core/config.inc.php');
require_once (ROOT_PATH.'modules/account/account.class.php');
require_once (ROOT_PATH.'modules/payment/payment.class.php');
$result = paymentClass::GetOne(array("nid"=>"baofoo"));
//****************************************	//MD5密钥要跟订单提交页相同，如Send.asp里的 key = "test" ,修改""号内 test 为您的密钥
											//如果您还没有设置MD5密钥请登陆我们为您提供商户后台，地址：https://merchant3.chinabank.com.cn/
	$_Md5Key=$result['fields']['VerficationCode']['value'];							//登陆后在上面的导航栏里可能找到“B2C”，在二级导航栏里有“MD5密钥设置”
											//建议您设置一个16位以上的密钥或更高，密钥最多64位，但设置16位已经足够了
//****************************************
	
$_MerchantID=$_REQUEST['MerchantID'];//商户号
$_TransID =$_REQUEST['TransID'];//流水号
$_Result=$_REQUEST['Result'];//支付结果(1:成功,0:失败)
$_resultDesc=$_REQUEST['resultDesc'];//支付结果描述
$_factMoney=$_REQUEST['factMoney'];//实际成交金额
$_additionalInfo=$_REQUEST['additionalInfo'];//订单附加消息
$_SuccTime=$_REQUEST['SuccTime'];//交易成功时间
$_Md5Sign=$_REQUEST['Md5Sign'];//md5签名
$_WaitSign=md5($_MerchantID.$_TransID.$_Result.$_resultDesc.$_factMoney.$_additionalInfo.$_SuccTime.$_Md5Key);

/**
 * 判断返回信息，如果支付成功，并且支付结果可信，则做进一步的处理
 */


if ($_Md5Sign == $_WaitSign)
{
		accountClass::OnlineReturn(array("trade_no"=>$_TransID));
		$msg = "支付成功";
		echo "<script>alert('{$msg}');location.href='/?user&q=code/account/log';</script>";
		//支付成功，可进行逻辑处理！
		//商户系统的逻辑处理（例如判断金额，判断支付状态，更新订单状态等等）......

}else{
	$msg = "支付失败";
		echo "<script>alert('{$msg}');location.href='/?user&q=code/account/log';</script>";
}
?>