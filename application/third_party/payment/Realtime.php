<?php


class Realtime{
    
    function getRealTime($key,$input_charset ,$account){
        $ask_for_time_stamp_gateway ="https://www.ebatong.com/gateway.htm"; // ebatong商户网关
        $service                    = "query_timestamp";                    // 服务名称：请求时间戳
        $partner                    = $account;                 // 合作者商户ID
        $sign_type                  = "MD5";                                // 摘要签名算法
        
        //对所有参数进行排列
        $params = array("service"=>$service,"partner"=>$partner,"input_charset"=>$input_charset,"sign_type"=>$sign_type);
        $paramKey = array_keys($params);
        sort($paramKey);
        $md5src = "";
        $i = 0;
        $paramStr="";
        foreach($paramKey as $arraykey){
            if($i == 0){
                $paramStr .= $arraykey."=".$params[$arraykey];
            }
            else{
                $paramStr .= "&".$arraykey."=".$params[$arraykey];
            }
            $i++;
        }
        $md5src .= $paramStr.$key;  
        $sign = md5($md5src);        
        $paramStr .= "&sign=".$sign;  
         
        $url=$ask_for_time_stamp_gateway."?".$paramStr;             
        $doc = new DOMDocument();
        $doc->load($url);
        $itemEncrypt_key = $doc->getElementsByTagName( "encrypt_key" );
        $encrypt_key = $itemEncrypt_key->item(0)->nodeValue;
        return $encrypt_key;
    }
}
?>