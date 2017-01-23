<?php
namespace app\index\controller;
use think\Controller;
use think\Cookie;

use think\Response;
use think\Session;
use think\Request;
use app\index\validate\Userlogin;
use app\third_party\jytpay\lib\ENC;
use app\third_party\jytpay\lib\ArrayToXML;
use app\third_party\jytpay\lib\Snoopy;
use app\index\model\Usermodel;
use app\index\model\Bank_model;
class Topup extends Controller
{
    private $accountkey = "8Q9ZQ94KF539UI1G3BH0SA8CMIW0LQaxgece";
    private $account = "201510261536537283";
    private $simulateMode = false;

    //金运通商户ID  测试环境
    //private $jytID = "100060100008";
    //金运通商户ID  生产环境
    private $jytID = "100060100021";

    //实名支付 接口  生产
    private  $shimingUrl="https://www.jytpay.com:9410/JytRNPay/tranCenter/encXmlReq.do";

    //代收付 接口   生产
    private  $daishoufuUrl="https://www.jytpay.com:9010/JytCPService/tranCenter/encXmlReq.do";

    //鉴权接口 测试
    //private  $jianquanUrl="http://test1.jytpay.com:20080/JytAuth/tranCenter/authReq.do";
    //鉴权接口 生产
    private  $jianquanUrl="https://www.jytpay.com:9210/JytAuth/tranCenter/authReq.do";

    function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $data["errormsg"] = "";
        //$this->load->view("out_money_page",$data);
        //$this->beiTopupPageOne(0); // type 0 is pay info, 1 is binding

        //判断用户登陆情况

        $userid = Session::get("userid");
        if ($userid == null) {

            $url = "/index/login/index/5";
            $this->redirect($url);
        }
        //$accountInfo = $this->session->userdata("accountInfo");
        $bankmodel = new Bank_model;
        $bankCardResult = array();
        $bankCardResult = $bankmodel->getBackRecordById($userid);

        //如果有绑定的卡记录
        if (!empty($bankCardResult) && isset($bankCardResult)) {
            //保存信息 到session
            Session::set('accountInfo', $bankCardResult);
            $bankCardResult = $bankCardResult['0'];
            //显示绑卡信息
            $bankCardResult['bank_card_no'] = '****  ****  ****  ' . substr($bankCardResult['bank_card_no'], -4, 4);
            $url = "/index/index/bangka";
            $this->redirect($url,$bankCardResult);
        } //去绑卡
        else {

            $params['actionUrl'] =  urlencode("/index/topup/beiTopUpStep3SmsVerify");
            $params['status'] =  0;
            $params['type'] =  0;

            $json_arr = json_encode($params);
            $url = "/index/topup/out_money_detail_bei_page?value=$json_arr";
            $this->redirect($url);
        }
    }
    public function out_bank_page(){
        $bankCardResult=input('bankCardResult');
        $this->assign('bankCardResult',$bankCardResult);
        return $this->fetch();
    }
    public function out_money_detail_bei_page(){
        if(Session::get('userid')==''){
            $url="/index/login/login";
            $this->redirect($url);
        }
        $part = input('value');
        $params = json_decode($part, true);
        $this->assign('params',$params);
        return $this->fetch();
    }

    public function beiTopupPageOne($type)
    {

        $userid = Session::get("userid");
        if ($userid == null) {
            redirect('/login/index/5', 'refresh');
        }
        $this->load->library("form_validation");
        $paymethod = 1;
        if ($paymethod == 1) { // select the bank payment
            $usermodel = new Usermodel();
            $userid = Session::get("userid");
            $bankCardResult = $usermodel->getBankCard($userid);
            $tradeNo = str_pad($userid . "u" . rand(1000, 9999) . "00504" . rand(1000, 9999), 16, "0", 1);

            $accountInfo = array(
                'bank_card_no' => $bankCardResult["bank_card_no"],
                'real_name' => $bankCardResult["real_name"],
                "cert_no" => $bankCardResult["cert_no"],
                "card_bind_mobile_phone_no" => $bankCardResult["card_bind_mobile_phone_no"],
                "bank_code" => $bankCardResult["bank_code"],
                "customer_id" => $userid,
                "out_trade_no" => $tradeNo,
                "type" => $type
            );

            Session::set('accountInfo', $accountInfo);

            $params = array
            (
                "actionUrl" => "/index/topup/beiTopUpStep3SmsVerify",
                "bank_code" => $bankCardResult["bank_code"],
                "card_bind_mobile_phone_no" => $bankCardResult["card_bind_mobile_phone_no"],
                "status" => $bankCardResult["status"],
                "type" => $type
            );
            if ($params["status"] == "1") {
                $str = $bankCardResult["bank_card_no"];

                $prenum = substr($str, 0, 6);
                $lastnum = substr($str, 15);
                $str = $prenum . "*********" . $lastnum;
                $params["binded_card_no"] = $str;
            }

            // check binding infomation
            $str = "show bank account page---" . json_encode($params);
            $this->logInfomation($str);


            $part = json_encode($params);
            $url = "/index/topup/out_money_detail_bei_page?value=$part";
            $this->redirect($url);

        } else { // select the wechat payment
            $data["errormsg"] = "不支持此付款方式";
            $data["errormsg"] = mb_convert_encoding($data["errormsg"],'utf-8','gbk');
            $str = "not support wechat payment--" . json_encode($data);
            $this->logInfomation($str);

            $part = json_encode($data);
            $url = "/index/topup/out_money_page?value=$part";
            $this->redirect($url);
        }
    }
    public function out_money_page(){

        if(Session::get('userid')==''){
            $url="/index/login/login";
            $this->redirect($url);
        }

        $part = input('value');
        $params = json_decode($part, true);
        $this->assign('params',$params);
        return $this->fetch();
    }


    /**
     * 日志打印函数
     * 如果在配置文件中定义了日志输出文件，那么日志信息就打到到该文件；
     * 如果没有定义，那日志信息输出到PHP自带的日志文件
     *
     * @param string $msg	日志信息
     */
    function logInfomation($msg) {
        if(defined('yyd_LOG')){
            error_log(
                sprintf("[%s]  %s\n", date("Y-m-d H:i:s"), $msg), 3, './smslog.log');
        }else {
            error_log(
                sprintf("[%s]  %s\n", date("Y-m-d H:i:s"), $msg), 3, './smslog.log');
        }
    }

    ///////////////////////////

    public function jiebang()
    {

        $userid = Session::get("userid");

        //解绑  用户信息 以及金运通绑定卡
        $usermodel = new Usermodel();
        $bankCardUserResult = $usermodel->getBankCard($userid);
//        var_dump($bankCardUserResult);
        if (!empty($bankCardUserResult)) {
            $jyttype = 4;
            $chongzhi_money = 0;
            $result = $this->shimingzhifu($bankCardUserResult, $chongzhi_money, $jyttype);
            $xml = simplexml_load_string($result);

            if ((string)$xml->head->resp_code[0] == "S0000000" && (string)$xml->body->tran_state[0] == "0") {
                $usermodel->deleteBankCard($userid);
                $bankmodel = new Bank_model();
                $bankCardResult = $bankmodel->removeBackRecordById($userid);
                $msg = "银行卡删除成功！";
                $msg = mb_convert_encoding($msg,'utf-8','gbk');
                $res = array("code" => 1, "msg" => $msg);
                echo json_encode($res);
            } else if ((substr((string)$xml->head->resp_code[0], 0, 1) == "E" && (string)$xml->head->resp_code[0] != "E0000000")) {
                $msg = "银行卡删除失败！";
                $msg = mb_convert_encoding($msg,'utf-8','gbk');
                $res = array("code" => 1, "msg" => $msg);
                echo json_encode($res);
            } else {
                $msg = "银行卡删除失败！";
                $msg = mb_convert_encoding($msg,'utf-8','gbk');
                $res = array("code" => 1, "msg" => $msg);
                echo json_encode($res);
            }
        }
        // echo json_encode($bankCardResult);
    }
    ////////////////

    ///init the first step for beifu

    ///change selected bankcode will call here

    public function bindingPageOne()
    {

        $userid = Session::get("userid");
        if ($userid == null) {
            redirect('/login/index/6', 'refresh');
        }
        $this->beiTopupPageOne(1); // type 0 is pay info, 1 is binding
    }

    //转入支付信息填写页面
    public function beiTopUpPageOneChangeBank()
    {

        $userid = Session::get("userid");
        $accountInfo = Session::get("accountInfo");
        if ($userid == null) {
            redirect('/login/index/5', 'refresh');
        }
        Session::set('topupStep', 1);
        $step = Session::get("topupStep");
        $this->load->library("form_validation");
        $params["amount"] = input("amount");
        $params["bankcode"] = $this->bankcardmap(input("bankcode"));
        $params["realname"] = input("realname");
        $params["cardno"] = input("cardno");
        $params["certno"] = input("certno");
        $params["cardbindmobilephoneno"] = input("cardbindmobilephoneno");
        $params["status"] = "2";
        $params["type"] = $accountInfo["type"];
        $this->logInfomation("change  selected bank card --" . json_encode($params));
        $this->backToTopupPageOne($params);
    }

    private function bankcardmap($value)
    {
        if ($value == "A") {
            return "ICBC_D_B2C";//工商银行
        } else if ($value == "B") {
            return "ABC_D_B2C";//农业银行
        } else if ($value == "C") {
            return "BOCSH_D_B2C";//中国银行
        } else if ($value == "D") {
            return "CCB_D_B2C";//建设银行
        } else if ($value == "E") {
            return "COMM_D_B2C";//交通银行
        } else if ($value == "F") {
            return "POSTGC_D_B2C";//中国邮政
        } else if ($value == "G") {
            return "CEB_D_B2C";//光大银行
        } else if ($value == "H") {
            return "CNCB_D_B2C";//中信银行
        } else if ($value == "I") {
            return "HXB_D_B2C";//华夏银行
        } else if ($value == "J") {
            return "SPDB_D_B2C";//浦发银行
        } else if ($value == "K") {
            return "CMBCD_D_B2C";//民生银行
        } else if ($value == "L") {
            return "PINGAN_D_B2C";//平安银行
        } else if ($value == "M") {
            return "GDB_D_B2C";//广发银行
        } else if ($value == "N") {
            return "CIB_D_B2C";//兴业银行
        } else {
            return "ICBC_D_B2C";//工商银行
        }
    }

    /**
     * 验证 验证码
     */
    public function backToTopupPageOne($datas)
    {
        $params = array
        (
            "actionUrl" => "/index/topup/beiTopUpStep3SmsVerify",
            "card_no" => $datas["cardno"],
            "real_name" => $datas["realname"],
            "cert_no" => $datas["certno"],
            "amount" => $datas["amount"],
            "bank_code" => $datas["bankcode"],
            "card_bind_mobile_phone_no" => $datas["cardbindmobilephoneno"],
            "status" => $datas["status"],
            "type" => $datas["type"]
        );
        $str = "return to bank acount page--" . json_encode($params);
        $this->logInfomation($str);

        $part = json_encode($params);
        $url = "/index/topup/out_money_detail_bei_page?value=$part";
        $this->redirect($url);



    }


