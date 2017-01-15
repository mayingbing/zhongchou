<?php
  include_once "lib/Snoopy.php";
  include_once "lib/ENC.php";
  include_once "lib/ArrayToXML.php";


  date_default_timezone_set('PRC');  // 设置时区

  /* 0. 请根据对接产品类型和实际商户号修改如下信息  */
//  $url = 'http://test1.jytpay.com:20080/JytAuth/tranCenter/authReq.do';  // 交易请求URL 
  $url = 'http://10.10.10.103:20080/JytAuth/tranCenter/authReq.do';  // 交易请求URL 

  $merchant_id = '290015200001';                                           // 交易商户号
  $mer_pub_file = 'cert/mer_rsa_public.pem';                         // 商户RSA公钥
  $mer_pri_file = 'cert/mer_rsa_private.pem';                        // 商户RSA私钥
  $pay_pub_file = 'cert/pay_rsa_public.pem';                         // 平台RSA公钥
  $m = new ENC($pay_pub_file, $mer_pri_file);  

  
  /* 1. 组织报文头  */
  $req_param[ 'merchant_id' ] = $merchant_id;
  $req_param[ 'tran_type' ] =  '01' ; 
  $req_param[ 'version' ] = '1.0.0' ; 
  $req_param[ 'tran_flowid' ] =  $req_param['merchant_id'].date('YmdHis').rand(10000,99999); // 请根据商户系统自行定义订单号
  $req_param[ 'tran_date' ] = date ( 'Ymd' ); 
  $req_param[ 'tran_time' ] = date ( 'His' ); 

  /* 2. --- 请根据接口报文组织请求报文体 ，下面例子为身份认证交易请求报文体，请按照实际交易接口填充内容  */
  $req_param[ 'tran_code' ] =  'TC1001'; 
  $req_body['mer_viral_acct'] = '';//空
  $req_body['agrt_no'] = '';
  $req_body['bank_name'] = '银行名称';
  $req_body['account_no'] = '客户卡号';
  $req_body['account_name'] = '持卡人姓名';
  $req_body['account_type'] = '00';//对私
  $req_body['brach_bank_province'] = '';//空
  $req_body['brach_bank_city'] = ''; //空
  $req_body['brach_bank_name'] = '';//空
  $req_body['tran_amt'] = '交易金额';
  $req_body['currency'] = 'CNY';//人民币
  $req_body['bsn_code'] = '';//对照代收付特性表找对应的代收业务代码
  $req_body['cert_type'] = '01';
  $req_body['cert_no'] = '身份证号';
  $req_body['mobile'] = '银行预留手机号';
  $req_body['remark'] = '摘要信息';//可为空
  $req_body['reserve'] = '预留字段';//可为空

  /* 3. 转换请求数组为xml格式  */	
  $data=array("head"=>$req_param,"body"=>$req_body);
  $xml_ori = ArrayToXML::toXml($data);

  /* 4. 组织POST字段  */	
  $req['merchant_id'] = $req_param['merchant_id'];
  $req['sign' ]  = $m->sign($xml_ori,'hex');  
  $key = rand(pow(10,(8-1)), pow(10,8)-1);
  $req['key_enc'] = $m->encrypt($key,'hex');
  $req['xml_enc'] = $m->desEncrypt($xml_ori,$key);

  /* 5. post提交到支付平台 */
  $snoopy = new Snoopy;
  $snoopy->submit( $url, $req); 
  
  /* 6. 正则表达式分解返回报文 */
  preg_match('/^merchant_id=(.*)&xml_enc=(.*)&key_enc=(.*)&sign=(.*)$/', $snoopy->results, $matches );
  $xml_enc = $matches[2];
  $key_enc = $matches[3];
  $sign = $matches[4];
  
  /* 7. 解密并验签返回报文  */  
  $key = $m->decrypt($key_enc,'hex');  
  $xml = $m->desDecrypt($xml_enc,$key);
  if(!$m->verify($xml,$sign,'hex')) echo "--- 验签失败!\n"; else echo $xml;
		 
?>