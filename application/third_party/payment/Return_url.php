<?
ob_start();
require_once ('../../core/config.inc.php');
require_once (ROOT_PATH.'modules/account/account.class.php');
require_once (ROOT_PATH.'modules/payment/payment.class.php');
$result = paymentClass::GetOne(array("nid"=>"baofoo"));
//****************************************	//MD5��ԿҪ�������ύҳ��ͬ����Send.asp��� key = "test" ,�޸�""���� test Ϊ������Կ
											//�������û������MD5��Կ���½����Ϊ���ṩ�̻���̨����ַ��https://merchant3.chinabank.com.cn/
	$_Md5Key=$result['fields']['VerficationCode']['value'];							//��½��������ĵ�����������ҵ���B2C�����ڶ������������С�MD5��Կ���á�
											//����������һ��16λ���ϵ���Կ����ߣ���Կ���64λ��������16λ�Ѿ��㹻��
//****************************************
	
$_MerchantID=$_REQUEST['MerchantID'];//�̻���
$_TransID =$_REQUEST['TransID'];//��ˮ��
$_Result=$_REQUEST['Result'];//֧�����(1:�ɹ�,0:ʧ��)
$_resultDesc=$_REQUEST['resultDesc'];//֧���������
$_factMoney=$_REQUEST['factMoney'];//ʵ�ʳɽ����
$_additionalInfo=$_REQUEST['additionalInfo'];//����������Ϣ
$_SuccTime=$_REQUEST['SuccTime'];//���׳ɹ�ʱ��
$_Md5Sign=$_REQUEST['Md5Sign'];//md5ǩ��
$_WaitSign=md5($_MerchantID.$_TransID.$_Result.$_resultDesc.$_factMoney.$_additionalInfo.$_SuccTime.$_Md5Key);

/**
 * �жϷ�����Ϣ�����֧���ɹ�������֧��������ţ�������һ���Ĵ���
 */


if ($_Md5Sign == $_WaitSign)
{
		accountClass::OnlineReturn(array("trade_no"=>$_TransID));
		$msg = "֧���ɹ�";
		echo "<script>alert('{$msg}');location.href='/?user&q=code/account/log';</script>";
		//֧���ɹ����ɽ����߼�����
		//�̻�ϵͳ���߼����������жϽ��ж�֧��״̬�����¶���״̬�ȵȣ�......

}else{
	$msg = "֧��ʧ��";
		echo "<script>alert('{$msg}');location.href='/?user&q=code/account/log';</script>";
}
?>