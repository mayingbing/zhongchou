<?
require_once ('../../core/config.inc.php');
require_once (ROOT_PATH.'modules/account/account.class.php');
require_once (ROOT_PATH.'modules/payment/payment.class.php');
$result = paymentClass::GetOne(array("nid"=>"ecpss"));
//****************************************	//MD5密钥要跟订单提交页相同，如Send.asp里的 key = "test" ,修改""号内 test 为您的密钥
											//如果您还没有设置MD5密钥请登陆我们为您提供商户后台，地址：https://merchant3.chinabank.com.cn/
	$MD5key=$result['fields']['VerficationCode']['value'];							//登陆后在上面的导航栏里可能找到“B2C”，在二级导航栏里有“MD5密钥设置”
											//建议您设置一个16位以上的密钥或更高，密钥最多64位，但设置16位已经足够了
//****************************************
	
	//订单号
	$BillNo = $_POST["BillNo"];
	//金额
	$Amount = $_POST["Amount"];
	
	//支付状态
	$Succeed = $_POST["Succeed"];
	//支付结果
	$Result = $_POST["Result"];
	//取得的MD5校验信息
	$MD5info = $_POST["MD5info"]; 
	//备注
	$Remark = $_POST["Remark"];
/**
 * 判断返回信息，如果支付成功，并且支付结果可信，则做进一步的处理
 */
  $md5src = $BillNo.$Amount.$Succeed.$MD5key;
  //MD5检验结果
	$md5sign = strtoupper(md5($md5src));
if ($MD5info==$md5sign)
{

       if ($Succeed=="88") {
		accountClass::OnlineReturn(array("trade_no"=>$BillNo));
		$msg = "支付成功";
		echo "<script>alert('{$msg}');location.href='/?user&q=code/account/log';</script>";
		//支付成功，可进行逻辑处理！
		//商户系统的逻辑处理（例如判断金额，判断支付状态，更新订单状态等等）......
		}else{
		$msg = $Succeed;
		echo "<script>alert('{$msg}');location.href='/?user&q=code/account/log';</script>";
		}

}else{
	$msg = $Succeed;
		echo "<script>alert('{$msg}');location.href='/?user&q=code/account/log';</script>";
}
?>