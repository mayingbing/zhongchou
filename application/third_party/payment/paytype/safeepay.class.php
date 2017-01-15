<?php
    include 'modules/payment/safeepayCommon.php';

    class safeepayPayment{
        
        var $name = 'ÉÁ¸¶';
        var $logo = 'safeepay';
        var $version=4.0;
        var $description="ÉÁ¸¶.";
        var $type = 1;
        var $charset = 'GB2312';

        var $orderby = 10;

        function ToSubmit($data){
            global $_G;

		$strSign = "";

		$p0_Cmd = "Buy";

		$p1_MerId = $data["p1_MerId"];

		$p2_Order =  $data["trade_no"];

		$p3_Amt = number_format($data['money'],2,".","");

		$p4_Cur = "CNY";

		$p5_Pid = "dcdh";

		$p8_Url = $_G['system']['con_weburl']."/modules/payment/callback.php";
		
		$strSign = $p0_Cmd.$p1_MerId.$p2_Order.$p3_Amt.$p4_Cur.$p5_Pid.$p8_Url;
		$merchantKey = $data["merchantKey"];
		$hmac = HmacMd5($strSign,$merchantKey);
		
		$url = $_G['system']['con_weburl']."/modules/payment/paySubmit.php?";
		
		$url.="p0_Cmd={$p0_Cmd}";
		$url.="&p1_MerId={$p1_MerId}";
		$url.="&p2_Order={$p2_Order}";
		$url.="&p3_Amt={$p3_Amt}";
		$url.="&p4_Cur={$p4_Cur}";
		$url.="&p5_Pid={$p5_Pid}";
		$url.="&p8_Url={$p8_Url}";
		$url.="&hmac={$hmac}";
		
		return $url;
        }

        function GetFields(){
        return array(
			'p1_MerId'=>array(
					'label'=>'ÉÌ¼Ò±àºÅ',
					'type'=>'string'
				),
                'merchantKey'=>array(
                        'label'=>'ÃÜÔ¿',
                        'type'=>'string'
                )
		);
    }
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title></title>
    </head>
    <body>
        
    </body>
</html>
