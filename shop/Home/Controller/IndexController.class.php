<?php

namespace Home\Controller;

use Think\Controller;

class IndexController extends CommonController
{

  
	//修改用户微信
    public function changewx(){
        $id = session('userid');
        $wx = M('user')->where("userid = $id")->getField('wx');
        $this->assign('wx',$wx);
        if(IS_POST){
            $wx = I('wx');
            $res = M('user')->where("userid = $id")->setField('wx',$wx);
            if($res == 1){
                ajaxReturn('修改成功',1);
            }
        }
        $this->display();
    }
    public function index() 
    {
		$userid = session('userid');
        $this->assign('uid',$userid);
        $a = D('user')->where("userid=$userid")->field('userlevel')->find();
        $userlevel = $a['userlevel'];
        $where['userid'] = $userid;
		$nihao = M('user')->where(array('userid'=>$userid))->getField('releas_time');
        $time = date('Y-m-d',time());
        $endtime = strtotime(date('Y-m-d',time()));
        $starttime = strtotime(date('Y-m-d',$nihao));
        $timediff = $endtime-$starttime;
        $days = intval($timediff/86400);
        if($days>=1){
            $re_ward['is_reward']=0;
            M('user')->where(array('userid'=>$userid))->save($re_ward);
        }
        $pic_array = $this->get_banner();
        //usernums
        $usernums = M('coindets')->where('id=3')->getField('coin_price');
        $usernum = M('user')->count();
        $zhenusernums = (int)$usernums+$usernum;
        $this->assign('usernums',$zhenusernums);
		
		//公告
        $gonggao = M('news')->where(array('type'=>1))->field('id,title,content')->select();
		foreach($gonggao as $key => $value){
			$info = $value['content'];
		}
        $this->assign('gonggao',$info);
		$explain = M('news')->where(array('type'=>4))->field('title,content')->order('id desc')->select();
        $this->assign('explain',$explain);
		
        $uinfo = M('user')->where($where)->field('userid,user_credit,releas_time,is_reward,today_releas,quanxian,vip_grade,activate,account,is_jihuo')->find();
        $moneyinfo = M('store')->where(array('uid' => $userid))->field('cangku_num,sv,sc,dt_jifen')->find();
        $huan = M('coindets')->where(array('id'=>1))->find();
        $this->assign('huan',$huan);
		if($uinfo['is_reward']==0&&(int)$moneyinfo['cangku_num']>0){
            $linqu = 1;
        }else{
            $linqu = 0;
        }
        $this->assign('linqu',$linqu);
        // 检测用户是否有100W积分
        $store = M('store')->where(array('uid'=>$uinfo['userid']))->find();
        $uservip = M('user')->where(array('userid'=>$userid))->getField('vip_grade');
            if($uservip==1||$uservip==0){
                $get_num = $store['cangku_num'] * 0.001; // 每天获取的值v71||v70得
            }
            if($uservip==2){
                $get_num = $store['cangku_num'] * 0.002; // 每天获取的值v72得
            }
            if($uservip==3){
                $get_num = $store['cangku_num'] * 0.003; // 每天获取的值v73得
            }
            if($uservip==4){
                $get_num = $store['cangku_num'] * 0.004; // 每天获取的值v74得
            }
            if($uservip==5){
                $get_num = $store['cangku_num'] * 0.005; // 每天获取的值v75得
            }
            if($uservip==6){
                $get_num = $store['cangku_num'] * 0.006; // 每天获取的值v76
            }
            if($uservip==7){
                $get_num = $store['cangku_num'] * 0.007; // 每天获取的值v77
            }
        // dump($userlevel);die;
        $this->assign('get_num',$get_num);

        if(IS_AJAX){
            $uid = session('userid');
            $uinfo = M('user')->where(array('userid'=>$uid))->field('releas_time,is_reward')->find();
            // 每天登陆静态释放
            $releas_time = $uinfo['releas_time'];//释放时间
            $is_reward = $uinfo['is_reward'];//是否领取
            $today = strtotime(date('Y-m-d'));
            $store = M('store')->where(array('uid'=>$userid))->find();
        
        if($is_reward == 0){
            $userid = session('userid');
            $uservip = M('user')->where(array('userid'=>$uid))->getField('vip_grade');
            if($uservip==1||$uservip==0){
                $get_num = $store['cangku_num'] * 0.001; // 每天获取的值v71||v70得
            }
            if($uservip==2){
                $get_num = $store['cangku_num'] * 0.002; // 每天获取的值v72得
            }
            if($uservip==3){
                $get_num = $store['cangku_num'] * 0.003; // 每天获取的值v73得
            }
            if($uservip==4){
                $get_num = $store['cangku_num'] * 0.004; // 每天获取的值v74得
            }
            if($uservip==5){
                $get_num = $store['cangku_num'] * 0.005; // 每天获取的值v75得
            }
            if($uservip==6){
                $get_num = $store['cangku_num'] * 0.006; // 每天获取的值v76
            }
            if($uservip==7){
                $get_num = $store['cangku_num'] * 0.007; // 每天获取的值v77
            }
            //var_dump($get_num);die();
            $nihao = M('store')->where(array('uid'=>$userid))->setDec('cangku_num',$get_num);
            $res_pay_get = M('store')->where(array('uid'=>$userid))->setInc('sv',$get_num);
            $reward_user['releas_time'] = time();
            $reward_user['is_reward'] = 1;
            if($nihao){
                D('user')->where(array('userid'=>$userid))->save($reward_user);
            }

            // 添加记录
            $get_n = M('store')->where(array('uid' => $userid))->getfield('sv');
            $data['pay_id'] = $userid;
            $data['get_id'] = $userid;
            $data['get_nums'] = $get_num;
            $data['now_nums_get'] = $get_n;
            $data['now_nums'] = $get_n;
            $data['is_release'] = 1;
            $data['get_time'] = time();
            $data['get_type'] = 2;
            $data['status'] = 1;
            $data['my'] = 1;
            $res_addrs = M('tranmoney')->add($data);

            // 添加记录
            $get_ny = M('store')->where(array('uid' => $userid))->getfield('cangku_num');
            $fen_num = $get_ny - $get_num;
            // if($fen_num < 1000000){
            //     // $data['add_or_reduce'] = $store['vip_grade'];
            //     $vip_grade = $store['add_or_reduce'];
            //     M('store')->where(array('uid'=>$userid))->setField('vip_grade',$vip_grade);
            // }
            $jifendets['pay_id'] = $userid;
            $jifendets['get_id'] = $userid;
            $jifendets['get_nums'] = -$get_num;
            $jifendets['now_nums'] = $get_ny;
            $jifendets['now_nums_get'] = $get_ny;
            $jifendets['is_release'] = 1;
            $jifendets['get_time'] = time();
            $jifendets['get_type'] = 1;
            $jifendets['status'] = 2;
            $jifendets['my'] = 3;
            $res_addrs2 = M('tranmoney')->add($jifendets);

            //$data['releas_time'] = time();

            //$genggai = M('user')->where(array('userid'=>$userid))->save($data);

                if($res_pay_get&&$nihao&&$res_addrs&&$res_addrs2){
                    ajaxReturn('领取成功',1);
                }

            }
        }

        

        $fUser = M('config')->where(array('name'=>'false_user'))->getField('value');
        $add_user = M('config')->where(array('name'=>'add_user'))->getField('value');
        
        $min_balance = M('config')->where(array('name'=>'min_balance'))->find();

        if($min_balance && $min_balance['status'] == 1){
            $this->assign('min_balance',$min_balance);
        }else{
            $min_balance['value'] = 0;
            $this->assign('min_balance',$min_balance);
        }

        $this->assign(array(
            'uinfo' => $uinfo,
            'moneyinfo' => $moneyinfo,
            'can_get' => $can_get,
            'is_setnums' => $is_setnums,
            'pic_array'=>$pic_array,
            'fUser'=>$fUser,
            'add_user'=>$add_user,
        ));
        $this->assign('userlevel',$userlevel);
        $this->display('/Index/index');
    }


