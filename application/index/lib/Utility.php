<?php
namespace app\index\lib;
/**
 * Created by PhpStorm.
 * User: linwei
 * Date: 2015/9/6
 * Time: 22:09
 */

class Utility {


    public function getRaiseStatus($value, $endday){
        if ($value == 0){
            if($endday>=0){
                $status = "筹资中";
            }else {
                $status = "已到期";
            }
        }
        else if ($value == 1)
            $status = "筹资成功";
        else if ($value == 2)
            $status = "筹资失败";
        else
            $status = "草稿";
        return $status;
    }
    public function getBorrowPeriod($borrow_period){
        if($borrow_period== 0.03){
            $borrow_period="1天";
        }elseif($borrow_period== 0.06){
            $borrow_period="2天";
        }elseif($borrow_period== 0.10){
            $borrow_period="3天";
        }elseif($borrow_period== 0.13){
            $borrow_period="4天";
        }elseif($borrow_period== 0.16){
            $borrow_period="5天";
        }elseif($borrow_period== 0.20){
            $borrow_period="6天";
        }elseif($borrow_period== 0.23){
            $borrow_period="7天";
        }elseif($borrow_period== 0.26){
            $borrow_period="8天";
        }elseif($borrow_period== 0.30){
            $borrow_period="9天";
        }elseif($borrow_period== 0.33){
            $borrow_period="10天";
        }elseif($borrow_period== 0.36){
            $borrow_period="11天";
        }elseif($borrow_period== 0.40){
            $borrow_period="12天";
        }elseif($borrow_period== 0.43){
            $borrow_period="13天";
        }elseif($borrow_period== 0.46){
            $borrow_period="14天";
        }elseif($borrow_period== 0.50){
            $borrow_period="15天";
        }elseif($borrow_period== 0.53){
            $borrow_period="16天";
        }elseif($borrow_period== 0.56){
            $borrow_period="17天";
        }elseif($borrow_period== 0.60){
            $borrow_period="18天";
        }elseif($borrow_period== 0.63){
            $borrow_period="19天";
        }elseif($borrow_period== 0.66){
            $borrow_period="20天";
        }elseif($borrow_period== 0.70){
            $borrow_period="21天";
        }elseif($borrow_period== 0.73){
            $borrow_period="22天";
        }elseif($borrow_period== 0.76){
            $borrow_period="23天";
        }elseif($borrow_period== 0.80){
            $borrow_period="24天";
        }elseif($borrow_period== 0.83){
            $borrow_period="25天";
        }elseif($borrow_period== 0.86){
            $borrow_period="25天";
        }elseif($borrow_period== 0.90){
            $borrow_period="26天";
        }elseif($borrow_period== 0.93){
            $borrow_period="27天";
        }elseif($borrow_period== 0.96){
            $borrow_period="28天";
        }elseif($borrow_period== 0.83){
            $borrow_period="29天";
        }else{
            $borrow_period=number_format($borrow_period,0)."个月";
        }
        return $borrow_period;

    }
    public function getReturntype($value){
        if ($value==0)
            $type ="按月还款";
        else if ($value==1)
            $type ="按季还款";
        else if ($value==2)
            $type ="到期还本还息";
        else if ($value==3)
            $type ="按月还息到期还本";
        return $type;
    }
    function getEducation($value)
    {
        if ($value == 1)
            $type = "小学";
        else if ($value == 2)
            $type = "初中";
        else if ($value == 3)
            $type = "高中";
        else if ($value == 4)
            $type = "大专";
        else if ($value == 5)
            $type = "本科";
        else if ($value == 6)
            $type = "硕士";
        else if ($value == 7)
            $type = "博士";
        else if ($value == 8)
            $type = "博士后";
        else if ($value == 0)
            $type = "未填";
        else
            $type = "其他";

        return $type;
    }
    function getMarried($value)
    {
        if ($value ==1)
            $type = "未婚";
        else if ($value ==2)
            $type = "已婚";
        else if ($value ==3)
            $type = "离婚";
        else if ($value ==4)
            $type = "丧偶";
        else
            $type = "未填";
        return $type;
    }

