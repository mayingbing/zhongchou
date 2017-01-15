<?php
namespace app\index\model;

use think\Log;
use think\Session;
use think\Model;
use think\Db;
use app\qylwap\model\Borrow_model;
use app\qylwap\model\Sms_model;
use app\qylwap\model\Email;
class Usermodel extends Model{
    // ���õ�ǰģ�Ͷ�Ӧ���������ݱ�����
    protected $table = 'yyd_users';
    // �������ݱ�����
    protected $pk    = 'user_id';


    public function IsExist($val)
    {
        if (isset($val)) {
            return $val;
        } else {
            return false;
        }
    }

    function ip_address()
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

//    public function db_fetch_array($sql)
//    {
//        $res = Db::query($sql);
//        $_res = "";
//        if(!empty($res)){
//            if (is_array($res['0'])) {
//                foreach ($res['0'] as $key => $value)
//                    $_res[$key] = $value;
//            }
//        }
//
//        return $_res;
//    }
    public function db_fetch_array($sql){
        $query = Db::query($sql);
        $_res = "";
        $res = $query;

        if(empty($res)){
            return $_res;
        }
        if (is_array($res[0])){
            foreach ($res[0] as $key =>$value)
                $_res[$key] =$value;
        }

        return $_res;
    }

    public function db_fetch_arrays($sql)
    {
        //echo $sql;
        $query = Db::query($sql);
        $i = 0;
        $_res = array();
        $res = $query;
        if (empty($res)) {
            return $_res;
        }

        foreach($res as $row){
            foreach ($row as $key =>$value){
                $_res[$i][$key] = $value;
            }
            $i++;
        }

        return $_res;
    }

//    public function db_fetch_arrays($sql)
//    {
//        //echo $sql;
//        $query = Db::query($sql);
//        $i = 0;
//        $_res = array();
//        $res = $query->result_array();
//        if (!$query) {
//            die(Db::_error_message());
//        }
//
//        foreach ($res as $row) {
//            foreach ($row as $key => $value) {
//                $_res[$i][$key] = $value;
//            }
//            $i++;
//        }
//        $query->free_result();
//        return $_res;
//    }

    function fetch_first($sql)
    {
        $query = Db::query($sql);
        if ($query) {
            return $query['0'];
        } else
            return null;
    }

    private function get_user_by_email($email)
    {
        $sql = "SELECT * FROM yyd_users WHERE email LIKE '$email'";

        $result = $this->fetch_first($sql);

        return $result;
    }

    public function get_user_by_username($username)
    {
        $sql = "SELECT * FROM yyd_users WHERE username LIKE '$username'";
        $result = $this->fetch_first($sql);
        return $result;
    }

    public function AddUsersLog($data)
    {
        $sql = "insert into yyd_users_log set addtime='" . time() . "',addip='" . $this->ip_address() . "'";
        foreach ($data as $key => $value) {
            $sql .= ",`$key` = '$value'";
        }
        Db::query($sql);
    }



    public function GetUsersVip($data = array())
    {
        if (self::IsExist($data['user_id']) == "")
            return false;

        $sql = "select p1.*,p2.adminname,p3.username from yyd_users_vip as p1 left join yyd_users_admin as p2 on p1.kefu_userid=p2.user_id left join yyd_users as p3 on p1.user_id=p3.user_id where p1.user_id={$data['user_id']}";
        $result = $this->db_fetch_array($sql);

        $this->logInfomation("get vip result " . json_encode($result));
        if ($result == false) {
            $sql = "insert into yyd_users_vip set user_id={$data['user_id']}";
            Db::query($sql);
            $result = $this->GetUsersVip($data);
            return $result;
        } else {
            $result["remark"] = iconv('GB2312', 'UTF-8', $result["remark"]);
            if ($result["status"] == 1) {
                if ($result["end_date"] != "" && $result["end_date"] < time()) {
                    $result["status"] = 3;
                }
            }
            return $result;
        }
    }

    public function getVerifyStatus($data = array())
    {
        $user_id = $data["user_id"];
        //.user_id 2.status 3.type 4.phone 5.credit 6.verify_userid 7.verify_time
        $resultlist = array();
        $sql = "select status, phone,addtime from yyd_approve_smslog where user_id ='$user_id'";

        $result = Db::query($sql);



        if ($result == false) {
            $resultlist['phone_status'] = '-1';
            $resultlist['phone_num'] = '-1';
            $resultlist['phone_verify_time'] = '-1';
        } else {
            $result =$result['0'];
            $resultlist['phone_status'] = $result['status'];
            $resultlist['phone_num'] = $result['phone'];
            $resultlist['phone_verify_time'] = date('Y-m-d H:i:s', $result['addtime']);
        }
        //.id 1.user_id 2.status 3.credit 4.remark 5.verify_userid 6.verify_time 7.verify_remark 8.addtime 9.addip
        $sql = "select status, verify_time from yyd_approve_video where user_id ='$user_id'";
        $result = $this->db_fetch_array($sql);

        if (!empty($result)) {
            $resultlist['video_status'] = $result['status'];
            $resultlist['video_verify_time'] = date('Y-m-d H:i:s', strtotime($result['verify_time']));
        }

        //0.id 1.user_id 2.realname 3.card_id 4.card_pic 5.card_pic1 6.card_pic2 7.id5_status 8.status 9.type 10.sex 11.verify_userid 12.verify_remark 13.verify_time 14.verify_id5_userid 15.verify_id5_time 16.verify_id5_remark 17.addtime 18.addip 	$sql= "select status, verify_time from yyd_approve_video where user_id ='$user_id'";
        $sql = "select status, verify_time,realname,card_id from yyd_approve_realname where user_id ='$user_id'";

        $result = Db::query($sql);



        if ($result == false) {
            $resultlist['realname_status'] = '-1';
            $resultlist['realname_verify_time'] = '-1';
            $resultlist['realname_name'] = '-1';
            $resultlist['realname_card_id'] = '-1';
        } else {
            $result = $result['0'];
            $resultlist['realname_status'] = $result['status'];
            $resultlist['realname_verify_time'] = date('Y-m-d H:i:s', strtotime($result['verify_time']));
            $resultlist['realname_name'] = $result['realname'];
            $resultlist['realname_card_id'] = $result['card_id'];

        }

        //0-id	1-user_id	2-email	3-status	4-addtime	5-addip

        $sql = "select status,email from yyd_users_email_active where user_id ='$user_id'";

        $result = $this->db_fetch_array($sql);
        if ($result == false) {
            $resultlist['email_status'] = '-1';

        } else {
            $resultlist['email_status'] = $result['status'];
            $resultlist['email_address'] = $result['email'];
        }
        return $resultlist;
    }

    function CheckEmail($data = array())
    {
        //���䲻��Ϊ��
        if (!self::IsExist($data['email'])) {
            return false;
        }
        //�ж��Ƿ��ǳ��˱������������
//        $_sql = "";
//        if (self::IsExist($data['user_id']) != false) {
//            $_sql = " and user_id!= {$data['user_id']}";
//        }
        $sql = "select 1 from yyd_users where  email = '{$data['email']}' ";
        $result = $this->db_fetch_array($sql);
        //������䲻���ڵĻ��򷵻�
        if ($result == false) return false;
        return true;
    }

    function CheckPhone($data = array())
    {
        //phone����Ϊ��
        if (!self::IsExist($data['phone'])) {
            return false;
        }
        //�ж��Ƿ��ǳ��˱������������
//        $_sql = "";
//        if (self::IsExist($data['user_id']) != false) {
//            $_sql = " and user_id!= {$data['user_id']}";
//        }
        $sql = "select 1 from yyd_users_info where  phone = '{$data['phone']}' ";
        $result = $this->db_fetch_array($sql);
        //���phone�����ڵĻ��򷵻�
        if ($result == false) return false;
        return true;
    }

    function CheckUsername($data = array())
    {

        //�û�������Ϊ��
        if (empty($data)) {

            return false;
        }
        //�ж��Ƿ��ǳ��˱������������
//        $_sql = "";
//        if (self::IsExist($data['user_id']) != false) {
//            $_sql = " and user_id!= {$data['user_id']}";
//        }

        $sql = "select 1 from yyd_users where  username = '{$data['username']}'";
        $result = $this->db_fetch_array($sql);
        //����û��������ڵĻ��򷵻�
        if (!$result) return false;
        return true;
    }

