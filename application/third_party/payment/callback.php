<?php

/*
 * @Description ��������֧���ӿڷ��� 
 * @V2.0
 * @Author wu.dekai
 */
 
include 'safeepayCommon.php';	
require_once ('../../core/config.inc.php');
require_once (ROOT_PATH.'modules/account/account.class.php');
require_once (ROOT_PATH.'modules/payment/payment.class.php');
	
#	ֻ��֧���ɹ�ʱ�����Ż�֪ͨ�̻�.
##֧���ɹ��ص������Σ�����֪ͨ������֧����������е�p8_Url�ϣ�������ض���;��������Ե�ͨѶ.

#	�������ز���.
$return = getCallBackValue($r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType,$hmac);

#	�жϷ���ǩ���Ƿ���ȷ��True/False��
$bRet = CheckHmac($r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType,$hmac);
#	���ϴ���ͱ�������Ҫ�޸�.
	 	
#	У������ȷ.
logstr("test","after check bRet=","123");
if($bRet){
	logstr("test","enter 1","123");
	if($r1_Code=="1"){

	#	��Ҫ�ȽϷ��صĽ�����̼����ݿ��ж����Ľ���Ƿ���ȣ�ֻ����ȵ�����²���Ϊ�ǽ��׳ɹ�.
	#	������Ҫ�Է��صĴ������������ƣ����м�¼�������Դ����ڽ��յ�֧�����֪ͨ���ж��Ƿ���й�ҵ���߼�������Ҫ�ظ�����ҵ���߼�������ֹ��ͬһ�������ظ��������������.      	  	
		$account = new accountClass();

		if($r9_BType=="1"){
			$account->OnlineReturn(array("trade_no"=>$r6_Order));
			echo "���׳ɹ�";
			echo  "<br />����֧��ҳ�淵��";
		}elseif($r9_BType=="2"){
			#�����ҪӦ�����������д��,��success��ͷ,��Сд������.
			$account->OnlineReturn(array("trade_no"=>$r6_Order));
			echo "success";
			echo "<br />���׳ɹ�";
			echo  "<br />����֧������������";      			 
		}
	}
	
}else{
	logstr("test","fail","123");
	echo "������Ϣ���۸�";
}
   
?>