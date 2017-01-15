<?php

class ebatongPayment {
    
	var $name = '����';
    var $logo = 'ebatong';
    var $version = 1.7;
    var $description = "������";
    var $type = 1;//1->ֻ��������2->�������
    var $charset = 'UTF-8';
	
    var $orderby = 3;

    function ToSubmit($data){
	global $_G;
	$MerchantNo = $data["MerchantID"];

    $BuyerContact="service@91toufang.com";
    $BuyerIp="115.29.78.161";


    /**************************************************
    ****************
    **/

    $input_charset      = "utf-8";                                     // �ַ���

    $service            = "create_direct_pay_by_user";              // �������ƣ���ʱ����
    $partner            = "201501131139398055";                         // �������̻�ID
    $sign_type          = "MD5";                                        // ǩ���㷨
    $notify_url         = $_G['system']['con_weburl']."/modules/payment/ebatong_server_return.php";  // �������첽֪ͨҳ��·��
    $return_url         = $_G['system']['con_weburl']."/modules/payment/ebatong_merchant_return.php";  // ҳ����תͬ��֪ͨҳ��·��
    $error_notify_url   = "";                                           // �������ʱ��֪ͨҳ��·�����ɿ�

    // �������ò���
    $anti_phishing_key  = "";                                           // ͨ��ʱ�����ѯ�ӿڻ�ȡ�ļ���ϵͳʱ�������Чʱ�䣺30��
    $exter_invoke_ip    = "";                                           // �û����ⲿϵͳ��������ʱ�����ⲿϵͳ��¼���û�IP��ַ 
           
    // �װ�ͨ�����̻���վΨһ������
    $out_trade_no       = $data["trade_no"];
    $subject            = "91toufang";                                // ��Ʒ����
    $payment_type       = "1";                                          // ֧�����ͣ�Ĭ��ֵΪ��1����Ʒ����
     
    /**
     * �������װ�ͨ�û�ID�������ڡ������װ�ͨ�û�����
     * ���߲���ͬʱΪ��
     */
    $seller_email        = "";                                          // �����װ�ͨ�û���
    $seller_id           = "201501131139398055";                        // �����װ�ͨ�û�ID  
    $buyer_email         = "";                                          // ����װ�ͨ�û������ɿ�
    $buyer_id            = "";                                          // ����װ�ͨ�û�ID���ɿ� 
   // $exter_invoke_ip     = "115.29.78.161";                             //����IP   
    $price               = number_format($data['money'],2,".","");                                          // ��Ʒ����
    $total_fee           = "";                                     // ���׽��
    $quantity            = "1";                                          // ��������  
    $body                = "";                                          // ��Ʒ�������ɿ�
    $show_url            = "";                                          // ��Ʒչʾ��ַ���ɿ�
    $pay_method          = "bankPay";                                   // ֧����ʽ��directPay(���֧��)��bankPay(����֧��)���ɿ�
    $default_bank        = "";                                    // Ĭ������ ,���֧������
    /**
     ABC_B2C=ũ��
     BJRCB_B2C=����ũ����ҵ����
     BOC_B2C=�й�����
     CCB_B2C=����
     CEBBANK_B2C=�й��������
     CGB_B2C=�㶫��չ����
     CITIC_B2C=��������
     CMB_B2C=��������
     CMBC_B2C=�й���������
     COMM_B2C=��ͨ����
     FDB_B2C=��������
     HXB_B2C=��������
     HZCB_B2C_B2C=��������
     ICBC_B2C=����������
     NBBANK_B2C=��������
     PINGAN_B2C=ƽ������
     POSTGC_B2C=�й�������������
     SDB_B2C=���ڷ�չ����
     SHBANK_B2C=�Ϻ�����
     SPDB_B2C=�Ϻ��ֶ���չ����
     */
     $royalty_parameters = ""; // ���10�������ϸ��ʾ����100001=0.01|100002=0.02 ��ʾidΪ100001���û�Ҫ����0.01Ԫ��idΪ100002���û�Ҫ����0.02Ԫ��
     $royalty_type = "";                                              // ������ͣ�Ŀǰֻ֧��һ�����ͣ�10����ʾ���Ҹ���������ɣ�

	$Md5Key   = $data["VerficationCode"];
	
	$url="http://test.91toufang.com/modules/payment/ebatong_post.php?";
	$url.="service={$service}";
    $url.="&partner={$partner}";
    $url.="&input_charset={$input_charset}";
    $url.="&sign_type={$sign_type}";
    $url.="&notify_url={$notify_url}";
    $url.="&return_url={$return_url}";
    $url.="&error_notify_url={$error_notify_url}";
    $url.="&anti_phishing_key={$anti_phishing_key}";
    $url.="&exter_invoke_ip={$exter_invoke_ip}";
    $url.="&out_trade_no={$out_trade_no}";
    $url.="&subject={$subject}";
    $url.="&payment_type={$payment_type}";
    $url.="&seller_email={$seller_email}";
    $url.="&seller_id={$seller_id}";
    $url.="&buyer_email={$buyer_email}";
    $url.="&buyer_id={$buyer_id}";
    $url.="&exter_invoke_ip={$exter_invoke_ip}";
    $url.="&price={$price}";
    $url.="&total_fee={$total_fee}";
    $url.="&quantity={$quantity}";
    $url.="&body={$body}";
    $url.="&show_url={$show_url}";
    $url.="&pay_method={$pay_method}";
    $url.="&default_bank={$default_bank}";
    $url.="&royalty_parameters={$royalty_parameters}";
    $url.="&royalty_type={$royalty_type}";

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