  /*
  * 轮播私有方法链接数据库
  */
private function get_banner()
{
    $user_object   = M('banner');
    $data_list = $user_object->order('sort')->select();
    return $data_list;
}
/**
 * 钱包余额
 * 
 */
 //团队人数
public function getsubuser($id,$level=1){
    if($level<=10){
        $room=M('user')->field('userid')->where('pid="'.$id.'"')->select();
        $str='';
        if(!empty($room))
        {
            foreach($room as $vo)
            {
                $str+=1;
                $str+=$this->getsubuser($vo['userid'],$level+1);
            }
        }
        return $str;
    }
}
//更新团队人数
public function friends(){
    $uid = session('userid');
    $sum = $this->getsubuser($uid);
	// if($uid == 779852 && $sum>=982){
        // $sum = 982;
    // }
    M('user')->where("userid = $uid")->setField('friends',$sum);
    ajaxReturn($sum);
}

public function allassets(){
   
    $uid = session('userid');
    $ress = M('store')->where('uid='.$uid)->find();

    //是否冷却
    $lengque = M('user')->where("userid = $uid")->getField('is_cooling');
    $this->assign('lengque',$lengque);
    
    // $sum = $this->getsubuser($uid);
    // $this->assign('sum',$sum);

	//var_dump($res);die();
    // $sum = 0;
    // //团队资产额度
    // $upid = M('user')->where(array('pid'=>$uid))->field('userid')->select();
    // foreach ($upid as $k => $v) {
        // $res = M('trans')->where(array('payin_id'=>$v['userid'],'pay_state'=>3))->sum('pay_nums');
        // $sum += $res;
    // }
    // $sum2 = 0;
    // //团队资产额度
    // $ugid = M('user')->where(array('gid'=>$uid))->field('userid')->select();
    // foreach ($ugid as $k => $v) {
        // $res = M('trans')->where(array('payin_id'=>$v['userid'],'pay_state'=>3))->sum('pay_nums');
        // $sum2 += $res;
    // }
    // $sum3 = 0;
    // //团队资产额度
    // $uggid = M('user')->where(array('ggid'=>$uid))->field('userid')->select();
    // foreach ($uggid as $k => $v) {
        // $res = M('trans')->where(array('payin_id'=>$v['userid'],'pay_state'=>3))->sum('pay_nums');
        // $sum3 += $res;
    // }
    // //加上本人的投单额度
    // $ben = M('trans')->where(array('payin_id'=>$uid,'pay_state'=>3))->sum('pay_nums');
    // $zongsum = $ben+$sum+$sum2+$sum3;
	// $uinfo = M('user')->where($where)->field('userid,user_credit,releas_time,is_reward,today_releas,quanxian,vip_grade,activate,wx,account,is_jihuo')->find();
    $info = M('user')->where("userid=$uid")->find();
	// if($uid == 779852 && $info['friends']>=982){
        // $info['friends'] = 982;
    // }
	$this->assign('info',$info);
	$this->assign('zongsum',$zongsum);
    $this->assign('uinfo',$uinfo);
    $this->assign('res',$ress);
    $this->display();
}
/**
 * 增加用户量
 */
public function adduser(){

    $add = I('add');

    $res2 = M('coindets')->where('id=3')->setInc('coin_price',$add);
    if($res2){
        ajaxReturn('YES',0);
    }

}
   /**
 * 总资产
 */
public function totalassetwallet(){
    $traInfo = M('tranmoney');
    $uid = session('userid');
    $zong = M('store')->where(array('uid'=>$uid))->getField('cangku_num');
    $where['pay_id|get_id'] = $uid;
    $where['get_type'] = array('in', '32,1,8,39');
    $where['my'] = 3;
    //分页
    $p = getpage($traInfo, $where, 50);
    $page = $p->show();
    $Chan_info = $traInfo->where($where)->order('id desc')->select();

    $this->assign('page', $page);
    $this->assign('Chan_info', $Chan_info);
    $this->assign('zong',$zong);
    $this->display();
}
    public function Dotrrela()
    {
        if (IS_AJAX) {
            $userid = session('userid');
            //是否存在当日转账释放红包
            $startime = date('Y-m-d');
            $endtime = date("Y-m-d", strtotime("+1 day"));
            $todaystime = strtotime($startime);
            $endtime = strtotime($endtime);
            $whereres['get_id'] = $userid;
            $whereres['is_release'] = 0;
            $whereres['get_type'] = 22;
            $whereres['get_time'] = array('between', array($todaystime, $endtime));
            $is_setnums = M('tranmoney')->where($whereres)->sum('get_nums') + 0;
            if ($is_setnums > 0) {
                $datapay['cangku_num'] = array('exp', 'cangku_num + ' . $is_setnums);
                $datapay['fengmi_num'] = array('exp', 'fengmi_num - ' . $is_setnums);
                $res_pay = M('store')->where(array('uid' => $userid))->save($datapay);//每日银积分释放金积分

                //添加释放记录
                $jifen_nums = $is_setnums;
                $jifen_dochange['pay_id'] = $userid;
                $jifen_dochange['get_id'] = $userid;
                $jifen_dochange['get_nums'] = $jifen_nums;
                $jifen_dochange['get_time'] = time();
                $jifen_dochange['get_type'] = 2;
                $res_addres = M('tranmoney')->add($jifen_dochange);
                //改成已释放
                $savedata['is_release'] = 1;
                $savedata['get_time'] = time();
                $is_setnums = M('tranmoney')->where($whereres)->save($savedata);
                if ($is_setnums) {
                    ajaxReturn('转账银积分释放成功', 1);
                } else {
                    ajaxReturn('转账银积分释放失败', 0);
                }
            }
        }
    }

    //金积分记录
    public function Bancerecord()
    {
        $traInfo = M('tranmoney');
        $uid = session('userid');
        $where['pay_id|get_id'] = $uid;
        $where['get_type'] = array('in', '8,9,10');
        //分页
        $p = getpage($traInfo, $where, 50);
        $page = $p->show();
        $Chan_info = $traInfo->where($where)->order('id desc')->select();

        
        foreach ($Chan_info as $k => $v) {


            $Chan_info[$k]['get_timeymd'] = date('Y-m-d', $v['get_time']);
            $Chan_info[$k]['get_timedate'] = date('H:i:s', $v['get_time']);
            //转入转出
            if ($uid == $v['pay_id']) {
                $Chan_info[$k]['trtype'] = 1;
                    if($v['get_type']==5){//当自己是求购方支付金积分，因挂求购单时已支付了金积分，故不存在
                        unset($Chan_info[$k]);

                    }

            } else {
                $Chan_info[$k]['trtype'] = 2;
            }


        }
        if (IS_AJAX) {
            if (count($Chan_info) >= 1) {
                ajaxReturn($Chan_info, 1);
            } else {
                ajaxReturn('暂无记录', 0);
            }
        }
        $this->assign('page', $page);
        $this->assign('Chan_info', $Chan_info);
        $this->assign('uid', $uid);
        $this->display();
    }

    //转出
    public function svtransfer()
    {
        $uid = session('userid');
        $res = M('store')->where(array('uid'=>$uid))->getField('sv');

        $this->assign('res',$res);
        if (IS_AJAX) {

            $paynums = trim(I('paynums'));
            $uinfo = trim(I('uinfo'));
            $pwd = trim(I('pwd'));
            //手机号码或者用户id
            $map['userid|mobile'] = $uinfo;

            $issetU = M('user')->where($map)->field('userid,username')->find();
            $userid = session('userid');

            $minepwd = M('user')->where(array('userid' => $uid))->Field('account,mobile,safety_pwd,safety_salt')->find();
            $user_object = D('Home/User');
            $user_info = $user_object->Trans($minepwd['account'], $pwd);
			
			if($paynums < 1){
				ajaxReturn('转账数量不能小于1',0);
			}
			
			// if($paynums % 100 != 0){
				// ajaxReturn('转账数量必须为100的倍数',0);
			// }
			
            if ($uinfo == $issetU['userid']) {
                ajaxReturn('您不能给自己转账哦~', 0);
            }
            //var_dump($uinfo);die();
            if ($issetU) {
                $yue = M('store')->where(array('uid'=>$uid))->getField('sv');
                if($paynums>$yue){
                    ajaxReturn('账户余额不足');
                }
                $ress = M('store')->where(array('uid'=>$issetU['userid']))->setInc('sv',$paynums);
                $ress2 = M('store')->where(array('uid'=>$uid))->setDec('sv',$paynums);
                if($ress&&$ress2){
                    $changenums['pay_id'] = $uid;
                    $changenums['get_id'] = $issetU['userid'];
                    $changenums['now_nums'] = $pay_ny;
                    $changenums['now_nums_get'] = $get_ny;
                    $changenums['get_nums'] = $paynums;
                    $changenums['is_release'] = 1;
                    $changenums['get_time'] = time();
                    $changenums['get_type'] = 1;
                    $changenums['status'] = 1;
                    $one = M('tranmoney')->add($changenums);
                    
                    // $arr = array(
                    //     'pay_id' => $issetU['userid'],
                    //     'get_id' => $uid,
                    //     'get_nums' => -$paynums,
                    //     'get_time'  => time(),
                    //     'get_type'=>1,
                    //     'now_nums' => 0,
                    //     'now_nums_get' => 0,
                    //     'status'=>2
                    // );
                    
                    // $two = M('tranmoney')->add($arr);
                    if($one){
                        ajaxReturn('转账成功哦', 1);
                    }
                }; 
            } else {
                ajaxReturn('并不存在该用户哦~', 0);
            }
        }
        $this->display();
    }


