<?php
require_once(ROOT_PATH."modules/payment/HttpClient.class.php");
class gopayPayment  {

    var $name = '国付宝';//支付宝（特别推荐！）
    var $logo = 'GOPAY';
    var $version = '2.1';
    var $description = "国付宝。";
    var $type = 1;//1->只能启动，2->可以添加
    var $charset = 'GB2312';
	
    var $orderby = 3;
 
   	
    function ToSubmit($data){
    	
	    $version = "2.1";          
		$charset = "1";          
		$language = "1";         
		$signType = "1";         
		$tranCode = "8888";         
		$merchantID = $data["merchantID"];       
		$merOrderNum = $data["trade_no"];      

		$feeAmt =  '0.00';           
		$currencyType = "156";     
        $virCardNoIn = $data["virCardNoIn"];      
		$isRepeatSubmit = "0";   
		$goodsName = "国付宝充值";        
		$goodsDetail = "国付宝充值";      
		$buyerName = "空";        
		$buyerContact = "0";     
		$merRemark1 = "";       
		$merRemark2 = "";      
		$bankCode = "";         
		$userType = "1";         
    	$tranAmt = number_format($data['money'], 2, '.', '');
		$tranDateTime  = date("YmdHis",time());
		$url2 = "https://www.gopay.com.cn/PGServer/time"; 
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL,$url2); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
		$gopayServerTime =  curl_exec($ch); 
		

    	$tranIP = ip_address();//$_SERVER["HTTP_CLIENT_IP"];

    	$frontMerUrl =  $data["return_url"];
		$backgroundMerUrl = $data["return_url"];
          $VerficationCode = isset($data["VerficationCode"])?$data["VerficationCode"]:"12345678";
   		$submitUrl = 'https://www.gopay.com.cn/PGServer/Trans/WebClientAction.do?';
   		$signStr='version=['.$version.']tranCode=['.$tranCode.']merchantID=['.$merchantID.']merOrderNum=['.$merOrderNum.']tranAmt=['.$tranAmt.']feeAmt=['.$feeAmt.']tranDateTime=['.$tranDateTime.']frontMerUrl=['.$frontMerUrl.']backgroundMerUrl=['.$backgroundMerUrl.']orderId=[]gopayOutOrderId=[]tranIP=['.$tranIP.']respCode=[]gopayServerTime=['.$gopayServerTime.']VerficationCode=['.$VerficationCode.']';

		$SignMD5 = md5($signStr);
        $url = $submitUrl;

		$url .= "currencyType={$currencyType}&";
		$url .= "signValue={$SignMD5}&";
		$url .= "version={$version}&";
		$url .= "charset={$charset}&";
		
		$url .= "language={$language}&";
		$url .= "signType={$signType}&";
		$url .= "tranCode={$tranCode}&";
		
		$url .= "merchantID={$merchantID}&";
		$url .= "merOrderNum={$merOrderNum}&";
		$url .= "feeAmt={$feeAmt}&";
		
		$url .= "virCardNoIn={$virCardNoIn}&";
		$url .= "isRepeatSubmit={$isRepeatSubmit}&";
		$url .= "goodsName={$goodsName}&";
		$url .= "goodsDetail={$goodsDetail}&";
		$url .= "buyerName={$buyerName}&";
		$url .= "buyerContact={$buyerContact}&";
		$url .= "bankCode={$bankCode}&";
		$url .= "userType={$userType}&";
		$url .= "tranAmt={$tranAmt}&";
		$url .= "tranDateTime={$tranDateTime}&";
		$url .= "tranIP={$tranIP}&";
		$url .= "gopayServerTime={$gopayServerTime}&";
		$url .= "frontMerUrl={$frontMerUrl}&";
        $url .= "backgroundMerUrl={$backgroundMerUrl}";
     
        return $url;
		
    }

   function GetFields(){
        return array(
                'merchantID'=>array(
                        'label'=>'商户ID',
                        'type'=>'string'
                ),
                'virCardNoIn'=>array(
                        'label'=>'国付宝帐号',
                        'type'=>'string'
                ),
                'VerficationCode'=>array(
                        'label'=>'商户识别码',
                        'type'=>'string'
                ),
            );
    }
}
?>