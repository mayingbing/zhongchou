<?
ob_start();
require_once ('../../core/config.inc.php');
require_once (ROOT_PATH.'modules/account/account.class.php');
require_once (ROOT_PATH.'modules/payment/payment.class.php');
$result = paymentClass::GetOne(array("nid"=>"dinpay"));
//****************************************	//MD5��ԿҪ�������ύҳ��ͬ����Send.asp��� key = "test" ,�޸�""���� test Ϊ������Կ
											//�������û������MD5��Կ���½����Ϊ���ṩ�̻���̨����ַ��https://merchant3.chinabank.com.cn/
	$_Md5Key=$result['fields']['VerficationCode']['value'];							//��½��������ĵ�����������ҵ���B2C�����ڶ������������С�MD5��Կ���á�
											//����������һ��16λ���ϵ���Կ����ߣ���Կ���64λ��������16λ�Ѿ��㹻��
//****************************************
	
    $merchant_code	= $_POST["merchant_code"];

	//֪ͨ����
	$notify_type = $_POST["notify_type"];

	//֪ͨУ��ID
	$notify_id = $_POST["notify_id"];

	//�ӿڰ汾
	$interface_version = $_POST["interface_version"];

	//ǩ����ʽ
	$sign_type = $_POST["sign_type"];

	//ǩ��
	$dinpaySign = $_POST["sign"];

	//�̼Ҷ�����
	$order_no = $_POST["order_no"];

	//�̼Ҷ���ʱ��
	$order_time = $_POST["order_time"];

	//�̼Ҷ������
	$order_amount = $_POST["order_amount"];

	//�ش�����
	$extra_return_param = $_POST["extra_return_param"];

	//�Ǹ����׶�����
	$trade_no = $_POST["trade_no"];

	//�Ǹ�����ʱ��
	$trade_time = $_POST["trade_time"];

	//����״̬ SUCCESS �ɹ�  FAILED ʧ��
	$trade_status = $_POST["trade_status"];

	//���н�����ˮ��
	$bank_seq_no = $_POST["bank_seq_no"];


	/**
	 *ǩ��˳���ղ�����a��z��˳��������������ͬ����ĸ���򿴵ڶ�����ĸ���Դ����ƣ�
	*ͬʱ���̼�֧����Կkey����������ǩ������ɹ������£�
	*������1=����ֵ1&������2=����ֵ2&����&������n=����ֵn&key=keyֵ
	**/


	//��֯������Ϣ
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
	    $signStr = $signStr."notify_id=".$notify_id."&notify_type=page_notify&";
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
	//����װ�õ���ϢMD5ǩ��
	$sign = md5($signInfo);
	//echo "sign=".$sign."<br>";

	//�Ƚ��Ǹ����ص�ǩ�������̼������װ��ǩ�����Ƿ�һ��
	if($dinpaySign==$sign) {
	
		//accountClass::OnlineReturn(array("trade_no"=>$order_no));
		$msg = "֧���ɹ�";
		echo "<script>alert('{$msg}');location.href='/Index.php?user';</script>";
		//֧���ɹ����ɽ����߼�����
		//�̻�ϵͳ���߼����������жϽ��ж�֧��״̬�����¶���״̬�ȵȣ�......
		//��ǩ�ɹ�
		/**
		�˴������̻�ҵ�����
		ҵ�����
		*/
	}else
        {
		$msg = "֧��ʧ��";
		echo "<script>alert('{$msg}');location.href='/Index.php?user';</script>";
		//��ǩʧ�� ҵ�����
	}
?>