    public function Changeout()
    {

        $sid = trim(I('sid'));
        $uinfo = M('user as us')->JOIN('ysk_store as ms')->where(array('us.userid' => $sid))->field('us.mobile,us.userid,us.img_head,us.username,ms.cangku_num')->find();
        if (IS_AJAX) {
            $data = $_POST['post_data'];
            $trid = trim($data['zuid']);
            $paynums = $data['paynums'];
            $mobila = trim($data['mobila']);
            $pwd = trim(I('pwd'));
            $type = $data['type'];
            $uid = session('userid');
        
            $info2=$paynums%1;

            if($paynums<1){
                ajaxReturn('不得小于1',0);
            }

            if($info2){
                ajaxReturn('请输入1的倍数',0);
            }

            if($type<0){
                ajaxReturn('请选择转出类型',0);
            }
            //验证交易密码
            $minepwd = M('user')->where(array('userid' => $uid))->Field('account,mobile,safety_pwd,safety_salt')->find();
            $user_object = D('Home/User');
            $user_info = $user_object->Trans($minepwd['account'], $pwd);
            //验证手机号码后四位
            $is_setm['userid|mobile'] = $trid;
            $tmobile = M('user')->where($is_setm)->getfield('mobile');
            $tmobile = substr($tmobile, -4);
            if ($tmobile != $mobila) {
                ajaxReturn('您输入的手机号码后四位有误', 0);
            }
            if ($paynums <= 0) {
                ajaxReturn('您输入的转账金额有误哦~', 0);
            }
            if ($uid == $trid) {
                ajaxReturn('您不能给自己转账哦~', 0);
            }
            if($type==1){
                $mine_money = M('store')->where(array('uid' => $uid))->getfield('cangku_num');
                $sqlname = 'cangku_num';
            }elseif($type==2){
                $user = M('user')->field('pid,gid,ggid')->where('userid='.$trid)->find();
                if(!in_array($uid,$user)){
                   ajaxReturn('您不是该用户的推荐人哦~', 0); 
                }
                $mine_money = M('store')->where(array('uid' => $uid))->getfield('paidan');
                $sqlname = 'paidan';
            }elseif($type==3){
                $user = M('user')->field('pid,gid,ggid')->where('userid='.$trid)->find();
                $user = M('user')->field('pid,gid,ggid')->where('userid='.$trid)->find();
                if(!in_array($uid,$user)){
                   ajaxReturn('您不是该用户的推荐人哦~', 0); 
                }
                $mine_money = M('store')->where(array('uid' => $uid))->getfield('active_num');
                $sqlname = 'active_num';
            }
            
            if ($mine_money < $paynums) {
                ajaxReturn('您的金额暂无这么多哦~', 0);
            }
            if($type==1){
                $tper = $paynums * 20 / 100;
                $eper = $paynums * 80 / 100;
                $datapay['cangku_num'] = array('exp', 'cangku_num - ' . $paynums);
                $datapay['fengmi_num'] = array('exp', 'fengmi_num + ' . $eper);
                $res_pay = M('store')->where(array('uid' => $uid))->save($datapay);//转出的人+80%银积分

                $dataget['cangku_num'] = array('exp', "cangku_num + $eper");
                $dataget['fengmi_num'] = array('exp', 'fengmi_num + ' . $tper);
                $res_get = M('store')->where(array('uid' => $trid))->save($dataget);//转入的人+20%银积分

                 $pay_ny = M('store')->where(array('uid' => $uid))->getfield('fengmi_num');
                 $get_ny = M('store')->where(array('uid' => $trid))->getfield('fengmi_num');

                //转入的人+20%银积分记录SSS
                $changenums['pay_id'] = $uid;
                $changenums['get_id'] = $trid;
                $changenums['now_nums'] = $pay_ny;
                $changenums['now_nums_get'] = $get_ny;
                $changenums['get_nums'] = $tper;
                $changenums['is_release'] = 1;
                $changenums['get_time'] = time();
                $changenums['get_type'] = 1;
                M('tranmoney')->add($changenums);


                // 如果父级等级为VIP额外赠送1%的积分
                $pid = M('user')->where(array('userid'=>$uid))->getField('pid');
                $p_info = M('store')->where(array('uid'=>$pid))->find();
                if($p_info['vip_grade'] == 4){
                    $tper = $paynums * 1 / 100;
                    $parent_feng = array('exp','fengmi_num + ' . $tper);
                    $res_parent = M('store')->where(array('uid'=>$pid))->setField('fengmi_num',$parent_feng);
                    
                    //父级用户银积分记录
                    $changenums['pay_id'] = $uid;
                    $changenums['get_id'] = $pid;
                    $changenums['now_nums'] = $p_info['fengmi_num'];
                    $changenums['now_nums_get'] = $p_info['fengmi_num'];
                    $changenums['get_nums'] = $tper;
                    $changenums['is_release'] = 1;
                    $changenums['get_time'] = time();
                    $changenums['get_type'] = 23;
                    M('tranmoney')->add($changenums);
                }
     
                //转入的人+20%银积分记录EEE
    //            $jifen_nums = $tper * 2 / 1000;
    //            $jifen_dochange['pay_id'] = $trid;
    //            $jifen_dochange['get_id'] = $trid;
    //            $jifen_dochange['get_nums'] = $jifen_nums;
    //            $jifen_dochange['get_time'] = time();
    //            $jifen_dochange['get_type'] = 22;
    //            M('tranmoney')->add($jifen_dochange);
                //对应20%银积分释放到金积分SSS
    //            $jifen_donums['cangku_num'] = array('exp', "cangku_num + $jifen_nums");
    //            $jifen_donums['fengmi_num'] = array('exp', 'fengmi_num - ' . $jifen_nums);
    //            $res_get = M('store')->where(array('uid' => $trid))->save($jifen_donums);//转入的人+20%银积分


                    //金积分转动奖---没有触发  
                    $this->zhuand15($uid,$paynums);//转出方15层得到转动奖
                    

                    $this->zhuand15($trid,$eper);//转入方15层得到转动奖
            }else{
                $pay_ny = M('store')->where(array('uid' => $uid))->getfield($sqlname);
                $get_ny = M('store')->where(array('uid' => $trid))->getfield($sqlname);
                $datapay[$sqlname] = $pay_ny-$paynums;
                $res_pay = M('store')->where(array('uid' => $uid))->save($datapay);
                //添加减去记录
                $arr = array(
                    'pay_id' => $uid,
                    'get_id' => $trid,
                    'get_nums' => $paynums,
                    'get_time'  => time(),
                    'now_nums' => $datapay[$sqlname],
                    'now_nums_get' => $datapay[$sqlname],
                );
                
                if($type == 2){
                    $addarr['get_type'] = 27;
                }elseif($type == 3){
                    $addarr['get_type'] = 28;
                }
                $arr = array_merge($arr,$addarr);
                M('tranmoney')->add($arr);

                $dataget[$sqlname] = $get_ny+$paynums;
                $res_get = M('store')->where(array('uid' => $trid))->save($dataget);
            }



            //判断用户等级
            // $uChanlev = D('Home/index');
            // $uChanlev->Checklevel($trid);
            //执行转账
             $pay_n = M('store')->where(array('uid' => $uid))->getfield($sqlname);
             $get_n = M('store')->where(array('uid' => $trid))->getfield($sqlname);

            if ($res_pay && $res_get) {
                $data['pay_id'] = $uid;
                $data['get_id'] = $trid;
                $data['get_nums'] = $paynums;
                $data['now_nums'] = $pay_n;                
                $data['now_nums_get'] =$get_n;                
                $data['is_release'] =1;                
                $data['get_time'] = time();
            }
            
            $add_Dets = M('tranmoney')->add($data);
            if ($add_Dets) {
                ajaxReturn('转账成功哦~', 1, '/Index/index');
            }
        }
        $this->assign('uinfo', $uinfo);
        $this->display();
    }

