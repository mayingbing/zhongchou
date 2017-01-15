<?php

class ebatongPayment {
    
	var $name = '贝付';
    var $logo = 'ebatong';
    var $version = 1.7;
    var $description = "贝付。";
    var $type = 1;//1->只能启动，2->可以添加
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

    $input_charset      = "utf-8";                                     // 字符集

    $service            = "create_direct_pay_by_user";              // 服务名称：即时交易
    $partner            = "201501131139398055";                         // 合作者商户ID
    $sign_type          = "MD5";                                        // 签名算法
    $notify_url         = $_G['system']['con_weburl']."/modules/payment/ebatong_server_return.php";  // 服务器异步通知页面路径
    $return_url         = $_G['system']['con_weburl']."/modules/payment/ebatong_merchant_return.php";  // 页面跳转同步通知页面路径
    $error_notify_url   = "";                                           // 请求出错时的通知页面路径，可空

    // 反钓鱼用参数
    $anti_phishing_key  = "";                                           // 通过时间戳查询接口获取的加密系统时间戳，有效时间：30秒
    $exter_invoke_ip    = "";                                           // 用户在外部系统创建交易时，由外部系统记录的用户IP地址 
           
    // 易八通合作商户网站唯一订单号
    $out_trade_no       = $data["trade_no"];
    $subject            = "91toufang";                                // 商品名称
    $payment_type       = "1";                                          // 支付类型，默认值为：1（商品购买）
     
    /**
     * ”卖家易八通用户ID“优先于”卖家易八通用户名“
     * 两者不可同时为空
     */
    $seller_email        = "";                                          // 卖家易八通用户名
    $seller_id           = "201501131139398055";                        // 卖家易八通用户ID  
    $buyer_email         = "";                                          // 买家易八通用户名，可空
    $buyer_id            = "";                                          // 买家易八通用户ID，可空 
   // $exter_invoke_ip     = "115.29.78.161";                             //订单IP   
    $price               = number_format($data['money'],2,".","");                                          // 商品单价
    $total_fee           = "";                                     // 交易金额
    $quantity            = "1";                                          // 购买数量  
    $body                = "";                                          // 商品描述，可空
    $show_url            = "";                                          // 商品展示网址，可空
    $pay_method          = "bankPay";                                   // 支付方式，directPay(余额支付)、bankPay(网银支付)，可空
    $default_bank        = "";                                    // 默认网银 ,快捷支付必填
    /**
     ABC_B2C=农行
     BJRCB_B2C=北京农村商业银行
     BOC_B2C=中国银行
     CCB_B2C=建行
     CEBBANK_B2C=中国光大银行
     CGB_B2C=广东发展银行
     CITIC_B2C=中信银行
     CMB_B2C=招商银行
     CMBC_B2C=中国民生银行
     COMM_B2C=交通银行
     FDB_B2C=富滇银行
     HXB_B2C=华夏银行
     HZCB_B2C_B2C=杭州银行
     ICBC_B2C=工商银行网
     NBBANK_B2C=宁波银行
     PINGAN_B2C=平安银行
     POSTGC_B2C=中国邮政储蓄银行
     SDB_B2C=深圳发展银行
     SHBANK_B2C=上海银行
     SPDB_B2C=上海浦东发展银行
     */
     $royalty_parameters = ""; // 最多10组分润明细。示例：100001=0.01|100002=0.02 表示id为100001的用户要分润0.01元，id为100002的用户要分润0.02元。
     $royalty_type = "";                                              // 提成类型，目前只支持一种类型：10，表示卖家给第三方提成；

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
