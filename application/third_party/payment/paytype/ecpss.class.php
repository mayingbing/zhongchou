<?php

class ecpssPayment {
    
	var $name = 'E��ͨ';//��������
    var $logo = 'ecpss';
    var $version = 4.0;
    var $description = "E��ͨ";
    var $type = 1;//1->ֻ��������2->�������
    var $charset = 'GB2312';
	
    var $orderby = 3;

    function ToSubmit($data){
	 global $_G;
	$MerNo = $data["MerchantID"];	// �̻��ţ�����Ϊ�����̻���1001���滻Ϊ�Լ����̻���(�ϰ��̻���Ϊ4λ��5λ,�°�Ϊ8λ)����
	$MD5key   = $data["VerficationCode"];
	$orderTime=date("YmdHis",time());
	
	$BillNo =  $data["trade_no"];
	$Remark='';
	
	$Amount=$data["money"];
	
	$ReturnURL=$_G['system']['con_weburl']."modules/payment/PayResult.php";
	 $AdviceURL =$_G['system']['con_weburl']."modules/payment/PayResult.php";
	
	 $md5src = $MerNo.$BillNo.$Amount.$ReturnURL.$MD5key;		//У��Դ�ַ���
	   $MD5info = strtoupper(md5($md5src));		//MD5������
	  $defaultBankNumber ="";
	  $products="Top Up";
	
		$url="https://pay.ecpss.cn/sslpayment?";
		$url.="MerNo={$MerNo}";
		$url.="&BillNo={$BillNo}";
		$url.="&Amount={$Amount}";
		$url.="&ReturnURL={$ReturnURL}";
		$url.="&AdviceURL={$AdviceURL}";
		$url.="&orderTime={$orderTime}";
		$url.="&defaultBankNumber={$defaultBankNumber}";
		$url.="&MD5info={$MD5info}";
		$url.="&Remark={$Remark}";
		$url.="&products={$products}";
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