    //金积分转银积分

    public function test()
    {
          $userid = session('userid');

       

    }


    // 用户激活
    public function activation(){
        $vip_grade = I('vip_grade');
        $userid = session('userid');
        if($vip_grade>0){
            $store = M('store')->where(array('uid'=>$userid))->find();
            // ajaxReturn($store['cangku_num'],0);
            // dump($store);
            if($store['cangku_num'] >= 100 && $store['vip_grade'] == 0){

                $store['cangku_num'] -= 100;
                $store['vip_grade'] += 1;
                M()->startTrans();
                $store_res = M('store')->where(array('uid'=>$userid))->save($store); 
                $user_res = D('user')->where(array('userid'=>$userid))->setField('vip_grade',$store['vip_grade']);
                $user_res2 = D('user')->where(array('userid'=>$userid))->setField('quanxian','');
                if( !$store_res && !$user_res && !$user_res2){
                    M()->rollback();
                }else{
                    // 添加转账记录
                    $get_n = M('store')->where(array('uid' => $userid))->getfield('fengmi_num');
                    $datass['pay_id'] = $userid;
                    $datass['get_id'] = $userid;
                    $datass['get_nums'] = 100;
                    $datass['now_nums_get'] = $get_n;
                    $datass['now_nums'] = $get_n;
                    $datass['is_release'] = 1;
                    $datass['get_time'] = time();
                    $datass['get_type'] = 23;
                    $res_addrs = M('tranmoney')->add($datass);

                    ajaxReturn('已消耗100余额激活账号,成为普通会员',1);
                }


            }else{
                if($store['vip_grade'] > 0){
                    ajaxReturn('已消耗100余额激活账号',0);
                }else{
                    ajaxReturn('余额不足100，请充值',0);
                }
            }
        }
    }


    // 用户升级
    public function upgrade(){
        return false;
        $vip_grade = I('vip_grade');
        $userid = session('userid');
        $user = D('user')->where(array('userid'=>$userid))->find();
        $store = M('store')->where(array('uid'=>$userid))->find();
        $add_or_reduce = 0;
        if( $user && $vip_grade == $user['vip_grade'] ){
            if($store['fengmi_num'] < 1000000 && $vip_grade !=4){
                switch($vip_grade){
                    // 体验用户
                    case '0':
                        ajaxReturn('请先让上级激活',0);
                        break;
                    // 普通用户升级蓝钻
                    case '1':

                        if($store['cangku_num'] >= 500 && $store['vip_grade'] <= 3 && $store['fengmi_num']){
                            $store['fengmi_num'] +=1000;
                            $store['cangku_num'] -= 500;
                            $store['vip_grade'] += 1;
                            M()->startTrans();
                            $store_res = M('store')->where(array('uid'=>$userid))->save($store); 
                            $user_res = D('user')->where(array('userid'=>$userid))->setField('vip_grade',$store['vip_grade']);

                            // 添加转账记录
                            $get_n = M('store')->where(array('uid' => $userid))->getfield('fengmi_num');
                            $datass['pay_id'] = $userid;
                            $datass['get_id'] = $userid;
                            $datass['get_nums'] = 1000;
                            $datass['now_nums_get'] = $get_n;
                            $datass['now_nums'] = $get_n;
                            $datass['is_release'] = 1;
                            $datass['get_time'] = time();
                            $datass['get_type'] = 23;
                            $res_addrs = M('tranmoney')->add($datass);


                            if( !$store_res && !$user_res){
                                M()->rollback();
                            }else{


                                // 推荐人奖励
                                $pid = D('user')->where(array('userid'=>$userid))->getField('pid');

                                $b_drills= D('config')->where("name='b_drills'")->getField("value");
                                $b_drills= (float)$b_drills;
                                $b_num= D('config')->where("name='b_num'")->getField("value");
                                $pid_n=D('user')->where(array('pid'=>$pid,'vip_grade'=>2))->count(1);

                                if($pid_n<=$b_num && $b_drills>0){

                                        

                                        $datapay22['fengmi_num'] = array('exp', 'fengmi_num + ' . $b_drills);
                                        $res_pay_get = M('store')->where(array('uid' => $pid))->save($datapay22);//推荐一人增加积分

                                        $get_n = M('store')->where(array('uid' => $pid))->getfield('fengmi_num');
                                        $datass['pay_id'] = $pid;
                                        $datass['get_id'] = $pid;
                                        $datass['get_nums'] = $b_drills;
                                        $datass['now_nums_get'] = $get_n;
                                        $datass['now_nums'] = $get_n;
                                        $datass['is_release'] = 1;
                                        $datass['get_time'] = time();
                                        $datass['get_type'] = 23;
                                        $res_addrs = M('tranmoney')->add($datass);
                                }

                                ajaxReturn('升级蓝钻会员成功',1);
                            }
                        }else{
                            ajaxReturn('余额不足500，请充值',0);
                        }
                        break;
                    // 蓝钻用户升级金钻
                    case '2':
                    // 推荐人数
                    $childUser = D('user')->where(array('pid'=>$user['userid']))->count(1);
                    // ajaxReturn($childUser,0);
                    // 
                    if($store['cangku_num'] >= 5000 && $store['vip_grade'] <= 3 && $childUser >= 3 && $childUser+1 >=5 ){
                            $store['cangku_num'] -= 5000;
                            $store['vip_grade'] += 1;
                            M()->startTrans();
                            $store_res = M('store')->where(array('uid'=>$userid))->save($store); 
                            $user_res = D('user')->where(array('userid'=>$userid))->setField('vip_grade',$store['vip_grade']);

                            if( !$store_res && !$user_res){
                                M()->rollback();
                            }else{

                                // 推荐人奖励
                                $pid = D('user')->where(array('userid'=>$userid))->getField('pid');

                                $g_drills= D('config')->where("name='g_drills'")->getField("value");
                                $g_drills= (float)$g_drills;
                                $g_num= D('config')->where("name='g_num'")->getField("value");
                                $pid_n=D('user')->where(array('pid'=>$pid,'vip_grade'=>3))->count(1);

                                if($pid_n<=$g_num && $g_drills>0){

                                        $datapay22['fengmi_num'] = array('exp', 'fengmi_num + ' . $g_drills);
                                        $res_pay_get = M('store')->where(array('uid' => $pid))->save($datapay22);//推荐一人增加积分

                                        $get_n = M('store')->where(array('uid' => $pid))->getfield('fengmi_num');
                                        $datass['pay_id'] = $pid;
                                        $datass['get_id'] = $pid;
                                        $datass['get_nums'] = $g_drills;
                                        $datass['now_nums_get'] = $get_n;
                                        $datass['now_nums'] = $get_n;
                                        $datass['is_release'] = 1;
                                        $datass['get_time'] = time();
                                        $datass['get_type'] = 23;
                                        $res_addrs = M('tranmoney')->add($datass);

                                }

                                ajaxReturn('升级金钻会员成功',2);
                            }
                        }else{
                            if($store['cangku_num'] < 5000){
                                ajaxReturn('余额不足5000，请充值',0);
                            }elseif($childUser < 3){
                                ajaxReturn('直推人数不足3人',0);
                            }
                        }
                        break;
                    // 金钻用户升级到vip用户
                    case '3':
                        if($store['fengmi_num'] < 1000000){
                            ajaxReturn('积分不足100W',0);
                        }
                        break;
                    default:
                        ajaxReturn('您已升级到最高等级',0);
                        break;
                }
            }else{

                // 判断积分是否满足100W 
                if($store['fengmi_num'] >= 1000000){
                    if(empty($store['add_or_reduce']) && $stroe['vip_grade'] != 4){
                        $store['add_or_reduce'] = $store['vip_grade'];                
                    }
                    $store['vip_grade'] = 4;
                    // ajaxReturn($store);
                    M()->startTrans();
                    $store_res = M('store')->where(array('uid'=>$userid))->save($store); 
                    $user_res = D('user')->where(array('userid'=>$userid))->setField('vip_grade',$store['vip_grade']);
                    if( !$store_res && !$user_res){
                        M()->rollback();
                    }
                    ajaxReturn('您已升级到最高等级',0);
                }else{
                    M()->startTrans();
                    $user_grade = $store['add_or_reduce'];
                    $store_res = M('store')->where(array('uid'=>$userid))->setField('vip_grade',$user_grade); 
                    $user_res = D('user')->where(array('userid'=>$userid))->setField('vip_grade',$user_grade);
                    if($user_grade == 0){
                        D('user')->where(array('userid'=>$userid))->setField('quanxian','2-5');
                    }
                    if( !$store_res && !$user_res){
                        M()->rollback();
                    }
                }
            }
        }else{
            ajaxReturn('系统错误',0);
        }
    }