    function getHometype($value)
    {
        if ($value == 1)
            $type = "有商品房（无贷款）";
        else if ($value == 2)
            $type = "有商品房（有贷款）";
        else if ($value == 3)
            $type = "有其他（非商品）房";
        else if ($value == 4)
            $type = "与父母同住";
        else if ($value == 5)
            $type = "租房";
        else
            $type = "未填";
        return $type;

    }


    function get_times($data=array()){
        if (isset($data['time']) &&$data['time']!=""){
            $time = $data['time'];
        }elseif (isset($data['date']) &&$data['date']!=""){
            $time = strtotime($data['date']);
        }else{
            $time = time();
        }
        if (isset($data['type']) &&$data['type']!=""){
            $type = $data['type'];
        }else{
            $type = "month";
        }
        if (isset($data['num']) &&$data['num']!=""){
            $num = $data['num'];
        }else{
            $num = 1;
        }
        if ($type=="month"){
            $month = date("m",$time);
            $year = date("Y",$time);
            $_result = strtotime("$num month",$time);
            $_month = (int)date("m",$_result);
            if ($month+$num>12){
                $_num = $month+$num-12;
                $year = $year+1;
            }else{
                $_num = $month+$num;
            }
            if ($_num!=$_month){
                $_result = strtotime("-1 day",strtotime("{$year}-{$_month}-01"));
            }
        }else{
            $_result = strtotime("$num $type",$time);
        }
        if (isset($data['format']) &&$data['format']!=""){
            return date($data['format'],$_result);
        }else{
            return $_result;
        }
    }

    public function getBorrowStatus($star_status,$status, $result){
        self::logInfomation("------getBorrowStatus1-------".$star_status);
        self::logInfomation("------getBorrowStatus2-------".$status);
        self::logInfomation("------getBorrowStatus3-------".json_encode($result));
        if($star_status ==1){
            if($result["borrow_star_time"]>time()){
                $borrow_valid_end_time =$result["borrow_end_time"]=$result["borrow_valid_time"]*60*60*24+$result["borrow_star_time"];
            }else{
                $borrow_valid_end_time =$result["borrow_end_time"]=$result["borrow_valid_time"]*60*60*24+$result["verify_time"];
            }

        }else if ($star_status == 0){
            if($status==0){
                $value = "审核中";
            }else  if($status==1){
                $borrow_valid_end_time =$borrow_end_time=$result["borrow_valid_time"]*60*60*24+$result["verify_time"];
                if($result["borrow_account_wait"]==0){
                    $value="已经满标";
                }else if ($result["borrow_end_time"]<time()){
                    $value="已经过期";
                }else{
                    $diff = self::timediff(time(),$borrow_end_time);
                    self::logInfomation("------getBorrowStatus4-------".json_encode($diff));
                    $value = $diff["day"].'天'.$diff["hour"].'小时'.$diff["min"].'分'.$diff["sec"].'秒';
                    if($result["borrow_account_scale"]==100){
                        $value="满标审核中";
                    }
                }
            }else  if($status==2){
                $value = "审核失败";
            }else  if($status==3){
                if($result["repay_account_wait"]==0){
                    $value = "已还完";
                }else{
                    $value = "还款中";
                }
            }else  if($status==4){
                $value = "满标审核失败";
            }else  if($status==5){
                $value = "撤回/流标/撤销";
            }else{
                $value = "未知状态";
            }
        }

        return $value;
    }

