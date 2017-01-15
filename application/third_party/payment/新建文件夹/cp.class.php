<?php

class cpPayment {


    function ToSubmit($data){
		$ChkValue=self::check($data);
		$data['notify_url'] = "http://www.catoreasy.com/modules/payment/return.php";//通知地址
		$data['return_url'] = "http://www.catoreasy.com/modules/payment/return.php";//回调地址
		$money=str_pad($data['money']*100,12,"0",STR_PAD_LEFT);
		$time=date("Ymd");
		$url="https://payment.chinapay.com/pay/TransGet?";
		$url.="MerId={$data['cp_id']}";
		$url.="&OrdId={$data['nid']}";
		$url.="&TransAmt={$money}";
		$url.="&CuryId=156";
		$url.="&TransDate={$time}";
		$url.="&TransType=0001";
		$url.="&Version=20070129";
		$url.="&Priv1={$data['username']}";
		$url.="&BgRetUrl={$data['notify_url']}";
		$url.="&PageRetUrl={$data['return_url']}";
		$url.="&ChkValue={$ChkValue}";
		return $url;
    }
	
	
	function check($data){
		include_once(ROOT_PATH."/modules/payment/classes/chinapay/netpayclient.php");
		$merid = buildKey(ROOT_PATH."/modules/payment/classes/chinapay/MerPrk.key");
		if(!$merid) {
			echo "导入私钥文件失败！";
			exit;
		}
		$money=str_pad($data['money']*100,12,"0",STR_PAD_LEFT);
		$ordid = $data['nid'];
		$transamt = $money;
		$curyid = 156;
		$transdate = date("Ymd");
		$transtype = "0001";
		$transtime = date("Ymd");
		$extflag = "00";
		$countryid = "0086";
		$priv1 = $data['username'];
		$timezone = "+02";
		$dstflag = "1";
		$plain = $merid.$ordid.$transamt.$curyid.$transdate.$transtype.$priv1;
		$chkvalue = sign($plain);
		if (!$chkvalue) {
			echo "签名失败！";
			exit;
		}else{
			return $chkvalue;
		}
	}

	function GetFields(){
        return array(
			'cp_id'=>array(
					'label'=>'商户号',
					'type'=>'string'
				)
		);
    }
}
?>