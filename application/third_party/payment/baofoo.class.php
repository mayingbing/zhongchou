<?php

class baofooPayment {
    
	var $name = '宝付';//网银在线
    var $logo = 'baofoo';
    var $version = 4.0;
    var $description = "宝付。";
    var $type = 1;//1->只能启动，2->可以添加
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
	$NoticeType=1; //-Notice=1时支付结束会从页面跳转到PageUrl

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
					'label'=>'商户号',
					'type'=>'string'
				),
                'VerficationCode'=>array(
                        'label'=>'密钥',
                        'type'=>'string'
                )
		);
    }
}
?>