    public function Register($data = array())
    {

        $data['regtype'] = self::IsExist($data['regtype']) ? $data['regtype'] : 0;
        $reply = array();
        $reply['status'] = 0;
        //�ж��û����Ƿ�Ϊ��
        if (empty($data['username'])) {
            $reply['status'] = -1;
            $reply['errormsg'] = "�û���Ϊ��";
            return $reply;
        }
        //�ж��û��������Ƿ����15λ
        if (strlen($data['username']) > 15) {
            $reply['status'] = -1;
            $reply['errormsg'] = "�û�������15λ";
            return $reply;
        }
        //�ж������Ƿ�Ϊ��
        if (empty($data['password'])) {
            $reply['status'] = -2;
            $reply['errormsg'] = "����Ϊ��";
            return $reply;
        }
        if ($data['regtype'] != 1) {
            //���䲻��Ϊ��
            if (empty($data['email'])) {
                $reply['status'] = -3;
                $reply['errormsg'] = "����Ϊ��";
                return $reply;
            }
            //�ж����䳤���Ƿ����32λ
            if (strlen($data['email']) > 32) {
                $reply['status'] = -3;
                $reply['errormsg'] = "���䳤��32λ";
                return $reply;
            }
            //�ж������Ƿ��Ѿ�����
            if (self::CheckEmail(array("email" => $data['email']))) {
                $reply['status'] = -3;
                $reply['errormsg'] = "�û������Ѵ���";
                return $reply;
            }
        }
        //�ж��û����Ƿ����
        if (self::CheckUsername(array("username" => $data['username']))) {
            $reply['status'] = -1;
            $reply['errormsg'] = "�û����Ѵ���";
            return $reply;
        }

        if ($data["regtype"] == 1) {
            //user not exist and check whether the phone existed
            //�ж�phone�Ƿ��Ѿ�����
            if (self::CheckPhone(array("phone" => $data["phone"]))) {
                $reply['status'] = -4;
                $reply['errormsg'] = "�绰�����Ѵ���";
                return $reply;
            }
        }
//        //�ж��ظ�����
//        if($data['password']!=$data['confirm_password']){
//            $reply['status']="users_password_error";
//            return $reply;
//        }

//        //�ж���֤���Ƿ�����
//        if($data['shouji']!=1){
//            if ($_SESSION['valicode']!=$_POST['valicode']){
//                return "users_valicode_error";
//            }
//            if ($_POST['valicode']==""){
//                return "users_keywords_empty";
//            }
//        }

        //MD5���ܣ���һ����������ֹ������

        $passwordmd5 = md5($data['password']);
        $data['regtype'] = self::IsExist($data['regtype']) ? $data['regtype'] : 0;

        //����users������
        $sql = "insert into yyd_users set `register_type` = '" . $data['regtype'] . "',`reg_time` = '" . time() . "',`reg_ip` = '" . $this->ip_address() . "',";
        $sql .= "`up_time` = '" . time() . "',`up_ip` = '" . $this->ip_address() . "',`last_time` = '" . time() . "',`last_ip` = '" . $this->ip_address() . "',";
        $sql .= "`username`='{$data['username']}',";
        $sql .= "`password`='{$passwordmd5}',";
        $sql .= "`email`='{$data['email']}'";

        $result = Db::query($sql);
        $query="select last_insert_id() ";
        $curr_userid = Db::query($query);

        if (empty($curr_userid)) {
            $reply['errormsg'] = "����û�ʧ��";
        } else {


            $user_id = $curr_userid['0']['last_insert_id()'];

            Db::query($query);
            //���uc�û�
            $_data['user_id'] = $user_id;


            $sql = "select p1.* from yyd_users_type as p1 where p1.checked=1";
            $result_users_type = $this->db_fetch_array($sql);

            $sql = "insert into yyd_users_info set phone= {$data['phone']}, user_id='{$user_id}',type_id={$result_users_type['id']},status =1";
            Db::query($sql);

            $reply['name'] = $data['username'];
            $reply['email'] = $data["email"];
            $reply['status'] = "success";
            $reply["user_id"] = $user_id;

        }

        return $reply;
    }

    public function setDepositPwd($data)
    {
        $userid = $data["user_id"];
        $con=mysqli_connect("localhost","root","root","ma20160713");
        $pwd = $data["password"];
        $pwd = md5($pwd);
        $reply = 0;
        $result = mysqli_query($con,"Update yyd_users SET  paypassword='$pwd' where user_id='$userid'");

        if (!mysqli_affected_rows($con)) {
            $reply = -1;
        } else {
            $reply = 1;
        }

        mysqli_close($con);
        return $reply;

    }

    function getPwd($data = array())
    {
        $status = 0;
        $username = isset($data['username']) ? $data['username'] : "";
        $username = iconv("GB2312", "UTF-8", $username);
        $email = isset($data['email']) ? $data['email'] : "";
        $user = $this->db_fetch_array("select * from yyd_users where username='$username' and email='$email'");

        if (!$user) {
            $status = iconv('GB2312', 'UTF-8', "�û������ڣ������û���������");
        } else {
            $input = array();
            $randStr = str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefjhigklmnokpestuwwsyz1234567890');
            $newpassword = substr($randStr, 0, 7);
            $newpwd = md5($newpassword);

            $result = Db::query("Update yyd_users SET  password='$newpwd' where  username='$username'");
            if (!$result) {
                $status = iconv('GB2312', 'UTF-8', "�������ݿ����");
            } else {
                $status = 1;
                $input["user_id"] = $user["user_id"];
                $input['email'] = $user['email'];
                $input['username'] = $user['username'];
                $input['webname'] = "91Ͷ��-���ز��ڳ��쵼��";
                $input['title'] = "��������ȷ����";
                $input["newpassword"] = $newpassword;
                $input['msg'] = $this->GetpwdMsg($input);
                $input['type'] = "reg";
                session_start();
                if (isset($_SESSION['sendemail_time']) && $_SESSION['sendemail_time'] + 60 > time()) {
                    $status = iconv('GB2312', 'UTF-8', "ϵͳ��æ����2���Ӻ�����");
                } else {
                    $status = $this->SendEmail($input);
                    $_SESSION['sendemail_time'] = time();
                }

            }
        }
        return $status;
    }

    function sendActiveEmail($data = array())
    {
        $username = isset($data['username']) ? $data['username'] : "";
        $email = isset($data['email']) ? $data['email'] : "";
        $username = iconv("GB2312", "UTF-8", $username);
        $user = $this->db_fetch_array("select * from yyd_users where username='$username' ");
        if (!$user) {
            $status = -3;
        } else {
            $input = array();
            $input['email'] = $email;
            $input['username'] = $user['username'];
            $input['user_id'] = $user['user_id'];
            $input['webname'] = "91Ͷ��-���ز��ڳ��쵼��";
            $input['title'] = "ע���ʼ�ȷ��";
            $input['msg'] = $this->RegEmailMsg($input);
            $input['type'] = "reg";
            session_start();
            //echo "------------2-----------";
            //var_dump($input);
            if (isset($_SESSION['sendemail_time']) && $_SESSION['sendemail_time'] + 60 > time()) {
                $status = iconv('GB2312', 'UTF-8', "ϵͳ��æ����2���Ӻ�����");;
            } else {
                $status = $this->sendEmail($input);
                $_SESSION['sendemail_time'] = time();
            }

        }
        return $status;
    }

    function GetpwdMsg($data = array())
    {

        $username = iconv("GB2312", "UTF-8", $data['username']);
        $newpassword = $data['newpassword'];
        $webname = $data['webname'];

        $send_email_msg = '
	<div style="background: url(http://' . $_SERVER['HTTP_HOST'] . '/data/images/base/email_bg.png) no-repeat left bottom; font-size:14px; width: 588px; ">
	<div style="padding: 10px 0px; background: url(http://' . $_SERVER['HTTP_HOST'] . '/data/images/base/email_button.png)  no-repeat ">
		<h1 style="padding: 0px 15px; margin: 0px; overflow: hidden; height: 60px;">
			<a title="' . $webname . '�û�����" href="http://' . $_SERVER['HTTP_HOST'] . '/Index_old.php?user" target="_blank" swaped="true">
			<img style="border-width: 0px; padding: 0px; margin: 0px;" alt="' . $webname . '�û�����" src="http://' . $_SERVER['HTTP_HOST'] . '/data/images/base/email_logo.png" >		</a>
		</h1>
		<div style="padding: 0px 20px; overflow: hidden; line-height: 40px; height: 50px; text-align: right;"> </div>
		<div style="padding: 2px 20px 30px;">
			<p>�װ��� <span style="color: rgb(196, 0, 0);">' . $username . '</span> , ���ã�</p>
			<p>����������Ϊ<span style="color: rgb(196, 0, 0);">' . $newpassword . '</span> </p>

			<p style="text-align: right;"><br>' . $webname . '�û����� ����</p>
			<p><br>��Ϊ�Զ������ʼ�������ֱ�ӻظ����������κ����ʣ�����<a title="�����ϵ����" style="color: rgb(15, 136, 221);" href="http://' . $_SERVER['HTTP_HOST'] . '/contact/index.html" target="_blank" >��ϵ����&gt;&gt;</a></p>
		</div>
	</div>
</div>
		';
        return $send_email_msg;

    }

