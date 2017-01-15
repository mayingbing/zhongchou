<?php
/* 
 * 支付请求处理页面
 * @V2.0
 * @Author wu.dekai
 * */
include 'safeepayCommon.php';
/*
 * 下面5个变量为必填参数*/

$strSign = "";

//业务类型
$p0_Cmd = 'Buy';

//商户编号
$p1_MerId = "8181086";

//商户订单号
$p2_Order = $_GET['p2_Order'];

//支付金额
$p3_Amt =  $_GET['p3_Amt'];

//交易币种
$p4_Cur ='CNY';

$strSign = $p0_Cmd.$p1_MerId.$p2_Order.$p3_Amt.$p4_Cur;


/*
 * 下面参数为非必填项*/

//商品名称
$p5_Pid =$_GET['p5_Pid'];
if($p5_Pid) $strSign .= $p5_Pid;

//商品种类
$p6_Pcat ='pay';
if($p6_Pcat) $strSign .= $p6_Pcat;

//商品描述
$p7_Pdesc = 'dcdh_pay';
if($p7_Pdesc) $strSign .= $p7_Pdesc;

//商户接收支付成功数据的地址
//$p8_Url = '';//$_GET['p8_Url'];
//if($p8_Url) $strSign .= $p8_Url;

//送货地址
$p9_SAF = 'dcdh';
if($p9_SAF) $strSign .= $p9_SAF;

//商户扩展信息
$pa_MP ='dcdh';
if($pa_MP) $strSign .= $pa_MP;

//支付通道编码
//$pd_FrpId = '';
//if($pd_FrpId) $strSign .= $pd_FrpId;

//应答机制
$pr_NeedResponse = "1";
if($pr_NeedResponse) $strSign .= $pr_NeedResponse;

//测试用
$key = "0d3a-e694-4884-a921-339c";
$hmac = HmacMd5($strSign,$key);
?>
<html>
<head>
<title>pay</title>
</head>
<body onLoad="document.safeepay.submit();">
<form name='safeepay' action='https://gateway.safeepay.com/interface.html' method='post'>
<input type='hidden' name='p0_Cmd'					value='<?php echo $p0_Cmd; ?>'>
<input type='hidden' name='p1_MerId'				value='<?php echo $p1_MerId; ?>'>
<input type='hidden' name='p2_Order'				value='<?php echo $p2_Order; ?>'>
<input type='hidden' name='p3_Amt'					value='<?php echo $p3_Amt; ?>'>
<input type='hidden' name='p4_Cur'					value='<?php echo $p4_Cur; ?>'>
<input type='hidden' name='p5_Pid'					value='<?php echo $p5_Pid; ?>'>
<input type='hidden' name='p6_Pcat'					value='<?php echo $p6_Pcat; ?>'>
<input type='hidden' name='p7_Pdesc'				value='<?php echo $p7_Pdesc; ?>'>
<!--<input type='hidden' name='p8_Url'					value='<?php echo $p8_Url; ?>'>-->
<input type='hidden' name='p9_SAF'					value='<?php echo $p9_SAF; ?>'>
<input type='hidden' name='pa_MP'						value='<?php echo $pa_MP; ?>'>
<!--<input type='hidden' name='pd_FrpId'				value='<?php echo $pd_FrpId; ?>'>-->
<input type='hidden' name='pr_NeedResponse'	value='<?php echo $pr_NeedResponse; ?>'>
<input type='hidden' name='hmac'						value='<?php echo $hmac; ?>'>
</form>
</body>
</html>