    // 更新升级人数
    public function add_unums(){
        $add = I('add');
        $update_time = time();
        $res = M('config')->where(array('name'=>'add_user'))->setInc('value',$add);
        $res3 = M('config')->where(array('name'=>'add_user'))->setField('update_time',$update_time);
        $res2 = M('config')->where(array('name'=>'false_user'))->setInc('value',$add);
        if( $res && $res2){
            ajaxReturn('YES',0);
        }
    }



 public function get_between($input, $start, $end) {
    $substr = substr($input, strlen($start)+strpos($input, $start),(strlen($input) - strpos($input, $end))*(-1));
    return $substr;

}


    //管理奖和直推奖， 管理拿2-4代
    private function Manage_reward($uid,$paynums){

    $Lasts = D('Home/index');
    $Lastinfo = $Lasts->Getlasts($uid, 15, 'path');  
    
    if (count($Lastinfo) > 0) {

        $Manage_b = M('config')->where(array('group' => 6, 'status' => 1))->order('id asc')->select();//分享奖比例
        $Manage_a = M('config')->where(array('group' => 7, 'status' => 1))->order('id asc')->select();//管理奖比例
   
        foreach ($Lastinfo as $k => $v) {
  
            if (!empty($v)) {//当前会员信息
              

                    if($k==0) {//第一代，即为直推奖 

                        $u_Grade = M('user')->where(array('userid' => $v))->getfield('use_grade');
                        $direct_fee=0;
                        if($u_Grade>0)$direct_fee=(float)$Manage_b[$u_Grade-1]["value"];//判断是什么比例

                        $zhitui_reward = $direct_fee / 100 * $paynums;//直推的人所得分享奖
                        M('user')->where(array('userid' => $v))->setInc('releas_rate', $zhitui_reward);
                    }

                    if ($k>0&&$k<=3) {//2-4代,拿直推的人的分享奖*相应比例，即为管理奖
                         $t=$k-1; 
                         $zhitui_num = M('user')->where(array('pid' => $v))->count(1);//计算直推人数
                         $suoxu_num=(int)$Manage_a[$t]["tip"];
                        
                        if($zhitui_num>=$suoxu_num){//直推人数满足条件

                            $My_reward=$Manage_a[$t]["value"]/100*$zhitui_reward;                    
                            $res_Incrate = M('user')->where(array('userid' => $v))->setInc('releas_rate', $My_reward);
                               
                        }
                    }                  
                   
                
            }//if
        }//foreach

    }
  }

    //区块奖和VIP奖   区块拿15层
    private function Addreas15($uid,$paynums){

    $Lasts = D('Home/index');
    $Lastinfo = $Lasts->Getlasts($uid, 15, 'path');
    if (count($Lastinfo) > 0) {
        $add_relinfo = M('config')->where(array('group' => 9, 'status' => 1))->order('id asc')->select();
        $vips = M('config')->where(array('group' => 10, 'status' => 1))->order('id asc')->select();
        $i = 0;
        $n = 0;
        foreach ($Lastinfo as $k => $v) {
            //查询当前自己等级
            if (!empty($v)) {

                    $zhitui_num = M('user')->where(array('pid' => $v))->count(1);//计算直推人数
                    $t=$k+1;
                    $tkey =0;
                    $daishu=array(3,6,9,12,15);
                    foreach ($daishu as $key1 => $value1) {
                     if($t>$value1)$tkey=$key1+1;
                    }
                    
                    
                    $v_Grade = M('user')->where(array('userid' => $v))->getfield('vip_grade');

                    $suoxu_num=(int)$add_relinfo[$tkey]["tip"];
                    if($add_relinfo['options'] >= 1 && $add_relinfo['options'] == $v_Grade && $zhitui_num>=$suoxu_num){//普通会员直推人数满足条件 得区块奖

                        $Lastone = $My_reward=$add_relinfo[$tkey]["value"]/100*$paynums; 
                        $res_Incrate = M('user')->where(array('userid' => $v))->setInc('releas_rate', $Lastone);

                                
                    }






                    //VIP奖，有集差，加速释放
                    if( $add_relinfo['options'] >= 2 && $u_Grade == $add_relinfo['options'] && $zhitui_num >= $suoxu_num){ //蓝钻会员奖
                        
                        $u_get_money = $vips[0]['value'] / 100 * $paynums;
                        $res_Add = M('user')->where(array('userid'=>$v))->setInc('release_rate' , $u_get_money);

                    }elseif( $add_relinfo['options'] == 3 && $u_Grade == $add_relinfo['options'] && $zhitui_num >= $suoxu_num){ //金钻会员奖
                        
                        $u_get_money = $vips[1]['value'] / 100 * $paynums;
                        $res_Add = M('user')->where(array('userid'=>$v))->setInc('release_rate' , $u_get_money);
                    
                    }elseif( $add_relinfo['options'] == 4 && $u_Grade == $add_relinfo['options'] && $zhitui_num >= $suoxu_num){
                        $u_get_money = $vips[2]['value'] / 100 * $paynums;
                        $res_Add = M('user')->where(array('userid'=>$v))->setInc('release_rate' , $u_get_money);
                    }
                    
                    // if(($v_Grade == 1 && $i == 0)||($v_Grade == 2 && $i == 0)){//VIP1奖

                    //         $u_get_money = $vips[0]['value'] / 100 * $paynums;
                    //         $res_Add = M('user')->where(array('userid' => $v))->setInc('releas_rate', $u_get_money);
                    //         $i++;
                            
                            
                    // }elseif($v_Grade==2 && $i!=0 &&$n==0){//VIP2奖
                    //      $u_get_money = $vips[1]['value'] / 100 * $paynums;
                    //      $res_Add = M('user')->where(array('userid' => $v))->setInc('releas_rate', $u_get_money);
                    //      $n++;

                    // }



               
            }//if
        }//foreach

     }
}



    //金积分转动奖  拿15层
    public function zhuand15($uid,$paynums)
    {
            $Lasts = D('Home/index');
            $Lastinfo = $Lasts->Getlasts($uid, 8, 'path');  
            if (count($Lastinfo) > 0) {
                    
                    $Manage_b = M('config')->where(array('group' => 8, 'status' => 1))->order('id asc')->select();//金积分转动奖比例   
                    foreach ($Lastinfo as $k => $v) {
              
                        if (!empty($v)) {//当前会员信息

                            $u_Grade = M('user')->where(array('userid' => $v))->getfield('use_grade');
                            $direct_fee=0;
                            if($u_Grade>0)$direct_fee=(float)$Manage_b[$u_Grade-1]["value"];//判断是什么比例

                            $zhuand_reward = $direct_fee / 100 * $paynums;//我得到转动奖的加速
                            M('user')->where(array('userid' => $v))->setInc('releas_rate', $zhuand_reward);
                        }
                    }

            }


    }


    public function Turncords()
    {
        $traInfo = M('tranmoney');
        $uid = session('userid');
        $where['pay_id'] = $uid;
        $where['get_type'] = 0;
        //分页
        $p = getpage($traInfo, $where, 20);
        $page = $p->show();
        $Chan_info = $traInfo->where($where)->order('id desc')->select();
        foreach ($Chan_info as $k => $v) {
            $getinfos = M('user')->where(array('userid' => $v['get_id']))->Field('img_head,username')->find();
            $Chan_info[$k]['imghead'] = $getinfos['img_head'];
            $Chan_info[$k]['guname'] = $getinfos['username'];

        }
        if (IS_AJAX) {
            if (count($Chan_info) >= 1) {
                ajaxReturn($Chan_info, 1);
            } else {
                ajaxReturn('暂无记录', 0);
            }
        }
        $this->assign('page', $page);
        $this->assign('Chan_info', $Chan_info);
        $this->assign('uid', $uid);

        $this->display();
    }


    //根据关系进行分销
    public function Doprofit($uid, $paynums, $type)
    {
        $Lasts = D('Home/index');
        $Lastinfo = $Lasts->Getlasts($uid, 15, 'path');
        //数量的多少
        if($type == 1){
            $paynums = $paynums * 20/100;
        }else{
            $paynums = $paynums;

        }
        if (count($Lastinfo) > 0) {
            $this->Addreas($uid,$Lastinfo,$paynums,$type);//加速银积分释放
        }
    }