    function RegEmailMsg($data = array())
    {
        $user_id = $data['user_id'];
        $username = iconv('GB2312', 'UTF-8', $data['username']);
        $webname = $data['webname'];
        $email = $data['email'];
        $query_url = isset($data['query_url']) ? $data['query_url'] : "active";
        $active_id = urlencode(self::authcode($user_id . "," . time(), "TTWCGY"));
        $_url = "http://{$_SERVER['HTTP_HOST']}/Index_old.php?user&q={$query_url}&id={$active_id}";
        $user_url = "http://{$_SERVER['HTTP_HOST']}/Index_old.php?user";
        $send_email_msg = '
	<div style="background: url(http://' . $_SERVER['HTTP_HOST'] . '/data/images/base/email_bg.png) no-repeat left bottom; font-size:14px; width: 588px; ">
	<div style="padding: 10px 0px; background: url(http://' . $_SERVER['HTTP_HOST'] . '/data/images/base/email_button.png)  no-repeat ">
		<h1 style="padding: 0px 15px; margin: 0px; overflow: hidden; height: 60px;">
			<a title="' . $webname . '�û�����" href="http://' . $_SERVER['HTTP_HOST'] . '/Index_old.php?user" target="_blank" swaped="true">
			<img style="border-width: 0px; padding: 0px; margin: 0px;" alt="' . $webname . '�û�����" src="http://' . $_SERVER['HTTP_HOST'] . '/data/images/base/email_logo.png" >		</a>
		</h1>
		<div style="padding: 0px 20px; overflow: hidden; line-height: 40px; height: 50px; text-align: right;"> </div>
		<div style="padding: 2px 20px 30px;">
			<p>�װ��� <span style="color: rgb(196, 0, 0);">' . $username . '</span> , ���ã�</p>
			<p>��л��ע��' . $webname . '������¼�������ʺ�Ϊ <strong style="font-size: 16px;">' . $email . '</strong></p>
			<p>������������Ӽ�����ɼ��</p>
			<p style="overflow: hidden; width: 100%; word-wrap: break-word;"><a title="������ע��" href="' . $_url . '" target="_blank" swaped="true">' . $_url . '</a>
			<br><span style="color: rgb(153, 153, 153);">(��������޷�������뽫��������������ĵ�ַ����)</span></p>

			<p>��л������' . $webname . '�û����ģ����ǵ���ּ��Ϊ���ṩ����Ĳ�Ʒ�����ʵķ��� <br>���ھ͵�¼��!
			<a title="�����¼' . $webname . '�û�����" style="color: rgb(15, 136, 221);" href="http://' . $_SERVER['HTTP_HOST'] . '/Index_old.php?user" target="_blank" swaped="true">http://' . $_SERVER['HTTP_HOST'] . '/Index_old.php?user</a>
			</p>
			<p style="text-align: right;"><br>' . $webname . '�û����� ����</p>
			<p><br>��Ϊ�Զ������ʼ�������ֱ�ӻظ����������κ����ʣ�����<a title="�����ϵ����" style="color: rgb(15, 136, 221);" href="http://' . $_SERVER['HTTP_HOST'] . '/helps/index.html" target="_blank" >��ϵ����&gt;&gt;</a></p>
		</div>
	</div>
</div>
		';
        return $send_email_msg;

    }

    function sendEmail($data = array())
    {
        $user_id = 0;
        $email = "";
        if ($data['user_id'] > 0) {

            $sql = "select email from yyd_users where user_id='{$data['user_id']}'";
            $result = $this->db_fetch_array($sql);
            $email = $result['email'];
            $user_id = $data['user_id'];
        }
        $title = isset($data['title']) ? $data['title'] : 'ϵͳ��Ϣ';//�ʼ����͵ı���
        $msg = isset($data['msg']) ? $data['msg'] : 'ϵͳ��Ϣ';//�ʼ����͵�����
        $type = isset($data['type']) ? $data['type'] : 'system';//�ʼ����͵�����

        $input = array();
        $input['email_info']["con_email_host"] = "smtp.exmail.qq.com";
        $input['email_info']["con_email_url"] = "service@91toufang.com";
        $input['email_info']["con_email_auth"] = 1;
        $input['email_info']["con_email_from"] = "service@91toufang.com";
        $input['email_info']["con_email_from_name"] = "�ز������";
        $input['email_info']["con_email_password"] = "6132146a";
        $input['email_info']["con_email_port"] = 25;
        $input['email_info']["con_email_now"] = 1;
        $send_email = $input['email_info']['con_email_from'];

        $email_info = isset($input['email_info']) ? $input['email_info'] : '';//�ʼ�������Ϣ
        if ($input['email_info']["con_email_now"] == 1 || $type == "set") {
            if ($email == "") {
                $email = $input['email_info']["con_email_from"];
            }

            $result = self::sendoutEmail($title, $msg, $email, $input["email_info"]);
            $status = $result ? 1 : 2;
        } else {
            $status = 0;
        }
        if ($email_info == "") {
            $send_email = $input['email_info']["con_email_from"];
        } else {
            $send_email = $email;
        }

        Db::query("insert into yyd_users_email_log set email='{$email}',send_email='{$send_email}',user_id='{$user_id}',title='{$title}',msg='{$msg}',type='{$type}',status='{$status}',addtime='" . time() . "',addip='" . self::ip_address() . "'");

        return $status;
    }

    public function sendoutEmail($subject, $body, $to, $data)
    {
//        $config = array(
//            'crlf' => "\r\n",
//            'newline' => "\r\n",
//            'charset' => 'utf-8',
//            'protocol' => 'smtp',
//            'mailtype' => 'html',
//            'smtp_host' => 'smtp.exmail.qq.com',
//            'smtp_port' => '25',
//            'smtp_user' => 'service@91toufang.com',
//            'smtp_pass' => '6132146a'
//        );
//        $emai = new Email($config);
//        $emai->from($data['con_email_from'], iconv('GB2312', 'UTF-8', $data['con_email_from_name']));
//        $emai->to($to);
//        $emai->subject(iconv('GB2312', 'UTF-8', $subject));
//        $emai->message(iconv('GB2312', 'UTF-8', $body));
//        $emai->send();
//
//        return $emai->print_debugger();
    }

