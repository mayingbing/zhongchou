<?php
/*
 * @Description loging 
 * @V1.0
 * @Author LIU YUE
 */
	
$logName	= "91toufang_daily";

function logstr($str)
{
/*
if( !is_dir('../../logs') ) {
  mkdir( '../../logs', 0750, true );
}
*/
$liuyue=fopen($GLOBALS['logName'],"a+");
fwrite($liuyue,"\r\n".date("Y-m-d H:i:s")."|str[".$str."]");
fclose($liuyue);
}
?>