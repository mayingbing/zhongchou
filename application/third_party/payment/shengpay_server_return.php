<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>充值接口-服务器返回结果</title>
</head>

<body>
<?php
require_once ('log.php');
$key = "abcdefg";//密钥
$signMessage = $_REQUEST["Name"]. $_REQUEST["Version"]. $_REQUEST["Charset"]. $_REQUEST["TraceNo"]
. $_REQUEST["MsgSender"]. $_REQUEST["SendTime"]. $_REQUEST["InstCode"]. $_REQUEST["OrderNo"]
. $_REQUEST["OrderAmount"]. $_REQUEST["TransNo"]. $_REQUEST["TransAmount"]
. $_REQUEST["TransStatus"]. $_REQUEST["TransType"]. $_REQUEST["TransTime"]. $_REQUEST["MerchantNo"]
. $_REQUEST["ErrorCode"]. $_REQUEST["ErrorMsg"]. $_REQUEST["Ext1"]
. $_REQUEST["SignType"]. $key;

logstr("shengpay-->signMessage=".$signMessage);
$signMsg= strtoupper(md5($signMessage));
$SignMsgMerchant = $_REQUEST["SignMsg"];
logstr("shengpay-->orderpay notifySFT.php signMsg=".$signMsg."     SignMsgMerchant=".$SignMsgMerchant);
if(isset($SignMsgMerchant)&&strcasecmp($signMsg, $SignMsgMerchant)===0)
{
    //处理自己的业务逻辑
    logstr("shengpay-->OK");
    echo "OK";
}
?>
</body>
</html>
