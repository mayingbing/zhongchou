<?php
namespace app\index\model;

use think\Model;

class Sms extends Model
{
    protected $table = 'yyd_approve_smslog';

    //验证手机号码格式
    public function check_phonenum($phonenum)
    {
        //判断手机是否正确
        if (!preg_match("/^1[3|4|5|7|8]\d{9}$/", $phonenum)) {
            return FALSE;
        } else {
            return TRUE;
        }

    }


    //获取用户IP
    public function ip_address()
    {
        if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
            $ip_address = $_SERVER["HTTP_CLIENT_IP"];
        } else if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip_address = array_pop(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
        } else if (!empty($_SERVER["REMOTE_ADDR"])) {
            $ip_address = $_SERVER["REMOTE_ADDR"];
        } else {
            $ip_address = '';
        }
        return $ip_address;
    }

    public function SendSMS($data)
    {
        $url = 'http://www.smswst.com:80/api/httpapi.aspx?wangzel?91toufang';

        $sms_url = explode("?", $url);
        $http = $sms_url[0];
        $uid = $sms_url[1];
        $pwd = $sms_url[2];

        $phone = $data['phone'];
//		$data['contents'] = $data['contents'];//.$_G['system']['con_sms_text'];
        $content = $data['contents'];
        $AddSing = 'N';
        $action = 'send';
        $result = self::SendSMS_Common($http, $uid, $pwd, $phone, $content, $AddSing, $action);

        return $result;
    }

    public function SendSMS_Common($http, $uid, $pwd, $phone, $content, $AddSing, $action)
    {
        $data = array
        (
            'account' => $uid,                    //用户账号
            'password' => $pwd,            //MD5位32密码,密码和用户名拼接字符
            'mobile' => $phone,                //号码
            'AddSing' => $AddSing,
            'action' => $action,
            //'content' => $content //Encoding::toUTF8($content) //mb_convert_encoding($content,"UTF-8",mb_detect_encoding($content)),			//内容
            'content'=>mb_convert_encoding($content,"GBK","UTF-8"),
        );


        $re = self::postSMS_Common($http, $data);            //POST方式提交
        //$re = mb_convert_encoding($re,'UTF-8','ASCII');//mb_detect_encoding($re);

        $xml = simplexml_load_string($re);

        if ($xml->errorstatus->error[0] . PHP_EOL > 0) {
            return "发送失败! 状态：";//.$xml->errorstatus->remark[0].PHP_EOL;
        } else {
            if ($xml->successCounts[0] . PHP_EOL > 0) {
                return 1;//"发送成功!";
            } else {
                if ($xml->taskID != null) {
                    return "发送失败! taskID:";//.$xml->taskID[0].PHP_EOL;
                }
            }
        }

        return "发送失败!";//+","+$data['password']+","+$data['mobile']+","+$data['content'];

    }
    public static function postSMS_Common($url, $data = '')
    {
        $row = parse_url($url);
        $host = $row['host'];
        $port = $row['port'] ? $row['port'] : 80;
        $file = $row['path'];
        $post = '';
        while (list($k, $v) = each($data)) {
            $post .= rawurlencode($k) . "=" . rawurlencode($v) . "&";    //转URL标准码
        }
        $post = substr($post, 0, -1);
        $len = strlen($post);
        $fp = @fsockopen($host, $port, $errno, $errstr, 10);
        if (!$fp) {
            return "$errstr ($errno)\n";
        } else {
            $receive = '';
            $out = "POST $file HTTP/1.1\r\n";
            $out .= "Host: $host\r\n";
            $out .= "Content-type: application/x-www-form-urlencoded;charset=gb2312\r\n";
            $out .= "Connection: Close\r\n";
            $out .= "Content-Length: $len\r\n\r\n";
            $out .= $post;//mb_convert_encoding($post,'UTF-8');

            //echo "--------------------";
            //var_dump($out);
            fwrite($fp, $out); //fwrite($fp, mb_convert_encoding($out,'UTF-8','ASCII'));
            while (!feof($fp)) {
                $receive .= fgets($fp);
            }
            fclose($fp);
            //return $receive;

            $receive = explode("\r\n\r\n", $receive);
            //echo "-----------111---------";
            //var_dump($receive);
            unset($receive[0]);
            //$xml=simplexml_load_string($receive[1]);
            //return count(exploade("",$receive));
            return implode("", $receive);

        }
    }

    public function sendsmstozk($tel,$contents){

        //发送信息给客户
        $smsdata = array();
        $smsdata['phone'] = $tel;
        $smsdata['status'] = 1;
        $smsdata['type'] = 1;
        $smsdata['contents'] = $contents;
        $smsresult1 = self::SendSMS($smsdata);

        if ($smsresult1 > 0) {
            return true;
        } else {
            return false;
        }
    }
    public function sendsmstozzk($tel,$contents){

        //发送信息给客户
        $smsdata = array();
        $smsdata['phone'] = $tel;
        $smsdata['status'] = 1;
        $smsdata['type'] = 1;
        $smsdata['contents'] = $contents;
        $smsresult = self::SendSMS($smsdata);
        if ($smsresult > 0) {
            return true;
        } else {
            return false;
        }
    }
}