    public function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
    {
        $ckey_length = 4;
        $key = md5($key ? $key : "dw10c20m05w18");
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';
        $cryptkey = $keya . md5($keya . $keyc);
        $key_length = strlen($cryptkey);
        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
        $string_length = strlen($string);
        $result = '';
        $box = range(0, 255);
        $rndkey = array();
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }
        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
        if ($operation == 'DECODE') {
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            return $keyc . str_replace('=', '', base64_encode($result));
        }
    }

    function UpdateUsersInfo($data = array())
    {

        //�ж������Ƿ����
        if (!self::IsExist($data['user_id'])) {
            return "users_info_userid_empty";
        }
        //�жϱ�ʶ���Ƿ����
        $sql = "select 1 from yyd_users_info where user_id='{$data['user_id']}' ";
        $result = $this->db_fetch_array($sql);
        if ($result == false) {
            $sql = "insert into yyd_users_info set user_id={$data['user_id']}";
            Db::query($sql);
        }

        $sql = "update yyd_users_info set ";
        foreach ($data as $key => $value) {
            $_sql[] = "`$key` = '$value'";
        }
        Db::query($sql . join(",", $_sql) . " where user_id='{$data['user_id']}' ");
        return $data['user_id'];
    }

    public function unRegister($data = array())
    {

        $sql = "select * from yyd_users where username = '{$data['username']}'";
        $tmp = Db::query($sql);
        $result = $tmp->result_array();
        $reply['status'] = "";
        if (!$result) {
            $reply['status'] = "user_unRegister_error1";
        } else {
            $user_id = $result["0"]['user_id'];

            $sql = "delete  from yyd_users where username = '{$data['username']}'";

            $result = Db::query($sql);

            if (!$result) {
                $reply['status'] = "user_unRegister_error2";
            } else {
                $sql = "delete  from yyd_users_info where user_id='{$user_id}'";
                Db::query($sql);
                if (!$result) {
                    $reply['status'] = "user_unRegister_error3";
                } else {
                    $reply['status'] = "success";
                }
            }
        }
        return $reply['status'];

    }

    //ʵ����֤
    function CheckRealname($data = array())
    {
        if (!self::IsExist($data['user_id'])) return "approve_realname_user_id_empty";

        $sql = "select p1.*,p2.username from yyd_approve_realname as p1  left join yyd_users as p2 on p1.user_id=p2.user_id where p1.user_id='{$data['user_id']}'";
        $result = $this->db_fetch_array($sql);
        if ($result == false)
            return "approve_realname_empty";

        $realname = $result['realname'];
        if ($data['status'] == 1) {
            $sql = "select * from yyd_approve_realname where card_id='{$result['card_id']}' and status=1 and user_id!='{$data['user_id']}'";
            $result = $this->db_fetch_array($sql);
            if ($result != false) {
                return "approve_realname_card_id_exiest";
            }
        }

        $sql = "update yyd_approve_realname set verify_userid='{$data['verify_userid']}',verify_remark='{$data['verify_remark']}', verify_time='" . time() . "',status='{$data['status']}' where user_id='{$data['user_id']}'";
        Db::dquery($sql);
        $user_info['user_id'] = $data["user_id"];
        if ($data['status'] != 1) {
            $realname = "";
        }
        $user_info['realname'] = $realname;
        $user_info['realname_status'] = $data['status'];
        $result = self::UpdateUsersInfo($user_info);
        //������˼�¼
        $_data["user_id"] = $result["user_id"];
        $_data["result"] = $data["status"];
        $_data["code"] = "approve";
        $_data["type"] = "realname";
        $_data["article_id"] = $data["user_id"];
        $_data["verify_userid"] = $data["verify_userid"];
        $_data["remark"] = $data["verify_remark"];
        self::AddExamine($_data);

        //��ӻ��ּ�¼
        $sql = "select * from yyd_credit_log where user_id={$data['user_id']} and type='realname'";
        $cre_result = $this->db_fetch_array($sql);
        if ($cre_result == false) {
            $credit_log['user_id'] = $data['user_id'];
            $credit_log['nid'] = "realname";
            $credit_log['code'] = "approve";
            $credit_log['type'] = "realname";
            $credit_log['addtime'] = time();
            $credit_log['article_id'] = $data['user_id'];
            $credit_log['remark'] = "ʵ����֤ͨ�����û���";
            self::ActionCreditLog($credit_log);
        }

        return $data['user_id'];
    }

    function AddExamine($data = array())
    {
        $sql = "insert into yyd_examines set addtime='" . time() . "',addip='" . self::ip_address() . "',";
        foreach ($data as $key => $value) {
            $_sql[] = "`$key` = '$value'";
        }
        Db::query($sql . join(",", $_sql));
        return Db::insert_id();
    }

    function ActionCreditLog($data)
    {
        $_nid = explode(",", $data['nid']);
        //��һ����ɾ��û�еĻ��ּ�¼
        $_sql = "delete from yyd_credit_log where code='{$data['code']}'  and type='{$data['type']}' and article_id='{$data['article_id']}' and nid not in ('{$data['nid']}')";
        Db::query($_sql);

        //�ڶ��������ʽ��¼
        if (count($_nid) > 0) {
            foreach ($_nid as $key => $nid) {
                if ($nid != "") {
                    if (isset($data['value']) && $data['value'] != "") {
                        $_value = $data['value'];
                    } else {
                        $sql = "select `value` from yyd_credit_type where nid='{$nid}'";
                        $result = $this->db_fetch_array($sql);
                        $_value = $result['value'];
                    }

                    /*
                    $sql = "select * from `{credit_log}` where code='{$data['code']}'  and type='{$data['type']}' and article_id='{$data['article_id']}' and nid='{$nid}' and user_id='{$data['user_id']}'";
                    $result = $mysql->db_fetch_array($sql);
                    if ($result==false){
                        $sql = "insert into `{credit_log}` set code='{$data['code']}',user_id='{$data['user_id']}',`value`='{$_value}',`credit`='{$_value}',type='{$data['type']}',article_id='{$data['article_id']}',nid='{$nid}',addtime='{$data['addtime']}',remark='{$data['remark']}',update_time='".time()."'";
                        $mysql->db_query($sql);
                    }else{
                        $sql = "update `{credit_log}` set addtime='{$data['addtime']}',user_id='{$data['user_id']}',`value`='{$_value}',update_time='".time()."' where code='{$data['code']}'  and type='{$data['type']}' and article_id='{$data['article_id']}' and nid='{$nid}'";

                        $mysql->db_query($sql);
                    }
                    */
                    $sql = "insert into yyd_credit_log set code='{$data['code']}',user_id='{$data['user_id']}',`value`='{$_value}',`credit`='{$_value}',type='{$data['type']}',article_id='{$data['article_id']}',nid='{$nid}',addtime='{$data['addtime']}',remark='{$data['remark']}'";
                    Db::query($sql);
                }
            }
            self::ActionCredit(array("user_id" => $data['user_id']));
        }

    }

    function ActionCredit($data)
    {
        $sql = "select sum(p1.credit) as num,p2.class_id from yyd_credit_log as p1 left join yyd_credit_type as p2 on p1.nid=p2.nid  where p1.user_id='{$data['user_id']}' group by p2.class_id order by p2.class_id desc";
        $result = $this->db_fetch_arrays($sql);
        $credits = serialize($result);
        $sql = "select 1 from yyd_credit where user_id='{$data['user_id']}'";
        $result = $this->db_fetch_array($sql);
        if ($result == false) {
            $sql = "insert into yyd_credit set user_id='{$data['user_id']}',`credits`='{$credits}'";
        } else {
            $sql = "update yyd_credit set `credits`='{$credits}' where user_id='{$data['user_id']}'";
        }
        Db::query($sql);
        self::CountCredit(array("user_id" => $data['user_id'], "type" => "catoreasy"));
    }

    function CountCredit($data)
    {
        if ($data['type'] == "catoreasy") {
            $borrow_model = new Borrow_model;
            $result = $borrow_model->GetBorrowCredit(array("user_id" => $data['user_id']));
            $sql = "update yyd_credit set credit='{$result['credit_total']}' where user_id='{$data['user_id']}'";
            Db::query($sql);
        }

    }

    function checkPhoneExist($userid)
    {
        $sql = "select * from yyd_approve_sms where user_id =  $userid";
        //echo $sql;
        $result = $this->db_fetch_array($sql);
        // var_dump($result);
        if ($result == false) {
            return false;
        } else {
            if ($result["phone"] == "")
                return false;
            else
                return true;
        }
    }

    function ProofProcess($input = array())
    {
        if ($input["type"] == "phone") {
            if ($_SESSION['smscode_time'] + 60 > time() && $_SESSION['smscode_phone'] == $input['phone']) {
                $msg = "���1���Ӻ�������";
            } else {

                $smsmodel = new Sms_model;
                $isExist = $this->checkPhoneExist($input["user_id"]);
                if ($isExist != false) {

                    $data['phone'] = $input['phone'];
                    $data['user_id'] = $input['user_id'];

                    $result = $smsmodel->AddSms($input);

                    if (strrpos($result, "approve_sms") !== false) {
                        $msg = "ϵͳ����";
                    } else {

                        $data['status'] = 1;
                        $data['user_id'] = $input['user_id'];
                        $data['type'] = "smscode";
                        $data['code'] = rand(100000, 999999);
                        $data['contents'] = "�������޸���֤�ֻ�����֤��Ϊ:" . $data['code'] . "���벻Ҫ����֤��й¶���κ��ˡ���Ǯ������";
                        //$data['phone'] = $_G['user_info']['phone'];
                        $result = $this->Sms_model->SendSMS($data);
                        $_SESSION['smscode_time'] = time();
                        $_SESSION['smscode_othertime'] = $_SESSION['smscode_time'] - time();
                        $_SESSION['smscode_phone'] = $data['phone'];

                        if ($result > 0) {
                            $msg = "success";

                        } else {
                            $_SESSION['smscode_username'] = "";
                            $msg = iconv("GB2312", "UTF-8", '��֤���ŷ���ʧ�ܣ�����ϵ�ͷ���');

                        }
                    }

                } else {
                    $smsmodel = new Sms_model;
                    $result = $this->Sms_model->AddSms($input);
                    if ($result > 0) {
                        $data['status'] = 1;
                        $data['user_id'] = $input['user_id'];
                        $data['type'] = "smscode";
                        $data['code'] = rand(100000, 999999);
                        $data['contents'] = "�����ֻ���֤��Ϊ:" . $data['code'] . "���벻Ҫ����֤��й¶���κ��ˡ���Ǯ������";

                        $result = $this->Sms_model->SendSMS($data);
                        $_SESSION['smscode_time'] = time();
                        $_SESSION['smscode_othertime'] = $_SESSION['smscode_time'] - time();
                        $_SESSION['smscode_phone'] = $data['phone'];
                        if ($result > 0) {
                            $msg = "success";
                        } else {
                            $_SESSION['smscode_username'] = "";
                            $msg = iconv("GB2312", "UTF-8", '��֤���ŷ���ʧ�ܣ�����ϵ�ͷ���');

                        }

                    } else {
                        $_SESSION['smscode_username'] = "";
                        $msg = iconv("GB2312", "UTF-8", '���ݿ�ϵͳ����');
                    }

                }

            }


        } else if ($input["type"] == "email") {
            $data['user_id'] = $input['user_id'];
            $data['email'] = $input['email'];

            $result = self::CheckEmail($data);

            if ($result == false) {
                $result = self::UpdateEmail($data);
                if ($result == false) {
                    $msg = $result;
                } else {
                    $data['username'] = $input['username'];
                    $data['webname'] = "91Ͷ��-���ز��ڳ��쵼��";
                    $data['title'] = "ע���ʼ�ȷ��";
                    $data['msg'] = $this->RegEmailMsg($data);
                    $data['type'] = "reg";
                    session_start();
                    if (isset($_SESSION['sendemail_time']) && $_SESSION['sendemail_time'] + 60 > time()) {
                        $msg = iconv('GB2312', 'UTF-8', "ϵͳ��æ����2���Ӻ�����");;
                    } else {
                        $status = $this->sendEmail($input);
                        if ($status == true) {
                            $_SESSION['sendemail_time'] = time();
                            $msg = "success";
                        } else {
                            $msg = iconv('GB2312', 'UTF-8', "����ʧ�ܣ��������Ա��ϵ��");
                        }

                    }
                }
            } else {
                $msg = iconv('GB2312', 'UTF-8', "��������д�������Ѿ�����");
            }

        } else if ($input["type"] == "identity") {

        }

    }

    function UpdateEmail($data = array())
    {

        //�ж��û�id�Ƿ��Ѿ�����
        if (!self::IsExist($data['user_id'])) {
            return "users_userid_empty";
        }
        //�ж������Ƿ�Ϊ��
        if (!self::IsExist($data['email'])) {
            return "users_email_empty";
        }
        //�ж����������Ƿ��Ѿ�����
        if (self::CheckEmail(array("email" => $data['email'], "user_id" => $data['user_id']))) {
            return "users_email_exist";
        }

        //�޸�����
        $sql = "update yyd_users set `email` = '" . $data['email'] . "' where `user_id` = '{$data['user_id']}'";

        $result = Db::query($sql);
        if ($result == false) {
            //�������Ա������¼
            $admin_log["user_id"] = $data['user_id'];
            $admin_log["code"] = "users";
            $admin_log["type"] = "email";
            $admin_log["operating"] = "update";
            $admin_log["article_id"] = $data['user_id'];
            $admin_log["result"] = 0;
            $admin_log["content"] = $data["user_id"] . "users_update_email_error_msg";
            $admin_log["data"] = $data;
            self::AddAdminLog($admin_log);
        } else {
            //�������Ա������¼
            $admin_log["user_id"] = $data['user_id'];
            $admin_log["code"] = "users";
            $admin_log["type"] = "email";
            $admin_log["operating"] = "update";
            $admin_log["article_id"] = $data['user_id'];
            $admin_log["result"] = 1;
            $admin_log["content"] = $data["username"] . "users_update_email_success_msg";
            $admin_log["data"] = $data;
            self::AddAdminLog($admin_log);
        }
        return $result;
    }

    function AddAdminLog($data)
    {

        $data["data"] = serialize($data["data"]);
        $sql = "insert into yyd_users_adminlog set  addtime='" . time() . "',addip='" . self::ip_address() . "'";
        foreach ($data as $key => $value) {
            $sql .= ",`$key` = '$value'";
        }
        Db::query($sql);
    }

    private function isIdCard($id='')
    {
        $set = array(7,9,10,5,8,4,2,1,6,3,7,9,10,5,8,4,2);
        $ver = array('1','0','x','9','8','7','6','5','4','3','2');
        $arr = str_split($id);
        $sum = 0;
        for ($i = 0; $i < 17; $i++)
        {
            if (!is_numeric($arr[$i]))
            {
                return false;
            }
            $sum += $arr[$i] * $set[$i];
        }
        $mod = $sum % 11;
        if (strcasecmp($ver[$mod],$arr[17]) != 0)
        {
            return false;
        }
        return true;
    }

    function UpdateRealname($data = array())
    {
        //id
        if (!self::IsExist($data['user_id'])) return "approve_realname_user_id_empty";

        //�ж���ʵ�����Ƿ����
        if (!self::IsExist($data['realname'])) {
            return "approve_realname_realname_empty";
        }
        //�ж����֤���Ƿ����
        if (!self::IsExist($data['card_id'])) {
            return "approve_realname_card_id_empty";
        }
        if (!self::isIdCard($data['card_id'])) {
            return "approve_realname_card_id_error";
        }

        $sql = "select * from yyd_approve_realname where card_id='{$data['card_id']}' and status=1 and user_id!='{$data['user_id']}'";
        $result = $this->db_fetch_array($sql);
        if ($result != false) {
            return "approve_realname_card_id_exiest";
        }

        $result = self::GetRealnameOne(array("user_id" => $data['user_id']));
        if (self::IsExist($data['card_pic1']) != false) {
            $_data['user_id'] = $result["user_id"];
            $_data['id'] = $result["card_pic1"];
//            $this->Delete($_data);
        }
        if (self::IsExist($data['card_pic2']) != false) {
            $_data['user_id'] = $result["user_id"];
            $_data['id'] = $result["card_pic2"];
//            $this->Delete($_data);
        }
        $sql = "update yyd_approve_realname set addtime='" . time() . "',addip='" . self::ip_address() . "',";
        foreach ($data as $key => $value) {
            $_sql[] = "`$key` = '$value'";
        }
        Db::query($sql . join(",", $_sql) . " where user_id='{$data['user_id']}'");
        return $data["user_id"];
    }

    private function DelPic($dir, $filename)
    {
        $_filename = substr($filename, 0, strlen($filename) - 4);
        if (is_dir($dir)) {
            $dh = opendir($dir);
            while (false !== ($file = readdir($dh))) {
                if ($file != "." && $file != "..") {
                    $fullpath = $dir . "/" . $file;
                    $_url = explode($_filename, $file);
                    if (!is_dir($fullpath) && isset($_url[0]) && $_url[0] == "") {
                        unlink($fullpath);
                    }
                }
            }
            closedir($dh);
        }
    }

