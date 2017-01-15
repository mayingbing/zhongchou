<?php
include 'merchantProperties.php';
/*
 * @Description ������Ʒͨ�ýӿڷ��� 
 * @V3.0
 * @Author rui.xin
 */
 	
	#��Ʒͨ�ýӿ������ַ
	#$reqURL_onLine = "https://www.safeepay.com/online/safeepay/node";
	
		
	# ҵ������
	# ֧�����󣬹̶�ֵ"Buy" .	
	$p0_Cmd = "Buy";
		
	#	�ͻ���ַ
	# Ϊ"1": ��Ҫ�û����ͻ���ַ�����ױ�֧��ϵͳ;Ϊ"0": ����Ҫ��Ĭ��Ϊ "0".
	$p9_SAF = "0";

function getCallbackHmacString($r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType)
{
  
	include 'merchantProperties.php';
  
	#ȡ�ü���ǰ���ַ���
	$sbOld = "";
	#�����̼�ID
	$sbOld = $sbOld.$p1_MerId;
	#������Ϣ����
	$sbOld = $sbOld.$r0_Cmd;
	#����ҵ�񷵻���
	$sbOld = $sbOld.$r1_Code;
	#���뽻��ID
	$sbOld = $sbOld.$r2_TrxId;
	#���뽻�׽��
	$sbOld = $sbOld.$r3_Amt;
	#������ҵ�λ
	$sbOld = $sbOld.$r4_Cur;
	#�����ƷId
	$sbOld = $sbOld.$r5_Pid;
	#���붩��ID
	$sbOld = $sbOld.$r6_Order;
	#�����û�ID
	$sbOld = $sbOld.$r7_Uid;
	#�����̼���չ��Ϣ
	$sbOld = $sbOld.$r8_MP;
	#���뽻�׽����������
	$sbOld = $sbOld.$r9_BType;

	logstr($r6_Order,$sbOld,HmacMd5($sbOld,$merchantKey));
	return HmacMd5($sbOld,$merchantKey);

}


#	ȡ�÷��ش��е����в���
function getCallBackValue(&$r0_Cmd,&$r1_Code,&$r2_TrxId,&$r3_Amt,&$r4_Cur,&$r5_Pid,&$r6_Order,&$r7_Uid,&$r8_MP,&$r9_BType,&$hmac)
{  
	$r0_Cmd		= $_REQUEST['r0_Cmd'];
	$r1_Code	= $_REQUEST['r1_Code'];
	$r2_TrxId	= $_REQUEST['r2_TrxId'];
	$r3_Amt		= $_REQUEST['r3_Amt'];
	$r4_Cur		= $_REQUEST['r4_Cur'];
	$r5_Pid		= $_REQUEST['r5_Pid'];
	$r6_Order	= $_REQUEST['r6_Order'];
	$r7_Uid		= $_REQUEST['r7_Uid'];
	$r8_MP		= $_REQUEST['r8_MP'];
	$r9_BType	= $_REQUEST['r9_BType']; 
	$hmac			= $_REQUEST['hmac'];
	
	return null;
}

function CheckHmac($r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType,$hmac)
{
	if($hmac==getCallbackHmacString($r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType))
		return true;
	else
		return false;
}
		
  
function HmacMd5($data,$key)
{
// RFC 2104 HMAC implementation for php.
// Creates an md5 HMAC.
// Eliminates the need to install mhash to compute a HMAC
// Hacked by Lance Rushing(NOTE: Hacked means written)

//��Ҫ���û���֧��iconv���������Ĳ���������������
$key = iconv("GB2312","UTF-8",$key);
$data = iconv("GB2312","UTF-8",$data);

$b = 64; // byte length for md5
if (strlen($key) > $b) {
$key = pack("H*",md5($key));
}
$key = str_pad($key, $b, chr(0x00));
$ipad = str_pad('', $b, chr(0x36));
$opad = str_pad('', $b, chr(0x5c));
$k_ipad = $key ^ $ipad ;
$k_opad = $key ^ $opad;

return md5($k_opad . pack("H*",md5($k_ipad . $data)));
}

function logstr($orderid,$str,$hmac)
{
include 'merchantProperties.php';
$james=fopen($logName,"a+");
fwrite($james,"\r\n".date("Y-m-d H:i:s")."|orderid[".$orderid."]|str[".$str."]|hmac[".$hmac."]");
fclose($james);
}

?> 