    //加速银积分释放【银积分基础释放， 1代银积分加速释放，2-15代，2代vip ，2-15代vip银积分】
    private function Addreas($uid,$Lastinfo,$paynums,$type){
        //银积分加速释放率
        $add_relinfo = M('config')->where(array('group' => 4, 'status' => 1))->order('id asc')->select();
        $i = 0;
        foreach ($Lastinfo as $k => $v) {
            $k = $k + 1;
            //查询当前自己等级
            if (!empty($v)) {
                //当前会员信息 等级字段
                $u_Grade = M('user')->where(array('userid' => $v))->getfield('use_grade');

                if ($u_Grade >= 1) {
                    if ($k == 1) {
                        $release_bili = $add_relinfo[1]['value'];
                    } else {
                        $release_bili = $add_relinfo[2]['value'];
                    }
                    $Lastone = $release_bili / 100 * $paynums;
                    //加速银积分释放
                    $res_Incrate = M('user')->where(array('userid' => $v))->setInc('releas_rate', $Lastone);

                    //增加银积分
                        $u_get_money = $add_relinfo[3]['value'] / 100 * $paynums;
                        if($u_Grade == 3 && $i == 0){
                            $res_Add = M('store')->where(array('uid' => $v))->setInc('fengmi_num', $u_get_money);//给上级会员加银积分
                            if ($res_Add) {
                                $earns['pay_id'] = $uid;
                                $earns['get_id'] = $v;
                                $earns['get_nums'] = $u_get_money;
                                $earns['get_level'] = $k;
                                $earns['get_types'] = $type;
                                $earns['get_time'] = time();
                                $res_Earn = M('moneyils')->add($earns);

                                // $jifendets['pay_id'] = $uid;
                                // $jifendets['get_id'] = $v;
                                // $jifendets['get_nums'] = $u_get_money;
                                // $jifendets['get_time'] = time();
                                // $jifendets['get_type'] = 1;
                                // M('tranmoney')->add($jifendets);
                                $i++;
                            }
                    }
                }
            }//if
        }//foreach
    }


    //转出记录
    public function Outrecords()
    {
        $traInfo = M('tranmoney');
        $uid = session('userid');
        $where['pay_id|get_id'] = $uid;
        $where['get_type'] = 0;
        //分页
        $p = getpage($traInfo, $where, 50);
        $page = $p->show();
        $Chan_info = $traInfo->where($where)->order('id desc')->select();
        foreach ($Chan_info as $k => $v) {
            $Chan_info[$k]['get_timeymd'] = date('Y-m-d', $v['get_time']);
            $Chan_info[$k]['get_timedate'] = date('H:i:s', $v['get_time']);
            //转入转出
            if ($uid == $v['pay_id']) {
                $Chan_info[$k]['trtype'] = 1;
            } else {
                $Chan_info[$k]['trtype'] = 2;
            }
        }
        if (IS_AJAX) {
            if (count($Chan_info) >= 1) {
                ajaxReturn($Chan_info, 1);
            } else {
                ajaxReturn('暂无记录', 0);
            }
        }
        $this->assign('page', $page);
        $this->assign('Chan_info', $Chan_info);
        $this->assign('uid', $uid);
        $this->display();
    }

/**
 * SC闪兑
 */
public function purchasesv(){
    $usdt = M('coindets')->where('id=4')->getField('coin_price');
	$id = uniqid();
    $time = date('d-s',time());
    $pay_no = $time.$id;
	if(IS_POST){
            $uid = session('userid');
            $data['pay_no'] = I('pay_no');
            
            $pay_num = I('number');
            $res = M('coindets')->where('id=4')->getField('coin_price')/100;
            $data['pay_num'] = $pay_num;
            $data['pay_time'] = time();
            $pwd = I('pwd');
            $data['uid'] = $uid;
            $picname = $_FILES['uploadfile']['name'];
            $picsize = $_FILES['uploadfile']['size'];
            
            if($data['pay_num']==null){
                ajaxReturn('请填写数量',0);
            }

            $minepwd = M('user')->where(array('userid' => $uid))->Field('account,mobile,safety_pwd,safety_salt')->find();
            $user_object = D('Home/User');
            $user_info = $user_object->Trans($minepwd['account'], $pwd);

            if ($picname != "") {
                if ($picsize > 10014000) { //限制上传大小
                    ajaxReturn('图片大小不能超过2M',0);
                }
                $type = strstr($picname, '.'); //限制上传格式
                if ($type != ".gif" && $type != ".jpg" && $type != ".png"  && $type != ".jpeg") {
                    ajaxReturn('图片格式不对',0);
                }
                $rand = rand(100, 999);
                $pics = uniqid() . $type; //命名图片名称
                //上传路径
                $pic_path = "./Uploads/Payvos/". $pics;
                move_uploaded_file($_FILES['uploadfile']['tmp_name'], $pic_path);
            }
            $size = round($picsize/1024,2); //转换成kb
            $pic_path = trim($pic_path,'.');
            if($size){
                $data['pay_img'] = $pic_path;
                $upwx = M('shandui')->add($data);
                if($upwx){
                    ajaxReturn('提交成功',1,'/Growth/Conpay');
                }else{
                    ajaxReturn('提交失败',0);
                }
            }       
        }
    $this->assign('pay_no',$pay_no);
    $this->assign('usdt',$usdt);
    $this->display();
}   
    /**
     * sv钱包记录
     */
    public function svassetwallet(){
        $traInfo = M('tranmoney');
        $uid = session('userid');
        $sv = M('store')->where(array('uid'=>$uid))->getField('sv');
        $this->assign('sv',$sv);
        $where['pay_id|get_id'] = $uid;
        $where['my'] = 1;
		$where['get_type'] = array('in', '1,28,13,9,27,2,34');
        $p = getpage($traInfo, $where, 50);
        $page = $p->show();
        $Chan_info = $traInfo->where($where)->order('id desc')->select();
        foreach ($Chan_info as $k => $v) {
            $Chan_info[$k]['get_timeymd'] = date('Y-m-d', $v['get_time']);
            $Chan_info[$k]['get_timedate'] = date('H:i:s', $v['get_time']);
            //转入转出
            if ($uid == $v['pay_id']) {
                $Chan_info[$k]['trtype'] = 1;
            } else {
                $Chan_info[$k]['trtype'] = 2;
            }
        }
        if (IS_AJAX) {
            if (count($Chan_info) >= 1) {
                ajaxReturn($Chan_info, 1);
            } else {
                ajaxReturn('暂无记录', 0);
            }
        }
        // var_dump($Chan_info);die();
        $this->assign('page', $page);
        $this->assign('Chan_info', $Chan_info);
        $this->assign('uid', $uid);
        $this->display();
    }

    /**
     * sc钱包记录
     */
    public function scassetwallet(){
        $traInfo = M('tranmoney');
        $uid = session('userid');
        $sv = M('store')->where(array('uid'=>$uid))->getField('sc');
        $this->assign('sc',$sv);
        $where['pay_id|get_id'] = $uid;
        $where['my'] = 2;
        $p = getpage($traInfo, $where, 50);
        $page = $p->show();
        $Chan_info = $traInfo->where($where)->order('id desc')->select();
        foreach ($Chan_info as $k => $v) {
            $Chan_info[$k]['get_timeymd'] = date('Y-m-d', $v['get_time']);
            $Chan_info[$k]['get_timedate'] = date('H:i:s', $v['get_time']);
            //转入转出
            if ($uid == $v['pay_id']) {
                $Chan_info[$k]['trtype'] = 1;
            } else {
                $Chan_info[$k]['trtype'] = 2;
            }
        }
        if (IS_AJAX) {
            if (count($Chan_info) >= 1) {
                ajaxReturn($Chan_info, 1);
            } else {
                ajaxReturn('暂无记录', 0);
            }
        }
        // var_dump($Chan_info);die();
        $this->assign('page', $page);
        $this->assign('Chan_info', $Chan_info);
        $this->assign('uid', $uid);
        $this->display();
    }
	