//    private function Delete($data = array())
//    {
//
//        $_sql = "where id='{$data['id']}'";
//        if (isset($data['user_id']) && $data['user_id'] != "") {
//            $_sql .= " and user_id = '{$data['user_id']}'";
//        }
//        $sql = "select * from yyd_users_upfiles  {$_sql}";
//        $result = $this->db_fetch_array($sql);
//        if ($result != false) {
//            $_dir = explode($result['filename'], $result['fileurl']);
//            self::DelPic($_dir[0], $result['filename']);
//            $sql = "delete from yyd_users_upfiles {$_sql}";
//            Db::query($sql);
//        }
//
//    }

    function GetUsersInfo($data = array())
    {

        //�ж��û�ID�Ƿ����
        if (!self::IsExist($data['user_id'])) {
            return "users_info_userid_empty";
        }
        $sql = "select p1.*,p2.username from yyd_users_info as p1 left join yyd_users as p2 on p1.user_id=p2.user_id  where p1.user_id='{$data['user_id']}'";
        return $this->db_fetch_array($sql);

    }

    /*
     * �ҵ���¼
     */
    function  getRechargedNo($flowid){
        $sql="select nid from yyd_account_recharge where nid='{$flowid}'";
        return $this->db_fetch_array($sql);
    }

    function AddLog($data = array())
    {
        //��һ������ѯ�Ƿ����ʽ��¼
        $sql = "select * from yyd_account_log where `nid` = '{$data['nid']}'";
        $result = $this->db_fetch_array($sql);
        if (is_array($result) && $result['nid'] != "") return "account_log_nid_exiest";

        //�ڶ�������ѯԭ�������ʽ�
        $sql = "select * from yyd_account where user_id='{$data['user_id']}'";
        $result = $this->db_fetch_array($sql);
        if ($result == false) {
            $sql = "insert into yyd_account set user_id='{$data['user_id']}',total=0";
            Db::query($sql);
            $sql = "select * from yyd_account where user_id='{$data['user_id']}'";
            $result = $this->db_fetch_array($sql);
        }

        //�������������û��Ĳ����¼
        $sql = "insert into yyd_account_log set ";

        $sql .= "nid='{$data['nid']}',";
        $sql .= "user_id='{$data['user_id']}',";
        $sql .= "type='{$data['type']}',";
        $sql .= "money='{$data['money']}',";
        $sql .= "remark='{$data['remark']}',";
        $sql .= "to_userid='{$data['to_userid']}',";

        $sql .= "balance_cash_new='{$data['balance_cash']}',";
        $sql .= "balance_cash_old='{$result['balance_cash']}',";
        $sql .= "balance_cash=balance_cash_new+balance_cash_old,";

        $sql .= "balance_frost_new='{$data['balance_frost']}',";
        $sql .= "balance_frost_old='{$result['balance_frost']}',";
        $sql .= "balance_frost=balance_frost_new+balance_frost_old,";

        $sql .= "balance_new=balance_cash_new+balance_frost_new,";
        $sql .= "balance_old='{$result['balance']}',";
        $sql .= "balance=balance_new+balance_old,";

        $sql .= "income_new='{$data['income']}',";
        $sql .= "income_old='{$result['income']}',";
        $sql .= "income=income_new+income_old,";

        $sql .= "expend_new='{$data['expend']}',";
        $sql .= "expend_old='{$result['expend']}',";
        $sql .= "expend=expend_new+expend_old,";

        $sql .= "frost_new='{$data['frost']}',";
        $sql .= "frost_old='{$result['frost']}',";
        $sql .= "frost=frost_new+frost_old,";

        $sql .= "await_new='{$data['await']}',";
        $sql .= "await_old='{$result['await']}',";
        $sql .= "await=await_new+await_old,";

        $sql .= "total_old='{$result['total']}',";
        $sql .= "total=balance+frost+await,";
        $sql .= " `addtime` = '" . time() . "',`addip` = '" . self::ip_address() . "'";
        Db::query($sql);
        $id = Db::query('select last_insert_id()');
        $id = $id['0']['last_insert_id()'];

        $sql = "select * from yyd_account_log where user_id='{$data['user_id']}' and id='{$id}'";
        $result = $this->db_fetch_array($sql);

        //���Ĳ��������û���
        $sql = "update yyd_account set income={$result['income']},expend='{$result['expend']}',";
        $sql .= "balance_cash={$result['balance_cash']},balance_frost={$result['balance_frost']},";
        $sql .= "frost={$result['frost']},";
        $sql .= "await={$result['await']},";
        $sql .= "balance={$result['balance']},";
        $sql .= "total={$result['total']}";
        $sql .= " where user_id='{$data['user_id']}'";
        Db::query($sql);

        //��������������վ���ܷ���
        $sql = "select * from yyd_account_balance where `nid` = '{$data['nid']}'";
        $result = $this->db_fetch_array($sql);
        if ($result == false) {
            //������վ�Ĳ����
            $sql = "select * from yyd_account_balance order by id desc";
            $result = $this->db_fetch_array($sql);
            if ($result == false) {
                $result['total'] = 0;
                $result['balance'] = 0;
            }
            $total = $result['total'] + $data['income'] + $data['expend'];
            $sql = "insert into yyd_account_balance set total='{$total}',balance={$result['balance']}+" . $data['income'] . "-" . $data['expend'] . ",income='{$data['income']}',expend='{$data['expend']}',type='{$data['type']}',`money`='{$data['money']}',user_id='{$data['user_id']}',nid='{$data['nid']}',remark='{$data['remark']}', `addtime` = '" . time() . "',`addip` = '" . self::ip_address() . "'";
            Db::query($sql);
        }

        //�������������û����ܷ���
        $sql = "select * from yyd_account_users where `nid` = '{$data['nid']}'";
        $result = $this->db_fetch_array($sql);
        if ($result == false) {
            //�����û��Ĳ����
            $sql = "select * from yyd_account_users where user_id='{$data['user_id']}' order by id desc ";
            $result = $this->db_fetch_array($sql);
            if ($result == false) {
                $result['total'] = 0;
                $result['balance'] = 0;
            }
            $total = $result['total'] + $data['income'] + $data['expend'];
            $sql = "insert into yyd_account_users set total='{$total}',balance={$result['balance']}+" . $data['income'] . "-" . $data['expend'] . ",income='{$data['income']}',expend='{$data['expend']}',type='{$data['type']}',`money`='{$data['money']}',user_id='{$data['user_id']}',nid='{$data['nid']}',remark='{$data['remark']}', `addtime` = '" . time() . "',`addip` = '" . self::ip_address() . "',await='{$data['await']}',frost='{$data['frost']}'";
            Db::query($sql);
        }
        return $data['nid'];
    }

    function GetRealnameOne($data = array())
    {

        if (!self::IsExist($data['user_id']))
            return "approve_realname_user_id_empty";

        $_select = " p1.*,p2.username,p3.fileurl as card_pic1_url,p4.fileurl as card_pic2_url";
        $sql = "select {$_select} from yyd_approve_realname as p1 left join yyd_users as p2 on p1.user_id=p2.user_id left join yyd_users_upfiles as p3 on p1.card_pic1 = p3.id left join yyd_users_upfiles as p4 on p1.card_pic2 = p4.id  where p1.user_id={$data['user_id']}";
        $result = $this->db_fetch_array($sql);
        if ($result == false) {
            $sql = " insert into yyd_approve_realname set user_id='{$data['user_id']}',status=0";
            Db::query($sql);
            $result = self::GetRealnameOne($data);
        }
        return $result;
    }

    //��ȡ������Ϣ
    function GetRechargeOne($data = array())
    {
        $_sql = "where 1=1 ";
        $this->logInfomation("GetRechargeOne data is  " . json_encode($data));
        if (isset($data['id']) && self::IsExist($data['id']) != false) {
            $_sql .= " and p1.id = {$data['id']}";
        }

        if (self::IsExist($data['user_id']) != false) {
            $_sql .= " and p1.user_id = {$data['user_id']}";
        }

        if (self::IsExist($data['nid']) != false) {
            $_sql .= " and p1.nid = '{$data['nid']}'";
        }

        $_select = "p1.*";
        $sql = "select $_select from yyd_account_recharge as p1 $_sql";
        //var_dump($sql);

        $result = $this->db_fetch_array($sql);
        $this->logInfomation("GetRechargeOne status is  " . $sql);
        return $result;
    }

    function UpdateRecharge($data = array())
    {
        $sql = "update yyd_account_recharge set ";
        foreach ($data as $key => $value) {
            $_sql[] = "`$key` = '$value'";
        }
        $sql .= join(",", $_sql) . " where id='{$data['id']}'";
        $str = "UpdateRecharge  sql is  ---" . $sql;
        $this->logInfomation($str);
        Db::query($sql);
        return $data['id'];
    }

    //���߳�ֵ�������ݴ���
    function OnlineReturn($data = array())
    {
        $this->logInfomation("OnlineReturn data are " . json_encode($data));
        $trade_no = $data['trade_no'];
        $a = strpos($trade_no, "u");
        $user_id = substr($trade_no, 0, $a);
        $rechage_result = self::GetRechargeOne(array("nid" => $trade_no, "user_id" => $user_id));
        $str = "OnlineReturn  status is  ---" . json_encode($rechage_result);
        $this->logInfomation($str);
        $this->logInfomation("OnlineReturn data are " . json_encode($data));
        $trade_no = $data['trade_no'];
        //$rechage_result['status']=2;

        if ($rechage_result['status'] == 2 && $rechage_result != false) {
            $user_id = $rechage_result['user_id'];
            $log_info["user_id"] = $user_id;//�����û�id
            $log_info["nid"] = "online_recharge_" . $user_id . "_" . time();//������
            $log_info["money"] = $rechage_result['money'];//�������
            $log_info["income"] = $rechage_result['money'];//����
            $log_info["expend"] = 0;//֧��
            $log_info["balance"] = $rechage_result['money'];//�����ֽ��
            $log_info["balance_cash"] = $rechage_result['money'];//�����ֽ��
            $log_info["balance_frost"] = 0;//�������ֽ��
            $log_info["frost"] = 0;//������
            $log_info["await"] = 0;//���ս��
            $log_info["type"] = "online_recharge";//����
            $log_info["to_userid"] = 0;//����˭
            $log_info["remark"] = iconv('GB2312', 'UTF-8', "���߳�ֵ");//��ע

            $str = "OnlineReturn  AddLog data 1 is  ---" . json_encode($log_info);
            $this->logInfomation($str);


            $result = self::AddLog($log_info);
            $log_info["user_id"] = $user_id;//�����û�id
            $log_info["nid"] = "recharge_fee_" . $data['trade_no'] . $user_id;//������
            $log_info["money"] = $rechage_result['fee'];//�������
            $log_info["income"] = 0;//����
            $log_info["expend"] = $rechage_result['fee'];//֧��
            $log_info["balance"] = -$rechage_result['fee'];//�����ֽ��
            $log_info["balance_cash"] = -$rechage_result['fee'];//�����ֽ��
            $log_info["balance_frost"] = 0;//�������ֽ��
            $log_info["frost"] = 0;//������
            $log_info["await"] = 0;//���ս��
            $log_info["type"] = "recharge_fee";//����
            $log_info["to_userid"] = 0;//����˭
            $log_info["remark"] = iconv('GB2312', 'UTF-8', "��ֵ�۳�������{$rechage_result['fee']}Ԫ");//��ע


            $result = self::AddLog($log_info);
            $str = "OnlineReturn  AddLog data 2 is  ---" . json_encode($log_info);
            $this->logInfomation($str);

            /*$credit_log['user_id'] = $user_id;
            $credit_log['nid'] = "online_recharge";
            $credit_log['code'] = "account";
            $credit_log['type'] = "recharge_approve";
            $credit_log['addtime'] = time();
            $credit_log['article_id'] =$result['id'];
            $credit_log['remark'] = "�û����߳�ֵ���õĻ���";
            creditClass::ActionCreditLog($credit_log);
            */

            /*hummer modify 201309211930
            $UsersVip=usersClass::GetUsersVip(array("user_id"=>$user_id));
            if ($UsersVip['status']==1){
                $fee=0.6-$_G['system']['con_account_recharge_vip_fee'];
                $web['money']=$rechage_result['money']/100*$fee;
            }else{
                $fee=0.6-$_G['system']['con_account_recharge_fee'];
                $web['money']=$rechage_result['money']/100*$fee;
            }

            $web['user_id']=$user_id;
            $web['nid']="web_recharge_fee_".$user_id."_".time();
            $web['type']="web_recharge_fee";
            $web['remark']="�û���ֵ{$rechage_result['balance']}����վ�渶{$web['money']}";
            self::AddAccountWeb($web);
            */


            $rec['id'] = $rechage_result['id'];
            $rec['return'] = iconv('GB2312', 'UTF-8', "��ֵ�ɹ�");
            $rec['status'] = 1;
            $rec['verify_userid'] = 0;
            $rec['verify_time'] = time();
            $rec['verify_remark'] = iconv('GB2312', 'UTF-8', "�ɹ���ֵ");
            self::UpdateRecharge($rec);
            $str = "OnlineReturn  UpdateRecharge is  ---" . json_encode($rec);
            $this->logInfomation($str);
        }

        return true;
    }


    function insertFlowidToRecharge($data = array()){
        $this->logInfomation("OnlineReturn data are " . json_encode($data));
        $trade_no = $data['trade_no'];
        $a = strpos($trade_no, "u");
        $user_id = substr($trade_no, 0, $a);
        $sql="update yyd_account_recharge SET jytflowid='{$data['jytflowid']}'  WHERE nid='{$trade_no}' AND  user_id='{$user_id}' ";
        return Db::query($sql);
    }

    public function invite_hongbao($data)
    {
        if (!empty($data['invite_userid'])) {
            $sql = "insert into yyd_users_friends set `user_id`={$data["user_id"]},`friends_userid`={$data['invite_userid']},`addtime` = '" . time() . "',`addip` = '" . $this->ip_address() . "',status=1";
            Db::query($sql);
            $_sql = "insert into yyd_users_friends set `user_id`={$data['invite_userid']},`friends_userid`={$data["user_id"]},`addtime` = '" . time() . "',`addip` = '" . $this->ip_address() . "',status=1";
            Db::query($_sql);
            $_sql = "insert into yyd_users_friends_invite set `user_id`={$data['invite_userid']},`friends_userid`={$data["user_id"]},`addtime` = '" . time() . "',`addip` = '" . $this->ip_address() . "',status=1,type=1";
            Db::query($_sql);

            $_sql = " where p1.`user_id` = '{$data['invite_userid']}'";
            $sql = "select SELECT from yyd_users_friends as p1 left join yyd_users as p2 on p1.user_id=p2.user_id SQL ORDER LIMIT";
            //�ж��ܵ�����
            $row = $this->db_fetch_array(str_replace(array('SELECT', 'SQL', 'ORDER', 'LIMIT'), array('count(1) as num', $_sql, '', ''), $sql));
            $total = intval($row['num']);

            if ($total < 6) {
                $credit_log['user_id'] = $data['invite_userid'];
                $credit_log['nid'] = "hongbao";
                $credit_log['code'] = "payment";
                $credit_log['type'] = "hongbao";
                $credit_log['addtime'] = time();
                $credit_log['article_id'] = $data['invite_userid'];
                $credit_log['hongbao'] = 50;
                $credit_log['remark'] = iconv('GB2312', 'utf-8', "������50���");
                self::UpdateHongbao($credit_log);
            }
        }
    }

    function UpdateHongbao($data = array())
    {
        //�ж�hongbao�Ƿ�Ϊ��
        if (self::IsExist($data['hongbao']) == "") {
            return FALSE;
        }
        if (round($data['hongbao']) == 0) {
            return FALSE;
        }
        $sql = "select * from yyd_credit_log where type='hongbao' and nid='hongbao' and user_id='{$data['user_id']}'";
        $result = $this->db_fetch_array($sql);
        /*
        if ($result==false){
            $sql = "insert into `{credit_log}` set code='{$data['code']}',user_id='{$data['user_id']}',`value`='{$_value}',`credit`='{$_value}',type='{$data['type']}',article_id='{$data['article_id']}',nid='{$nid}',addtime='{$data['addtime']}',remark='{$data['remark']}',update_time='".time()."'";
            $mysql->db_query($sql);
        }else{
            $sql = "update `{credit_log}` set addtime='{$data['addtime']}',user_id='{$data['user_id']}',`value`='{$_value}',update_time='".time()."' where code='{$data['code']}'  and type='{$data['type']}' and article_id='{$data['article_id']}' and nid='{$nid}'";

            $mysql->db_query($sql);
        }
        */
        $sql = "insert into yyd_credit_log set code='{$data['code']}',user_id='{$data['user_id']}',`value`='{$data['hongbao']}',`credit`='{$data['hongbao']}',type='{$data['type']}',article_id='{$data['article_id']}',nid='{$data['nid']}',addtime='{$data['addtime']}',remark='{$data['remark']}'";

        //$sql = "update `{credit_log}` set `credit`=`credit`+{$data['hongbao']},`value`=`value`+{$data['hongbao']} where type='hongbao' and nid='hongbao' and user_id='{$data['user_id']}'";
        if (Db::query($sql)) {
            $sql = "select sum(p1.credit) as num,p2.class_id from yyd_credit_log as p1 left join yyd_credit_type as p2 on p1.nid=p2.nid  where p1.user_id='{$data['user_id']}' group by p2.class_id order by p2.class_id desc";
            $result = $this->db_fetch_arrays($sql);
            $credits = serialize($result);
            if ($result != false) {
                $sql = "update yyd_credit set `credits`='{$credits}' where user_id='{$data['user_id']}'";
                Db::query($sql);
            }
            return TRUE;
        }
        return FALSE;
    }

    function GetUsers($data = array())
    {

        $_sql = " where 1=1 ";
        //�ж��û�id
        if (isset($data['user_id'])) {
            $_sql .= " and p1.`user_id`  = '{$data['user_id']}'";
        } //�ж��Ƿ������û���
        elseif (isset($data['username'])) {
            $_sql .= " and p1.`username` like '%{$data['username']}%'";
        } //�ж��Ƿ���������
        elseif (isset($data['email'])) {
            $_sql .= " and p1.`email` like '%{$data['email']}%'";
        }

        $_select = "*";
        $sql = "select SELECT  from yyd_users as p1 SQL";
        return $this->db_fetch_array(str_replace(array('SELECT', 'SQL'), array($_select, $_sql), $sql));
        return $result;
    }

    public function UsersVipApply($data = array())
    {

        if (self::IsExist($data['user_id']) == "") return false;
        $result = self::GetUsersVip($data);
        if ($result["status"] == 1) {
            return "vip_status_yes";
        } else {
            if (isset($data['vip_time']) && $data['vip_time'] > 0) {
                $vip_time = $data['vip_time'] * 30;
                $years = $data['vip_time'];
            } else {
                $vip_time = 365;
                $years = 12;
            }
            $sql = "update yyd_users_vip set years={$years},`addtime` = '" . time() . "',`addip` = '" . self::ip_address() . "',kefu_userid='{$data['kefu_userid']}',remark='{$data['remark']}',money='{$data['money']}',vip_type='{$data['vip_type']}',first_date='" . time() . "',end_date='" . (time() + 60 * 60 * 24 * $vip_time) . "',status=1 where user_id='{$data['user_id']}'";
            return Db::query($sql);
        }
    }

    function GetUsersTypeCheck()
    {
        $sql = "select p1.* from yyd_users_type as p1 where p1.checked=1";
        $result = $this->db_fetch_array($sql);
        if ($result == false)
            return "users_type_empty";
        return $result;
    }



    public function deleteBankCard($user_id){
        if (!self::IsExist($user_id)) {
            return "account_user_id_empty";
        }
        $sql = "delete  from yyd_user_bank where user_id = " . $user_id;
        return  Db::query($sql);
    }

    public function getBankCard($user_id)
    {
        if (!self::IsExist($user_id)) {
            return "account_user_id_empty";
        }
        $sql = "select *  from yyd_user_bankcard where user_id = " . $user_id;

        $result = $this->db_fetch_array($sql);
        $result["status"] = 1;
        if ($result == "") {
            $result["status"] = -1;
        }
        return $result;
    }

    public function updateBankInfo($data)
    {
        if (!self::IsExist($data["user_id"])) {
            return "account_user_id_empty";
        }
        $time = date('Y-m-d H:i:s', time());
        if ($data["type"] == 0) {
            $sql = "update yyd_user_bank set status='{$data['status']}',updatetime='" . $time . "' where user_id ='{$data['user_id']}'";
        } else if ($data["type"] == 1) {
            $sql = "update yyd_user_bank set status='{$data['status']}',card_bind_mobile_phone_no='{$data['card_bind_mobile_phone_no']}',bank_card_no='{$data['bank_card_no']}',bank_name='{$data['bank_name']}',bank_code='{$data['bank_code']}',cert_type='{$data['cert_type']}',cert_no='{$data['cert_no']}',real_name='{$data['real_name']}',payment_id= '{$data['payment_id']}',updatetime='" . $time . "' where user_id ='{$data['user_id']}'";
        } else if ($data["type"] == 2) {
            $sql = "insert into yyd_user_bank set status='{$data['status']}',card_bind_mobile_phone_no='{$data['card_bind_mobile_phone_no']}',bank_card_no='{$data['bank_card_no']}',bank_name='{$data['bank_name']}',bank_code='{$data['bank_code']}',cert_type='{$data['cert_type']}',cert_no='{$data['cert_no']}',real_name='{$data['real_name']}',payment_id= '{$data['payment_id']}',updatetime='" . $time . "' , user_id ='{$data['user_id']}'";
        }

        Db::query($sql);
        $this->logInfomation("updateBankInfo ---" . $sql);
    }

    public function getPhoneNumber($user_id)
    {
        $sql = "select  phone from yyd_approve_sms where user_id ='$user_id'";
        $result = $this->db_fetch_array($sql);
        return $result["phone"];
    }

    public function insertRecordPayment($params)
    {
        $sql = "select  value from yyd_system where nid ='con_account_recharge_vip_fee'";
        $result = $this->db_fetch_array($sql);
        $G['con_account_recharge_vip_fee'] = $result['value'];
        $sql = "select  `value` from yyd_system where nid ='con_account_recharge_fee'";
//      $result =Db::query($sql);
        $result = $this->db_fetch_array($sql);
        $G['con_account_recharge_fee'] = $result['value'];
        $sql = "select  `value` from yyd_system where nid ='con_account_recharge_jiangli'";
        $result = $this->db_fetch_array($sql);
        $G['con_account_recharge_jiangli'] = $result['value'];
        $msg = '';
        if (isset($params['amount'])) {
            if ($msg == "") {
                $data["money"] = $params['amount'];
                $data["type"] = $params['type'];
                $data['user_id'] = $params['user_id'];
                $data['status'] = 0;

                if (!is_numeric($data['money'])) {
                    // $msg = array("�����д����","",$_U['query_url']."/".$_U['query_type']);
                    $msg = array("�����д����");
                    $str = "insertRecordPayment ---" . "error for amout" . $data["money"];
                    $this->logInfomation($str);
                }
                if ($msg == "") {
                    $url = "";
                    if ($data['type'] == 1) {
                        $data['payment'] = $params['payment'];
                        $data['remark'] = $params['remark'] . "���߳�ֵ";

                        $result = self::GetUsersVip(array("user_id" => $data['user_id']));
                        $str = "insertRecordPayment status is ---" . json_encode($result);
                        $this->logInfomation($str);

                        if ($result['status'] == 1) {
                            $data['fee'] = $G['con_account_recharge_vip_fee'] / 100 * $data['money'];
                        } else {
                            $data['fee'] = $G['con_account_recharge_fee'] / 100 * $data['money'];
                        }
//                        var_dump($data['fee']);
//                        die;

                        $data['balance'] = $data['money'] - $data['fee'];
                        /*$money1 = isset($_G['system']['con_recharge_max_account'])?$_G['system']['con_recharge_max_account']:5000;
                        if ($data['money'] >= $money1 ){
                            $fee1 = isset($_G['system']['con_recharge_max_fee'])?$_G['system']['con_recharge_max_fee']:50;
                            $data['fee'] = $fee1;
                        }else{
                            $fee2 = isset($_G['system']['con_recharge_fee'])?$_G['system']['con_recharge_fee']:0.01;
                            $data['fee'] = $data['money']*$fee2;
                        }*/
                    }

                    $data['nid'] = $params["out_trade_no"];
                    //$data['nid'] = $_G['user_id'];

                    if ($data['type'] == 2) {
                        $data['status'] = 0;
                    } else {
                        $data['status'] = 2;
                    }

                    $str = "insertRecordPayment AddRecharge data is ---" . json_encode($data);
                    $this->logInfomation($str);

                    $result = self::AddRecharge($data);

                    $str = "insertRecordPayment AddRecharge is ---" . json_encode($result);
                    $this->logInfomation($str);

                    $data['trade_no'] = $data['nid'];

                    if ($data['type'] == 1) {
                        $data['subject'] = "�˺ų�ֵ";
                        $data['body'] = "�˺ų�ֵ";

                    }
                    if ($result != true) {
                        // $msg = array($result,"",$_U['query_url']."/".$_U['query_type']);
                        $msg = array($result);
                    } else {
//                        if ($url!=""){
//                            header("Location: {$url}");
//                            exit;
                        //$msg = array("��վ����ת��֧����վ<br>���û��Ӧ�����������֧����վ�ӿ�","֧����վ",$url);
                        //}else{
                        //  $msg = array("���Ѿ��ɹ��ύ�˳�ֵ����ȴ�����Ա����ˡ�");
                        //}
                    }
                } else {
                    $msg = array("�����д����");
                }
            }
        }
    }

    function AddRecharge($data = array())
    {
        if (!self::IsExist($data['user_id'])) {
            return "account_user_id_empty";
        }

        $sql = "insert into yyd_account_recharge set `addtime` = '" . time() . "',`addip` = '" . self::ip_address() . "'";
        foreach ($data as $key => $value) {
            $sql .= ",`$key` = '$value'";
        }
        $str = "AddRecharge sql is ---" . $sql;
        $this->logInfomation($str);
        $result = Db::query($sql);
        return $result;
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
                sprintf("[%s]  %s\n", date("Y-m-d H:i:s"), $msg), 3, yyd_LOG);
        }else {

            Log::info('[Ǯ������־:]'.date("Y-m-d H:i:s").$msg);
        }
    }


    /**
     * ��ȡ�û���������
     */
    public function getPayPass($id)
    {
        $sql = "select paypassword from  yyd_users where user_id='{$id}'";
        return $this->db_fetch_array($sql);
    }

    /**
     * �����շ�100Ԫ��5��(%) �ķ���
     * @param $nid
     * @return string
     */
    public function feilv($nid)
    {
        $sql = "select  value from yyd_system where nid ='{$nid}'";
        $result = $this->db_fetch_array($sql);
        return $result;
    }

    function IsExiest($val)
    {
        if (isset($val) && ($val != "" || $val == 0)) {
            return $val;
        } else {
            return false;
        }
    }


    /**
     * ���ּ�¼
     * @param array $data
     * @return int
     */
    function AddCash($data = array())
    {
        $sql = "select balance,balance_cash from yyd_account where user_id='{$data['user_id']}'";
        $result = $this->db_fetch_array($sql);
//        if (self::IsExiest($_G['system']['con_account_balance_cash_status']) == 1){
//            if ($result['balance_cash']<$data['total']){
//                return "account_cash_max_errot";
//            }
//        }else{
//            if ($result['balance']<$data['total']){
//                return "account_cash_max_errot";
//            }
//        }
        $sql = "insert into yyd_account_cash set `addtime` = '" . time() . "',`addip` = '" . self::ip_address() . "'";
        foreach ($data as $key => $value) {
            $sql .= ",`$key` = '$value'";
        }
        Db::query($sql);
        //�����ϴ�insert �� id
        $id = Db::insert_id();
        $log_info["user_id"] = $data['user_id'];//�����û�id
        $log_info["nid"] = $data['nid'];//������
        $log_info["money"] = $data['total'];//�������
        $log_info["income"] = 0;//����
        $log_info["expend"] = 0;//֧��
        $log_info["balance_cash"] = -$data['total'];//�����ֽ��
        $log_info["balance_frost"] = 0;//�������ֽ��
        $log_info["frost"] = $data['total'];//������
        $log_info["await"] = 0;//���ս��
        $log_info["type"] = "cash";//����
        $log_info["to_userid"] = 0;//����˭
        $log_info["remark"] = "��������,�������ֽ��{$data['account']}Ԫ��������{$data['fee']}Ԫ";//��ע
        $result = self::AddLog($log_info);
        //�����û�������¼
        $user_log["user_id"] = $data['user_id'];
        $user_log["code"] = "account";
        $user_log["type"] = "cash";
        $user_log["operating"] = "require";
        $user_log["article_id"] = $id;
        $user_log["result"] = 1;
        $user_log["content"] = $log_info["remark"];
        self::AddUsersLog($user_log);
        return $id;
    }

//public  function  updatasms(){
//    $sql = "update yyd_approve_smslog set code_status=1,code_time= time() where id='\" . $code['id'] . \"' \"";
//    Db::query($sql);
//}

}






