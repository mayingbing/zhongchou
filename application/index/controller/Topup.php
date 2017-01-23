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

    //����ͨ�̻�ID  ���Ի���
    //private $jytID = "100060100008";
    //����ͨ�̻�ID  ��������
    private $jytID = "100060100021";

    //ʵ��֧�� �ӿ�  ����
    private  $shimingUrl="https://www.jytpay.com:9410/JytRNPay/tranCenter/encXmlReq.do";

    //���ո� �ӿ�   ����
    private  $daishoufuUrl="https://www.jytpay.com:9010/JytCPService/tranCenter/encXmlReq.do";

    //��Ȩ�ӿ� ����
    //private  $jianquanUrl="http://test1.jytpay.com:20080/JytAuth/tranCenter/authReq.do";
    //��Ȩ�ӿ� ����
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

        //�ж��û���½���

        $userid = Session::get("userid");
        if ($userid == null) {

            $url = "/index/login/index/5";
            $this->redirect($url);
        }
        //$accountInfo = $this->session->userdata("accountInfo");
        $bankmodel = new Bank_model;
        $bankCardResult = array();
        $bankCardResult = $bankmodel->getBackRecordById($userid);

        //����а󶨵Ŀ���¼
        if (!empty($bankCardResult) && isset($bankCardResult)) {
            //������Ϣ ��session
            Session::set('accountInfo', $bankCardResult);
            $bankCardResult = $bankCardResult['0'];
            //��ʾ����Ϣ
            $bankCardResult['bank_card_no'] = '****  ****  ****  ' . substr($bankCardResult['bank_card_no'], -4, 4);
            $url = "/index/index/bangka";
            $this->redirect($url,$bankCardResult);
        } //ȥ��
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
            $data["errormsg"] = "��֧�ִ˸��ʽ";
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
     * ��־��ӡ����
     * ����������ļ��ж�������־����ļ�����ô��־��Ϣ�ʹ򵽵����ļ���
     * ���û�ж��壬����־��Ϣ�����PHP�Դ�����־�ļ�
     *
     * @param string $msg	��־��Ϣ
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

        //���  �û���Ϣ �Լ�����ͨ�󶨿�
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
                $msg = "���п�ɾ���ɹ���";
                $msg = mb_convert_encoding($msg,'utf-8','gbk');
                $res = array("code" => 1, "msg" => $msg);
                echo json_encode($res);
            } else if ((substr((string)$xml->head->resp_code[0], 0, 1) == "E" && (string)$xml->head->resp_code[0] != "E0000000")) {
                $msg = "���п�ɾ��ʧ�ܣ�";
                $msg = mb_convert_encoding($msg,'utf-8','gbk');
                $res = array("code" => 1, "msg" => $msg);
                echo json_encode($res);
            } else {
                $msg = "���п�ɾ��ʧ�ܣ�";
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

    //ת��֧����Ϣ��дҳ��
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
            return "ICBC_D_B2C";//��������
        } else if ($value == "B") {
            return "ABC_D_B2C";//ũҵ����
        } else if ($value == "C") {
            return "BOCSH_D_B2C";//�й�����
        } else if ($value == "D") {
            return "CCB_D_B2C";//��������
        } else if ($value == "E") {
            return "COMM_D_B2C";//��ͨ����
        } else if ($value == "F") {
            return "POSTGC_D_B2C";//�й�����
        } else if ($value == "G") {
            return "CEB_D_B2C";//�������
        } else if ($value == "H") {
            return "CNCB_D_B2C";//��������
        } else if ($value == "I") {
            return "HXB_D_B2C";//��������
        } else if ($value == "J") {
            return "SPDB_D_B2C";//�ַ�����
        } else if ($value == "K") {
            return "CMBCD_D_B2C";//��������
        } else if ($value == "L") {
            return "PINGAN_D_B2C";//ƽ������
        } else if ($value == "M") {
            return "GDB_D_B2C";//�㷢����
        } else if ($value == "N") {
            return "CIB_D_B2C";//��ҵ����
        } else {
            return "ICBC_D_B2C";//��������
        }
    }

    /**
     * ��֤ ��֤��
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
//--����ͨ
//////////////////////////////////////////////////////////////////////

    /***
     * ��Ӧ view��topup�� ��ȡ��֤��
     */
    public function checkBankAccountInfo()
    {
        //�ж��û���½���

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
     * ������֤��
     */
    public function beiTopUpStep3SmsVerify()
    {
        $usedefault = input("usedefault");
        $amount = input("amount");

        $userid = Session::get("userid");
        // ��֤������
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
                "customer_id" =>$userid,  //�û��˺���
                "card_no" => $card_no,                //���п���
                "real_name" => $real_name,            //�û���
                "cert_no" => $cert_no,                //���֤��
                "cert_type" => "01",                  //֤������
                "amount" => $amount,                  //���
                "out_trade_no" => $tradeNo,           //��ˮ��
                "bank_code" => $newbankcode,          //���б��
                "card_bind_mobile_phone_no" => $card_bind_mobile_phone_no, //�ֻ���
            );

            $params['sign'] = $this->dataSigning($params);

            //������֤��
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
            $accountInfo["jytValidate"] = (string)$xml->head->resp_code[0];   //����ͨ ���ص���֤��
            Session::set('accountInfo', $accountInfo);

            $params["errormsg"] = "";
            if ((string)$xml->head->resp_code[0] == "S0000000") {
                $params["errormsg"] = "��֤���·��ɹ��������";
                $params["errormsg"] = mb_convert_encoding($params["errormsg"],'utf-8','gbk');
                $params["hasError"] = false;
            } else {
                $params["errormsg"] = (string)$xml->head->resp_desc[0];
                $params["hasError"] = true;
            }
            if(isset($accountInfo["type"])){
                $params["type"] = $accountInfo["type"];
            }


            //��֤��֤��   ֧���ӿ�
            $params["actionUrl"] = "/index/topup/beiTopUpStep4WithToken";

            $str = "load sms verify page--" . json_encode($params);
            $this->logInfomation($str);

            $part = http_build_query($params);
            $url = "/index/topup/out_money_detail_bei_token_page";
            $url = $url ."?".$part;
            $this->redirect($url);
            //��֤��֤��
        } //������д������Ϣ ��������
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
     * ��֤��֤��  jyt
     */
    private function sendQueryRequestTojyt($params, $url='')
    {
        include_once(APP_PATH . "third_party/jytpay/lib/Snoopy.php");
        include_once(APP_PATH . "third_party/jytpay/lib/ENC.php");
        include_once(APP_PATH . "third_party/jytpay/lib/ArrayToXML.php");

        date_default_timezone_set('PRC');  // ����ʱ��
        /* 0. ����ݶԽӲ�Ʒ���ͺ�ʵ���̻����޸�������Ϣ  */
        //$url = 'http://test1.jytpay.com:20080/JytAuth/tranCenter/authReq.do';  // ��������URL
        //$url = 'http://test1.jytpay.com:20080/JytAuth/tranCenter/authReq.do';
        $url = $this->jianquanUrl;
        //$url = 'http://10.10.10.103:20080/JytAuth/tranCenter/authReq.do';  // ��������URL

        $merchant_id = $this->jytID;                                     // �����̻���
        $mer_pub_file = APP_PATH . 'third_party/jytpay/cert/rsa_public_key_2048.pem';                         // �̻�RSA��Կ
        $mer_pri_file = APP_PATH . 'third_party/jytpay/cert/rsa_private_key_2048.pem';                        // �̻�RSA˽Կ
        $pay_pub_file = APP_PATH . 'third_party/jytpay/cert/pay_server_public_key.pem';                         // ƽ̨RSA��Կ

        $m = new ENC($pay_pub_file, $mer_pri_file);

        /* 1. ��֯����ͷ  */
        $req_param['merchant_id'] = $merchant_id;
        $req_param['tran_type'] = '01';
        $req_param['version'] = '1.0.0';
        $req_param['tran_flowid'] = $req_param['merchant_id'] . date('YmdHis') . rand(10000, 99999); // ������̻�ϵͳ���ж��嶩����
        $req_param['tran_date'] = date('Ymd');
        $req_param['tran_time'] = date('His');
        $req_param['tran_code'] = 'TR4004';

        /* 2. --- ����ݽӿڱ�����֯�������� ����������Ϊ�����֤�����������壬�밴��ʵ�ʽ��׽ӿ��������  */
        $req_body['bank_card_no'] = $params['card_no'];//���п���
        $req_body['id_num'] = $params['cert_no'];//֤����(���֤)
        // $name=$params['real_name'];
        $req_body['id_name'] = $params['real_name'];//iconv('gbk', 'UTF-8', $name);//����
        $req_body['terminal_type'] = '01';//�����ն�����
        $req_body['bank_card_type'] = 'D';//���п�����
        $req_body['phone_no'] = $params['card_bind_mobile_phone_no'];//����Ԥ���ֻ���

        /* 3. ת����������Ϊxml��ʽ  */
        $data = array("head" => $req_param, "body" => $req_body);
        $xml_ori = ArrayToXML::toXml($data);


        /* 4. ��֯POST�ֶ�  */
        $req['merchant_id'] = $req_param['merchant_id'];
        $req['sign'] = $m->sign($xml_ori, 'hex');

        //var_dump("sign ::  " .  $req['sign']);

        $key = rand(pow(10, (8 - 1)), pow(10, 8) - 1);
        $req['key_enc'] = $m->encrypt($key, 'hex');
        $req['xml_enc'] = $m->desEncrypt($xml_ori, $key);

//        var_dump("key ::  " .$key);
//        var_dump("req['key_enc'] ::  " .$req['key_enc']);
//        var_dump("req['xml_enc'] ::  " .$req['xml_enc']);

        /* 5. post�ύ��֧��ƽ̨ */
        $snoopy = new Snoopy;
        //httpЭ��
        //$snoopy->submit($url, $req);
        //httpsЭ��
        $snoopy->curl_https($url,$req);

        /* 6. ������ʽ�ֽⷵ�ر��� */
        preg_match('/^merchant_id=(.*)&xml_enc=(.*)&key_enc=(.*)&sign=(.*)$/', $snoopy->results, $matches);
        $xml_enc = $matches[2];
        $key_enc = $matches[3];
        $sign = $matches[4];

//        var_dump("xml_enc ::  " .$xml_enc);
//        var_dump("key_enc ::  " .$key_enc);
//        var_dump("sign ::  " .$sign);

        /* 7. ���ܲ���ǩ���ر���  */
        $key = $m->decrypt($key_enc, 'hex');
        $xml = $m->desDecrypt($xml_enc, $key);

//        var_dump("key :: ". iconv('UTF-8', 'gbk', $key));
        //var_dump("xml :: ".iconv('UTF-8', 'gbk', $xml));

        if (!$m->verify($xml, $sign, 'hex'))
            return "--- ��ǩʧ��!\n";
        else
            return $xml;

    }

    /**
     * ����ͨ  ���Ͷ�����֤������, return the token
     *
     */
    public function beiTopUpStep4WithToken()
    {
        //��ȡ����
        //$key = $this->accountkey;

        $userid = Session::get("userid");
        $params = array();
        if ($userid == null) {
            return;
        } else {

            //��֤�� �û�����
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
                "subject" => iconv('GB2312', 'UTF-8', "�˺ų�ֵ"),
                "total_fee" => $accountInfo["total_fee"],
                "body" => iconv('GB2312', 'UTF-8', "�˺ų�ֵ"),
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

        //��֤��֤��
        $this->logInfomation("check bank account start -----");
        $result = $this->sendValidateTojyt($params);
        $this->logInfomation("check bank account return  -----");
        $xml = simplexml_load_string($result);


        if ((string)$xml->head->resp_code[0] == "S0000000") {
            //��֤����ȷ  ���� ������

            echo "<script language='javascript'>
                     alert(iconv('gbk', 'UTF-8', '��֤����ȷ'););

                   </script>";

            $bankmodel = new Bank_model();
            $result = $bankmodel->insertBackRecord($params);

            if ($result) {
                $jump = "index/index/bangka";
                $msg = "�󶨳ɹ�";
                $msg = iconv('gbk', 'UTF-8', $msg);
                echo "<script language='javascript'>
                     alert('$msg');
                  window.location.href= '$jump';
                   </script>";
            } else {
                $jump = "/index/index/tobangka";
                $msg = "��ʧ��,����������Ϣ";
                $msg = iconv('gbk', 'UTF-8', $msg);
                echo "<script language='javascript'> alert('$msg');
               window.location.href= '$jump';
              </script>";
            }
        } else {
            $jump = "/index/index/tobangka";
            $msg = "��ʧ��,����������Ϣ";
            $msg = iconv('gbk', 'UTF-8', $msg);
            echo "<script language='javascript'> alert('$msg');
               window.location.href= '$jump';
              </script>";
        }
    }
    public function zhifucode2(){
        $result = '�״γ�ֵ ��¼�û�������Ϣ';
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
     * ʵ��֧��   ��ȡ��֤��
     */
    public function zhifucode()
    {
        $codetype = input('codetype');
        $chongzhi_money = input('money');

//        $msg = iconv("gbk", "utf-8", $chongzhi_money);
//        $res = array("code" => 0, "msg" => $msg);
//        echo json_encode($res);

        if ($codetype == 0) {
            //���Է���ʧ��
//            $msg = iconv("gbk", "utf-8", "��֤�뷢��ʧ�ܣ����Ժ����ԣ�");
//            $res = array("code" => 0, "msg" => $msg);
//            echo json_encode($res);
//            die;
            $jyttype = 0;
            $msg = "";
            $code = 0;//0 ʧ��  1  �ɹ�


            $userid = Session::get("userid");
            if (!empty($chongzhi_money)) {
                $tradeNo = str_pad($userid . "u" . rand(1000, 9999) . "00504" . rand(1000, 9999), 16, "0", 1);

                $userid = Session::get("userid");
                $usermodel = new Usermodel();
                $bankCardUserResult = $usermodel->getBankCard($userid);
//                var_dump($bankCardUserResult);
                //�״γ�ֵ ��¼�û�������Ϣ  ���û�м�¼������ �״�֧��ʧ�� ������� �״μ�Ȩ
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
                    //�ɹ��� ȥ��ȡ��֤��
                    $jyttype = 0;
                    $result = $this->shimingzhifu($bankdata, $chongzhi_money, $jyttype);
                    $xml = simplexml_load_string($result);
//                    var_dump($xml);
                    if ((string)$xml->head->resp_code[0] == "S0000000" && (string)$xml->body->tran_state[0] == "01") {
                        Session::set('zhifudata', $bankdata);
                        $msg = "��֤���ѷ��ͣ���ע����գ�";
                        $msg=iconv("GB2312","UTF-8//IGNORE",$msg);
                        echo json_encode(array("code" => 1, "msg" => $msg));
                        die;
                    } else if ((substr((string)$xml->head->resp_code[0], 0, 1) == "E" && (string)$xml->head->resp_code[0] != "E0000000")) {
                        Session::set('zhifudata', $bankdata);
                        $msg = "��֤�뷢��ʧ�ܣ����Ժ����ԣ�";
                        $msg=iconv("GB2312","UTF-8//IGNORE",$msg);

                        $test = array("code" => 0, "msg" => $msg);

                        echo json_encode ( $test );die;



                    } else {
                        Session::set('zhifudata', $bankdata);
                        $msg =  "��֤�����ڷ���,�����ĵȴ���";
                        $msg=iconv("GB2312","UTF-8//IGNORE",$msg);
                        echo json_encode(array("code" => 2, "msg" => $msg));
                        die;
                    }

                } //���γ�ֵ
                else {
                    $tradeNo = str_pad($userid. "u" . rand(1000, 9999) . "00504" . rand(1000, 9999), 16, "0", 1);
                    $bankCardUserResult['payment_id'] = $tradeNo;
                    //�ɹ��� ȥ��ȡ��֤��
                    $jyttype = 1;
//                    var_dump('1111133331111111');
                    $result = $this->shimingzhifu($bankCardUserResult, $chongzhi_money, $jyttype);
//                    var_dump($result);
//                    var_dump('11111114444411111');
                    $xml = simplexml_load_string($result);

                    if ((string)$xml->head->resp_code[0] == "S0000000" && (string)$xml->body->tran_state[0] == "01") {
                        Session::set('zhifudata', $bankCardUserResult);

                        //���Է���ʧ��
                        $msg = "��֤�뷢��ʧ�ܣ����Ժ����ԣ�";
                        $msg = mb_convert_encoding($msg,'utf-8','gbk');
                        $res = array("code" => 0, "msg" => $msg);
                        echo json_encode($res);
                        die;

//                        $msg = iconv("gbk", "utf-8", "��֤���ѷ��ͣ���ע����գ�");
//                        $res = array("code" => 1, "msg" => $msg);
//                        echo json_encode($res);
                    } else if ((substr((string)$xml->head->resp_code[0], 0, 1) == "E" && (string)$xml->head->resp_code[0] != "E0000000")) {
                        Session::set('zhifudata', '');
                        $msg = "��֤�뷢��ʧ�ܣ����Ժ����ԣ�";
                        $msg = mb_convert_encoding($msg,'utf-8','gbk');
                        $res = array("code" => 0, "msg" => $msg);
                        echo json_encode($res);
                    } else {
                        Session::set('zhifudata', '');
                        $msg = "��֤�����ڷ���,�����ĵȴ���";
                        $msg = mb_convert_encoding($msg,'utf-8','gbk');
                        $res = array("code" => 2, "msg" => $msg);
                        echo json_encode($res);
                    }
                }
            }
        } //���·�����֤��
        elseif ($codetype == 1) {
            $jyttype = 3;

            $zhifudata = Session::get("zhifudata");
            $result = $this->shimingzhifu($zhifudata, $chongzhi_money, $jyttype);
            $xml = simplexml_load_string($result);
//            var_dump($result);
            if ((string)$xml->head->resp_code[0] == "S0000000" && (string)$xml->body->tran_state[0] == "01") {
                $msg =  "��֤���ѷ��ͣ���ע����գ�";
                $msg = mb_convert_encoding($msg,'utf-8','gbk');
                $res = array("code" => 1, "msg" => $msg);
                echo json_encode($res);
            } else if ((substr((string)$xml->head->resp_code[0], 0, 1) == "E" && (string)$xml->head->resp_code[0] != "E0000000")) {
                $msg = "��֤�뷢��ʧ�ܣ����Ժ����ԣ�";
                $msg = mb_convert_encoding($msg,'utf-8','gbk');
                $res = array("code" => 0, "msg" => $msg);
                echo json_encode($res);
            } else {
                $msg = "��֤�����ڷ���,�����ĵȴ���";
                $msg = mb_convert_encoding($msg,'utf-8','gbk');
                $res = array("code" => 2, "msg" => $msg);
                echo json_encode($res);
            }
        }
    }

    /**
     * ����֧ͨ����ֵ
     */
    public function chongzhi()
    {
        $msg = "";
        $code = 0;//0 ʧ��  1  �ɹ�
        $chongzhi_money = input('money');
        $chongzhi_code = input('code');
        if (!empty($chongzhi_money)) {
            if (is_numeric($chongzhi_money) && is_numeric($chongzhi_code)) {

                $userid = Session::get("userid");
                $zhifudata = Session::get("zhifudata");
                //��ֵ֮ǰ ��¼ ��ֵ ��Ϣ
                $data = array(
                    "amount" => $chongzhi_money,
                    "type" => 1,   //��ֵ����  �ڲ�status Ĭ��0����� ʵ�ʲ��� 2ʧ��
                    "user_id" => $userid,
                    "payment" => 30,   //֮ǰ�Ǳ���  ������ ����ͨ
                    "remark" => iconv('GB2312', 'UTF-8', "����ͨ"),
                    "out_trade_no" => $zhifudata['payment_id'],);
                $usermodel = new Usermodel();
                $this->logInfomation("insert in to record pay and params are " . json_encode($data));
                $usermodel->insertRecordPayment($data); //�����ֵ��¼
                //����Ƿ����״�����
                if ($zhifudata['status'] == 0) {
                    $this->logInfomation("pay request start1-----");
                    //�ɹ��� ȥ������ ��ֵ
                    $jyttype = 0;
                    $result = $this->chongzhijyt($zhifudata, $chongzhi_money, $chongzhi_code, $jyttype);
                    $xml = simplexml_load_string($result);
//                    var_dump($result);
//                    var_dump('1111111');
                    //die;
                    //����ɹ��� ���� ��ؼ�¼
                    if ((string)$xml->head->resp_code[0] == "S0000000" && (string)$xml->body->tran_state[0] == "00") {
                        //ģ������ �����
//                   $usermodel = new Usermodel();
//                    //����ͨ ��ˮ��
//                    $res = $usermodel->insertFlowidToRecharge(array("trade_no" => $tradeNo, "jytflowid" => (string)$xml->head->tran_flowid[0]));
//                    //��¼�ɹ�
//                    if ($res) {
//                        $msg = iconv("gbk", "utf-8", "��������ɹ�,�������,�����ĵȴ���");//(string)$xml->head->resp_desc[0];
//                        $res = array("code" => 0, "msg" => $msg);
//                        echo json_encode($res);
//                    } else {
//                        $msg = iconv("gbk", "utf-8", "����ʧ�����Ժ�����");//(string)$xml->head->resp_desc[0];
//                        $res = array("code" => 0, "msg" => $msg);
//                        echo json_encode($res);
//                    }
//                    return;
                        //ģ������ �����

                        //���� account ����
                        $this->logInfomation("trade_no is " . $zhifudata['payment_id']);
                        $usermodel = new Usermodel();
                        $usermodel->OnlineReturn(array("trade_no" => $zhifudata['payment_id']));
                        $this->logInfomation("update bank info");
                        $zhifudata['status'] = 1;
                        $zhifudata['type'] = 1;   //���¼�¼
//                        var_dump($bankdata);
                        $usermodel->updateBankInfo($zhifudata);
                        $msg = (string)$xml->head->resp_desc[0];
                        $msg=iconv("GB2312","UTF-8//IGNORE",$msg);
                        $res = array("code" => 1, "msg" => $msg);
                        echo json_encode($res);
                        return;
                    } //ʧ��
                    else if ((substr((string)$xml->head->resp_code[0], 0, 1) == "E" && (string)$xml->head->resp_code[0] != "E0000000")) {
                        $this->logInfomation("pay request faile1111111-----");

                        $msg = "����ʧ�����Ժ�����!";
                        $msg=iconv("GB2312","UTF-8//IGNORE",$msg);
                        echo json_encode(array("code" => 0, "msg" => $msg));
                    } //������ ����Ǯ ��֪ͨ ���� ������ѯ
                    else {
                        $this->logInfomation("pay request faile2222222222-----");
                        $usermodel = new Usermodel();
                        //����ͨ ��ˮ��
                        $res = $usermodel->insertFlowidToRecharge(array("trade_no" => $zhifudata['payment_id'], "jytflowid" => (string)$xml->head->tran_flowid[0]));
                        //��¼�ɹ�
//                        var_dump($res);
//                        var_dump('111111111222233333');
                        if ($res) {
                            $this->logInfomation("pay request faile33333333-----");
                            $msg = "��������ɹ�,�������,�����ĵȴ���";
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
                            $msg = "����ʧ�����Ժ�����!!";
                            $msg=iconv("GB2312","UTF-8//IGNORE",$msg);
                            //(string)$xml->head->resp_desc[0];
                            $res = array("code" => 0, "msg" => $msg);
                            echo json_encode($res);
                        }
                        return;
                    }

                } else {
                    $this->logInfomation("pay request start2-----");
                    //�ɹ��� ȥ������ ��ֵ
                    $jyttype = 1;
                    $result = $this->chongzhijyt($zhifudata, $chongzhi_money, $chongzhi_code, $jyttype);
                    $xml = simplexml_load_string($result);
//                    var_dump($result);
//                    var_dump('5555555555');
                    //die;
                    //����ɹ��� ���� ��ؼ�¼
                    if ((string)$xml->head->resp_code[0] == "S0000000" && (string)$xml->body->tran_state[0] == "00") {
//                        ģ������ �����
//                        $usermodel = new Usermodel();
//                        //����ͨ ��ˮ��
//                        $res = $usermodel->insertFlowidToRecharge(array("trade_no" =>  $zhifudata['payment_id'], "jytflowid" => (string)$xml->head->tran_flowid[0]));
//                        //��¼�ɹ�
//                        if ($res) {
//                            $msg = iconv("gbk", "utf-8", "��������ɹ�,�������,�����ĵȴ���");//(string)$xml->head->resp_desc[0];
//                            $res = array("code" => 0, "msg" => $msg);
//                            echo json_encode($res);
//                        } else {
//                            $msg = iconv("gbk", "utf-8", "����ʧ�����Ժ�����");//(string)$xml->head->resp_desc[0];
//                            $res = array("code" => 0, "msg" => $msg);
//                            echo json_encode($res);
//                        }
//                        return;
                        //ģ������ �����

                        //���� account ����
                        $this->logInfomation("trade_no is " . $zhifudata['payment_id']);
                        $usermodel = new Usermodel();
                        $usermodel->OnlineReturn(array("trade_no" => $zhifudata['payment_id']));
                        $this->logInfomation("update bank info");
                        $zhifudata['status'] = 1;
                        $zhifudata['type'] = 1;   //���¼�¼
                        //var_dump($bankdata);
                        $usermodel->updateBankInfo($zhifudata);
                        $msg = (string)$xml->head->resp_desc[0];
                        $msg=iconv("GB2312","UTF-8//IGNORE",$msg);
                        $res = array("code" => 1, "msg" => $msg);
                        echo json_encode($res);
                        return;
                    } //ʧ��
                    else if ((substr((string)$xml->head->resp_code[0], 0, 1) == "E" && (string)$xml->head->resp_code[0] != "E0000000")) {
                        $msg = "����ʧ�����Ժ�����";
                        $msg=iconv("GB2312","UTF-8//IGNORE",$msg);
                        //(string)$xml->head->resp_desc[0];
                        $res = array("code" => 0, "msg" => $msg);
                        echo json_encode($res);
                        return;
                    } //������ ����Ǯ ��֪ͨ ���� ������ѯ
                    else {
                        $usermodel = new Usermodel();
                        //����ͨ ��ˮ��
                        $res = $usermodel->insertFlowidToRecharge(array("trade_no" =>  $zhifudata['payment_id'], "jytflowid" => (string)$xml->head->tran_flowid[0]));
                        //��¼�ɹ�
                        if ($res) {
                            $msg = "��������ɹ�,�������,�����ĵȴ���";
                            $msg=iconv("GB2312","UTF-8//IGNORE",$msg);
                            //(string)$xml->head->resp_desc[0];
                            $res = array("code" => 0, "msg" => $msg);
                            echo json_encode($res);
                        } else {
                            $msg = "����ʧ�����Ժ�����";
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
     * @param $jyttype 0 ���״� 1 �Ƕ���
     * @return string
     * ʵ����֤��Ȩ ������֤��
     */
    public function shimingzhifu($bankCardResult, $chongzhi_money, $jyttype)
    {

        include_once(APP_PATH . "third_party/jytpay/lib/Snoopy.php");
        include_once(APP_PATH . "third_party/jytpay/lib/ENC.php");
        include_once(APP_PATH . "third_party/jytpay/lib/ArrayToXML.php");

        date_default_timezone_set('PRC');  // ����ʱ��

        /* 0. ����ݶԽӲ�Ʒ���ͺ�ʵ���̻����޸�������Ϣ  */
        //$url = 'http://test1.jytpay.com:20080/JytAuth/tranCenter/authReq.do';  // ��������URL
        //$url = 'http://test1.jytpay.com:16080/JytRNPay/tranCenter/encXmlReq.do';  // ��������URL
        $url = $this->shimingUrl;

        $merchant_id = $this->jytID;                                           // �����̻���
        $mer_pub_file = APP_PATH . 'third_party/jytpay/cert/rsa_public_key_2048.pem';                         // �̻�RSA��Կ
        $mer_pri_file = APP_PATH . 'third_party/jytpay/cert/rsa_private_key_2048.pem';                        // �̻�RSA˽Կ
        $pay_pub_file = APP_PATH . 'third_party/jytpay/cert/pay_server_public_key.pem';
        $m = new ENC($pay_pub_file, $mer_pri_file);

        /* 1. ��֯����ͷ  */
        $req_param['merchant_id'] = $merchant_id;
        $req_param['tran_type'] = '01';
        $req_param['version'] = '1.0.0';
        $req_param['tran_flowid'] = $req_param['merchant_id'] . date('YmdHis') . rand(10000, 99999); // ������̻�ϵͳ���ж��嶩����
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

        /* 2. --- ����ݽӿڱ�����֯�������� ����������Ϊ�����֤�����������壬�밴��ʵ�ʽ��׽ӿ��������  */
//      $req_body['bank_name'] = $bankCardResult['bank_card_code'];//�������� �����룩
        if ($jyttype == 0) {
            $req_body['cust_no'] = $bankCardResult['user_id']; //�ͻ���
            $req_body['order_id'] = $bankCardResult['payment_id']; //������
            $req_body['bank_card_no'] = $bankCardResult['bank_card_no']; //����
            $req_body['name'] = $bankCardResult['real_name'];//����
            $req_body['tran_amount'] = round($chongzhi_money, 2);//���׽��
            $req_body['id_card_no'] = $bankCardResult['cert_no'];//'���֤��';
            $req_body['mobile'] = $bankCardResult['card_bind_mobile_phone_no'];//'����Ԥ���ֻ���';
        } else if ($jyttype == 1) {
            $req_body['cust_no'] = $bankCardResult['user_id']; //�ͻ���
            $req_body['order_id'] = $bankCardResult['payment_id']; //������
            $req_body['bank_card_no'] = $bankCardResult['bank_card_no']; //����
            $req_body['tran_amount'] = round($chongzhi_money, 2);//���׽��
        } elseif ($jyttype == 3) {
            $req_body['order_id'] = $bankCardResult['payment_id']; //������
            $req_body['mobile'] = $bankCardResult['card_bind_mobile_phone_no'];//'����Ԥ���ֻ���';
        } elseif ($jyttype == 4) {
            $req_body['bank_card_no'] = $bankCardResult['bank_card_no']; //����
            $req_body['cust_no'] = $bankCardResult['user_id'];//'�ͻ���';
        }
        /* 3. ת����������Ϊxml��ʽ  */
        $data = array("head" => $req_param, "body" => $req_body);
        $xml_ori = ArrayToXML::toXml($data);

//        var_dump($xml_ori);
        // die;
        /* 4. ��֯POST�ֶ�  */
        $req['merchant_id'] = $req_param['merchant_id'];
        $req['sign'] = $m->sign($xml_ori, 'hex');
        $key = rand(pow(10, (8 - 1)), pow(10, 8) - 1);
        $req['key_enc'] = $m->encrypt($key, 'hex');
        $req['xml_enc'] = $m->desEncrypt($xml_ori, $key);

        /* 5. post�ύ��֧��ƽ̨ */
        $snoopy = new Snoopy;
        //httpЭ��
        //$snoopy->submit($url, $req);
        //httpsЭ��
        $snoopy->curl_https($url,$req);

        /* 6. ������ʽ�ֽⷵ�ر��� */
        preg_match('/^merchant_id=(.*)&xml_enc=(.*)&key_enc=(.*)&sign=(.*)$/', $snoopy->results, $matches);
        $xml_enc = $matches[2];
        $key_enc = $matches[3];
        $sign = $matches[4];

        /* 7. ���ܲ���ǩ���ر���  */
        $key = $m->decrypt($key_enc, 'hex');
        $xml = $m->desDecrypt($xml_enc, $key);
        if (!$m->verify($xml, $sign, 'hex'))
            return "--- ��ǩʧ��!\n";
        else
            return $xml;
    }

    /**
     * @param $bankCardResult
     * @param $chongzhi_money
     * @param $chongzhi_code
     * @param $jyttype  �״����� ���Ƕ�������
     * @return string
     * ����ͨʵ��֧�� ����
     */
    public function chongzhijyt($bankCardResult, $chongzhi_money, $chongzhi_code, $jyttype)
    {
        include_once(APP_PATH . "third_party/jytpay/lib/Snoopy.php");
        include_once(APP_PATH . "third_party/jytpay/lib/ENC.php");
        include_once(APP_PATH . "third_party/jytpay/lib/ArrayToXML.php");

        date_default_timezone_set('PRC');  // ����ʱ��

        /* 0. ����ݶԽӲ�Ʒ���ͺ�ʵ���̻����޸�������Ϣ  */
        //$url = 'http://test1.jytpay.com:20080/JytAuth/tranCenter/authReq.do';  // ��������URL
        //���Խӿ�
        //$url = 'http://test1.jytpay.com:16080/JytRNPay/tranCenter/encXmlReq.do';  // ��������URL
        $url = $this->shimingUrl;

        $merchant_id = $this->jytID;                                           // �����̻���
        $mer_pub_file = APP_PATH . 'third_party/jytpay/cert/rsa_public_key_2048.pem';                         // �̻�RSA��Կ
        $mer_pri_file = APP_PATH . 'third_party/jytpay/cert/rsa_private_key_2048.pem';                        // �̻�RSA˽Կ
        $pay_pub_file = APP_PATH . 'third_party/jytpay/cert/pay_server_public_key.pem';
        $m = new ENC($pay_pub_file, $mer_pri_file);

        /* 1. ��֯����ͷ  */
        $req_param['merchant_id'] = $merchant_id;
        $req_param['tran_type'] = '01';
        $req_param['version'] = '1.0.0';
        $req_param['tran_flowid'] = $req_param['merchant_id'] . date('YmdHis') . rand(10000, 99999); // ������̻�ϵͳ���ж��嶩����
        $req_param['tran_date'] = date('Ymd');
        $req_param['tran_time'] = date('His');
        if ($jyttype == 0) {
            $req_param['tran_code'] = 'TD4001';
        } elseif ($jyttype == 1) {
            $req_param['tran_code'] = 'TD4004';
        }
        /* 2. --- ����ݽӿڱ�����֯�������� ����������Ϊ�����֤�����������壬�밴��ʵ�ʽ��׽ӿ��������  */
        if ($jyttype == 0) {
            $req_body['mobile'] = $bankCardResult['card_bind_mobile_phone_no'];//'����Ԥ���ֻ���';
            $req_body['verify_code'] = $chongzhi_code;//��֤��
            $req_body['order_id'] = $bankCardResult['payment_id'];//������
        } elseif ($jyttype == 1) {
            $req_body['mobile'] = $bankCardResult['card_bind_mobile_phone_no'];//'����Ԥ���ֻ���';
            $req_body['verify_code'] = $chongzhi_code;//��֤��
            $req_body['order_id'] = $bankCardResult['payment_id'];//������
        }
        /* 3. ת����������Ϊxml��ʽ  */
        $data = array("head" => $req_param, "body" => $req_body);
        $xml_ori = ArrayToXML::toXml($data);

//        var_dump($xml_ori);
        // die;
        /* 4. ��֯POST�ֶ�  */
        $req['merchant_id'] = $req_param['merchant_id'];
        $req['sign'] = $m->sign($xml_ori, 'hex');
        $key = rand(pow(10, (8 - 1)), pow(10, 8) - 1);
        $req['key_enc'] = $m->encrypt($key, 'hex');
        $req['xml_enc'] = $m->desEncrypt($xml_ori, $key);

        /* 5. post�ύ��֧��ƽ̨ */
        $snoopy = new Snoopy;
        //httpЭ��
        //$snoopy->submit($url, $req);
        //httpsЭ��
        $snoopy->curl_https($url,$req);

        /* 6. ������ʽ�ֽⷵ�ر��� */
        preg_match('/^merchant_id=(.*)&xml_enc=(.*)&key_enc=(.*)&sign=(.*)$/', $snoopy->results, $matches);
        $xml_enc = $matches[2];
        $key_enc = $matches[3];
        $sign = $matches[4];

        /* 7. ���ܲ���ǩ���ر���  */
        $key = $m->decrypt($key_enc, 'hex');
        $xml = $m->desDecrypt($xml_enc, $key);
        if (!$m->verify($xml, $sign, 'hex'))
            return "--- ��ǩʧ��!\n";
        else
            return $xml;
    }

    /**
     * ���ֹ���
     */
    public function tixian()
    {
        $msg = "";
        $code = 0;//0 ʧ��  1  �ɹ�
        $tixian_money = input('money');
        $pass = input('pass');   //����



        if (!empty($tixian_money)) {
            if (is_numeric($tixian_money)) {

                $userid = Session::get("userid");
                $bankmodel = new Bank_model();
                $bankCardResult = $bankmodel->getBackRecordById($userid);
                $bankCardResult = $bankCardResult['0'];
                //��齻������
                $usermodel = new Usermodel();
                $paypass = $usermodel->getPayPass($userid);
                if (isset($paypass) && !empty($paypass)) {
                    if ($paypass['paypassword'] != md5($pass)) {
                        $msg = "����������д����";
                        $msg=iconv("GB2312","UTF-8//IGNORE",$msg);
                        echo json_encode(array('msg' => $msg, 'code' => $code));
                        return;
                    }
                }
                //���������
                $bankmodel = new Bank_model();
                $accountResult = $bankmodel->checkUserMoney($userid);
                $accountResult = $accountResult['0'];
                if ($tixian_money <= $accountResult['balance']) {
                    //�������߼�
                    $accountInfo = Session::get("accountInfo");
                    //var_dump($accountInfo);
                    //������ �ӿ� ֮ǰ ��¼��Ϣ
                    //��ȡ����
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
                        $msg = "�������������Ѿ��ɹ��ύ������������Ԥ��24Сʱ����";
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
                        $msg = "�������������ύʧ��,���Ժ�����";
                        $msg=iconv("GB2312","UTF-8//IGNORE",$msg);
                        echo json_encode(array('msg' =>  $msg, 'code' => $code));
                        return;
                    }
//                    //������ �ӿ�
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
                    $msg = "���ֽ����������";
                }
            } else {
                $msg = "���ֽ������������";
            }
        } else {
            $msg = "���ֽ���Ϊ��";
        }
        $msg=iconv("GB2312","UTF-8//IGNORE",$msg);
        echo json_encode(array('msg' =>  $msg, 'code' => $code));
    }

    /**
     *����ͨ���ֽӿ�
     * @param $bankCardResult
     * @param $tixian_money
     * @return string
     */
    public function tixianjyt($bankCardResult, $tixian_money)
    {
        include_once(APP_PATH . "third_party/jytpay/lib/Snoopy.php");
        include_once(APP_PATH . "third_party/jytpay/lib/ENC.php");
        include_once(APP_PATH . "third_party/jytpay/lib/ArrayToXML.php");

        date_default_timezone_set('PRC');  // ����ʱ��

        /* 0. ����ݶԽӲ�Ʒ���ͺ�ʵ���̻����޸�������Ϣ  */
        //$url = 'http://test1.jytpay.com:20080/JytAuth/tranCenter/authReq.do';  // ��������URL
        //$url = 'http://test1.jytpay.com:8080/JytCPService/tranCenter/encXmlReq.do';  // ��������URL
        $url = $this->daishoufuUrl;

        $merchant_id = $this->jytID;                                           // �����̻���
        $mer_pub_file = APP_PATH . 'third_party/jytpay/cert/rsa_public_key_2048.pem';                         // �̻�RSA��Կ
        $mer_pri_file = APP_PATH . 'third_party/jytpay/cert/rsa_private_key_2048.pem';                        // �̻�RSA˽Կ
        $pay_pub_file = APP_PATH . 'third_party/jytpay/cert/pay_server_public_key.pem';
        $m = new ENC($pay_pub_file, $mer_pri_file);

        /* 1. ��֯����ͷ  */
        $req_param['merchant_id'] = $merchant_id;
        $req_param['tran_type'] = '01';
        $req_param['version'] = '1.0.0';
        $req_param['tran_flowid'] = $req_param['merchant_id'] . date('YmdHis') . rand(10000, 99999); // ������̻�ϵͳ���ж��嶩����
        $req_param['tran_date'] = date('Ymd');
        $req_param['tran_time'] = date('His');

        /* 2. --- ����ݽӿڱ�����֯�������� ����������Ϊ�����֤�����������壬�밴��ʵ�ʽ��׽ӿ��������  */
        $req_param['tran_code'] = 'TC1002';
        $req_body['mer_viral_acct'] = '';//��
        $req_body['agrt_no'] = '';
        $req_body['bank_name'] = $bankCardResult['bank_card_code'];
        $req_body['account_no'] = $bankCardResult['bank_card_no']; //����
        $req_body['account_name'] = $bankCardResult['real_name'];//����
        $req_body['account_type'] = '00';//��˽
        $req_body['brach_bank_province'] = '';//ʡ��
        $req_body['brach_bank_city'] = ''; //����
        $req_body['brach_bank_name'] = '';//��
        $req_body['tran_amt'] = round($tixian_money, 2);//���׽��
        $req_body['currency'] = 'CNY';//�����
        $req_body['bsn_code'] = '09400';//���մ��ո����Ա��Ҷ�Ӧ�Ĵ���ҵ�����   Ŀǰ�ʹ�
        $req_body['cert_type'] = '';
        //$req_body['cert_no'] = $bankCardResult['id_no'];//'���֤��';
        //$req_body['mobile'] = $bankCardResult['bind_mobile'];//'����Ԥ���ֻ���';
        $req_body['remark'] = '';//��Ϊ��
        $req_body['reserve'] = '';//��Ϊ��

        /* 3. ת����������Ϊxml��ʽ  */
        $data = array("head" => $req_param, "body" => $req_body);
        //print_r($data);
        $xml_ori = ArrayToXML::toXml($data);

        //var_dump($xml_ori);

        /* 4. ��֯POST�ֶ�  */
        $req['merchant_id'] = $req_param['merchant_id'];
        $req['sign'] = $m->sign($xml_ori, 'hex');
        $key = rand(pow(10, (8 - 1)), pow(10, 8) - 1);
        $req['key_enc'] = $m->encrypt($key, 'hex');
        $req['xml_enc'] = $m->desEncrypt($xml_ori, $key);

        /* 5. post�ύ��֧��ƽ̨ */
        $snoopy = new Snoopy;
        //httpЭ��
        //$snoopy->submit($url, $req);
        //httpsЭ��
        $snoopy->curl_https($url,$req);

        /* 6. ������ʽ�ֽⷵ�ر��� */
        preg_match('/^merchant_id=(.*)&xml_enc=(.*)&key_enc=(.*)&sign=(.*)$/', $snoopy->results, $matches);
        $xml_enc = $matches[2];
        $key_enc = $matches[3];
        $sign = $matches[4];

        /* 7. ���ܲ���ǩ���ر���  */
        $key = $m->decrypt($key_enc, 'hex');
        $xml = $m->desDecrypt($xml_enc, $key);
        if (!$m->verify($xml, $sign, 'hex'))
            return "--- ��ǩʧ��!\n";
        else
            return $xml;

    }

    private function sendBeiVerifyCode($params)
    {
        die;
        include_once(APP_PATH . "third_party/jytpay/lib/Snoopy.php");
        include_once(APP_PATH . "third_party/jytpay/lib/ENC.php");
        include_once(APP_PATH . "third_party/jytpay/lib/ArrayToXML.php");

        date_default_timezone_set('PRC');  // ����ʱ��
        /* 0. ����ݶԽӲ�Ʒ���ͺ�ʵ���̻����޸�������Ϣ  */
        //$url = 'http://test1.jytpay.com:20080/JytAuth/tranCenter/authReq.do';  // ��������URL
        //$url = 'http://test1.jytpay.com:20080/JytAuth/tranCenter/authReq.do';
        $url = $this->jianquanUrl;
        //$url = 'http://10.10.10.103:20080/JytAuth/tranCenter/authReq.do';  // ��������URL

        //$merchant_id = '290015200001';                                     // �����̻���
        //$mer_pub_file = 'cert/mer_rsa_public.pem';                         // �̻�RSA��Կ
        //$mer_pri_file = 'cert/mer_rsa_private.pem';                        // �̻�RSA˽Կ
        //$pay_pub_file = 'cert/pay_rsa_public.pem';                         // ƽ̨RSA��Կ

        $merchant_id = $this->jytID;                                     // �����̻���
        $mer_pub_file = APP_PATH . 'third_party/jytpay/cert/rsa_public_key_2048.pem';                         // �̻�RSA��Կ
        $mer_pri_file = APP_PATH . 'third_party/jytpay/cert/rsa_private_key_2048.pem';                        // �̻�RSA˽Կ
        $pay_pub_file = APP_PATH . 'third_party/jytpay/cert/pay_server_public_key.pem';                         // ƽ̨RSA��Կ

        $m = new ENC($pay_pub_file, $mer_pri_file);

        /* 1. ��֯����ͷ  */
        $req_param['merchant_id'] = $merchant_id;
        $req_param['tran_type'] = '01';
        $req_param['version'] = '1.0.0';
        $req_param['tran_flowid'] = $req_param['merchant_id'] . date('YmdHis') . rand(10000, 99999); // ������̻�ϵͳ���ж��嶩����
        $req_param['tran_date'] = date('Ymd');
        $req_param['tran_time'] = date('His');
        $req_param['tran_code'] = 'TR4004';

        /* 2. --- ����ݽӿڱ�����֯�������� ����������Ϊ�����֤�����������壬�밴��ʵ�ʽ��׽ӿ��������  */
        $req_body['bank_card_no'] = '6228480402564890018';//���п���
        $req_body['id_num'] = '320322199408025957';//֤����(���֤)
        $name = '���';
        $req_body['id_name'] = iconv('gbk', 'UTF-8', $name);//����
        $req_body['terminal_type'] = 'wap';//�����ն�����
        $req_body['bank_card_type'] = 'D';//���п�����
        $req_body['phone_no'] = '13011827890';//����Ԥ���ֻ���

        /* 3. ת����������Ϊxml��ʽ  */
        $data = array("head" => $req_param, "body" => $req_body);
        $xml_ori = ArrayToXML::toXml($data);

        //var_dump("xml_ori ::" . $xml_ori);

        /* 4. ��֯POST�ֶ�  */
        $req['merchant_id'] = $req_param['merchant_id'];
        $req['sign'] = $m->sign($xml_ori, 'hex');

        $key = rand(pow(10, (8 - 1)), pow(10, 8) - 1);
        $req['key_enc'] = $m->encrypt($key, 'hex');
        $req['xml_enc'] = $m->desEncrypt($xml_ori, $key);

//        var_dump("key ::  " . $key);
//        var_dump("req['key_enc'] ::  " . $req['key_enc']);
//        var_dump("req['xml_enc'] ::  " . $req['xml_enc']);

        /* 5. post�ύ��֧��ƽ̨ */
        $snoopy = new Snoopy;
        //httpЭ��
        //$snoopy->submit($url, $req);
        //httpsЭ��
        $snoopy->curl_https($url,$req);

        /* 6. ������ʽ�ֽⷵ�ر��� */
        preg_match('/^merchant_id=(.*)&xml_enc=(.*)&key_enc=(.*)&sign=(.*)$/', $snoopy->results, $matches);
        $xml_enc = $matches[2];
        $key_enc = $matches[3];
        $sign = $matches[4];

//        var_dump("xml_enc ::  " . $xml_enc);
//        var_dump("key_enc ::  " . $key_enc);
//        var_dump("sign ::  " . $sign);

        /* 7. ���ܲ���ǩ���ر���  */
        $key = $m->decrypt($key_enc, 'hex');
        $xml = $m->desDecrypt($xml_enc, $key);

//        var_dump("key :: " . iconv('UTF-8', 'gbk', $key));
//        var_dump("xml :: " . $xml);
        die;

        if (!$m->verify($xml, $sign, 'hex'))
            return "--- ��ǩʧ��!\n";
        else
            return $xml;
    }

    /**
     *������Ϣ
     */
    public function ReceiveJyt()
    {
        //post ��������
        if (isset($_POST)&&!empty($_POST)) {
            //���� ����
            include_once(APP_PATH . "third_party/jytpay/lib/Snoopy.php");
            include_once(APP_PATH . "third_party/jytpay/lib/ENC.php");
            include_once(APP_PATH . "third_party/jytpay/lib/ArrayToXML.php");
            date_default_timezone_set('PRC');  // ����ʱ��
            // �����̻���
            $mer_pub_file = APP_PATH . 'third_party/jytpay/cert/rsa_public_key_2048.pem';                         // �̻�RSA��Կ
            $mer_pri_file = APP_PATH . 'third_party/jytpay/cert/rsa_private_key_2048.pem';                        // �̻�RSA˽Կ
            $pay_pub_file = APP_PATH . 'third_party/jytpay/cert/pay_server_public_key.pem';
            $m = new ENC($pay_pub_file, $mer_pri_file);

            /* 6. ������ʽ�ֽⷵ�ر��� */
            //preg_match('/^merchant_id=(.*)&xml_enc=(.*)&key_enc=(.*)&sign=(.*)$/', $snoopy->results, $matches);
            $xml_enc = $_POST['xml_enc'];
            $key_enc = $_POST['key_enc'];
            $sign = $_POST['sign'];

            /* 7. ���ܲ���ǩ���ر���  */
            $key = $m->decrypt($key_enc, 'hex');
            $xml = $m->desDecrypt($xml_enc, $key);
            if (!$m->verify($xml, $sign, 'hex')) {
                //�����ܵ�Ȼ�������޸� Ȼ����ܷ���
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
                //�����ķ���
                echo substr($str, 0, -1);
            } else {

                //��¼����
//                $open=fopen("payxml.txt","a");
//                fwrite($open,$xml);
//                fclose($open);

                $xmlRes = simplexml_load_string($xml);
                //var_dump($xmlRes);
                //���� account ����
                $orderid = (string)$xmlRes->body->order_id[0];
                $usermodel = new Usermodel();
                $res = $usermodel->getRechargedNo($orderid);
                //var_dump($res);
                //die;

                $this->logInfomation("trade_no is " . $res['nid']);

                $usermodel->OnlineReturn(array("trade_no" => $res['nid']));
                $this->logInfomation("update bank info");

                //����������Ϣ����״̬
                $userid = Session::get("userid");
                $bankCardUserResult = $usermodel->getBankCard($userid);
                if(!empty($bankCardUserResult)&&$bankCardUserResult['status']!==1){
                    $bankCardUserResult['status'] = 1;
                    $bankCardUserResult['type'] = 1;   //���¼�¼
                    //var_dump($bankdata);
                    $usermodel->updateBankInfo($bankCardUserResult);
                }
                //$res = array("code" => 1, "msg" => iconv("utf-8","gbk","��ֵ�ɹ�"));
                //echo header("jyt: S0000000");
                return;
            }
//        echo iconv("gbk", "UTF-8", "���ܽ���ͨ��Ϣ");
//        //echo  "���ܽ���ͨ��Ϣ";
//        var_dump($_POST);
//        var_dump($_GET);
        }
    }
/////////////////////////////////////////////////////////////////////
//����ͨ--
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

        date_default_timezone_set('PRC');  // ����ʱ��
        /* 0. ����ݶԽӲ�Ʒ���ͺ�ʵ���̻����޸�������Ϣ  */
        //$url = 'http://test1.jytpay.com:20080/JytAuth/tranCenter/authReq.do';  // ��������URL
        //$url = 'http://test1.jytpay.com:20080/JytAuth/tranCenter/authReq.do';
        $url = $this->jianquanUrl;
        //$url = 'http://10.10.10.103:20080/JytAuth/tranCenter/authReq.do';  // ��������URL

        $merchant_id = $this->jytID;                                     // �����̻���
        $mer_pub_file = APP_PATH . 'third_party/jytpay/cert/rsa_public_key_2048.pem';                         // �̻�RSA��Կ
        $mer_pri_file = APP_PATH . 'third_party/jytpay/cert/rsa_private_key_2048.pem';                        // �̻�RSA˽Կ
        $pay_pub_file = APP_PATH . 'third_party/jytpay/cert/pay_server_public_key.pem';                         // ƽ̨RSA��Կ

        $m = new ENC($pay_pub_file, $mer_pri_file);

        /* 1. ��֯����ͷ  */
        $req_param['merchant_id'] = $merchant_id;
        $req_param['tran_type'] = '01';
        $req_param['version'] = '1.0.0';
        $req_param['tran_flowid'] = $req_param['merchant_id'] . date('YmdHis') . rand(10000, 99999); // ������̻�ϵͳ���ж��嶩����
        $req_param['tran_date'] = date('Ymd');
        $req_param['tran_time'] = date('His');
        $req_param['tran_code'] = 'TR4005';


        /* 2. --- ����ݽӿڱ�����֯�������� ����������Ϊ�����֤�����������壬�밴��ʵ�ʽ��׽ӿ��������  */
        $req_body['bind_card_id'] = $params['bind_card_id'];//���п���
        $req_body['verify_code'] = $params['dynamic_code'];//��֤��
        $req_body['phone_no'] = $params['card_bind_mobile_phone_no'];//����Ԥ���ֻ���

        /* 3. ת����������Ϊxml��ʽ  */
        $data = array("head" => $req_param, "body" => $req_body);
        $xml_ori = ArrayToXML::toXml($data);

        // var_dump("xml_ori ::" . $xml_ori);

        /* 4. ��֯POST�ֶ�  */
        $req['merchant_id'] = $req_param['merchant_id'];
        $req['sign'] = $m->sign($xml_ori, 'hex');

        $key = rand(pow(10, (8 - 1)), pow(10, 8) - 1);
        $req['key_enc'] = $m->encrypt($key, 'hex');
        $req['xml_enc'] = $m->desEncrypt($xml_ori, $key);

//        var_dump("key ::  " .$key);
//        var_dump("req['key_enc'] ::  " .$req['key_enc']);
//        var_dump("req['xml_enc'] ::  " .$req['xml_enc']);

        /* 5. post�ύ��֧��ƽ̨ */
        $snoopy = new Snoopy;
        //httpЭ��
        //$snoopy->submit($url, $req);
        //httpsЭ��
        $snoopy->curl_https($url,$req);

        /* 6. ������ʽ�ֽⷵ�ر��� */
        preg_match('/^merchant_id=(.*)&xml_enc=(.*)&key_enc=(.*)&sign=(.*)$/', $snoopy->results, $matches);
        $xml_enc = $matches[2];
        $key_enc = $matches[3];
        $sign = $matches[4];

//        var_dump("xml_enc ::  " .$xml_enc);
//        var_dump("key_enc ::  " .$key_enc);
//        var_dump("sign ::  " .$sign);

        /* 7. ���ܲ���ǩ���ر���  */
        $key = $m->decrypt($key_enc, 'hex');
        $xml = $m->desDecrypt($xml_enc, $key);

//        var_dump("key :: ". iconv('UTF-8', 'gbk', $key));
//        var_dump("xml :: ".$xml);

        if (!$m->verify($xml, $sign, 'hex'))
            return "--- ��ǩʧ��!\n";
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
        //��ȡ������ͬ��ǩ���̽�����ǩ

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


        if (strcmp($checkSign, $sign) == 0) {       //��ǩͨ�������ݰ�ȫ

            $this->logInfomation("check sign , is same---");

            $trade_status = $params["trade_status"];//����״̬
            if (strcmp($trade_status, "T") == 0) {  //���׳ɹ�
                $this->logInfomation("check trade_status , is T---");

                if (isset($_SESSION['OrderMoney'])) {
                    $OrderMoney = $_SESSION['OrderMoney'];//��ȡ�ύ����Session
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
                        $test = "/showmsg/index/" . urlencode("��ֵ") . "/" . urlencode("֧���ɹ�");
                    } else {
                        $test = "/showmsg/index/" . urlencode("�ֻ���") . "/" . urlencode("�󶨳ɹ�");
                    }
                    //echo iconv("GB2312","UTF-8",'֧���ɹ�');
                    //echo $test;
                    redirect($test, 'refresh');
                } else {
                    //echo("<script>alert(iconv('GB2312', 'UTF-8','ʵ�ʳɽ���������ύ�Ķ�����һ�£�����յ�֧���������ϸ�˶�ʵ�ʳɽ���������ɶ���������'));</script>");
                    if ($type == 0) {
                        $test = "/showmsg/index/" . urlencode("��ֵ") . "/" . urlencode('ʵ�ʳɽ���������ύ�Ķ�����һ�£�����յ�֧���������ϸ�˶�ʵ�ʳɽ���������ɶ���������') . "/topup";
                    } else {
                        $test = "/showmsg/index/" . urlencode("�ֻ���") . "/" . urlencode('�󶨴�����ϵ����Ա��') . "/topup/bindingPageOne";

                    }
                    //echo $test;
                    redirect($test, 'refresh');
                }
            } else {    //֧��ʧ�� ����Ϊ�������ɼ���֧��

                $replyInfo = array();
                if ($type == 0) {
                    $replyInfo["title"] = iconv('GB2312', 'UTF-8', '��ֵ');
                    $replyInfo["status"] = iconv('GB2312', 'UTF-8', '����ʧ�ܣ�������');
                    $replyInfo["error"] = $params["error_message"];
                    $replyInfo["return"] = 'topup';

                } else {
                    $replyInfo["title"] = iconv('GB2312', 'UTF-8', '�ֻ���');
                    $replyInfo["status"] = iconv('GB2312', 'UTF-8', '�ֻ���ʧ�ܣ�������');
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
            $this->form_validation->set_message('check_bindphonenum', iconv('GB2312', 'UTF-8', '�����ֻ���ʽ����ȷ'));
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
            "body" => iconv('GB2312', 'UTF-8', "�˺ų�ֵ"),
            "subject" => iconv('GB2312', 'UTF-8', "�˺ų�ֵ"),
            "sign_type" => "MD5",
            "input_charset" => "utf-8",
            "notify_url" => "",
            "out_trade_no" => "212u3133005048572",
            "trade_status" => "T",
            "extra_common_param" => "",
            "total_fee" => "1000",
            "error_message" => iconv('GB2312', 'UTF-8', "�������"),
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
            "subject" => iconv('GB2312', 'UTF-8', "�˺ų�ֵ"),
            "sign_type" => "MD5",
            "error_message" => iconv('GB2312', 'UTF-8', "�������"),
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

        $data = array('title' => '��ΰ', 'status' => 'lastname', 'errormsg' => '����', 'return' => 'topup');
        Session::set('message', $data);
        redirect('showmsg/showInfo');

    }

    /*
    * RSA ����
    */
    public function testrsa()
    {
        include_once(APP_PATH . "third_party/jytpay/lib/Snoopy.php");
        include_once(APP_PATH . "third_party/jytpay/lib/ENC.php");
        include_once(APP_PATH . "third_party/jytpay/lib/ArrayToXML.php");
        /*
         * ��Կ�ļ���·��
         */
        $privateKeyFilePath = APP_PATH . "third_party/jytpay/cert/rsa_private_key_2048.pem";
        /*
         * ��Կ�ļ���·��
         */
        //$publicKeyFilePath = APP_PATH . "third_party/jytpay/cert/rsa_public_key_2048.pem";
        $publicKeyFilePath = APP_PATH . "third_party/jytpay/cert/pay_server_public_key.pem";
        /*
         * ��鿪����չ
         */
        extension_loaded('openssl') or die('php��Ҫopenssl��չ֧��');
        /*
         * �ж��ļ��Ƿ����
         */
        (file_exists($privateKeyFilePath) && file_exists($publicKeyFilePath)) or die('��Կ���߹�Կ���ļ�·������ȷ');
        /**
         * ����Resource���͵���Կ�������Կ�ļ����ݱ��ƻ���openssl_pkey_get_private��������false
         * ����  openssl_get_privatekey ����  openssl_pkey_get_private
         */
        $privateKey = openssl_pkey_get_private(file_get_contents($privateKeyFilePath));
        /**
         * ����Resource���͵Ĺ�Կ�������Կ�ļ����ݱ��ƻ���openssl_pkey_get_public��������false
         *  ���� openssl_get_publickey  ���� openssl_pkey_get_public
         */
        $publicKey = openssl_pkey_get_public(file_get_contents($publicKeyFilePath));
        //�����Կ�͹�Կ �Ƿ񶼿���
        ($privateKey && $publicKey) or die('��Կ���߹�Կ������');
        /**
         * ԭ����
         */
        $originalData = 12345678;
        echo "<br/>"; //'�ҵ��ʺ���:shiki,������:matata';
        /**
         * �����Ժ�����ݣ���������·�ϴ���
         */
        $encryptData = '';

        echo 'ԭ����Ϊ:', $originalData, PHP_EOL;
        echo "<br/>";

        /*
         * openssl_private_encrypt ��˽Կ���� ���� ������ Ҳ�� ֻҪ�Գ�
         */

        //if (openssl_private_encrypt($originalData, $encryptData, $privateKey)) {
        $m = new ENC($publicKeyFilePath, $privateKeyFilePath);

        $key = $m->encrypt($originalData, 'hex');
        if ($key) {
            /**
             * ���ܺ� ����base64_encode�󷽱�����ַ�д��� ���ߴ�ӡ  �����ӡΪ����
             */
            echo '���ܳɹ������ܺ�����(hex)Ϊ:', $key, PHP_EOL;
            echo "<br/>";

        } else {
            die('����ʧ��');
            echo "<br/>";
        }

        /**
         * openssl_public_decrypt �ù�Կ���� ���� ������ Ҳ�� ֻҪ�Գ�
         * �����Ժ������
         */
        $decryptData;
        $keysss = '4de15961ea40755afd88182a5de49aefc269a2cb5e0ae34f86f8e7715d7ba18437cde7da89e79876c0d0bb32ce3316ac9aa2366a5fc60cac878adff5e521245319a927b4ca82aba909bf3f4ef53295d3625b359e63656cdf469ba769b36f2a2cfbc6dc03fc4a0cb25f520acf751efacbd82cc61e3ebbc7f05b6eec5a8ff1aa68728b066caa471da4afdd080c6b3d761e6504fc7c0333190c31df246306a4340b55f272471a0cb5d4604403204cfb126a582c29963d9179b447215e02fc288d8644508a35617fd8fe332d84aee1a7f700a900cea8dcd48f5191054e7d5e8e39392df1192ebc8780cf28f8bb03ad413ff10388b5175fd2ba1365ebbc260618a5ca';
        //if (openssl_public_decrypt($encryptData, $decryptData, $publicKey)) {
        $decryptData = $m->decrypt($keysss, 'hex');
        if ($key) {
            echo '���ܳɹ������ܺ�����Ϊ:', $decryptData, PHP_EOL;
            echo "<br/>";

        } else {
            die('���ܳɹ�');
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

        date_default_timezone_set('PRC');  // ����ʱ��
        /* 0. ����ݶԽӲ�Ʒ���ͺ�ʵ���̻����޸�������Ϣ  */
        //$url = 'http://test1.jytpay.com:20080/JytAuth/tranCenter/authReq.do';  // ��������URL
        //$url = 'http://test1.jytpay.com:20080/JytAuth/tranCenter/authReq.do';
        $url = $this->jianquanUrl;
        //$url = 'http://10.10.10.103:20080/JytAuth/tranCenter/authReq.do';  // ��������URL

        $merchant_id = $this->jytID;                                     // �����̻���
        $mer_pub_file = APP_PATH . 'third_party/jytpay/cert/rsa_public_key_2048.pem';                         // �̻�RSA��Կ
        $mer_pri_file = APP_PATH . 'third_party/jytpay/cert/rsa_private_key_2048.pem';                        // �̻�RSA˽Կ
        $pay_pub_file = APP_PATH . 'third_party/jytpay/cert/pay_server_public_key.pem';                         // ƽ̨RSA��Կ

        $m = new ENC($pay_pub_file, $mer_pri_file);

        /* 1. ��֯����ͷ  */
        $req_param['merchant_id'] = $merchant_id;
        $req_param['tran_type'] = '01';
        $req_param['version'] = '1.0.0';
        $req_param['tran_flowid'] = $req_param['merchant_id'] . date('YmdHis') . rand(10000, 99999); // ������̻�ϵͳ���ж��嶩����
        $req_param['tran_date'] = date('Ymd');
        $req_param['tran_time'] = date('His');
        $req_param['tran_code'] = 'TR4004';

        /* 2. --- ����ݽӿڱ�����֯�������� ����������Ϊ�����֤�����������壬�밴��ʵ�ʽ��׽ӿ��������  */
        $req_body['bank_card_no'] = "6228480018555991977";//���п���
        $req_body['id_num'] ="142201199304222552";//֤����(���֤)
        $name="���Ң";
        $req_body['id_name'] =iconv('gbk', 'UTF-8', $name);//����
        $req_body['terminal_type'] = '01';//�����ն�����
        $req_body['bank_card_type'] = 'D';//���п�����
        $req_body['phone_no'] = "18810577880";//����Ԥ���ֻ���

        /* 3. ת����������Ϊxml��ʽ  */
        $data = array("head" => $req_param, "body" => $req_body);
        $xml_ori = ArrayToXML::toXml($data);

//        var_dump("xml_ori ::".$xml_ori);

        /* 4. ��֯POST�ֶ�  */
        $req['merchant_id'] = $req_param['merchant_id'];
        $req['sign'] = $m->sign($xml_ori, 'hex');

        //var_dump("sign ::  " .  $req['sign']);

        $key = rand(pow(10, (8 - 1)), pow(10, 8) - 1);
        $req['key_enc'] = $m->encrypt($key, 'hex');
        $req['xml_enc'] = $m->desEncrypt($xml_ori, $key);

//        var_dump("key ::  " .$key);
//        var_dump("req['key_enc'] ::  " .$req['key_enc']);
//        var_dump("req['xml_enc'] ::  " .$req['xml_enc']);

        /* 5. post�ύ��֧��ƽ̨ */
        $snoopy = new Snoopy;
//        $snoopy->postdata($url, $req);
        $snoopy->curl_https($url,$req);

//        var_dump($url);
//        var_dump($merchant_id);
//        var_dump("xml_ori ::".$xml_ori);
//        var_dump($snoopy->results);
//        die;

        /* 6. ������ʽ�ֽⷵ�ر��� */
        preg_match('/^merchant_id=(.*)&xml_enc=(.*)&key_enc=(.*)&sign=(.*)$/', $snoopy->results, $matches);
        $xml_enc = $matches[2];
        $key_enc = $matches[3];
        $sign = $matches[4];

//        var_dump("xml_enc ::  " .$xml_enc);
//        var_dump("key_enc ::  " .$key_enc);
//        var_dump("sign ::  " .$sign);

        /* 7. ���ܲ���ǩ���ر���  */
        $key = $m->decrypt($key_enc, 'hex');
        $xml = $m->desDecrypt($xml_enc, $key);

//        var_dump("key :: ". iconv('UTF-8', 'gbk', $key));
        //var_dump("xml :: ".iconv('UTF-8', 'gbk', $xml));

//        var_dump($xml);
        if (!$m->verify($xml, $sign, 'hex'))
            return "--- ��ǩʧ��!\n";
        else
            return $xml;
    }
}
