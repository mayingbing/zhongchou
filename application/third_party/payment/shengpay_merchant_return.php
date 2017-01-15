<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head >
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>支付结果通知</title>
</head>
<body>
    <form id="form1" >
    <div>
<?php
    //设置默认时区
date_default_timezone_set('PRC');//设置成中国时区
require_once ('../../core/config.inc.php');
require_once (ROOT_PATH.'modules/account/account.class.php');
require_once ('log.php');

    $verifyResult = "false";

    $key ="abcdefg";
    $signMessage = $_REQUEST["Name"].$_REQUEST["Version"].$_REQUEST["Charset"].$_REQUEST["TraceNo"].$_REQUEST["MsgSender"].$_REQUEST["SendTime"].$_REQUEST["InstCode"].$_REQUEST["OrderNo"]
    .$_REQUEST["OrderAmount"] .$_REQUEST["TransNo"] .$_REQUEST["TransAmount"]
    .$_REQUEST["TransStatus"] .$_REQUEST["TransType"] .$_REQUEST["TransTime"] .$_REQUEST["MerchantNo"]
    .$_REQUEST["ErrorCode"] .$_REQUEST["ErrorMsg"] .$_REQUEST["Ext1"]
    .$_REQUEST["SignType"].$key;

    $signMsg= strtoupper(md5($signMessage));
    $org_signMsg = $_REQUEST["SignMsg"];
    
    if(isset($org_signMsg)&&strcasecmp($signMsg, $org_signMsg)===0)
    {
        $verifyResult = "true";
    }
    $SignMsgMerchant= $signMsg;
    
    echo "比对结果:" . $verifyResult. "****<br/>";
    if (isset($verifyResult)&&strcasecmp($verifyResult, "true")===0)
    {
        echo "签名验证成功#######<br/>";
        $transStatus=$_REQUEST["TransStatus"];
        if (isset($transStatus)&&strcasecmp(trim($transStatus), "01")===0)
        {
            $account = new accountClass();
		    $account->OnlineReturn(array("trade_no"=>$_REQUEST["TransNo"]));
            setcookie("OrderMoney", "", time() - 3600);
		    echo("<script>alert('支付成功');</script>");
            echo "更新数据库成功OK";
        }else
        {
            echo  "更新订单失败";
        }
    ?>
	<h1>支付成功，请确认！</h1>
    <table align="center" width="350" cellpadding="5" cellspacing="0">
        <tr>
            <td align="right">订单号：</td>
            <td align="left"><?php echo $_REQUEST["OrderNo"]?></td>
        </tr>
		<tr>
           <td align="right">盛付通订单号：</td>
           <td align="left"><?php echo $_REQUEST["TraceNo"]?></td>
        </tr>
        <tr>
           <td align="right">实际付款总金额：</td>
           <td align="left"><?php echo $_REQUEST["TransAmount"]?></td>
        </tr>
		<tr>
           <td  align="right">支付时间：</td>
           <td align="left"><?php echo $_REQUEST["TransTime"]?></td>
        </tr>
    </table>
	<?php 
    }else{
        echo "Verify MAC failed, _TransType.form:".$_REQUEST["TransType"]."<br/>";
        echo "Verify MAC failed, _InstCode.form:".$_REQUEST["InstCode"]."<br/>";
        echo "Verify MAC failed, _SignMsg.form:".$_REQUEST["SignMsg"]."<br/>";
        echo "Verify MAC failed, SignMsgMerchant:".$SignMsgMerchant."<br/>";
        echo "Verify MAC failed, _SignMsg.form:".$signMessage."<br/>";
    }
	?>
    </div>
    </form>
</body>
</html>