    public function logInfomation($msg)
    {
        $str = date("Y-m-d h:i:sa") . '----' . $msg;
        log_message('debug', $str);
    }
    function timediff($begin_time,$end_time)
    {
        if($begin_time < $end_time){
            $starttime = $begin_time;
            $endtime = $end_time;
        }
        else{
            $starttime = $end_time;
            $endtime = $begin_time;
        }
        $timediff = $endtime-$starttime;
        $days = intval($timediff/86400);
        $remain = $timediff%86400;
        $hours = intval($remain/3600);
        $remain = $remain%3600;
        $mins = intval($remain/60);
        $secs = $remain%60;
        $res = array("day" => $days,"hour" => $hours,"min" => $mins,"sec" => $secs);
        return $res;
    }
    private function IsExist($val)
    {
        if (isset($val)) {
            return $val;
        } else {
            return false;
        }
    }
    function EqualInterest ($data = array()){
        //借款方式,account,period,apr,time,style,type
        //return account_all,account_interest，account_capital,repay_time
        if (self::IsExist($data["account"])=="") return "equal_account_empty";
        if (self::IsExist($data["period"])=="") return "equal_period_empty";
        if (self::IsExist($data["apr"])=="") return "equal_apr_empty";
        if (isset($data['time']) && $data['time']>0){
            $data['time'] = $data['time'];
        }else{
            $data['time'] = time();
        }
        $borrow_style =$data['style'];
        if ($borrow_style==0){
            return self::EqualMonth($data);
        }elseif ($borrow_style==1){
            return self::EqualSeason($data);
        }elseif ($borrow_style==2){
            return self::EqualDayEnd($data);
        }elseif ($borrow_style==3){
            return self::EqualEndMonth($data);
        }elseif ($borrow_style==4){
            return self::EqualDeng($data);
        }
        //体验标
        elseif ($borrow_style==5){
            return self::EqualTiyan($data);
        }

    }

//等额本息法
//贷款本金×月利率×（1+月利率）还款月数/[（1+月利率）还款月数-1]
//a*[i*(1+i)^n]/[(1+I)^n-1]
//（a×i－b）×（1＋i）
    function EqualMonth ($data = array()){

        $account = $data['account'];
        $year_apr = $data['apr'];
        $period = $data['period'];
        $time = $data['time'];

        $month_apr = $year_apr/(12*100);
        $_li = pow((1+$month_apr),$period);
        if ($account<0) return;
        $repay_account = round($account * ($month_apr * $_li)/($_li-1),2);//515.1

        $_result = array();
        //$re_month = date("n",$borrow_time);
        $_capital_all = 0;
        $_interest_all = 0;
        $_account_all = 0.00;
        for($i=0;$i<$period;$i++){
            if ($i==0){
                $interest = round($account*$month_apr,2);
            }else{
                $_lu = pow((1+$month_apr),$i);
                $interest = round(($account*$month_apr - $repay_account)*$_lu + $repay_account,2);
            }

            //echo $repay_account."<br>";
            //防止一分钱的问题
            if ($i==$period-1)
            {
                $capital = $account - $_capital_all;
                $interest = $repay_account-$capital;
            }else{
                $capital =  $repay_account - $interest;
            }

            //echo $capital."<br>";
            $_account_all +=  $repay_account;
            $_interest_all +=  $interest;
            $_capital_all +=  $capital;

            $_result[$i]['account_all'] =  round($repay_account,2);
            $_result[$i]['account_interest'] = round( $interest,2);
            $_result[$i]['account_capital'] =  round($capital,2);
            $_result[$i]['account_other'] =  round($repay_account*$period-$repay_account*($i+1),2);
            $_result[$i]['repay_month'] =  round($repay_account,2);
            $_result[$i]['repay_time'] = get_times(array("time"=>$time,"num"=>$i+1));
        }
        if ($data["type"]=="all"){
            $_result_all['account_total'] =  round($_account_all,2);
            $_result_all['interest_total'] =  round($_interest_all,2);
            $_result_all['capital_total'] =  round($_capital_all,2);
            $_result_all['repay_month'] =  round($repay_account,2);
            $_result_all['month_apr'] = round($month_apr*100,2);
            return $_result_all;
        }
        return $_result;
    }

//按季等额本息法
    function EqualSeason ($data = array()){

        //借款的月数
        if (isset($data['period']) && $data['period']>0){
            $period = $data['period'];
        }
        $time = $data['time'];
        //按季还款必须是季的倍数
        if ($period%3!=0){
            return false;
        }

        //借款的总金额
        if (isset($data['account']) && $data['account']>0){
            $account = $data['account'];
        }else{
            return "";
        }

        //借款的年利率
        if (isset($data['apr']) && $data['apr']>0){
            $year_apr = $data['apr'];
        }else{
            return "";
        }


        //借款的时间
        if (isset($data['borrow_time']) && $data['borrow_time']>0){
            $borrow_time = $data['borrow_time'];
        }else{
            $borrow_time = time();
        }

        //月利率
        $month_apr = $year_apr/(12*100);

        //得到总季数
        $_season = $period/3;

        //每季应还的本金
        $_season_money = round($account/$_season,2);

        //$re_month = date("n",$borrow_time);
        $_yes_account = 0 ;
        $repay_account = 0;//总还款额
        $_capital_all = 0;
        $_interest_all = 0;
        $_account_all = 0.00;
        for($i=0;$i<$period;$i++){
            $repay = $account - $_yes_account;//应还的金额

            $interest = round($repay*$month_apr,2);//利息等于应还金额乘月利率
            $repay_account = $repay_account+$interest;//总还款额+利息
            $capital = 0;
            if ($i%3==2){
                $capital = $_season_money;//本金只在第三个月还，本金等于借款金额除季度
                $_yes_account = $_yes_account+$capital;
                $repay = $account - $_yes_account;
                $repay_account = $repay_account+$capital;//总还款额+本金
            }
            $_repay_account = $interest+$capital;
            $_result[$i]['account_interest'] = round($interest,2);
            $_result[$i]['account_capital'] = round($capital,2);
            $_result[$i]['account_all'] =round($_repay_account,2);

            $_account_all +=  $_repay_account;
            $_interest_all +=  $interest;
            $_capital_all +=  $capital;

            $_result[$i]['account_other'] = round($repay,2);
            $_result[$i]['repay_month'] = round($repay_account,2);
            $_result[$i]['repay_time'] = get_times(array("time"=>$time,"num"=>$i+1));
        }
        if ($data["type"]=="all"){
            $_result_all['account_total'] =  round($_account_all,2);
            $_result_all['interest_total'] =  round($_interest_all,2);
            $_result_all['capital_total'] =  round($_capital_all,2);
            $_result_all['repay_month'] = "-";
            $_result_all['repay_season'] = $_season_money;
            $_result_all['month_apr'] = round($month_apr*100,2);
            return $_result_all;
        }
        return $_result;
    }

//按天到期还款
    function EqualDayEnd ($data = array()){
        //借款的月数
        if (isset($data['period']) && $data['period']>0){
            $period = $data['period'];
            if ($period == 0.03){
                $period = 1;
            }
            else if($period == 0.06){
                $period = 2;
            }
            else if($period == 0.10){
                $period = 3;
            }
            else if($period == 0.13){
                $period = 4;
            }
            else if($period == 0.16){
                $period = 5;
            }
            else if($period == 0.20){
                $period = 6;
            }
            else if($period == 0.23){
                $period = 7;
            }
            else if($period == 0.26){
                $period = 8;
            }
            else if($period == 0.30){
                $period = 9;
            }
            else if($period == 0.33){
                $period = 10;
            }
            else if($period == 0.36){
                $period = 11;
            }
            else if($period == 0.40){
                $period = 12;
            }
            else if($period == 0.43){
                $period = 13;
            }
            else if($period == 0.46){
                $period = 14;
            }
            else if($period == 0.50){
                $period = 15;
            }
            else if($period == 0.53){
                $period = 16;
            }
            else if($period == 0.56){
                $period = 17;
            }
            else if($period == 0.60){
                $period = 18;
            }
            else if($period == 0.63){
                $period = 19;
            }
            else if($period == 0.66){
                $period = 20;
            }
            else if($period == 0.70){
                $period = 21;
            }
            else if($period == 0.73){
                $period = 22;
            }
            else if($period == 0.76){
                $period = 23;
            }
            else if($period == 0.80){
                $period = 24;
            }
            else if($period == 0.83){
                $period = 25;
            }
            else if($period == 0.86){
                $period = 26;
            }
            else if($period == 0.90){
                $period = 27;
            }
            else if($period == 0.93){
                $period = 28;
            }
            else if($period == 0.96){
                $period = 29;
            }else{
                $period=$period*30;
            }
        }

        //借款的总金额
        if (isset($data['account']) && $data['account']>0){
            $account = $data['account'];
        }else{
            return "";
        }

        //借款的年利率
        if (isset($data['apr']) && $data['apr']>0){
            $year_apr = $data['apr'];
        }else{
            return "";
        }


        //借款的时间
        if (isset($data['time']) && $data['time']>0){
            $borrow_time = $data['time'];
        }else{
            $borrow_time = time();
        }

        //月利率
        $month_apr = $year_apr/(12*100);
        $day_apr = $month_apr/30;

        $interest = $day_apr*$period*$account;
        if (isset($data['type']) && $data['type']=="all"){
            $_result_all['account_total'] =   round($account + $interest,2);
            $_result_all['interest_total'] =  round($interest,2);
            $_result_all['capital_total'] =  round($account,2);
            $_result_all['repay_month'] =  round($account + $interest,2);
            $_result_all['month_apr'] = round($month_apr*100,2);
            return $_result_all;
        }else{
            $_result[0]['account_all'] = round($interest+$account,2);
            $_result[0]['account_interest'] = round($interest,2);
            $_result[0]['account_capital'] = round($account,2);
            $_result[0]['account_other'] = round($account,2);
            $_result[0]['repay_month'] = round($interest+$account,2);
            $_result[0]['repay_time'] = strtotime("+".$period." day");

            return $_result;
        }
    }

//到期付款
    function EqualEnd ($data = array()){

        //借款的月数
        if (isset($data['period']) && $data['period']>0){
            $period = $data['period'];
        }


        //借款的总金额
        if (isset($data['account']) && $data['account']>0){
            $account = $data['account'];
        }else{
            return "";
        }

        //借款的年利率
        if (isset($data['apr']) && $data['apr']>0){
            $year_apr = $data['apr'];
        }else{
            return "";
        }


        //借款的时间
        if (isset($data['time']) && $data['time']>0){
            $borrow_time = $data['time'];
        }else{
            $borrow_time = time();
        }

        //月利率
        $month_apr = $year_apr/(12*100);

        $interest = $month_apr*$period*$account;

        if (isset($data['type']) && $data['type']=="all"){
            $_result_all['account_total'] =   round($account + $interest,2);
            $_result_all['interest_total'] =  round($interest,2);
            $_result_all['capital_total'] =  round($account,2);
            $_result_all['repay_month'] =  round($account + $interest,2);
            $_result_all['month_apr'] = round($month_apr*100,2);
            return $_result_all;
        }else{
            $_result[0]['account_all'] = round($interest+$account,2);
            $_result[0]['account_interest'] = round($interest,2);
            $_result[0]['account_capital'] = round($account,2);
            $_result[0]['account_other'] = round($account,2);
            $_result[0]['repay_month'] = round($interest+$account,2);
            $_result[0]['repay_time'] = get_times(array("time"=>$borrow_time,"num"=>$period));

            return $_result;
        }
    }


//到期还本，按月付息
    function EqualEndMonth ($data = array()){

        //借款的月数
        if (isset($data['period']) && $data['period']>0){
            $period = $data['period'];
        }

        //借款的总金额
        if (isset($data['account']) && $data['account']>0){
            $account = $data['account'];
        }else{
            return "";
        }

        //借款的年利率
        if (isset($data['apr']) && $data['apr']>0){
            $year_apr = $data['apr'];
        }else{
            return "";
        }


        //借款的时间
        if (isset($data['time']) && $data['time']>0){
            $borrow_time = $data['time'];
        }else{
            $borrow_time = time();
        }

        //月利率
        $month_apr = $year_apr/(12*100);
        //$re_month = date("n",$borrow_time);
        $_yes_account = 0 ;
        $repayment_account = 0;//总还款额

        $interest = round($account*$month_apr,2);//利息等于应还金额乘月利率
        for($i=0;$i<$period;$i++){
            $capital = 0;
            if ($i+1 == $period){
                $capital = $account;//本金只在第三个月还，本金等于借款金额除季度
            }
            $_account_all +=  $_repay_account;
            $_interest_all +=  $interest;
            $_capital_all +=  $capital;

            $_result[$i]['account_all'] = $interest+$capital;
            $_result[$i]['account_interest'] = $interest;
            $_result[$i]['account_capital'] = $capital;
            $_result[$i]['account_other'] = round($account+$interest*$period-$interest*$i-$interest,2);
            $_result[$i]['repay_year'] = $account;
            $_result[$i]['repay_time'] = get_times(array("time"=>$borrow_time,"num"=>$i+1));
        }
        if ($data["type"]=="all"){
            $_result_all['account_total'] =  $account + $interest*$period;
            $_result_all['interest_total'] = $_interest_all;
            $_result_all['capital_total'] = $account;
            $_result_all['repay_month'] = $interest;
            $_result_all['month_apr'] = round($month_apr*100,2);
            return $_result_all;
        }
        return $_result;
    }


//等本等息法
    function EqualDeng ($data = array()){

        $account = $data['account'];
        $year_apr = $data['apr'];
        $period = $data['period'];
        $time = $data['time'];

        $month_apr = $year_apr/(12*100);
        $_li = pow((1+$month_apr),$period);
        if ($account<0) return;
        $repay_account = round($account * ($month_apr * $_li)/($_li-1),2);//515.1
        $_result = array();
        //$re_month = date("n",$borrow_time);
        $_capital_all = 0;
        $_interest_all = 0;
        $_account_all = 0.00;
        for($i=0;$i<$period;$i++){
            $interest = round($account*$month_apr,2);
            $capital = round($account/$period,2);
            //echo $capital."<br>";
            $repay_account = $interest+$capital;
            $_account_all +=  $repay_account;
            $_interest_all +=  $interest;
            $_capital_all +=  $capital;

            $_result[$i]['account_all'] =  $repay_account;
            $_result[$i]['account_interest'] = $interest;
            $_result[$i]['account_capital'] =  $capital;
            $_result[$i]['account_other'] =  round($repay_account*$period-$repay_account*($i+1),2);
            $_result[$i]['repay_month'] =  round($repay_account,2);
            $_result[$i]['repay_time'] = get_times(array("time"=>$time,"num"=>$i+1));
        }
        if ($data["type"]=="all"){
            $_result_all['account_total'] =  round($_account_all,2);
            $_result_all['interest_total'] =  round($_interest_all,2);
            $_result_all['capital_total'] =  round($_capital_all,2);
            $_result_all['repay_month'] =  round($repay_account,2);
            $_result_all['month_apr'] = round($month_apr*100,2);
            return $_result_all;
        }
        return $_result;
    }



//体验标
    function EqualTiyan ($data = array()){

        $account = 100;
        $year_apr = 20;
        $period = 1;
        $time = $data['time'];
        $_result = array();
        //$re_month = date("n",$borrow_time);
        $_capital_all = 0;
        $_interest_all = 0;
        $_account_all = 0.00;
        $interest = 2;
        $capital = 100;
        //echo $capital."<br>";
        $repay_account = 102;
        $_account_all =  $repay_account;
        $_interest_all =  $interest;
        $_capital_all =  $capital;

        $_result[0]['account_all'] =  $repay_account;
        $_result[0]['account_interest'] = $interest;
        $_result[0]['account_capital'] =  $capital;
        $_result[0]['account_other'] =  102;
        $_result[0]['repay_month'] =  102;
        $_result[0]['repay_time'] = get_times(array("time"=>$time,"num"=>$i+1));

        if ($data["type"]=="all"){
            $_result_all['account_total'] =  102;
            $_result_all['interest_total'] = 2;
            $_result_all['capital_total'] =  100;
            $_result_all['repay_month'] =  102;
            $_result_all['month_apr'] = round($month_apr*100,2);
            return $_result_all;
        }
        return $_result;
    }

}