/////////////////////////////////////////////////////////////////////
//--金运通
//////////////////////////////////////////////////////////////////////

    /***
     * 对应 view（topup） 获取验证码
     */
    public function checkBankAccountInfo()
    {
        //判断用户登陆情况

        $userid = Session::get("userid");
        if ($userid == null) {
            $url ='/index/login/login/from/5';
            $this->redirect($url);
        }


        $bankCardResult["result"] = "F";
        $bankCardResult["bank_card_num"] = "0";
        $bankCardResult["error_message"] = "111112222";

        $bankCardResult = array();
        echo json_encode($bankCardResult);
    }

    /**
     * 发送验证码
     */
    public function beiTopUpStep3SmsVerify()
    {
        $usedefault = input("usedefault");
        $amount = input("amount");

        $userid = Session::get("userid");
        // 验证表单数据
        $bool = $this->validate(input('post.'),'beitopupstep2');
        if ($userid == null) {
            redirect('/login/index/5', 'refresh');
        }

        $accountInfo = Session::get("accountInfo");


//        var_dump($accountInfo);
//        $card_no = iconv('UTF-8', 'gb2312', input("cardno")) ;
//        $real_name = iconv('UTF-8', 'gb2312', input("realname")) ;
//        $cert_no = iconv('UTF-8', 'gb2312', input("certno")) ;
//        $card_bind_mobile_phone_no =  iconv('UTF-8', 'gb2312', input("cardbindmobilephoneno")) ;
//        $newbankcode = iconv('UTF-8', 'gb2312', input("bankcode")) ;


        $card_no = input("cardno");
        $real_name = input("realname") ;
        $cert_no = input("certno");
        $card_bind_mobile_phone_no =  input("cardbindmobilephoneno") ;
        $newbankcode = input("bankcode");




        $extral["errormsg"] = "";
//        $tradeNo = $accountInfo["out_trade_no"];
        $tradeNo = '';

        if ($bool=="true") {
            $userid = Session::get("userid");
            if ($card_no == "" || $real_name == "" || $cert_no == "" || $card_bind_mobile_phone_no == "" || $newbankcode == "") {
                $card_no = "";
                $real_name = "";
                $cert_no = "";
                $card_bind_mobile_phone_no = "";
                $newbankcode = "";
            }

            $params = array
            (
                "service" => "ebatong_mp_dyncode",
                "partner" => $this->account,
                "input_charset" => "utf-8",
                "sign_type" => "MD5",
                "customer_id" =>$userid,  //用户账号名
                "card_no" => $card_no,                //银行卡号
                "real_name" => $real_name,            //用户名
                "cert_no" => $cert_no,                //身份证号
                "cert_type" => "01",                  //证件类型
                "amount" => $amount,                  //金额
                "out_trade_no" => $tradeNo,           //流水号
                "bank_code" => $newbankcode,          //银行编号
                "card_bind_mobile_phone_no" => $card_bind_mobile_phone_no, //手机号
            );

            $params['sign'] = $this->dataSigning($params);

            //发送验证码
            //echo "begin_send";
            $this->logInfomation("check bank account start -----");
            $result = $this->sendQueryRequestTojyt($params);
            $this->logInfomation("check bank account return  -----");
            $xml = simplexml_load_string($result);
            $this->logInfomation("check bank 2017".(string)$xml->head->resp_code[0] );


            $accountInfo["bank_card_no"] = $params["card_no"];
            $accountInfo['bind_card_id'] = (string)$xml->body->bind_card_id[0];
            $accountInfo["default_bank"] = $params["bank_code"];
//            $accountInfo["dynamic_code_token"] = $tokenreply["token"];
            $accountInfo["total_fee"] = $params["amount"];
            $accountInfo['real_name'] = $params["real_name"];
            $accountInfo["cert_no"] = $params["cert_no"];
            $accountInfo["card_bind_mobile_phone_no"] = $params["card_bind_mobile_phone_no"];
            $accountInfo["total_fee"] = $params["amount"];
            $accountInfo["out_trade_no"] = $params["out_trade_no"];
            $accountInfo["jytValidate"] = (string)$xml->head->resp_code[0];   //金运通 返回的验证码
            Session::set('accountInfo', $accountInfo);

            $params["errormsg"] = "";
            if ((string)$xml->head->resp_code[0] == "S0000000") {
                $params["errormsg"] = "验证码下发成功，请查收";
                $params["errormsg"] = mb_convert_encoding($params["errormsg"],'utf-8','gbk');
                $params["hasError"] = false;
            } else {
                $params["errormsg"] = (string)$xml->head->resp_desc[0];
                $params["hasError"] = true;
            }
            if(isset($accountInfo["type"])){
                $params["type"] = $accountInfo["type"];
            }


            //验证验证码   支付接口
            $params["actionUrl"] = "/index/topup/beiTopUpStep4WithToken";

            $str = "load sms verify page--" . json_encode($params);
            $this->logInfomation($str);

            $part = http_build_query($params);
            $url = "/index/topup/out_money_detail_bei_token_page";
            $url = $url ."?".$part;
            $this->redirect($url);
            //验证验证码
        } //重新填写数据信息 保留数据
        else {
            $params = array();
            $params["amount"] = $amount;
            $params["bankcode"] = $newbankcode;
            $params["realname"] = $real_name;
            $params["cardno"] = $card_no;
            $params["certno"] = $cert_no;
            $params["cardbindmobilephoneno"] = $card_bind_mobile_phone_no;
            $params["type"] = $accountInfo["type"];
            $this->backToTopupPageOne($params);
        }
//        var_dump($accountInfo);
    }
    public function out_money_detail_bei_token_page(){
        if(Session::get('userid')==''){
            $url="/index/login/login";
            $this->redirect($url);
        }
        $params = input();
        $url_back = $_SERVER['QUERY_STRING'];

        $url_back = substr($url_back,43,strlen($url_back)-43);


//        parse_str($url_back,$params);

        $this->assign('params',$params);
        return $this->fetch();
    }
    /**
     * 验证验证码  jyt
     */
    private function sendQueryRequestTojyt($params, $url='')
    {
        include_once(APP_PATH . "third_party/jytpay/lib/Snoopy.php");
        include_once(APP_PATH . "third_party/jytpay/lib/ENC.php");
        include_once(APP_PATH . "third_party/jytpay/lib/ArrayToXML.php");

        date_default_timezone_set('PRC');  // 设置时区
        /* 0. 请根据对接产品类型和实际商户号修改如下信息  */
        //$url = 'http://test1.jytpay.com:20080/JytAuth/tranCenter/authReq.do';  // 交易请求URL
        //$url = 'http://test1.jytpay.com:20080/JytAuth/tranCenter/authReq.do';
        $url = $this->jianquanUrl;
        //$url = 'http://10.10.10.103:20080/JytAuth/tranCenter/authReq.do';  // 交易请求URL

        $merchant_id = $this->jytID;                                     // 交易商户号
        $mer_pub_file = APP_PATH . 'third_party/jytpay/cert/rsa_public_key_2048.pem';                         // 商户RSA公钥
        $mer_pri_file = APP_PATH . 'third_party/jytpay/cert/rsa_private_key_2048.pem';                        // 商户RSA私钥
        $pay_pub_file = APP_PATH . 'third_party/jytpay/cert/pay_server_public_key.pem';                         // 平台RSA公钥

        $m = new ENC($pay_pub_file, $mer_pri_file);

        /* 1. 组织报文头  */
        $req_param['merchant_id'] = $merchant_id;
        $req_param['tran_type'] = '01';
        $req_param['version'] = '1.0.0';
        $req_param['tran_flowid'] = $req_param['merchant_id'] . date('YmdHis') . rand(10000, 99999); // 请根据商户系统自行定义订单号
        $req_param['tran_date'] = date('Ymd');
        $req_param['tran_time'] = date('His');
        $req_param['tran_code'] = 'TR4004';

        /* 2. --- 请根据接口报文组织请求报文体 ，下面例子为身份认证交易请求报文体，请按照实际交易接口填充内容  */
        $req_body['bank_card_no'] = $params['card_no'];//银行卡号
        $req_body['id_num'] = $params['cert_no'];//证件号(身份证)
        // $name=$params['real_name'];
        $req_body['id_name'] = $params['real_name'];//iconv('gbk', 'UTF-8', $name);//姓名
        $req_body['terminal_type'] = '01';//请求终端类型
        $req_body['bank_card_type'] = 'D';//银行卡类型
        $req_body['phone_no'] = $params['card_bind_mobile_phone_no'];//银行预留手机号

        /* 3. 转换请求数组为xml格式  */
        $data = array("head" => $req_param, "body" => $req_body);
        $xml_ori = ArrayToXML::toXml($data);


        /* 4. 组织POST字段  */
        $req['merchant_id'] = $req_param['merchant_id'];
        $req['sign'] = $m->sign($xml_ori, 'hex');

        //var_dump("sign ::  " .  $req['sign']);

        $key = rand(pow(10, (8 - 1)), pow(10, 8) - 1);
        $req['key_enc'] = $m->encrypt($key, 'hex');
        $req['xml_enc'] = $m->desEncrypt($xml_ori, $key);

//        var_dump("key ::  " .$key);
//        var_dump("req['key_enc'] ::  " .$req['key_enc']);
//        var_dump("req['xml_enc'] ::  " .$req['xml_enc']);

        /* 5. post提交到支付平台 */
        $snoopy = new Snoopy;
        //http协议
        //$snoopy->submit($url, $req);
        //https协议
        $snoopy->curl_https($url,$req);

        /* 6. 正则表达式分解返回报文 */
        preg_match('/^merchant_id=(.*)&xml_enc=(.*)&key_enc=(.*)&sign=(.*)$/', $snoopy->results, $matches);
        $xml_enc = $matches[2];
        $key_enc = $matches[3];
        $sign = $matches[4];

//        var_dump("xml_enc ::  " .$xml_enc);
//        var_dump("key_enc ::  " .$key_enc);
//        var_dump("sign ::  " .$sign);

        /* 7. 解密并验签返回报文  */
        $key = $m->decrypt($key_enc, 'hex');
        $xml = $m->desDecrypt($xml_enc, $key);

//        var_dump("key :: ". iconv('UTF-8', 'gbk', $key));
        //var_dump("xml :: ".iconv('UTF-8', 'gbk', $xml));

        if (!$m->verify($xml, $sign, 'hex'))
            return "--- 验签失败!\n";
        else
            return $xml;

    }

    /**
     * 金运通  发送短信验证吗请求, return the token
     *
     */
    public function beiTopUpStep4WithToken()
    {
        //获取数据
        //$key = $this->accountkey;

        $userid = Session::get("userid");
        $params = array();
        if ($userid == null) {
            return;
        } else {

            //验证码 用户输入
            //$dynamic_code = $this->input->post("dynamic_code");
            $userid = Session::get("userid");
            $accountInfo = Session::get("accountInfo");

            //$time = new Realtime();
//            $realtime = $time->getRealTime($key, "utf-8", $this->account);
            $params = array(
                "sign_type" => "MD5",
                "service" => "create_direct_pay_by_mp",
                "partner" => $this->account,
                "input_charset" => "utf-8",
                "notify_url" => "/index/topup/beiTopUpPageUrl",
                "customer_id" => $userid,
//                "dynamic_code_token" => $accountInfo["dynamic_code_token"],
                "dynamic_code" => input("dynamic_code"),
                "bank_card_no" => $accountInfo["bank_card_no"],
                "real_name" => $accountInfo["real_name"],
                "cert_no" => $accountInfo["cert_no"],
                "cert_type" => "01",
                "out_trade_no" => $accountInfo["out_trade_no"],
                "card_bind_mobile_phone_no" => $accountInfo["card_bind_mobile_phone_no"],
                "subject" => iconv('GB2312', 'UTF-8', "账号充值"),
                "total_fee" => $accountInfo["total_fee"],
                "body" => iconv('GB2312', 'UTF-8', "账号充值"),
                "show_url" => "",
                "pay_method" => "bankPay",
                "exter_invoke_ip" => $this->ip_address(),
                'anti_phishing_key' => time(),
                "extra_common_param" => "",
                "extend_param" => "",
                "default_bank" => $accountInfo["default_bank"],
                "userid" => $userid,
                "bind_card_id" => $accountInfo['bind_card_id'],
            );
        }

        //var_dump($params);

        //验证验证码
        $this->logInfomation("check bank account start -----");
        $result = $this->sendValidateTojyt($params);
        $this->logInfomation("check bank account return  -----");
        $xml = simplexml_load_string($result);


        if ((string)$xml->head->resp_code[0] == "S0000000") {
            //验证码正确  插入 绑卡数据

            echo "<script language='javascript'>
                     alert(iconv('gbk', 'UTF-8', '验证码正确'););

                   </script>";

            $bankmodel = new Bank_model();
            $result = $bankmodel->insertBackRecord($params);

            if ($result) {
                $jump = "index/index/bangka";
                $msg = "绑定成功";
                $msg = iconv('gbk', 'UTF-8', $msg);
                echo "<script language='javascript'>
                     alert('$msg');
                  window.location.href= '$jump';
                   </script>";
            } else {
                $jump = "/index/index/tobangka";
                $msg = "绑定失败,请检查输入信息";
                $msg = iconv('gbk', 'UTF-8', $msg);
                echo "<script language='javascript'> alert('$msg');
               window.location.href= '$jump';
              </script>";
            }
        } else {
            $jump = "/index/index/tobangka";
            $msg = "绑定失败,请检查输入信息";
            $msg = iconv('gbk', 'UTF-8', $msg);
            echo "<script language='javascript'> alert('$msg');
               window.location.href= '$jump';
              </script>";
        }
    }
    public function zhifucode2(){
        $result = '首次充值 记录用户个人信息';
        $result = mb_convert_encoding($result,'utf-8','gbk');
        $test=array("code" => 0, "msg" => $result);
        foreach ( $test as $key => $value ) {
            $test[$key] = urlencode ( $value );
        }
        echo urldecode ( json_encode ( $test ) );
        die;
    }
    /**
     *
     * 实名支付   获取验证码
     */
    public function zhifucode()
    {
        $codetype = input('codetype');
        $chongzhi_money = input('money');

//        $msg = iconv("gbk", "utf-8", $chongzhi_money);
//        $res = array("code" => 0, "msg" => $msg);
//        echo json_encode($res);

        if ($codetype == 0) {
            //测试发送失败
//            $msg = iconv("gbk", "utf-8", "验证码发送失败，请稍后重试！");
//            $res = array("code" => 0, "msg" => $msg);
//            echo json_encode($res);
//            die;
            $jyttype = 0;
            $msg = "";
            $code = 0;//0 失败  1  成功


            $userid = Session::get("userid");
            if (!empty($chongzhi_money)) {
                $tradeNo = str_pad($userid . "u" . rand(1000, 9999) . "00504" . rand(1000, 9999), 16, "0", 1);

                $userid = Session::get("userid");
                $usermodel = new Usermodel();
                $bankCardUserResult = $usermodel->getBankCard($userid);
//                var_dump($bankCardUserResult);
                //首次充值 记录用户个人信息  如果没有记录或者是 首次支付失败 则继续走 首次鉴权
                if ($bankCardUserResult["status"] == -1 || $bankCardUserResult["status"] == 0) {
                    $bankmodel = new Bank_model();
                    $bankCardResult = $bankmodel->getBackRecordById($userid);
                    $bankCardResult = $bankCardResult['0'];
                    $bankdata = array("status" => 0,
                        "user_id" => $userid,
                        "payment_id" => $tradeNo,
                        "real_name" => $bankCardResult["real_name"],
                        "cert_no" => $bankCardResult["id_no"],
                        "cert_type" => "01",
                        "bank_code" => $bankCardResult["bank_card_code"],
                        "bank_name" => "",
                        "bank_card_no" => $bankCardResult["bank_card_no"],
                        "card_bind_mobile_phone_no" => $bankCardResult["bind_mobile"]);
                    if ($bankCardUserResult["status"] == -1) {
                        $bankdata["type"] = 2;
                        $usermodel->updateBankInfo($bankdata);
                    }
                    //成功后 去获取验证码
                    $jyttype = 0;
                    $result = $this->shimingzhifu($bankdata, $chongzhi_money, $jyttype);
                    $xml = simplexml_load_string($result);
//                    var_dump($xml);
                    if ((string)$xml->head->resp_code[0] == "S0000000" && (string)$xml->body->tran_state[0] == "01") {
                        Session::set('zhifudata', $bankdata);
                        $msg = "验证码已发送，请注意查收！";
                        $msg=iconv("GB2312","UTF-8//IGNORE",$msg);
                        echo json_encode(array("code" => 1, "msg" => $msg));
                        die;
                    } else if ((substr((string)$xml->head->resp_code[0], 0, 1) == "E" && (string)$xml->head->resp_code[0] != "E0000000")) {
                        Session::set('zhifudata', $bankdata);
                        $msg = "验证码发送失败，请稍后重试！";
                        $msg=iconv("GB2312","UTF-8//IGNORE",$msg);

                        $test = array("code" => 0, "msg" => $msg);

                        echo json_encode ( $test );die;



                    } else {
                        Session::set('zhifudata', $bankdata);
                        $msg =  "验证码正在发送,请耐心等待！";
                        $msg=iconv("GB2312","UTF-8//IGNORE",$msg);
                        echo json_encode(array("code" => 2, "msg" => $msg));
                        die;
                    }

                } //二次充值
                else {
                    $tradeNo = str_pad($userid. "u" . rand(1000, 9999) . "00504" . rand(1000, 9999), 16, "0", 1);
                    $bankCardUserResult['payment_id'] = $tradeNo;
                    //成功后 去获取验证码
                    $jyttype = 1;
//                    var_dump('1111133331111111');
                    $result = $this->shimingzhifu($bankCardUserResult, $chongzhi_money, $jyttype);
//                    var_dump($result);
//                    var_dump('11111114444411111');
                    $xml = simplexml_load_string($result);

                    if ((string)$xml->head->resp_code[0] == "S0000000" && (string)$xml->body->tran_state[0] == "01") {
                        Session::set('zhifudata', $bankCardUserResult);

                        //测试发送失败
                        $msg = "验证码发送失败，请稍后重试！";
                        $msg = mb_convert_encoding($msg,'utf-8','gbk');
                        $res = array("code" => 0, "msg" => $msg);
                        echo json_encode($res);
                        die;

//                        $msg = iconv("gbk", "utf-8", "验证码已发送，请注意查收！");
//                        $res = array("code" => 1, "msg" => $msg);
//                        echo json_encode($res);
                    } else if ((substr((string)$xml->head->resp_code[0], 0, 1) == "E" && (string)$xml->head->resp_code[0] != "E0000000")) {
                        Session::set('zhifudata', '');
                        $msg = "验证码发送失败，请稍后重试！";
                        $msg = mb_convert_encoding($msg,'utf-8','gbk');
                        $res = array("code" => 0, "msg" => $msg);
                        echo json_encode($res);
                    } else {
                        Session::set('zhifudata', '');
                        $msg = "验证码正在发送,请耐心等待！";
                        $msg = mb_convert_encoding($msg,'utf-8','gbk');
                        $res = array("code" => 2, "msg" => $msg);
                        echo json_encode($res);
                    }
                }
            }
        } //重新发送验证码
        elseif ($codetype == 1) {
            $jyttype = 3;

            $zhifudata = Session::get("zhifudata");
            $result = $this->shimingzhifu($zhifudata, $chongzhi_money, $jyttype);
            $xml = simplexml_load_string($result);
//            var_dump($result);
            if ((string)$xml->head->resp_code[0] == "S0000000" && (string)$xml->body->tran_state[0] == "01") {
                $msg =  "验证码已发送，请注意查收！";
                $msg = mb_convert_encoding($msg,'utf-8','gbk');
                $res = array("code" => 1, "msg" => $msg);
                echo json_encode($res);
            } else if ((substr((string)$xml->head->resp_code[0], 0, 1) == "E" && (string)$xml->head->resp_code[0] != "E0000000")) {
                $msg = "验证码发送失败，请稍后重试！";
                $msg = mb_convert_encoding($msg,'utf-8','gbk');
                $res = array("code" => 0, "msg" => $msg);
                echo json_encode($res);
            } else {
                $msg = "验证码正在发送,请耐心等待！";
                $msg = mb_convert_encoding($msg,'utf-8','gbk');
                $res = array("code" => 2, "msg" => $msg);
                echo json_encode($res);
            }
        }
    }

    /**
     * 金运通支付充值
     */
    public function chongzhi()
    {
        $msg = "";
        $code = 0;//0 失败  1  成功
        $chongzhi_money = input('money');
        $chongzhi_code = input('code');
        if (!empty($chongzhi_money)) {
            if (is_numeric($chongzhi_money) && is_numeric($chongzhi_code)) {

                $userid = Session::get("userid");
                $zhifudata = Session::get("zhifudata");
                //充值之前 记录 充值 信息
                $data = array(
                    "amount" => $chongzhi_money,
                    "type" => 1,   //充值类型  内部status 默认0待审核 实际操作 2失败
                    "user_id" => $userid,
                    "payment" => 30,   //之前是贝付  现在是 金运通
                    "remark" => iconv('GB2312', 'UTF-8', "金运通"),
                    "out_trade_no" => $zhifudata['payment_id'],);
                $usermodel = new Usermodel();
                $this->logInfomation("insert in to record pay and params are " . json_encode($data));
                $usermodel->insertRecordPayment($data); //插入充值记录
                //检测是否是首次消费
                if ($zhifudata['status'] == 0) {
                    $this->logInfomation("pay request start1-----");
                    //成功后 去第三方 充值
                    $jyttype = 0;
                    $result = $this->chongzhijyt($zhifudata, $chongzhi_money, $chongzhi_code, $jyttype);
                    $xml = simplexml_load_string($result);
//                    var_dump($result);
//                    var_dump('1111111');
                    //die;
                    //如果成功后 更新 相关记录
                    if ((string)$xml->head->resp_code[0] == "S0000000" && (string)$xml->body->tran_state[0] == "00") {
                        //模拟请求 待审核
//                   $usermodel = new Usermodel();
//                    //金运通 流水号
//                    $res = $usermodel->insertFlowidToRecharge(array("trade_no" => $tradeNo, "jytflowid" => (string)$xml->head->tran_flowid[0]));
//                    //记录成功
//                    if ($res) {
//                        $msg = iconv("gbk", "utf-8", "交易请求成功,正在审核,请耐心等待！");//(string)$xml->head->resp_desc[0];
//                        $res = array("code" => 0, "msg" => $msg);
//                        echo json_encode($res);
//                    } else {
//                        $msg = iconv("gbk", "utf-8", "交易失败请稍后重试");//(string)$xml->head->resp_desc[0];
//                        $res = array("code" => 0, "msg" => $msg);
//                        echo json_encode($res);
//                    }
//                    return;
                        //模拟请求 待审核

                        //更新 account 数据
                        $this->logInfomation("trade_no is " . $zhifudata['payment_id']);
                        $usermodel = new Usermodel();
                        $usermodel->OnlineReturn(array("trade_no" => $zhifudata['payment_id']));
                        $this->logInfomation("update bank info");
                        $zhifudata['status'] = 1;
                        $zhifudata['type'] = 1;   //更新记录
//                        var_dump($bankdata);
                        $usermodel->updateBankInfo($zhifudata);
                        $msg = (string)$xml->head->resp_desc[0];
                        $msg=iconv("GB2312","UTF-8//IGNORE",$msg);
                        $res = array("code" => 1, "msg" => $msg);
                        echo json_encode($res);
                        return;
                    } //失败
                    else if ((substr((string)$xml->head->resp_code[0], 0, 1) == "E" && (string)$xml->head->resp_code[0] != "E0000000")) {
                        $this->logInfomation("pay request faile1111111-----");

                        $msg = "交易失败请稍后重试!";
                        $msg=iconv("GB2312","UTF-8//IGNORE",$msg);
                        echo json_encode(array("code" => 0, "msg" => $msg));
                    } //处理中 不加钱 等通知 或者 主动查询
                    else {
                        $this->logInfomation("pay request faile2222222222-----");
                        $usermodel = new Usermodel();
                        //金运通 流水号
                        $res = $usermodel->insertFlowidToRecharge(array("trade_no" => $zhifudata['payment_id'], "jytflowid" => (string)$xml->head->tran_flowid[0]));
                        //记录成功
//                        var_dump($res);
//                        var_dump('111111111222233333');
                        if ($res) {
                            $this->logInfomation("pay request faile33333333-----");
                            $msg = "交易请求成功,正在审核,请耐心等待！";
//                            $msg=iconv("GB2312","UTF-8//IGNORE",$msg);
                            //(string)$xml->head->resp_desc[0];

                            $result = mb_convert_encoding($msg,'utf-8','gbk');
                            $test=array("code" => 0, "msg" => $result);
                            foreach ( $test as $key => $value ) {
                                $test[$key] = urlencode ( $value );
                            }
                            echo urldecode ( json_encode ( $test ) );


                        } else {
                            $this->logInfomation("pay request faile4444444-----");
                            $msg = "交易失败请稍后重试!!";
                            $msg=iconv("GB2312","UTF-8//IGNORE",$msg);
                            //(string)$xml->head->resp_desc[0];
                            $res = array("code" => 0, "msg" => $msg);
                            echo json_encode($res);
                        }
                        return;
                    }

                } else {
                    $this->logInfomation("pay request start2-----");
                    //成功后 去第三方 充值
                    $jyttype = 1;
                    $result = $this->chongzhijyt($zhifudata, $chongzhi_money, $chongzhi_code, $jyttype);
                    $xml = simplexml_load_string($result);
//                    var_dump($result);
//                    var_dump('5555555555');
                    //die;
                    //如果成功后 更新 相关记录
                    if ((string)$xml->head->resp_code[0] == "S0000000" && (string)$xml->body->tran_state[0] == "00") {
//                        模拟请求 待审核
//                        $usermodel = new Usermodel();
//                        //金运通 流水号
//                        $res = $usermodel->insertFlowidToRecharge(array("trade_no" =>  $zhifudata['payment_id'], "jytflowid" => (string)$xml->head->tran_flowid[0]));
//                        //记录成功
//                        if ($res) {
//                            $msg = iconv("gbk", "utf-8", "交易请求成功,正在审核,请耐心等待！");//(string)$xml->head->resp_desc[0];
//                            $res = array("code" => 0, "msg" => $msg);
//                            echo json_encode($res);
//                        } else {
//                            $msg = iconv("gbk", "utf-8", "交易失败请稍后重试");//(string)$xml->head->resp_desc[0];
//                            $res = array("code" => 0, "msg" => $msg);
//                            echo json_encode($res);
//                        }
//                        return;
                        //模拟请求 待审核

                        //更新 account 数据
                        $this->logInfomation("trade_no is " . $zhifudata['payment_id']);
                        $usermodel = new Usermodel();
                        $usermodel->OnlineReturn(array("trade_no" => $zhifudata['payment_id']));
                        $this->logInfomation("update bank info");
                        $zhifudata['status'] = 1;
                        $zhifudata['type'] = 1;   //更新记录
                        //var_dump($bankdata);
                        $usermodel->updateBankInfo($zhifudata);
                        $msg = (string)$xml->head->resp_desc[0];
                        $msg=iconv("GB2312","UTF-8//IGNORE",$msg);
                        $res = array("code" => 1, "msg" => $msg);
                        echo json_encode($res);
                        return;
                    } //失败
                    else if ((substr((string)$xml->head->resp_code[0], 0, 1) == "E" && (string)$xml->head->resp_code[0] != "E0000000")) {
                        $msg = "交易失败请稍后重试";
                        $msg=iconv("GB2312","UTF-8//IGNORE",$msg);
                        //(string)$xml->head->resp_desc[0];
                        $res = array("code" => 0, "msg" => $msg);
                        echo json_encode($res);
                        return;
                    } //处理中 不加钱 等通知 或者 主动查询
                    else {
                        $usermodel = new Usermodel();
                        //金运通 流水号
                        $res = $usermodel->insertFlowidToRecharge(array("trade_no" =>  $zhifudata['payment_id'], "jytflowid" => (string)$xml->head->tran_flowid[0]));
                        //记录成功
                        if ($res) {
                            $msg = "交易请求成功,正在审核,请耐心等待！";
                            $msg=iconv("GB2312","UTF-8//IGNORE",$msg);
                            //(string)$xml->head->resp_desc[0];
                            $res = array("code" => 0, "msg" => $msg);
                            echo json_encode($res);
                        } else {
                            $msg = "交易失败请稍后重试";
                            $msg=iconv("GB2312","UTF-8//IGNORE",$msg);
                            //(string)$xml->head->resp_desc[0];
                            $res = array("code" => 0, "msg" => $msg);
                            echo json_encode($res);
                        }
                        return;
                    }
                }

            }
        }
    }

    /**
     * @param $bankCardResult
     * @param $chongzhi_money
     * @param $jyttype 0 是首次 1 是二次
     * @return string
     * 实名认证鉴权 发送验证码
     */
    public function shimingzhifu($bankCardResult, $chongzhi_money, $jyttype)
    {

        include_once(APP_PATH . "third_party/jytpay/lib/Snoopy.php");
        include_once(APP_PATH . "third_party/jytpay/lib/ENC.php");
        include_once(APP_PATH . "third_party/jytpay/lib/ArrayToXML.php");

        date_default_timezone_set('PRC');  // 设置时区

        /* 0. 请根据对接产品类型和实际商户号修改如下信息  */
        //$url = 'http://test1.jytpay.com:20080/JytAuth/tranCenter/authReq.do';  // 交易请求URL
        //$url = 'http://test1.jytpay.com:16080/JytRNPay/tranCenter/encXmlReq.do';  // 交易请求URL
        $url = $this->shimingUrl;

        $merchant_id = $this->jytID;                                           // 交易商户号
        $mer_pub_file = APP_PATH . 'third_party/jytpay/cert/rsa_public_key_2048.pem';                         // 商户RSA公钥
        $mer_pri_file = APP_PATH . 'third_party/jytpay/cert/rsa_private_key_2048.pem';                        // 商户RSA私钥
        $pay_pub_file = APP_PATH . 'third_party/jytpay/cert/pay_server_public_key.pem';
        $m = new ENC($pay_pub_file, $mer_pri_file);

        /* 1. 组织报文头  */
        $req_param['merchant_id'] = $merchant_id;
        $req_param['tran_type'] = '01';
        $req_param['version'] = '1.0.0';
        $req_param['tran_flowid'] = $req_param['merchant_id'] . date('YmdHis') . rand(10000, 99999); // 请根据商户系统自行定义订单号
        $req_param['tran_date'] = date('Ymd');
        $req_param['tran_time'] = date('His');
        if ($jyttype == 0) {
            $req_param['tran_code'] = 'TD1001';
        } elseif ($jyttype == 1) {
            $req_param['tran_code'] = 'TD1002';
        } elseif ($jyttype == 3) {
            $req_param['tran_code'] = 'TD4003';
        } elseif ($jyttype == 4) {
            $req_param['tran_code'] = 'TD4002';
        }

        /* 2. --- 请根据接口报文组织请求报文体 ，下面例子为身份认证交易请求报文体，请按照实际交易接口填充内容  */
//      $req_body['bank_name'] = $bankCardResult['bank_card_code'];//银行名称 （编码）
        if ($jyttype == 0) {
            $req_body['cust_no'] = $bankCardResult['user_id']; //客户号
            $req_body['order_id'] = $bankCardResult['payment_id']; //订单号
            $req_body['bank_card_no'] = $bankCardResult['bank_card_no']; //卡号
            $req_body['name'] = $bankCardResult['real_name'];//姓名
            $req_body['tran_amount'] = round($chongzhi_money, 2);//交易金额
            $req_body['id_card_no'] = $bankCardResult['cert_no'];//'身份证号';
            $req_body['mobile'] = $bankCardResult['card_bind_mobile_phone_no'];//'银行预留手机号';
        } else if ($jyttype == 1) {
            $req_body['cust_no'] = $bankCardResult['user_id']; //客户号
            $req_body['order_id'] = $bankCardResult['payment_id']; //订单号
            $req_body['bank_card_no'] = $bankCardResult['bank_card_no']; //卡号
            $req_body['tran_amount'] = round($chongzhi_money, 2);//交易金额
        } elseif ($jyttype == 3) {
            $req_body['order_id'] = $bankCardResult['payment_id']; //订单号
            $req_body['mobile'] = $bankCardResult['card_bind_mobile_phone_no'];//'银行预留手机号';
        } elseif ($jyttype == 4) {
            $req_body['bank_card_no'] = $bankCardResult['bank_card_no']; //卡号
            $req_body['cust_no'] = $bankCardResult['user_id'];//'客户号';
        }
        /* 3. 转换请求数组为xml格式  */
        $data = array("head" => $req_param, "body" => $req_body);
        $xml_ori = ArrayToXML::toXml($data);

//        var_dump($xml_ori);
        // die;
        /* 4. 组织POST字段  */
        $req['merchant_id'] = $req_param['merchant_id'];
        $req['sign'] = $m->sign($xml_ori, 'hex');
        $key = rand(pow(10, (8 - 1)), pow(10, 8) - 1);
        $req['key_enc'] = $m->encrypt($key, 'hex');
        $req['xml_enc'] = $m->desEncrypt($xml_ori, $key);

        /* 5. post提交到支付平台 */
        $snoopy = new Snoopy;
        //http协议
        //$snoopy->submit($url, $req);
        //https协议
        $snoopy->curl_https($url,$req);

        /* 6. 正则表达式分解返回报文 */
        preg_match('/^merchant_id=(.*)&xml_enc=(.*)&key_enc=(.*)&sign=(.*)$/', $snoopy->results, $matches);
        $xml_enc = $matches[2];
        $key_enc = $matches[3];
        $sign = $matches[4];

        /* 7. 解密并验签返回报文  */
        $key = $m->decrypt($key_enc, 'hex');
        $xml = $m->desDecrypt($xml_enc, $key);
        if (!$m->verify($xml, $sign, 'hex'))
            return "--- 验签失败!\n";
        else
            return $xml;
    }

    /**
     * @param $bankCardResult
     * @param $chongzhi_money
     * @param $chongzhi_code
     * @param $jyttype  首次消费 还是二次消费
     * @return string
     * 金运通实名支付 消费
     */
    public function chongzhijyt($bankCardResult, $chongzhi_money, $chongzhi_code, $jyttype)
    {
        include_once(APP_PATH . "third_party/jytpay/lib/Snoopy.php");
        include_once(APP_PATH . "third_party/jytpay/lib/ENC.php");
        include_once(APP_PATH . "third_party/jytpay/lib/ArrayToXML.php");

        date_default_timezone_set('PRC');  // 设置时区

        /* 0. 请根据对接产品类型和实际商户号修改如下信息  */
        //$url = 'http://test1.jytpay.com:20080/JytAuth/tranCenter/authReq.do';  // 交易请求URL
        //测试接口
        //$url = 'http://test1.jytpay.com:16080/JytRNPay/tranCenter/encXmlReq.do';  // 交易请求URL
        $url = $this->shimingUrl;

        $merchant_id = $this->jytID;                                           // 交易商户号
        $mer_pub_file = APP_PATH . 'third_party/jytpay/cert/rsa_public_key_2048.pem';                         // 商户RSA公钥
        $mer_pri_file = APP_PATH . 'third_party/jytpay/cert/rsa_private_key_2048.pem';                        // 商户RSA私钥
        $pay_pub_file = APP_PATH . 'third_party/jytpay/cert/pay_server_public_key.pem';
        $m = new ENC($pay_pub_file, $mer_pri_file);

        /* 1. 组织报文头  */
        $req_param['merchant_id'] = $merchant_id;
        $req_param['tran_type'] = '01';
        $req_param['version'] = '1.0.0';
        $req_param['tran_flowid'] = $req_param['merchant_id'] . date('YmdHis') . rand(10000, 99999); // 请根据商户系统自行定义订单号
        $req_param['tran_date'] = date('Ymd');
        $req_param['tran_time'] = date('His');
        if ($jyttype == 0) {
            $req_param['tran_code'] = 'TD4001';
        } elseif ($jyttype == 1) {
            $req_param['tran_code'] = 'TD4004';
        }
        /* 2. --- 请根据接口报文组织请求报文体 ，下面例子为身份认证交易请求报文体，请按照实际交易接口填充内容  */
        if ($jyttype == 0) {
            $req_body['mobile'] = $bankCardResult['card_bind_mobile_phone_no'];//'银行预留手机号';
            $req_body['verify_code'] = $chongzhi_code;//验证码
            $req_body['order_id'] = $bankCardResult['payment_id'];//订单号
        } elseif ($jyttype == 1) {
            $req_body['mobile'] = $bankCardResult['card_bind_mobile_phone_no'];//'银行预留手机号';
            $req_body['verify_code'] = $chongzhi_code;//验证码
            $req_body['order_id'] = $bankCardResult['payment_id'];//订单号
        }
        /* 3. 转换请求数组为xml格式  */
        $data = array("head" => $req_param, "body" => $req_body);
        $xml_ori = ArrayToXML::toXml($data);

//        var_dump($xml_ori);
        // die;
        /* 4. 组织POST字段  */
        $req['merchant_id'] = $req_param['merchant_id'];
        $req['sign'] = $m->sign($xml_ori, 'hex');
        $key = rand(pow(10, (8 - 1)), pow(10, 8) - 1);
        $req['key_enc'] = $m->encrypt($key, 'hex');
        $req['xml_enc'] = $m->desEncrypt($xml_ori, $key);

        /* 5. post提交到支付平台 */
        $snoopy = new Snoopy;
        //http协议
        //$snoopy->submit($url, $req);
        //https协议
        $snoopy->curl_https($url,$req);

        /* 6. 正则表达式分解返回报文 */
        preg_match('/^merchant_id=(.*)&xml_enc=(.*)&key_enc=(.*)&sign=(.*)$/', $snoopy->results, $matches);
        $xml_enc = $matches[2];
        $key_enc = $matches[3];
        $sign = $matches[4];

        /* 7. 解密并验签返回报文  */
        $key = $m->decrypt($key_enc, 'hex');
        $xml = $m->desDecrypt($xml_enc, $key);
        if (!$m->verify($xml, $sign, 'hex'))
            return "--- 验签失败!\n";
        else
            return $xml;
    }

    /**
     * 提现规则
     */
    public function tixian()
    {
        $msg = "";
        $code = 0;//0 失败  1  成功
        $tixian_money = input('money');
        $pass = input('pass');   //提现



        if (!empty($tixian_money)) {
            if (is_numeric($tixian_money)) {

                $userid = Session::get("userid");
                $bankmodel = new Bank_model();
                $bankCardResult = $bankmodel->getBackRecordById($userid);
                $bankCardResult = $bankCardResult['0'];
                //检查交易密码
                $usermodel = new Usermodel();
                $paypass = $usermodel->getPayPass($userid);
                if (isset($paypass) && !empty($paypass)) {
                    if ($paypass['paypassword'] != md5($pass)) {
                        $msg = "交易密码填写有误";
                        $msg=iconv("GB2312","UTF-8//IGNORE",$msg);
                        echo json_encode(array('msg' => $msg, 'code' => $code));
                        return;
                    }
                }
                //检查可用余额
                $bankmodel = new Bank_model();
                $accountResult = $bankmodel->checkUserMoney($userid);
                $accountResult = $accountResult['0'];
                if ($tixian_money <= $accountResult['balance']) {
                    //走提现逻辑
                    $accountInfo = Session::get("accountInfo");
                    //var_dump($accountInfo);
                    //走提现 接口 之前 记录信息
                    //获取费率
                    $systemfeilv = $usermodel->feilv('con_account_cash_1');
                    $data['status'] = 0;
                    $data['account'] = $tixian_money;
                    $data['total'] = $tixian_money + $systemfeilv['value'] * $data['account'] * 0.01;

                    $data['bank'] = $bankCardResult['bank_card_code'];
                    $data['bank_id'] = "";
                    $data['nid'] = "cash_" . $userid . time() . rand(100, 999);
//                        $data['fee'] = $_G['system']['con_account_cash_1'] * $data['account'] * 0.01;
                    $data['fee'] = $systemfeilv['value'] * $data['account'] * 0.01;
                    $data['credited'] = $data['total'] - $data['fee'];
                    $data['user_id'] = $userid;

                    $result = $usermodel->AddCash($data);
                    if ($result > 0) {
                        $code = 1;
                        $msg = "您的提现申请已经成功提交，除工作日外预计24小时到账";
//                        $msg=iconv("GB2312","UTF-8",$msg);
//                        $msg=mb_convert_encoding($msg,"UTF-8","gb2312");

                        $result = mb_convert_encoding($msg,'utf-8','gbk');
                        $test=array("code" => $code, "msg" => $result);
                        foreach ( $test as $key => $value ) {
                            $test[$key] = urlencode ( $value );
                        }
                        echo urldecode ( json_encode ( $test ) );

                        die;

                    } else {
                        $msg = array($MsgInfo[$result], "", "/?user&q=code/account/cash_new");
                        $msg = "您的提现申请提交失败,请稍后重试";
                        $msg=iconv("GB2312","UTF-8//IGNORE",$msg);
                        echo json_encode(array('msg' =>  $msg, 'code' => $code));
                        return;
                    }
//                    //走提现 接口
//                    $result = $this->tixianjyt($bankCardResult, $tixian_money);
//                    $xml = simplexml_load_string($result);
//                    var_dump($result);
//                    die;
//                    if ((string)$xml->head->resp_code[0] == "S0000000") {
//
//
//                        $msg = (string)$xml->head->resp_desc[0];
//                        die;
//                        echo json_encode(array('msg' => iconv('gb2312', 'UTF-8', $msg), 'code' => 1));
//                        return;
//                    } else {
//
//                        $msg = (string)$xml->head->resp_desc[0];
//                        echo json_encode(array('msg' => iconv('gb2312', 'UTF-8', $msg), 'code' => 0));
//                        return;
//                    }
                } else {
                    $msg = "提现金额超出可用余额";
                }
            } else {
                $msg = "提现金额请输入数字";
            }
        } else {
            $msg = "提现金额不能为空";
        }
        $msg=iconv("GB2312","UTF-8//IGNORE",$msg);
        echo json_encode(array('msg' =>  $msg, 'code' => $code));
    }

    /**
     *金运通提现接口
     * @param $bankCardResult
     * @param $tixian_money
     * @return string
     */
    public function tixianjyt($bankCardResult, $tixian_money)
    {
        include_once(APP_PATH . "third_party/jytpay/lib/Snoopy.php");
        include_once(APP_PATH . "third_party/jytpay/lib/ENC.php");
        include_once(APP_PATH . "third_party/jytpay/lib/ArrayToXML.php");

        date_default_timezone_set('PRC');  // 设置时区

        /* 0. 请根据对接产品类型和实际商户号修改如下信息  */
        //$url = 'http://test1.jytpay.com:20080/JytAuth/tranCenter/authReq.do';  // 交易请求URL
        //$url = 'http://test1.jytpay.com:8080/JytCPService/tranCenter/encXmlReq.do';  // 交易请求URL
        $url = $this->daishoufuUrl;

        $merchant_id = $this->jytID;                                           // 交易商户号
        $mer_pub_file = APP_PATH . 'third_party/jytpay/cert/rsa_public_key_2048.pem';                         // 商户RSA公钥
        $mer_pri_file = APP_PATH . 'third_party/jytpay/cert/rsa_private_key_2048.pem';                        // 商户RSA私钥
        $pay_pub_file = APP_PATH . 'third_party/jytpay/cert/pay_server_public_key.pem';
        $m = new ENC($pay_pub_file, $mer_pri_file);

        /* 1. 组织报文头  */
        $req_param['merchant_id'] = $merchant_id;
        $req_param['tran_type'] = '01';
        $req_param['version'] = '1.0.0';
        $req_param['tran_flowid'] = $req_param['merchant_id'] . date('YmdHis') . rand(10000, 99999); // 请根据商户系统自行定义订单号
        $req_param['tran_date'] = date('Ymd');
        $req_param['tran_time'] = date('His');

        /* 2. --- 请根据接口报文组织请求报文体 ，下面例子为身份认证交易请求报文体，请按照实际交易接口填充内容  */
        $req_param['tran_code'] = 'TC1002';
        $req_body['mer_viral_acct'] = '';//空
        $req_body['agrt_no'] = '';
        $req_body['bank_name'] = $bankCardResult['bank_card_code'];
        $req_body['account_no'] = $bankCardResult['bank_card_no']; //卡号
        $req_body['account_name'] = $bankCardResult['real_name'];//姓名
        $req_body['account_type'] = '00';//对私
        $req_body['brach_bank_province'] = '';//省份
        $req_body['brach_bank_city'] = ''; //市区
        $req_body['brach_bank_name'] = '';//空
        $req_body['tran_amt'] = round($tixian_money, 2);//交易金额
        $req_body['currency'] = 'CNY';//人民币
        $req_body['bsn_code'] = '09400';//对照代收付特性表找对应的代收业务代码   目前资管
        $req_body['cert_type'] = '';
        //$req_body['cert_no'] = $bankCardResult['id_no'];//'身份证号';
        //$req_body['mobile'] = $bankCardResult['bind_mobile'];//'银行预留手机号';
        $req_body['remark'] = '';//可为空
        $req_body['reserve'] = '';//可为空

        /* 3. 转换请求数组为xml格式  */
        $data = array("head" => $req_param, "body" => $req_body);
        //print_r($data);
        $xml_ori = ArrayToXML::toXml($data);

        //var_dump($xml_ori);

        /* 4. 组织POST字段  */
        $req['merchant_id'] = $req_param['merchant_id'];
        $req['sign'] = $m->sign($xml_ori, 'hex');
        $key = rand(pow(10, (8 - 1)), pow(10, 8) - 1);
        $req['key_enc'] = $m->encrypt($key, 'hex');
        $req['xml_enc'] = $m->desEncrypt($xml_ori, $key);

        /* 5. post提交到支付平台 */
        $snoopy = new Snoopy;
        //http协议
        //$snoopy->submit($url, $req);
        //https协议
        $snoopy->curl_https($url,$req);

        /* 6. 正则表达式分解返回报文 */
        preg_match('/^merchant_id=(.*)&xml_enc=(.*)&key_enc=(.*)&sign=(.*)$/', $snoopy->results, $matches);
        $xml_enc = $matches[2];
        $key_enc = $matches[3];
        $sign = $matches[4];

        /* 7. 解密并验签返回报文  */
        $key = $m->decrypt($key_enc, 'hex');
        $xml = $m->desDecrypt($xml_enc, $key);
        if (!$m->verify($xml, $sign, 'hex'))
            return "--- 验签失败!\n";
        else
            return $xml;

    }

    private function sendBeiVerifyCode($params)
    {
        die;
        include_once(APP_PATH . "third_party/jytpay/lib/Snoopy.php");
        include_once(APP_PATH . "third_party/jytpay/lib/ENC.php");
        include_once(APP_PATH . "third_party/jytpay/lib/ArrayToXML.php");

        date_default_timezone_set('PRC');  // 设置时区
        /* 0. 请根据对接产品类型和实际商户号修改如下信息  */
        //$url = 'http://test1.jytpay.com:20080/JytAuth/tranCenter/authReq.do';  // 交易请求URL
        //$url = 'http://test1.jytpay.com:20080/JytAuth/tranCenter/authReq.do';
        $url = $this->jianquanUrl;
        //$url = 'http://10.10.10.103:20080/JytAuth/tranCenter/authReq.do';  // 交易请求URL

        //$merchant_id = '290015200001';                                     // 交易商户号
        //$mer_pub_file = 'cert/mer_rsa_public.pem';                         // 商户RSA公钥
        //$mer_pri_file = 'cert/mer_rsa_private.pem';                        // 商户RSA私钥
        //$pay_pub_file = 'cert/pay_rsa_public.pem';                         // 平台RSA公钥

        $merchant_id = $this->jytID;                                     // 交易商户号
        $mer_pub_file = APP_PATH . 'third_party/jytpay/cert/rsa_public_key_2048.pem';                         // 商户RSA公钥
        $mer_pri_file = APP_PATH . 'third_party/jytpay/cert/rsa_private_key_2048.pem';                        // 商户RSA私钥
        $pay_pub_file = APP_PATH . 'third_party/jytpay/cert/pay_server_public_key.pem';                         // 平台RSA公钥

        $m = new ENC($pay_pub_file, $mer_pri_file);

        /* 1. 组织报文头  */
        $req_param['merchant_id'] = $merchant_id;
        $req_param['tran_type'] = '01';
        $req_param['version'] = '1.0.0';
        $req_param['tran_flowid'] = $req_param['merchant_id'] . date('YmdHis') . rand(10000, 99999); // 请根据商户系统自行定义订单号
        $req_param['tran_date'] = date('Ymd');
        $req_param['tran_time'] = date('His');
        $req_param['tran_code'] = 'TR4004';

        /* 2. --- 请根据接口报文组织请求报文体 ，下面例子为身份认证交易请求报文体，请按照实际交易接口填充内容  */
        $req_body['bank_card_no'] = '6228480402564890018';//银行卡号
        $req_body['id_num'] = '320322199408025957';//证件号(身份证)
        $name = '你好';
        $req_body['id_name'] = iconv('gbk', 'UTF-8', $name);//姓名
        $req_body['terminal_type'] = 'wap';//请求终端类型
        $req_body['bank_card_type'] = 'D';//银行卡类型
        $req_body['phone_no'] = '13011827890';//银行预留手机号

        /* 3. 转换请求数组为xml格式  */
        $data = array("head" => $req_param, "body" => $req_body);
        $xml_ori = ArrayToXML::toXml($data);

        //var_dump("xml_ori ::" . $xml_ori);

        /* 4. 组织POST字段  */
        $req['merchant_id'] = $req_param['merchant_id'];
        $req['sign'] = $m->sign($xml_ori, 'hex');

        $key = rand(pow(10, (8 - 1)), pow(10, 8) - 1);
        $req['key_enc'] = $m->encrypt($key, 'hex');
        $req['xml_enc'] = $m->desEncrypt($xml_ori, $key);

//        var_dump("key ::  " . $key);
//        var_dump("req['key_enc'] ::  " . $req['key_enc']);
//        var_dump("req['xml_enc'] ::  " . $req['xml_enc']);

        /* 5. post提交到支付平台 */
        $snoopy = new Snoopy;
        //http协议
        //$snoopy->submit($url, $req);
        //https协议
        $snoopy->curl_https($url,$req);

        /* 6. 正则表达式分解返回报文 */
        preg_match('/^merchant_id=(.*)&xml_enc=(.*)&key_enc=(.*)&sign=(.*)$/', $snoopy->results, $matches);
        $xml_enc = $matches[2];
        $key_enc = $matches[3];
        $sign = $matches[4];

//        var_dump("xml_enc ::  " . $xml_enc);
//        var_dump("key_enc ::  " . $key_enc);
//        var_dump("sign ::  " . $sign);

        /* 7. 解密并验签返回报文  */
        $key = $m->decrypt($key_enc, 'hex');
        $xml = $m->desDecrypt($xml_enc, $key);

//        var_dump("key :: " . iconv('UTF-8', 'gbk', $key));
//        var_dump("xml :: " . $xml);
        die;

        if (!$m->verify($xml, $sign, 'hex'))
            return "--- 验签失败!\n";
        else
            return $xml;
    }

    /**
     *接收消息
     */
    public function ReceiveJyt()
    {
        //post 接受密文
        if (isset($_POST)&&!empty($_POST)) {
            //解密 报文
            include_once(APP_PATH . "third_party/jytpay/lib/Snoopy.php");
            include_once(APP_PATH . "third_party/jytpay/lib/ENC.php");
            include_once(APP_PATH . "third_party/jytpay/lib/ArrayToXML.php");
            date_default_timezone_set('PRC');  // 设置时区
            // 交易商户号
            $mer_pub_file = APP_PATH . 'third_party/jytpay/cert/rsa_public_key_2048.pem';                         // 商户RSA公钥
            $mer_pri_file = APP_PATH . 'third_party/jytpay/cert/rsa_private_key_2048.pem';                        // 商户RSA私钥
            $pay_pub_file = APP_PATH . 'third_party/jytpay/cert/pay_server_public_key.pem';
            $m = new ENC($pay_pub_file, $mer_pri_file);

            /* 6. 正则表达式分解返回报文 */
            //preg_match('/^merchant_id=(.*)&xml_enc=(.*)&key_enc=(.*)&sign=(.*)$/', $snoopy->results, $matches);
            $xml_enc = $_POST['xml_enc'];
            $key_enc = $_POST['key_enc'];
            $sign = $_POST['sign'];

            /* 7. 解密并验签返回报文  */
            $key = $m->decrypt($key_enc, 'hex');
            $xml = $m->desDecrypt($xml_enc, $key);
            if (!$m->verify($xml, $sign, 'hex')) {
                //将解密当然报进行修改 然后加密返回
                $xmlRes = simplexml_load_string($xml);
                $xmlRes->head->resp_code[0] = "E1234567";
                //var_dump($xmlRes);
                $dataToJyt['merchant_id'] = $_POST['merchant_id'];
                $dataToJyt['sign'] = $m->sign($xmlRes, 'hex');
                $key = rand(pow(10, (8 - 1)), pow(10, 8) - 1);
                $dataToJyt['key_enc'] = $m->encrypt($key, 'hex');
                $dataToJyt['xml_enc'] = $m->desEncrypt($xmlRes, $key);
                //var_dump($dataToJyt);
                $str = '';
                foreach ($_POST as $k => $v) {
                    $str .= $k . '=' . $v . '&';
                }
                //将报文返回
                echo substr($str, 0, -1);
            } else {

                //记录报文
//                $open=fopen("payxml.txt","a");
//                fwrite($open,$xml);
//                fclose($open);

                $xmlRes = simplexml_load_string($xml);
                //var_dump($xmlRes);
                //更新 account 数据
                $orderid = (string)$xmlRes->body->order_id[0];
                $usermodel = new Usermodel();
                $res = $usermodel->getRechargedNo($orderid);
                //var_dump($res);
                //die;

                $this->logInfomation("trade_no is " . $res['nid']);

                $usermodel->OnlineReturn(array("trade_no" => $res['nid']));
                $this->logInfomation("update bank info");

                //查找银行信息更新状态
                $userid = Session::get("userid");
                $bankCardUserResult = $usermodel->getBankCard($userid);
                if(!empty($bankCardUserResult)&&$bankCardUserResult['status']!==1){
                    $bankCardUserResult['status'] = 1;
                    $bankCardUserResult['type'] = 1;   //更新记录
                    //var_dump($bankdata);
                    $usermodel->updateBankInfo($bankCardUserResult);
                }
                //$res = array("code" => 1, "msg" => iconv("utf-8","gbk","充值成功"));
                //echo header("jyt: S0000000");
                return;
            }
//        echo iconv("gbk", "UTF-8", "接受金运通消息");
//        //echo  "接受金运通消息";
//        var_dump($_POST);
//        var_dump($_GET);
        }
    }
