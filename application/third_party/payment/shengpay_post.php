
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>充值接口-提交信息处理</title>
<?php
    $MsgSender='245888';//$_GET['MerchantNo'];//商户号 245888
    $Name='B2CPayment';
    $Version='V4.1.1.1.1';
    $Charset='UTF-8';

    $SendTime=$_GET["OrderTime"];
    $OrderNo=$_GET['OrderNo'];//商户充值订单号（50个字符内、只允许使用数字、字母,确保在商户系统唯一）
    $OrderAmount=$_GET['OrderAmount'];//充值金额（以元为单位，仅允许两位小数。譬如：600.00）
    $OrderTime=$_GET['OrderTime'];//商户充值订单的提交时间（yyyyMMddHHmmss格式）
    $PayType='PT001,PT00,PT002'; //支付渠道 网银支付 余额支付 盛付通支付
    if(isset($PayType)&&strcasecmp($PayType, "PT001")===0){
        $InstCode=$_GET["InstCode"];
    }else{
        $InstCode="";
    }
    $PageUrl=$_GET["PageUrl"];
    $BackUrl='';
    $NotifyUrl=$_GET["NotifyUrl"];
    $ProductName=$_GET["ProductName"];
    $BuyerContact=$_GET["BuyerContact"];
    $BuyerIp=$_GET["BuyerIp"];
    $Ext1=$_GET["Ext1"];
    $SignType=$_GET["SignType"];
    $key="abcdefg";
    

    $testStr = $Name.$Version.$Charset.$MsgSender.$SendTime.$OrderNo.$OrderAmount.$OrderTime.$PayType.$InstCode.$PageUrl.$NotifyUrl.$ProductName.$BuyerContact.$BuyerIp.$Ext1.$SignType.$key;
    $signMsg=md5($testStr);    
    $payUrl="https://mas.sdo.com/web-acquire-channel/cashier.htm";

    $_SESSION['OrderMoney']=$OrderAmount; //设置提交金额的Session
    setcookie('OrderMoney', $OrderAmount, time() + 86400, "/");
//此处加入判断，如果前面出错了跳转到其他地方而不要进行提交
?>
</head>

<body onload="document.form1.submit()">
<form id="form1" name="form1" method="post" action="<?php echo $payUrl; ?>">

        <input name="Name"  type="hidden" id="Name" value="<?php echo $Name;?>" />
        <input name="Version"  type="hidden" id="Version" value="<?php echo $Version; ?>" />
        <input name="Charset"  type="hidden" id="Charset" value="<?php echo $Charset;?>" />
        <input name="MsgSender"  type="hidden" id="MsgSender" value="<?php echo $MsgSender;?>" />
        <input name="SendTime"  type="hidden" id="SendTime" value="<?php echo $SendTime?>" />
        <input name="OrderNo"  type="hidden" id="OrderNo" value="<?php echo $OrderNo;?>" />
        <input name="OrderAmount"  type="hidden" id="OrderAmount" value="<?php echo $OrderAmount;?>" />
        <input name="OrderTime"  type="hidden" id="OrderTime" value="<?php echo $OrderTime;?>" />
        <input name="PayType"  type="hidden" id="PayType" value="<?php echo $PayType;?>" />
        <input name="InstCode"  type="hidden" id="InstCode" value="<?php echo $InstCode;?>" />
        <input name="PageUrl"  type="hidden" id="PageUrl" value="<?php echo $PageUrl;?>" />
        <input name="BackUrl"  type="hidden" id="BackUrl" value="<?php echo $BackUrl;?>" />
        <input name="NotifyUrl"  type="hidden" id="NotifyUrl" value="<?php echo $NotifyUrl;?>" />
        <input name="ProductName"  type="hidden" id="ProductName" value="<?php echo $ProductName;?>" />
        <input name="BuyerContact"  type="hidden" id="BuyerContact" value="<?php echo $BuyerContact;?>" />
        <input name="BuyerIp"  type="hidden" id="BuyerIp" value="<?php echo $BuyerIp;?>" />
        <input name="Ext1"  type="hidden" id="Ext1" value="<?php echo $Ext1;?>" />
        <input name="SignType"  type="hidden" id="SignType" value="<?php echo $SignType;?>" />
        <input name="SignMsg"  type="hidden" id="SignMsg" value="<?php echo $signMsg;?>" />
</form>

</body>
</html>
