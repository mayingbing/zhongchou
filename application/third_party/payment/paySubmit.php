<?php
/* 
 * ֧��������ҳ��
 * @V2.0
 * @Author wu.dekai
 * */
include 'safeepayCommon.php';
/*
 * ����5������Ϊ�������*/

$strSign = "";

//ҵ������
$p0_Cmd = 'Buy';

//�̻����
$p1_MerId = "8181086";

//�̻�������
$p2_Order = $_GET['p2_Order'];

//֧�����
$p3_Amt =  $_GET['p3_Amt'];

//���ױ���
$p4_Cur ='CNY';

$strSign = $p0_Cmd.$p1_MerId.$p2_Order.$p3_Amt.$p4_Cur;


/*
 * �������Ϊ�Ǳ�����*/

//��Ʒ����
$p5_Pid =$_GET['p5_Pid'];
if($p5_Pid) $strSign .= $p5_Pid;

//��Ʒ����
$p6_Pcat ='pay';
if($p6_Pcat) $strSign .= $p6_Pcat;

//��Ʒ����
$p7_Pdesc = 'dcdh_pay';
if($p7_Pdesc) $strSign .= $p7_Pdesc;

//�̻�����֧���ɹ����ݵĵ�ַ
//$p8_Url = '';//$_GET['p8_Url'];
//if($p8_Url) $strSign .= $p8_Url;

//�ͻ���ַ
$p9_SAF = 'dcdh';
if($p9_SAF) $strSign .= $p9_SAF;

//�̻���չ��Ϣ
$pa_MP ='dcdh';
if($pa_MP) $strSign .= $pa_MP;

//֧��ͨ������
//$pd_FrpId = '';
//if($pd_FrpId) $strSign .= $pd_FrpId;

//Ӧ�����
$pr_NeedResponse = "1";
if($pr_NeedResponse) $strSign .= $pr_NeedResponse;

//������
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