/////////////////////////////////////////////////////////////////////
//金运通--
//////////////////////////////////////////////////////////////////////

    //release the code
    function ip_address()
    {
        if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
            $ip_address = $_SERVER["HTTP_CLIENT_IP"];
        } else if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip_address = array_pop(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
        } else if (!empty($_SERVER["REMOTE_ADDR"])) {
            $ip_address = $_SERVER["REMOTE_ADDR"];
        }
        return $ip_address;
    }

    private function sendValidateTojyt($params)
    {
        include_once(APP_PATH . "third_party/jytpay/lib/Snoopy.php");
        include_once(APP_PATH . "third_party/jytpay/lib/ENC.php");
        include_once(APP_PATH . "third_party/jytpay/lib/ArrayToXML.php");

        date_default_timezone_set('PRC');  // 设置时区
        /* 0. 请根据对接产品类型和实际商户号修改如下信息  */
        //$url = 'http://test1.jytpay.com:20080/JytAuth/tranCenter/authReq.do';  // 交易请求URL
        //$url = 'http://test1.jytpay.com:20080/JytAuth/tranCenter/authReq.do';
        $url = $this->jianquanUrl;
        //$url = 'http://10.10.10.103:20080/JytAuth/tranCenter/authReq.do';  // 交易请求URL

        $merchant_id = $this->jytID;                                     // 交易商户号
        $mer_pub_file = APP_PATH . 'third_party/jytpay/cert/rsa_public_key_2048.pem';                         // 商户RSA公钥
        $mer_pri_file = APP_PATH . 'third_party/jytpay/cert/rsa_private_key_2048.pem';                        // 商户RSA私钥
        $pay_pub_file = APP_PATH . 'third_party/jytpay/cert/pay_server_public_key.pem';                         // 平台RSA公钥

        $m = new ENC($pay_pub_file, $mer_pri_file);

        /* 1. 组织报文头  */
        $req_param['merchant_id'] = $merchant_id;
        $req_param['tran_type'] = '01';
        $req_param['version'] = '1.0.0';
        $req_param['tran_flowid'] = $req_param['merchant_id'] . date('YmdHis') . rand(10000, 99999); // 请根据商户系统自行定义订单号
        $req_param['tran_date'] = date('Ymd');
        $req_param['tran_time'] = date('His');
        $req_param['tran_code'] = 'TR4005';


        /* 2. --- 请根据接口报文组织请求报文体 ，下面例子为身份认证交易请求报文体，请按照实际交易接口填充内容  */
        $req_body['bind_card_id'] = $params['bind_card_id'];//银行卡号
        $req_body['verify_code'] = $params['dynamic_code'];//验证码
        $req_body['phone_no'] = $params['card_bind_mobile_phone_no'];//银行预留手机号

        /* 3. 转换请求数组为xml格式  */
        $data = array("head" => $req_param, "body" => $req_body);
        $xml_ori = ArrayToXML::toXml($data);

        // var_dump("xml_ori ::" . $xml_ori);

        /* 4. 组织POST字段  */
        $req['merchant_id'] = $req_param['merchant_id'];
        $req['sign'] = $m->sign($xml_ori, 'hex');

        $key = rand(pow(10, (8 - 1)), pow(10, 8) - 1);
        $req['key_enc'] = $m->encrypt($key, 'hex');
        $req['xml_enc'] = $m->desEncrypt($xml_ori, $key);

//        var_dump("key ::  " .$key);
//        var_dump("req['key_enc'] ::  " .$req['key_enc']);
//        var_dump("req['xml_enc'] ::  " .$req['xml_enc']);

        /* 5. post提交到支付平台 */
        $snoopy = new Snoopy;
        //http协议
        //$snoopy->submit($url, $req);
        //https协议
        $snoopy->curl_https($url,$req);

        /* 6. 正则表达式分解返回报文 */
        preg_match('/^merchant_id=(.*)&xml_enc=(.*)&key_enc=(.*)&sign=(.*)$/', $snoopy->results, $matches);
        $xml_enc = $matches[2];
        $key_enc = $matches[3];
        $sign = $matches[4];

//        var_dump("xml_enc ::  " .$xml_enc);
//        var_dump("key_enc ::  " .$key_enc);
//        var_dump("sign ::  " .$sign);

        /* 7. 解密并验签返回报文  */
        $key = $m->decrypt($key_enc, 'hex');
        $xml = $m->desDecrypt($xml_enc, $key);

//        var_dump("key :: ". iconv('UTF-8', 'gbk', $key));
//        var_dump("xml :: ".$xml);

        if (!$m->verify($xml, $sign, 'hex'))
            return "--- 验签失败!\n";
        else
            return $xml;
    }

    public function beiReleaseBinding()
    {
        $accountInfo = Session::get("accountInfo");
        $userid = Session::get("userid");
        if ($userid == null) {
            $url = '/login/index/from/5';
        }

        $params = array(
            "service" => "ebatong_mp_unbind",
            "partner" => $this->account,
            "input_charset" => "utf-8",
            "sign_type" => "MD5",
            "notify_url" => "",
            "customer_id" => $accountInfo["customer_id"],
            "bank_card_no" => $accountInfo["binded_bank_card_no"],
            "out_trade_no" => $accountInfo["out_trade_no"],
            "card_bind_mobile_phone_no" => "",
            "subject" => "",
        );
        $params['sign'] = $this->dataSigning($params);

        $this->logInfomation("send release binding start---");
        if ($this->simulateMode) {
            $url = "/index/topup/bindingReleaseSinmulate";
        } else {
            $url = "https://www.ebatong.com/mobileFast/unbind.htm";
        }
        $result = $this->sendQueryRequestToBei($params, $url);

        //var_dump($result);
        $this->logInfomation("send release binding return---");


        if ($result["result"] == 'T') {
            //update database to set released status
            $usrmodel = new Usermodel();
            $this->logInfomation("--result is true, and update bankInfo");
            if ($accountInfo["customer_id"] != "") {
                $usrmodel->updateBankInfo(array("type" => 0, "status" => 0, "user_id" => $user["loginUser_id"]));
            }
        }

        $reply = array(
            "result" => $result["result"],
            "error_message" => $result["error_message"]
        );
        $msg=iconv("GB2312","UTF-8//IGNORE",$reply);
        echo json_encode($reply);

    }

    private function dataSigning($params)
    {

        $str = "data sign input is---" . json_encode($params);
        $this->logInfomation($str);

        $key = $this->accountkey;
        $paramKey = array_keys($params);
        sort($paramKey);
        $md5src = "";
        $i = 0;
        $paramStr = "";
        foreach ($paramKey as $arraykey) {
            if ($i == 0) {
                $paramStr .= $arraykey . "=" . $params[$arraykey];
            } else {
                $paramStr .= "&" . $arraykey . "=" . $params[$arraykey];
            }
            $i++;
        }
        $md5src .= $paramStr . $key;
        $sign = md5($md5src);
        return $sign;
    }

    private function sendQueryRequestToBei($params, $url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        $str = "send request to --- " . $url . "---and params is---" . json_encode($params);
        $this->logInfomation($str);
        $result = curl_exec($ch);
        curl_close($ch);
        $str = "request return from --- " . $url . "---and result is---" . json_encode($result);
        $this->logInfomation($str);

        $result = json_decode($result, true);
        return $result;
    }

    public function beiTopUpPageUrl()
    {
        $this->logInfomation("call back from beifu - beiTopUpPageUrl----");
        $params = input('post.');
        $result = json_decode($params, true);
        try {
            $this->logInfomation("call back from beifu - beiTopUpPageUrl and params is not json? " . $params);
            $this->logInfomation("call back from beifu - beiTopUpPageUrl and params is json" . json_encode($params));
        } catch (Exception $e) {
            $this->logInfomation("call back from beifu error ---  caught exception" . $e->getMessage());
        }
        $result = json_decode($params, true);
        //var_dump($params);
        $this->beiReturnAnalyse($params, 0);
    }

    private function beiReturnAnalyse($params, $type)
    {
        //var_dump($params);
        require_once(APP_PATH . 'third_party/payment/log.php');
        // $params = $this->input->post();

        $userid = Session::get("userid");
        if ($userid == null) {
            redirect('/login/index/5', 'refresh');
        }
        $str = "return from final pay request" . json_encode($params);
        $this->logInfomation($str);
        // header("content-type:text/html; charset=utf-8");
        $checkSign = $params['sign'];
        //获取参数后同加签流程进行验签

        $sign = $this->dataReturnSigning($params);
        $accountInfo = Session::get("accountInfo");
        $bankdata = array("status" => 1,
            "user_id" => $userid,
            "payment_id" => $accountInfo["out_trade_no"],
            "real_name" => $accountInfo["real_name"],
            "cert_no" => $accountInfo["cert_no"],
            "cert_type" => "01",
            "bank_code" => $accountInfo["default_bank"],
            "bank_name" => "",
            "bank_card_no" => $accountInfo["bank_card_no"],
            "card_bind_mobile_phone_no" => $accountInfo["card_bind_mobile_phone_no"],
            "type" => 1
        );


        if (strcmp($checkSign, $sign) == 0) {       //验签通过，数据安全

            $this->logInfomation("check sign , is same---");

            $trade_status = $params["trade_status"];//交易状态
            if (strcmp($trade_status, "T") == 0) {  //交易成功
                $this->logInfomation("check trade_status , is T---");

                if (isset($_SESSION['OrderMoney'])) {
                    $OrderMoney = $_SESSION['OrderMoney'];//获取提交金额的Session
                } else {
                    if (isset($_COOKIE['OrderMoney'])) {
                        $OrderMoney = $_COOKIE['OrderMoney'];
                        setcookie("OrderMoney", "", time() - 3600);
                    } else {
                        $OrderMoney = 0;
                    }
                }

                $str = "OrderMoney is  ---" . $OrderMoney . " --- params fee is " . $params["total_fee"];
                $this->logInfomation($str);

                if ($OrderMoney == $params["total_fee"]) {
                    $this->logInfomation("check fee is same , trade num is " . $params["out_trade_no"]);
                    $usermodel = new Usermodel();
                    $usermodel->OnlineReturn(array("trade_no" => $params["out_trade_no"]));
                    $this->logInfomation("update bank info");
                    $usermodel->updateBankInfo($bankdata);

                    if ($type == 0) {
                        $test = "/showmsg/index/" . urlencode("充值") . "/" . urlencode("支付成功");
                    } else {
                        $test = "/showmsg/index/" . urlencode("手机绑定") . "/" . urlencode("绑定成功");
                    }
                    //echo iconv("GB2312","UTF-8",'支付成功');
                    //echo $test;
                    redirect($test, 'refresh');
                } else {
                    //echo("<script>alert(iconv('GB2312', 'UTF-8','实际成交金额与您提交的订单金额不一致，请接收到支付结果后仔细核对实际成交金额，以免造成订单金额处理差错。'));</script>");
                    if ($type == 0) {
                        $test = "/showmsg/index/" . urlencode("充值") . "/" . urlencode('实际成交金额与您提交的订单金额不一致，请接收到支付结果后仔细核对实际成交金额，以免造成订单金额处理差错。') . "/topup";
                    } else {
                        $test = "/showmsg/index/" . urlencode("手机绑定") . "/" . urlencode('绑定错误，联系管理员。') . "/topup/bindingPageOne";

                    }
                    //echo $test;
                    redirect($test, 'refresh');
                }
            } else {    //支付失败 订单为待处理，可继续支付

                $replyInfo = array();
                if ($type == 0) {
                    $replyInfo["title"] = iconv('GB2312', 'UTF-8', '充值');
                    $replyInfo["status"] = iconv('GB2312', 'UTF-8', '交易失败，请重试');
                    $replyInfo["error"] = $params["error_message"];
                    $replyInfo["return"] = 'topup';

                } else {
                    $replyInfo["title"] = iconv('GB2312', 'UTF-8', '手机绑定');
                    $replyInfo["status"] = iconv('GB2312', 'UTF-8', '手机绑定失败，请重试');
                    $replyInfo["error"] = $params["error_message"];
                    $replyInfo["return"] = 'topupbindingPageOne';
                }
                Session::set('message', $replyInfo);
                redirect('showmsg/showInfo');

            }
        }
    }

    private function dataReturnSigning($params)
    {
        $key = $this->accountkey;

        $str = "dataReturnSigning key is " . $key . " params is ---" . json_encode($params);
        $this->logInfomation($str);

        $paramKey = array_keys($params);
        sort($paramKey);
        $md5src = "";
        $i = 0;
        $paramStr = "";
        foreach ($paramKey as $arraykey) {
            if (strcmp($arraykey, "sign") == 0) {
            } else {
                if ($i == 0) {
                    $paramStr .= $arraykey . "=" . $params[$arraykey];
                } else {
                    $paramStr .= "&" . $arraykey . "=" . $params[$arraykey];
                }
                $i++;
            }
        }
        $md5src .= $paramStr . $key;
        $sign = md5($md5src);
        $str = "dataReturnSigning md5src is ---" . $md5src . " and sign is " . $sign;
        $this->logInfomation($str);
        return $sign;
    }

    public function check_bindphonenum()
    {
        $phonenum = input('cardbindmobilephoneno');
        if (!preg_match("/^13[0-9]{1}[0-9]{8}$|15[0-9]{1}[0-9]{8}$|18[0-9]{9}$/", $phonenum)) {
            $this->form_validation->set_message('check_bindphonenum', iconv('GB2312', 'UTF-8', '输入手机格式不正确'));
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function beiCardBindingReturnUrl()
    {
        $params = input('post.');
        //var_dump($params);
        $this->beiReturnAnalyse($params, 1);
    }

    public function finalPaySimulate()
    {

        $accountInfo = Session::get("accountInfo");

        $para = array(
            "body" => iconv('GB2312', 'UTF-8', "账号充值"),
            "subject" => iconv('GB2312', 'UTF-8', "账号充值"),
            "sign_type" => "MD5",
            "input_charset" => "utf-8",
            "notify_url" => "",
            "out_trade_no" => "212u3133005048572",
            "trade_status" => "T",
            "extra_common_param" => "",
            "total_fee" => "1000",
            "error_message" => iconv('GB2312', 'UTF-8', "错误情况"),
            "service" => "",
            "partner" => "",
            "customer_id" => "aslin3344"
        );

        if ($this->random()) {
            $para["result"] = "T";
            $para["error_message"] = "";
        } else {
            $para["result"] = "F";
        }
        $paramKey = array_keys($para);
        sort($paramKey);
        $md5src = "";
        $i = 0;
        $paramStr = "";
        foreach ($paramKey as $arraykey) {
            if ($i == 0) {
                $paramStr .= $arraykey . "=" . $para[$arraykey];
            } else {
                $paramStr .= "&" . $arraykey . "=" . $para[$arraykey];
            }
            $i++;
        }
        $md5src .= $paramStr . $this->accountkey;
        $sign = md5($md5src);
        $para['sign'] = $sign;
        echo json_encode($para);
    }

    public function random()
    {
        $out = rand(0, 100);
        if ($out % 2) {
            return true;
        } else {
            return false;
        }
    }

    public function testSmsVerifySimulate()
    {

        $accountInfo = Session::get("accountInfo");

        $para = array(
            "sign" => "431610b47ad9dc3fd2e19093f3e175cd",
            "amount" => "1",
            "result" => "T",
            "token" => "201512122004335230",
            "sign_type" => "MD5",
            "error_message" => "ddddd",
            "input_charset" => "utf-8",
            "service" => "ebatong_mp_dyncode",
            "partner" => "201510261536537283",
            "out_trade_no" => $accountInfo["out_trade_no"],
            "customer_id" => $accountInfo["customer_id"]
        );
//        if($this->random()){
        if (true) {
            $para["result"] = "T";
            $para["error_message"] = "";
        } else {
            $para["result"] = "F";
        }
        echo json_encode($para);
    }

    public function bindingReleaseSinmulate()
    {
        $para = array(
            "sign" => "ef73d8f0beb1eca5ffc05c7d6ba57698",
            "result" => "T",
            "subject" => iconv('GB2312', 'UTF-8', "账号充值"),
            "sign_type" => "MD5",
            "error_message" => iconv('GB2312', 'UTF-8', "错误情况"),
            "input_charset" => "utf-8",
            "service" => "ebatong_mp_unbind",
            "partner" => "201510261536537283",
            "bank_card_no" => "6232086400001475856",
            "out_trade_no" => "212u7198005044914",
            "customer_id" => "aslin3344"
        );
        if ($this->random()) {
            $para["result"] = "T";
            $para["error_message"] = "";
        } else {
            $para["result"] = "F";
        }
        echo json_encode($para);
    }

    public function testInsert()
    {
        $usermodel = new Usermodel();
        $bankCardResult = $usermodel->getBankCard(29);
        $bankdata = array("status" => 0,
            "user_id" => 29,
            "payment_id" => "111",
            "real_name" => "333",
            "cert_no" => "4444",
            "cert_type" => "02",
            "bank_code" => "abcde",
            "bank_name" => "",
            "bank_card_no" => "sdfsdfsdf",
            "card_bind_mobile_phone_no" => "11363527"
        );

        if ($bankCardResult["status"] == -1) {
            $bankdata["type"] = 2;
            $usermodel->updateBankInfo($bankdata);
        } else {
            $bankdata["type"] = 1;
            $usermodel->updateBankInfo($bankdata);
        }
    }

    public function testMsgInfo()
    {

        $data = array('title' => '林伟', 'status' => 'lastname', 'errormsg' => '礼物', 'return' => 'topup');
        Session::set('message', $data);
        redirect('showmsg/showInfo');

    }

    /*
    * RSA 测试
    */
    public function testrsa()
    {
        include_once(APP_PATH . "third_party/jytpay/lib/Snoopy.php");
        include_once(APP_PATH . "third_party/jytpay/lib/ENC.php");
        include_once(APP_PATH . "third_party/jytpay/lib/ArrayToXML.php");
        /*
         * 密钥文件的路径
         */
        $privateKeyFilePath = APP_PATH . "third_party/jytpay/cert/rsa_private_key_2048.pem";
        /*
         * 公钥文件的路径
         */
        //$publicKeyFilePath = APP_PATH . "third_party/jytpay/cert/rsa_public_key_2048.pem";
        $publicKeyFilePath = APP_PATH . "third_party/jytpay/cert/pay_server_public_key.pem";
        /*
         * 检查开启扩展
         */
        extension_loaded('openssl') or die('php需要openssl扩展支持');
        /*
         * 判断文件是否存在
         */
        (file_exists($privateKeyFilePath) && file_exists($publicKeyFilePath)) or die('密钥或者公钥的文件路径不正确');
        /**
         * 生成Resource类型的密钥，如果密钥文件内容被破坏，openssl_pkey_get_private函数返回false
         * 函数  openssl_get_privatekey 别名  openssl_pkey_get_private
         */
        $privateKey = openssl_pkey_get_private(file_get_contents($privateKeyFilePath));
        /**
         * 生成Resource类型的公钥，如果公钥文件内容被破坏，openssl_pkey_get_public函数返回false
         *  函数 openssl_get_publickey  别名 openssl_pkey_get_public
         */
        $publicKey = openssl_pkey_get_public(file_get_contents($publicKeyFilePath));
        //检查密钥和公钥 是否都可用
        ($privateKey && $publicKey) or die('密钥或者公钥不可用');
        /**
         * 原数据
         */
        $originalData = 12345678;
        echo "<br/>"; //'我的帐号是:shiki,密码是:matata';
        /**
         * 加密以后的数据，用于在网路上传输
         */
        $encryptData = '';

        echo '原数据为:', $originalData, PHP_EOL;
        echo "<br/>";

        /*
         * openssl_private_encrypt 用私钥加密 或者 反过来 也行 只要对称
         */

        //if (openssl_private_encrypt($originalData, $encryptData, $privateKey)) {
        $m = new ENC($publicKeyFilePath, $privateKeyFilePath);

        $key = $m->encrypt($originalData, 'hex');
        if ($key) {
            /**
             * 加密后 可以base64_encode后方便在网址中传输 或者打印  否则打印为乱码
             */
            echo '加密成功，加密后数据(hex)为:', $key, PHP_EOL;
            echo "<br/>";

        } else {
            die('加密失败');
            echo "<br/>";
        }

        /**
         * openssl_public_decrypt 用公钥解密 或者 反过来 也行 只要对称
         * 解密以后的数据
         */
        $decryptData;
        $keysss = '4de15961ea40755afd88182a5de49aefc269a2cb5e0ae34f86f8e7715d7ba18437cde7da89e79876c0d0bb32ce3316ac9aa2366a5fc60cac878adff5e521245319a927b4ca82aba909bf3f4ef53295d3625b359e63656cdf469ba769b36f2a2cfbc6dc03fc4a0cb25f520acf751efacbd82cc61e3ebbc7f05b6eec5a8ff1aa68728b066caa471da4afdd080c6b3d761e6504fc7c0333190c31df246306a4340b55f272471a0cb5d4604403204cfb126a582c29963d9179b447215e02fc288d8644508a35617fd8fe332d84aee1a7f700a900cea8dcd48f5191054e7d5e8e39392df1192ebc8780cf28f8bb03ad413ff10388b5175fd2ba1365ebbc260618a5ca';
        //if (openssl_public_decrypt($encryptData, $decryptData, $publicKey)) {
        $decryptData = $m->decrypt($keysss, 'hex');
        if ($key) {
            echo '解密成功，解密后数据为:', $decryptData, PHP_EOL;
            echo "<br/>";

        } else {
            die('解密成功');
            echo "<br/>";
        }
    }

    /*
     * https
     */
    public  function  testHttps(){
        include_once(APP_PATH . "third_party/jytpay/lib/Snoopy.php");
        include_once(APP_PATH . "third_party/jytpay/lib/ENC.php");
        include_once(APP_PATH . "third_party/jytpay/lib/ArrayToXML.php");

        date_default_timezone_set('PRC');  // 设置时区
        /* 0. 请根据对接产品类型和实际商户号修改如下信息  */
        //$url = 'http://test1.jytpay.com:20080/JytAuth/tranCenter/authReq.do';  // 交易请求URL
        //$url = 'http://test1.jytpay.com:20080/JytAuth/tranCenter/authReq.do';
        $url = $this->jianquanUrl;
        //$url = 'http://10.10.10.103:20080/JytAuth/tranCenter/authReq.do';  // 交易请求URL

        $merchant_id = $this->jytID;                                     // 交易商户号
        $mer_pub_file = APP_PATH . 'third_party/jytpay/cert/rsa_public_key_2048.pem';                         // 商户RSA公钥
        $mer_pri_file = APP_PATH . 'third_party/jytpay/cert/rsa_private_key_2048.pem';                        // 商户RSA私钥
        $pay_pub_file = APP_PATH . 'third_party/jytpay/cert/pay_server_public_key.pem';                         // 平台RSA公钥

        $m = new ENC($pay_pub_file, $mer_pri_file);

        /* 1. 组织报文头  */
        $req_param['merchant_id'] = $merchant_id;
        $req_param['tran_type'] = '01';
        $req_param['version'] = '1.0.0';
        $req_param['tran_flowid'] = $req_param['merchant_id'] . date('YmdHis') . rand(10000, 99999); // 请根据商户系统自行定义订单号
        $req_param['tran_date'] = date('Ymd');
        $req_param['tran_time'] = date('His');
        $req_param['tran_code'] = 'TR4004';

        /* 2. --- 请根据接口报文组织请求报文体 ，下面例子为身份认证交易请求报文体，请按照实际交易接口填充内容  */
        $req_body['bank_card_no'] = "6228480018555991977";//银行卡号
        $req_body['id_num'] ="142201199304222552";//证件号(身份证)
        $name="冯嘉尧";
        $req_body['id_name'] =iconv('gbk', 'UTF-8', $name);//姓名
        $req_body['terminal_type'] = '01';//请求终端类型
        $req_body['bank_card_type'] = 'D';//银行卡类型
        $req_body['phone_no'] = "18810577880";//银行预留手机号

        /* 3. 转换请求数组为xml格式  */
        $data = array("head" => $req_param, "body" => $req_body);
        $xml_ori = ArrayToXML::toXml($data);

//        var_dump("xml_ori ::".$xml_ori);

        /* 4. 组织POST字段  */
        $req['merchant_id'] = $req_param['merchant_id'];
        $req['sign'] = $m->sign($xml_ori, 'hex');

        //var_dump("sign ::  " .  $req['sign']);

        $key = rand(pow(10, (8 - 1)), pow(10, 8) - 1);
        $req['key_enc'] = $m->encrypt($key, 'hex');
        $req['xml_enc'] = $m->desEncrypt($xml_ori, $key);

//        var_dump("key ::  " .$key);
//        var_dump("req['key_enc'] ::  " .$req['key_enc']);
//        var_dump("req['xml_enc'] ::  " .$req['xml_enc']);

        /* 5. post提交到支付平台 */
        $snoopy = new Snoopy;
//        $snoopy->postdata($url, $req);
        $snoopy->curl_https($url,$req);

//        var_dump($url);
//        var_dump($merchant_id);
//        var_dump("xml_ori ::".$xml_ori);
//        var_dump($snoopy->results);
//        die;

        /* 6. 正则表达式分解返回报文 */
        preg_match('/^merchant_id=(.*)&xml_enc=(.*)&key_enc=(.*)&sign=(.*)$/', $snoopy->results, $matches);
        $xml_enc = $matches[2];
        $key_enc = $matches[3];
        $sign = $matches[4];

//        var_dump("xml_enc ::  " .$xml_enc);
//        var_dump("key_enc ::  " .$key_enc);
//        var_dump("sign ::  " .$sign);

        /* 7. 解密并验签返回报文  */
        $key = $m->decrypt($key_enc, 'hex');
        $xml = $m->desDecrypt($xml_enc, $key);

//        var_dump("key :: ". iconv('UTF-8', 'gbk', $key));
        //var_dump("xml :: ".iconv('UTF-8', 'gbk', $xml));

//        var_dump($xml);
        if (!$m->verify($xml, $sign, 'hex'))
            return "--- 验签失败!\n";
        else
            return $xml;
    }
}