	// ST记录
	public function stassetwallet(){
		$userid = session('userid');
		$dj_num = M('store')->where(array('uid'=>$userid))->getField('dt_jifen');
        $list = M('tranmoney')->where(array('get_id'=>$userid,'get_type'=>36,'my'=>4))->union("select * from ysk_tranmoney where pay_id = {$userid} AND get_type = 9 AND my=4 order by id desc")->select();
        $data = array();
        foreach($list as $k => $v){
            if($v['get_nums'] > 0){
                $mobile = M('user')->where(array('userid'=>$v['pay_id']))->getField('mobile');
                $v['mobile'] = substr_replace($mobile, '****',3,4);
                $data[$k] = $v;
            }elseif($v['get_nums'] < 0){
                $data[$k] = $v;
            }
        }
        $this->assign('list',$data);
		$this->assign('dj_num',$dj_num);
		$this->display();
	}
    
//兑换银积分
    public function exehange()
    {
        $uid = session('userid');
        $minems = M('store')->where(array('uid' => $uid))->find();
        $huan = M('coindets')->where(array('id'=>1))->find();
        $this->assign('huan',$huan);
        if (IS_AJAX) {
            $dhnums = I('dhnums');
            $pwd = I('pwd');
            // if ($dhnums < $minmoney) {
            //     $this->ajaxReturn('最少兑换数量为'.$minmoney.'哦~', 0);
            // }
            // if ($dhnums % 2 != 0) {
            //     $this->ajaxReturn('兑换数量必须为2的倍数哦~', 0);
            // }
			
			if($dhnums < 100){
				ajaxReturn('转账数量不能小于100',0);
			}
			
			if($dhnums % 100 != 0){
				ajaxReturn('转账数量必须为100的倍数',0);
			}
            if ($dhnums > $minems['sv']) {
                ajaxReturn('账户余额不足',0);
            }
            //验证交易密码
            $minepwd = M('user')->where(array('userid' => $uid))->Field('account,mobile,safety_pwd,safety_salt')->find();
            $user_object = D('Home/User');
            $user_info = $user_object->Trans($minepwd['account'], $pwd);


            // $dataget['cangku_num'] = array('exp', "cangku_num - $dhnums");
            // $dataget['fengmi_num'] = array('exp', 'fengmi_num + ' . $canget);
            $huanh = $dhnums*$huan['coin_price'];
            $huanq = M('store')->where(array('uid'=>$uid))->find();//兑换前
            $res_get = M('store')->where(array('uid'=>$uid))->setInc('sc',$huanh);
            $res_gets = M('store')->where(array('uid'=>$uid))->setDec('sv',$dhnums);

           //执行兑换
            if ($res_get&&$res_gets) {
                $datac['pay_id'] = $uid;
                $datac['get_id'] = $uid;
                $datac['now_nums'] = $huanq['sv']-$dhnums;
                $datac['now_nums_get'] = $huanq['sv']-$dhnums;               
                $datac['get_nums'] = '-'.$dhnums;
                $datac['get_time'] = time();
                $datac['get_type'] = 13;
                $datac['status'] = 2;
                $datac['my'] = 1;

               // $pay_n = M('store')->where(array('uid' => $uid))->getfield('fengmi_num');
                $data['pay_id'] = $uid;
                $data['get_id'] = $uid;
                $data['now_nums'] = $huanq['sc']+$huanh;
                $data['now_nums_get'] = $huanq['sc']+$huanh;              
                $data['get_nums'] = '+'.$huanh;
                $data['get_time'] = time();
                $data['get_type'] = 13;
                $data['status'] = 1;
                $data['my'] = 2;
            }

            $add_Detsc = M('tranmoney')->add($datac);

            $add_Dets = M('tranmoney')->add($data);

            if ($add_Dets) {
               
               
               // $this->Manage_reward($uid,$dhnums); //产生管理奖和分享奖
               // $this->Addreas15($uid,$dhnums);//产生区块奖和VIP奖
                //判断用户等级
                // $uChanlev = D('Home/index');
                // $uChanlev->Checklevel($uid);
                ajaxReturn('兑换成功', 1, '/Index/exehange');
            }
        }
        $this->assign('count',(int)$count);
        $this->assign('minmoney',$minmoney);
        $this->assign('minems', $minems);
        $this->display();
    }
  
    //银积分记录
    public function Exchangerecords()
    {   
        $uid = session('userid');
        $type = I('get.type');
        $where['get_id|pay_id'] = $uid;
        if(empty($type)){
            $where['get_type'] = array('in', '1,23,24');
        }elseif($type == 1){
            // 动态积分
            $where['get_type'] = 29;
        }elseif($type == 2){
            // 排单币
            $where['get_type'] = 27;
        }elseif($type == 3){
            // 激活码
            $where['get_type'] = 28;
        }
        // $where['get_type'] = 1;
        $traInfo = M('tranmoney');
        //分页
        $p = getpage($traInfo, $where, 50);
        $page = $p->show();
        $Chan_info = $traInfo->where($where)->order('id desc')->select();
        foreach ($Chan_info as $k => $v) {
            $Chan_info[$k]['get_nums'] = $v['get_nums'];
            $Chan_info[$k]['get_timeymd'] = date('Y-m-d', $v['get_time']);
            $Chan_info[$k]['get_timedate'] = date('H:i:s', $v['get_time']);
            if ($uid == $v['pay_id']) {
                $Chan_info[$k]['trtype'] = 1;
            } else {
                $Chan_info[$k]['trtype'] = 2;
            }
        }
        if (IS_AJAX) {
            if (count($Chan_info) >= 1) {
                ajaxReturn($Chan_info, 1);
            } else {
                ajaxReturn('暂无记录', 0);
            }
        }
        $this->assign('uid', $uid);
        $this->assign('Chan_info', $Chan_info);
        $this->assign('page', $page);
        $this->display();
    }

    


    //获取仓库数据
    public function StoreData()
    {
        if (!IS_AJAX) {
            return false;
        }
        $store = D('Store');
        $userid = get_userid();
        $where['uid'] = $userid;
        $s_info = $store->field('cangku_num,fengmi_num,plant_num,huafei_total')->where($where)->find();

        $data['cangku'] = $s_info['cangku_num'] + 0;
        // $data['fengmi']=$s_info['fengmi_num']+0;
        $data['plant'] = $s_info['plant_num'] + 0;
        // $data['huafei_total']=$s_info['huafei_total']+0;
        // $data['total']=$s_info['cangku_num']+$s_info['plant_num'];
        $this->ajaxReturn($data);
    }

    //果树数据
    public function landdata()
    {
        if (!IS_AJAX) {
            return false;
        }
        $table = M('nzusfarm');
        $uid = session('userid');
        $where['uid'] = $uid;
        $where['status'] = array('gt', 0);
        $info = $table->field('id,seeds+fruits as num,farm_type type,status')->where($where)->order('id')->select();
        if ($info) {
            $this->ajaxReturn($info);
        }

    }


    public function tooldata()
    {
        if (!IS_AJAX) {
            return false;
        }

        $tree = M('config')->where(array('id' => array('in', array(8, 10, 12, 36))))->order('id asc')->field('value as price,id')->select();
        $tool = M('tool')->field('t_num as price,id')->order('id asc')->select();
        $data = array_merge($tree, $tool);
        if (empty($data)) {
            ajaxReturn('数据加载失败');
        } else {
            ajaxReturn('数据加载成功', 1, '', $data);
        }
    }

    //一键采蜜和狗粮
    public function onefooddata()
    {
        if (!IS_AJAX) {
            return false;
        }

        $where['uid'] = session('userid');
        $data = M('user_tool_month')->field('oneclick one,end_oneclick_time endo,dogfood food,end_dogfood_time endf')->where($where)->find();

        if (empty($data)) {
            ajaxReturn(null);
        } else {
            $time = time();
            if ($data['one'] > 0) {
                if ($time > $data['endo'])
                    $data['one_status'] = '已过期';
                else
                    $data['one_status'] = '使用中';

                $data['endo1'] = date('Y-m-d', $data['endo']);
            }
            if ($data['food'] > 0) {
                if ($time > $data['endf'])
                    $data['food_status'] = '已过期';
                else
                    $data['food_status'] = '使用中';

                $data['endf1'] = date('Y-m-d', $data['endf']);
            }
            ajaxReturn('数据加载成功', 1, '', $data);
        }
    }

