<?
require_once ('../../core/config.inc.php');
require_once (ROOT_PATH.'modules/account/account.class.php');
require_once (ROOT_PATH.'modules/payment/payment.class.php');
$result = paymentClass::GetOne(array("nid"=>"ecpss"));
//****************************************	//MD5��ԿҪ�������ύҳ��ͬ����Send.asp��� key = "test" ,�޸�""���� test Ϊ������Կ
											//�������û������MD5��Կ���½����Ϊ���ṩ�̻���̨����ַ��https://merchant3.chinabank.com.cn/
	$MD5key=$result['fields']['VerficationCode']['value'];							//��½��������ĵ�����������ҵ���B2C�����ڶ������������С�MD5��Կ���á�
											//����������һ��16λ���ϵ���Կ����ߣ���Կ���64λ��������16λ�Ѿ��㹻��
//****************************************
	
	//������
	$BillNo = $_POST["BillNo"];
	//���
	$Amount = $_POST["Amount"];
	
	//֧��״̬
	$Succeed = $_POST["Succeed"];
	//֧�����
	$Result = $_POST["Result"];
	//ȡ�õ�MD5У����Ϣ
	$MD5info = $_POST["MD5info"]; 
	//��ע
	$Remark = $_POST["Remark"];
/**
 * �жϷ�����Ϣ�����֧���ɹ�������֧��������ţ�������һ���Ĵ���
 */
  $md5src = $BillNo.$Amount.$Succeed.$MD5key;
  //MD5������
	$md5sign = strtoupper(md5($md5src));
if ($MD5info==$md5sign)
{

       if ($Succeed=="88") {
		accountClass::OnlineReturn(array("trade_no"=>$BillNo));
		$msg = "֧���ɹ�";
		echo "<script>alert('{$msg}');location.href='/?user&q=code/account/log';</script>";
		//֧���ɹ����ɽ����߼�����
		//�̻�ϵͳ���߼����������жϽ��ж�֧��״̬�����¶���״̬�ȵȣ�......
		}else{
		$msg = $Succeed;
		echo "<script>alert('{$msg}');location.href='/?user&q=code/account/log';</script>";
		}

}else{
	$msg = $Succeed;
		echo "<script>alert('{$msg}');location.href='/?user&q=code/account/log';</script>";
}
?>