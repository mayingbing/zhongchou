<?php

class baofooPayment {
    
	var $name = '����';//��������
    var $logo = 'baofoo';
    var $version = 4.0;
    var $description = "������";
    var $type = 1;//1->ֻ��������2->�������
    var $charset = 'GB2312';
	
    var $orderby = 3;

    function ToSubmit($data){
	global $_G;
	$MerchantID = $data["MerchantID"];	// 100000178
    $TransID =  $data["trade_no"];
	$PayID=1000;
	$TradeDate=date("YmdHis",time());
	
	
	$OrderMoney=$data["money"]*100;

    $ProductName="91toufang";
    $Amount=1;
    $Username="91toufang";
    $AdditionalInfo="";

	$PageUrl=$_G['system']['con_weburl']."modules/payment/baofoo_merchant_return.php";
	$ReturnUrl=$_G['system']['con_weburl']."modules/payment/baofoo_server_return.php";
	$NoticeType=1; //-Notice=1ʱ֧���������ҳ����ת��PageUrl

	$Md5Key   = $data["VerficationCode"];
	
		$url="http://91toufang.com/modules/payment/baofoo_post.php?";
		$url.="MemberID={$MerchantID}";
		$url.="&PayID={$PayID}";
		$url.="&TradeDate={$TradeDate}";
		$url.="&TransID={$TransID}";

		$url.="&OrderMoney={$OrderMoney}";

		$url.="&ProductName={$ProductName}";
		$url.="&Amount={$Amount}";
		$url.="&Username={$Username}";
        $url.="&AdditionalInfo={$AdditionalInfo}";

		$url.="&PageUrl={$PageUrl}";
		$url.="&ReturnUrl={$ReturnUrl}";
		$url.="&NoticeType={$NoticeType}";
		$url.="&Md5Sign={$Md5Sign}";
		return $url;
    }
	
	

	function GetFields(){
        return array(
			'MerchantID'=>array(
					'label'=>'�̻���',
					'type'=>'string'
				),
                'VerficationCode'=>array(
                        'label'=>'��Կ',
                        'type'=>'string'
                )
		);
    }
}
?>