    /**
     * 站内信
     */
    public function znx()
    {
        if (IS_AJAX) {
            $db_letter = M('nzletter');
            $userid = session('userid');

            $userInfo = session('user_login');

            $data['recipient_id'] = 0;
            $data['send_id'] = $userid;
            $data['title'] = trim(I('post.title'));
            $data['content'] = trim(I('post.content'));
            $data['username'] = $userInfo['username'];
            $data['account'] = $userInfo['account'];

            if (empty($data['title']) || empty($data['content'])) {
                ajaxReturn('标题或内容不能为空');
                return;
            }

            $data['time'] = time();
            $res = $db_letter->data($data)->add();
            if ($res) {
                ajaxReturn('我们已收到，会尽快处理您的问题', 1);
            } else {
                ajaxReturn('提交失败');
            }
        }

    }


    //购买道具
    public function buytool()
    {
        if (!IS_AJAX) {
            return false;
        }

        $id = I('post.id', 0, 'intval');
        $num = I('post.num', 1, 'intval');
        $typetree = I('post.type');
        if (empty($id)) {
            ajaxReturn('参数错误');
        }

        $uid = session('userid');
        if ($typetree == 'tree') {

            if ($id == 8 || $id == 36) {
                $type = 1;
            } elseif ($id == 10) {
                $type = 2;
            } elseif ($id == 12) {
                $type = 3;
            } else {
                ajaxReturn("操作失败");
            }
            //农田里最低的果子数
            $config = D('config');
            $min_guozi = $config->where(array('id' => $id))->getField('value');

            $des_num = $min_guozi;
            $is_land = no_land();
            if ($is_land && $id != 36) {
                $des_num = $des_num + 30;
            }

            $t_info['t_num'] = $des_num;
            $t_info['t_name'] = '树';
            $t_info['t_img'] = '';
            $num = 1;
            $order_type = 1; //树
        } else {

            $t_info = M('tool')->find($id);
            if (empty($t_info)) {
                ajaxReturn('参数错误');
            }

            //判断是否已拥有此道具，如果已拥有，不在购买
            $type = $t_info['t_type'];
            if ($type == 2) {
                $field = $t_info['t_fieldname'];
                $isbuytool = M('user_level')->where(array('uid' => $uid))->getField($field);
                if ($isbuytool > 0) {
                    ajaxReturn('您已经拥有该宠物了哦！');
                }
            }
            $order_type = 0; //道具
        }


        $data['tool_id'] = $id;
        $data['tool_name'] = $t_info['t_name'];
        $data['tool_price'] = $t_info['t_num'];
        $data['tool_img'] = $t_info['t_img'];
        $data['order_status'] = 0;
        $data['order_no'] = date('YmdHis');
        $data['tool_num'] = $num;
        $data['total_price'] = $num * $t_info['t_num'];
        $data['uid'] = $uid;
        $data['order_type'] = $order_type;


        $order = M('order');
        $order->startTrans();
        $res = $order->add($data);
        if ($res) {
            $url = U('Index/orderdetail', array('order_no' => $data['order_no']));
            ajaxReturn('购买成功', 1, $url);
        } else {
            ajaxReturn('购买失败');
            $order->startTrans();
        }
    }


    //选择支付
    public function orderdetail()
    {
        $order_no = I('order_no');
        $order_no = safe_replace($order_no);
        if (empty($order_no)) {
            return false;
        }
        $where['order_no'] = $order_no;
        $where['order_status'] = 0;
        $order = M('order');
        $o_info = $order->where($where)->find();
        if (empty($o_info)) {
            return false;
        }
        $uid = session('userid');
        $cangku_num = M('store')->where(array('uid' => $uid))->getField('cangku_num');
        $this->assign('o_info', $o_info)->assign('cangku_num', $cangku_num)->display();

    }

    public function gopay()
    {
        if (!IS_POST) {
            return false;
        }

        $order_paytype = I('post.paytype');
        $type_arr = array(1, 2, 3);
        if (!in_array($order_paytype, $type_arr)) {
            ajaxReturn('请选择支付方式');
        }
        $order_no = I('post.order_no');
        $order_no = safe_replace($order_no);
        if (empty($order_no)) {
            ajaxReturn('订单不存在');
        }
        $where['order_no'] = $order_no;
        $where['order_status'] = 0;
        $order = M('order');
        $count = $order->where($where)->count(1);
        if ($count == 0) {
            ajaxReturn('该订单已失效，请重新下单');
        }

        $arr = array(1 => '微信支付', 2 => '支付宝支付', 3 => '果子支付');
        $res = $order->where($where)->setField('order_paytype', $arr[$order_paytype]);
        $wxurl = 'http://yxgsgy.com/wxPay/example/jsapi.php?order_no=' . $order_no;
        $arr_url = array(1 => $wxurl, 2 => '', 3 => U('Ajaxdz/kaiken'));
        if ($res === false) {
            ajaxReturn('下单失败');
        } else {
            ajaxReturn('', 1, $arr_url[$order_paytype]);
        }
    }

    public function DogEat()
    {

        $uid = session('userid');

        $eat = M('user_dogeat');
        $pcount = $eat->where(array('uid' => $uid, 'datestr' => date('Ymd')))->count(1);
        if ($pcount > 0) {
            ajaxReturn('今天已经喂食过了哦');
        }

        $where['uid'] = $uid;
        $dog = M('user_level')->where($where)->getField('zangao_num');
        if ($dog) {
            //判断是否过期
            $table = M('user_tool_month');
            $where['dogfood'] = array('gt', 0);
            $info = $table->where($where)->field('dogfood,end_dogfood_time')->find();
            if (empty($info)) {
                ajaxReturn('您还没狗粮哦，赶紧去购买吧');
            }
            $time = time();
            if ($info['end_dogfood_time'] < $time) {
                ajaxReturn('狗粮没有了，赶紧去购买吧');
            }

            $eat = M('user_dogeat');
            $count = $eat->where(array('uid' => $uid))->count(1);
            $data['uid'] = $uid;
            if ($count == 0) {
                $eat->add($data);
            }
            $data['datestr'] = date('Ymd');
            $data['create_time'] = time();
            $res = $eat->where(array('uid' => $uid))->save($data);
            if ($res)
                ajaxReturn('喂食成功！', 1);
            else
                ajaxReturn('喂食失败！');
        }
    }
	/**
     * 闪兑sv卖出
     */
    public function selloutsv(){
        $uid = session('userid');

        $cm = M('coindets');
        $yue = M('store')->where(array('uid'=>$uid))->getField('sc');
        $usdt = $cm->where('id=4')->getField('coin_price');
        $usdtsx = $cm->where('id=5')->getField('coin_price');
        $id = uniqid();
        $time = date('d-s',time());
        $pay_no = $time.$id;
        if(IS_POST){
            $uid = session('userid');
            $data['pay_no'] = I('pay_no');
            $pay_num = I('number');
            $res = M('coindets')->where('id=4')->getField('coin_price');
            $res1 =  M('coindets')->where('id=5')->getField('coin_price');
            $usdthl = $res*$pay_num/100;
            $ksxf = $res1*$usdthl;
            $data['pay_num'] = $pay_num+$ksxf;
            $data['pay_time'] = time();
            $pwd = I('pwd');
            $data['uid'] = $uid;
            $data['usdt'] = I('usdtaddress');
            $data['type'] = 2;

            if($data['pay_num']==null){
                ajaxReturn('请填写数量',0);
            }
            if($data['usdt']==null){
                ajaxReturn('usdt地址为空',0);
            }
            if($data['pay_num']>$yue){
                ajaxReturn('账户余额不足',0);
            }
            $minepwd = M('user')->where(array('userid' => $uid))->Field('account,mobile,safety_pwd,safety_salt')->find();
            $user_object = D('Home/User');
            $user_info = $user_object->Trans($minepwd['account'], $pwd);

            $ress = M('store')->where(array('uid'=>$uid))->setDec('sc',$data['pay_num']);
            $shandui['pay_id'] = $uid;
            $shandui['get_id'] = $uid;
            $shandui['get_nums'] = '-'.$data['pay_num'];
            $shandui['get_time'] = time();
            $shandui['get_type'] = 34;
            $shandui['status'] = 2;
            $shandui['my'] = 2;
            $ress2 = M('tranmoney')->add($shandui);
            $ress3 = M('shandui')->add($data);
            if($ress&&$ress2&&ress3){
                ajaxReturn('提交成功',1);
            }
        }
        $userusdt = M('user')->where(array('userid'=>$uid))->getField('usdt');
        $this->assign('userusdt',$userusdt);
        $this->assign('pay_no',$pay_no);
        $this->assign('usdtsx',$usdtsx);
        $this->assign('usdt',$usdt);
        $this->assign('yue',$yue);
        
        $this->display();
    }
}