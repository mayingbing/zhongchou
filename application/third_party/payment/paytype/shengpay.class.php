<?php

class shengpayPayment {
    
	var $name = 'ʢ��';//��������
    var $logo = 'shengpay';
    var $version = 4.1;
    var $description = "ʢ����";
    var $type = 1;//1->ֻ��������2->�������
    var $charset = 'UTF-8';
	
    var $orderby = 3;

    function ToSubmit($data){
	global $_G;
	$MerchantID = $data["MerchantID"];	// 100000178
    $TransID =  $data["trade_no"];
	$PayID="";
	$TradeDate=date("YmdHis",time());
	
	
	$OrderMoney=number_format($data['money'],2,".","");

    $ProductName="91toufang";
    $Amount=1;
    $Username="91toufang";
    $AdditionalInfo="";

	$PageUrl=$_G['system']['con_weburl']."/modules/payment/shengpay_merchant_return.php";
	$ReturnUrl=$_G['system']['con_weburl']."/modules/payment/shengpay_server_return.php";
	$NoticeType=1; //-Notice=1ʱ֧���������ҳ����ת��PageUrl

	$Md5Key   = $data["VerficationCode"];
	
		$url="http://91toufang.com/modules/payment/shengpay_post.php?";
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