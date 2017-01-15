<?php

class dinpayPayment {
    
	var $name = '�Ǹ�';//��������
    var $logo = 'dinpay';
    var $version = 3.0;
    var $description = "�Ǹ���";
    var $type = 1;//1->ֻ��������2->�������
    var $charset = 'GB2312';
	
    var $orderby = 3;

    function ToSubmit($data){
	global $_G;

		//���������ַ���(��ѡ)
		$input_charset = "GBK";

		//�ӿڰ汾(��ѡ)�̶�ֵ:V3.0
		$interface_version = "V3.0";

		//�̼Һţ����
		$merchant_code = $data["MerchantID"];

		//��̨֪ͨ��ַ(����)
		$notify_url = $_G['system']['con_weburl']."modules/payment/dinpay_notify_url.php";

		//���������
		$order_amount = $data["money"];

		//�̼Ҷ�����(����)
		$order_no =  $data["trade_no"];

		//�̼Ҷ���ʱ��(����)
		$order_time = date("Y-m-d H:i:s",time());

		//ǩ����ʽ(����)
		$sign_type = "MD5";

		//��Ʒ���(ѡ��)
		$product_code = "";

		//��Ʒ������ѡ�
		$product_desc = "";

		//��Ʒ���ƣ����
		$product_name = "chongzhi";

		//�˿�����(ѡ��)
		$product_num = "";

		//ҳ����תͬ��֪ͨ��ַ(ѡ��)
		$return_url =  $_G['system']['con_weburl']."modules/payment/dinpay_return_url.php";

		//ҵ������(����)
		$service_type = "direct_pay";

		//��Ʒչʾ��ַ(ѡ��)
		$show_url = "";

		//����ҵ����չ������ѡ�
		$extend_param = "";

		//����ҵ��ش�������ѡ�
		$extra_return_param = "";

		// ֱ��ͨ�����루ѡ�
		$bank_code = "";

		//�ͻ���IP��ѡ�
		$client_ip = "";

	/* ע  new String(����.getBytes("UTF-8"),"��ҳ������ʽ"); ��ΪGBK���� ���滻UTF-8 ΪGBK*/
	if($product_name != "") {
	  $product_name = mb_convert_encoding($product_name, "GBK", "GBK");
	}
	if($product_desc != "") {
	  $product_desc = mb_convert_encoding($product_desc, "GBK", "GBK");
	}
	if($extend_param != "") {
	  $extend_param = mb_convert_encoding($extend_param, "GBK", "GBK");
	}
	if($extra_return_param != "") {
	  $extra_return_param = mb_convert_encoding($extra_return_param, "GBK", "GBK");
	}
	if($product_code != "") {
	  $product_code = mb_convert_encoding($product_code, "GBK", "GBK");
	}
	if($return_url != "") {
	  $return_url = mb_convert_encoding($return_url, "GBK", "GBK");
	}
	if($show_url != "") {
	  $show_url = mb_convert_encoding($show_url, "GBK", "GBK");
	}


	/*
	**
	 ** ǩ��˳���ղ�����a��z��˳��������������ͬ����ĸ���򿴵ڶ�����ĸ���Դ����ƣ�ͬʱ���̼�֧����Կkey����������ǩ����
	 ** ��ɹ������£�
	 ** ������1=����ֵ1&������2=����ֵ2&����&������n=����ֵn&key=keyֵ
	 **/
	$signSrc= "";

	//��֯������Ϣ
	if($bank_code != "") {
		$signSrc = $signSrc."bank_code=".$bank_code."&";
	}
	if($client_ip != "") {
                $signSrc = $signSrc."client_ip=".$client_ip."&";
	}
	if($extend_param != "") {
		$signSrc = $signSrc."extend_param=".$extend_param."&";
	}
	if($extra_return_param != "") {
		$signSrc = $signSrc."extra_return_param=".$extra_return_param."&";
	}
	if($input_charset != "") {
		$signSrc = $signSrc."input_charset=".$input_charset."&";
	}
	if($interface_version != "") {
		$signSrc = $signSrc."interface_version=".$interface_version."&";
	}
	if($merchant_code != "") {
		$signSrc = $signSrc."merchant_code=".$merchant_code."&";
	}
	if($notify_url != "") {
		$signSrc = $signSrc."notify_url=".$notify_url."&";
	}
	if($order_amount != "") {
		$signSrc = $signSrc."order_amount=".$order_amount."&";
	}
	if($order_no != "") {
		$signSrc = $signSrc."order_no=".$order_no."&";
	}
	if($order_time != "") {
		$signSrc = $signSrc."order_time=".$order_time."&";
	}
	if($product_code != "") {
		$signSrc = $signSrc."product_code=".$product_code."&";
	}
	if($product_desc != "") {
		$signSrc = $signSrc."product_desc=".$product_desc."&";
	}
	if($product_name != "") {
		$signSrc = $signSrc."product_name=".$product_name."&";
	}
	if($product_num != "") {
		$signSrc = $signSrc."product_num=".$product_num."&";
	}
	if($return_url != "") {
		$signSrc = $signSrc."return_url=".$return_url."&";
	}
	if($service_type != "") {
		$signSrc = $signSrc."service_type=".$service_type."&";
	}
	if($show_url != "") {
		$signSrc = $signSrc."show_url=".$show_url."&";
	}
        //������Կ
	$key = $data["VerficationCode"]; // <֧����Կ> ע:�˴���Կ�������̼Һ�̨�����Կһ��
	$signSrc = $signSrc."key=".$key;

	$singInfo = $signSrc;
	//echo "singInfo=".$singInfo."<br>";

	//ǩ��
	$sign = md5($singInfo);
	//echo "sign=".$sign."<br>";
	//echo $singInfo;
	
		$url=$_G['system']['con_weburl']."modules/payment/dinpay_order.php?input_charset=GBK";
		$url.="&sign={$sign}";
		$url.="&merchant_code={$merchant_code}";
		$url.="&bank_code={$bank_code}";
		$url.="&order_no={$order_no}";
		$url.="&order_amount={$order_amount}";
		$url.="&service_type={$service_type}";
		$url.="&input_charset={$input_charset}";
		$url.="&notify_url={$notify_url}";
		$url.="&interface_version={$interface_version}";
		$url.="&sign_type={$sign_type}";
		$url.="&order_time={$order_time}";
		$url.="&product_name={$product_name}";
		$url.="&client_ip={$client_ip}";
		$url.="&extend_param={$extend_param}";
		$url.="&extra_return_param={$extra_return_param}";
		$url.="&product_code={$product_code}";
		$url.="&product_desc={$product_desc}";
		$url.="&product_num={$product_num}";
		$url.="&return_url={$return_url}";
		$url.="&show_url={$show_url}";
		$url.="&key={$key}";
		
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