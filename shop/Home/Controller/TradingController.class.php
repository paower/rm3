<?php
namespace Home\Controller;
use Think\Controller;
class TradingController extends CommonController {

	public function upgradeok()
	{
		$id = session('userid');
		$wait = M('upgrade')->where("uid=$id and state=1")->select();
		foreach ($wait as $k => $v) {
			$shouid=$v['shouid'];
			$pid = M('user')->where("userid=$shouid")->getField('pid');
			$mobile = M('user')->where("userid = $shouid")->getField('sk_mobile');
			$wx = M('user')->where("userid = $shouid")->getField('wx');
			$wait[$k]['mobile'] = $mobile;
			$wait[$k]['wx'] = $wx;
		}
		$ok = M('upgrade')->where("uid=$id and state=2")->select();
		$money = D('config')->where("name='money'")->getField('value');
		//收款人信息
		  foreach ($ok as $k => $v) {
		   # code...
		   $uid = $v['shouid'];
		   $ok[$k]['mobile'] = M('user')->where("userid = $uid")->getField('mobile');
		   $ok[$k]['username'] = M('user')->where("userid = $uid")->getField('username');
		   $ok[$k]['wx'] = M('user')->where("userid = $uid")->getField('wx');
		  }
		$this->assign('money',$money);
		$this->assign('wait',$wait);
		$this->assign('ok',$ok);
		$this->display();
	}
	/**
	 * 上传收款码
	 */
	public function uplodeimg(){
		$data=img_uploading();
		echo json_encode($data);
	}

	// 动态收益
	/*
	*@ $userid int 预约用户
	*@ $pay_nums  预约合算总金额 
	 */
	public function DynamicRevenue($userid,$pay_nums){
		$list = $this->getParents($userid);
		foreach($list as $k => $v){
			$shao = M('trans')->where(array('payin_id'=>$v['userid']))->order('id asc')->getField('pay_nums');
			if($shao<$pay_nums){
				$pay_nums = $shao;
			}
		 	if($v['level'] > 0 && $v['child_count'] >= 1){
		 		if($v['level'] == 1 && $v['child_count'] >=1){
		 			$get_dt_jifen = $pay_nums * 0.08;
		 		}elseif($v['level'] == 2 && $v['child_count'] >=2){
		 			$get_dt_jifen = $pay_nums * 0.03;
		 		}elseif($v['level'] >= 3 && $v['level'] <= 5 && $v['child_count'] >= 3){
		 			$get_dt_jifen = $pay_nums * 0.01;
		 		}elseif($v['level'] >= 6 && $v['level'] <= 10 && $v['child_count'] >= 5){
		 			$get_dt_jifen = $pay_nums * 0.005;
		 		}
				$map = "pay_state=2 OR pay_state=3 AND payin_id={$v['userid']}";
				$youxiao = M('trans')->where($map)->order('id asc')->find();
		 		if(!empty($youxiao)&&$get_dt_jifen > 0){
			 		$dyna1 = M('store')->where(array('uid'=>$v['userid']))->getField('dt_jifen'); 
					$fenghao = M('user')->where(array('userid'=>$v['userid']))->getField('status');
					$shou = M('trans')->where(array('payin_id'=>$v['userid']))->order('id asc')->find();
					if($shou['pay_state']==3&&$fenghao!=0){
						$res = M('store')->where(array('uid'=>$v['userid']))->setInc('dt_jifen',$get_dt_jifen);
						$arr2 = array(
							'pay_id' => $userid,
							'get_id' => $v['userid'],
							'get_nums' => $get_dt_jifen,
							'get_time' => time(),
							'get_type'  => 36,
							'my'=>4,
							'status'=>1
						);
						$res3 = M('tranmoney')->add($arr2);
					}
				}
		 		
		 	}
		}
	}

	/**
	 * 确认收款
	 */
	Public function Paidimg(){
		$id = I('get.id');
		$a['id'] = $id;
		$paytime = M('upgrade')->where($a)->getField('paytime');
		$auto = M('config')->where("name='dj_time'")->getField('value');
		$auto = $auto*60*60;
		$autotime = $paytime+$auto;
		$daojishi = $autotime-time();
		$this->assign('daojishi',$daojishi);		
		$src = M('upgrade')->where($a)->getField('src');
		$money = D('upgrade')->where($a)->getField('money');
		$this->assign('money',$money);
		$this->assign('src',$src);	
		$this->display();
	}
	public function SellCentr(){
	   


		//是否有设置默认银行卡
		$uid = session('userid');
		$cid = trim(I('cid'));
		$store1 = M('store')->where(array('uid'=>$uid))->find();
		$this->assign('store1',$store1);
		if(empty($cid)){
			$mapcas['user_id&is_default'] =array($uid,1,'_multi'=>true);
			$carinfo = M('ubanks')->where($mapcas)->count(1);
			if($carinfo < 1){
				$morecars = M('ubanks as u')->join('RIGHT JOIN ysk_bank_name as banks ON u.card_id = banks.pid' )->where(array('u.user_id'=>$uid))->limit(1)->field('u.hold_name,u.id,u.card_number,u.user_id,banks.banq_genre')->find();
			}else{
				$morecars = M('ubanks as u')->join('RIGHT JOIN ysk_bank_name as banks ON u.card_id = banks.pid' )->where(array('u.user_id'=>$uid,'is_default'=>1))->limit(1)->field('u.hold_name,u.id,u.card_number,u.user_id,banks.banq_genre')->find();
			}
		}else{
			$morecars = M('ubanks as u')->join('RIGHT JOIN ysk_bank_name as banks ON u.card_id = banks.pid' )->where(array('u.id'=>$cid))->limit(1)->field('u.hold_name,u.id,u.card_number,u.user_id,banks.banq_genre')->find();
		}
		$existence = M('user')->where(array('userid'=>$uid))->find();

		// 判断用户需要买入第二单才能卖出
		// 1.获取上一单卖出和买入
		// 上一单卖出

		//生成出售订单
		if(IS_AJAX){
			// $kaiguan = M('config')->where('name="djmc"')->getField('value');
			// if ($kaiguan==1) {
			// 	$lastsale = M('trans')->where('(payin_id='.$uid.' and pay_state=3) or payout_id='.$uid)->order('pay_time desc')->getField('trans_type');
			// 	// dump(M('trans')->getLastSql());exit;
			// 	if ($lastsale!=='0') {
			// 		ajaxReturn('必须要有一单在冻结状态才能卖出');
			// 	}
			// }
			

			$pwd = trim(I('pwd'));
			$sellnums = trim(I('sellnums'));//出售数量
			//$cardid = trim(I('cardid'));//银行卡id
			// var_dump($sellnums);exit;
			$my = I('my');
			
			$uid = session('userid');
			// 判断距离上一单转让是否有24小时
			$last_trans_time = M('trans')->where(array('payout_id'=>$uid,'trans_type'=>1))->order('pay_time desc')->getField('pay_time');
			$last_trans_time = $last_trans_time + 24 * 3600;

			if(time() <= $last_trans_time){
				ajaxReturn('24小时后才能进行转让',0);
			}

			$sellcentr_start = D('config')->where(array('name'=>'sellcentr_start'))->getField('value');
	        $sellcentr_end = D('config')->where(array('name'=>'sellcentr_end'))->getField('value');

	        $startTime = strtotime(date('Y-m-d '.$sellcentr_start));
	        $endTime = strtotime(date('Y-m-d '.$sellcentr_end));
	        if(time() < $startTime || time() >$endTime){
	        	ajaxReturn('只能在'.$sellcentr_start.'到'.$sellcentr_end.'这段时间内进行转让',0);
	        }

			//判断自己是否已激活
			$is_jihuo = M('user')->where(array('userid'=>$uid))->getField('is_jihuo');
			if($is_jihuo == 0){
				ajaxReturn('你未激活,请先前往激活',1,U('User/activation'));
			}


			//自己是否有足够余额
			$is_enough = M('store')->field('sc,dt_jifen,cangku_num')->where(array('uid'=>$uid))->find();
			if ($my==1) {
				if($sellnums > $is_enough['sc']){
					ajaxReturn('您当前账户暂无这么多余额~',0);
				}
				if($sellnums < 100){
					ajaxReturn('SC转让金额不得小于100',0);
				}
				if($sellnums%100 != 0){
					ajaxReturn('SC转让金额必须为100的倍数',0);
				}
			}else{

				if($sellnums > $is_enough['dt_jifen']){
					ajaxReturn('您当前账户暂无这么多余额~',0);
				}
				if($sellnums < 500){
					ajaxReturn('ST转让金额不得小于500',0);
				}
				if($sellnums%500 != 0){
					ajaxReturn('ST转让金额必须为500的倍数',0);
				}
			}
			
			if($existence['card_number'] == null || $existence['wx']==null || $existence['zfb']==null){
				ajaxReturn('请先认证收款方式',0);
			}
			
			//验证银行卡是否是自己
			// $id_Uid = M('ubanks')->where(array('id'=>$cardid))->getField('user_id');
			// if($id_Uid != $uid){
			// 	ajaxReturn('对不起,该张银行卡不是您的哦~',0);
			// }
			//验证交易密码
			$minepwd = M('user')->where(array('userid'=>$uid))->Field('account,mobile,safety_pwd,safety_salt')->find();
			$user_object = D('Home/User');
			$user_info = $user_object->Trans($minepwd['account'], $pwd);
			//生成订单
			if($my == 1){
				$data['pay_no'] = build_order_no();
			}else{
				$data['pay_no'] = build_order_st();
			}
			$data['payout_id'] = $uid;
			//$data['card_id'] = $cardid;
			$data['pay_nums'] = $sellnums;
			$data['pay_time'] = time();
			$data['trans_type'] = 1;
			if ($my==1) {
				$data['my'] = 2;
			}else{
				$data['my'] = 4;
			}
			$res_Add = M('trans')->add($data);

			//添加卖出余额记录 扣余额及10%手续费

			// $jifen_nums = $sellnums+$sellnums*0.1;

			   
			//给自己减少这么多余额
			if($res_Add){
				// $sellnums = $sellnums + $sellnums*0.1;
			
				if ($my==1) {
					 $doDec = M('store')->where(array('uid'=>$uid))->setDec('sc',$sellnums);
						$pay_n = M('store')->where(array('uid' => $uid))->getfield('sc');
				}else{
					$doDec = M('store')->where(array('uid'=>$uid))->setDec('dt_jifen',$sellnums);
					$pay_n = M('store')->where(array('uid' => $uid))->getfield('dt_jifen');
				}

				$jifen_dochange['now_nums'] = $pay_n;
				$jifen_dochange['now_nums_get'] = $pay_n;
				$jifen_dochange['is_release'] = 1;
				$jifen_dochange['pay_id'] = $uid;
				$jifen_dochange['get_id'] = 0;
				$jifen_dochange['get_nums'] = '-'.$sellnums;
				$jifen_dochange['get_time'] = time();
				$jifen_dochange['get_type'] = 9;
				if($my == 1){
					$jifen_dochange['my'] = 2;	
				}else{
					$jifen_dochange['my'] = 4;
				}
				
				$res_addres = M('tranmoney')->add($jifen_dochange);

				ajaxReturn('订单创建成功',1,U('Trading/Nofinsh'));
			}
		}
		//$this->assign('existence',$existence);
		$this->assign('issell',$issell);
		$this->assign('morecars',$morecars);
		$this->display();
	}

	//未完成订单
	public function Nofinsh(){
		//$state = trim(I('state'));
		$uid = session('userid');
		$info = M('upgrade')->where("shouid=$uid and state=2")->order('paytime desc')->select();
		// dump($info);die;
		$this->assign('info',$info);
		$traInfo = M('trans');
		// if($state > 0){
		// 	$where['pay_state'] =  array('between','1,2');
		// }else{
		// 	$where['pay_state'] = 0;
		// }
		$where['pay_state'] = array('neq',6);
		$where['payout_id'] = $uid;

		//分页 
		$p=getpage($traInfo,$where,20);
		$page=$p->show();
		//$orders = $traInfo->where(array('payout_id'=>$uid))->order('id desc')->select();
		$orders = M('trans')->where(array('payout_id'=>$uid))->order('pay_state asc,id desc')->select();
		if(!empty($orders)){
				foreach($orders as $k =>$v){
				$v['time'] = $v['dakuan_time']-time();
				if($v['time']>0){
					//计算天数
					$v['day'] = intval($v['time']/86400);
					
					//计算小时
					$remain = $v['time']%86400;
					$v['house'] = intval($remain/3600);

					//计算分钟
					$remain = $v['time']%3600;
					$v['min'] = intval($remain/60);

					//计算秒
					$remain = $v['time']%60;
					$v['seconds'] = intval($remain);
				}
			}
			$orders[$k]['day'] = $v['day'];
			$orders[$k]['house'] = $v['house'];
			$orders[$k]['min'] = $v['min'];
			$orders[$k]['seconds'] = $v['seconds'];
			$this->assign('orders',$orders);
		}
		
		$orders[$k]['day'] = $v['day'];
		$orders[$k]['house'] = $v['house'];
		$orders[$k]['min'] = $v['min'];
		$orders[$k]['seconds'] = $v['seconds'];
		$this->assign('state',$state);
		
		$this->assign('page',$page);
		$this->display();
	}

	//上传付款凭证
	public function Conpayd(){
		//查询我买入的
		$id = I('get.id');
		$uid = session('userid');
		$traInfo = M('trans');
		$banks = M('ubanks');
		$where['payout_id'] = $uid;
		$where['pay_state'] = array('in','1,2');
		//$where['pay_state'] = 1;
		$where['id'] = $id;
		//分页
		$p=getpage($traInfo,$where,20);
		$page=$p->show();
		$orders = $traInfo->where($where)->find();
		//var_dump($orders);die();
		//收款人

		//银行卡号.开户支行.开户银行
		$v['time'] = $orders['dakuan_time']-time();
		
		if($v['time']>0){
				//计算小时
				$remain = $v['time']%86400;
				$v['house'] = intval($remain/3600);

				//计算分钟
				$remain = $v['time']%3600;
				$v['min'] = intval($remain/60);

				//计算秒
				$remain = $v['time']%60;
				$v['seconds'] = intval($remain);
		}
		$uinfomsg = M('user')->where(array('userid'=>$orders['payin_id']))->Field('username,mobile,card_number,zfb,wx,open_card,usdt,bank_uname,bank_id')->find();
		$orders['cardnum'] = $uinfomsg['card_number'];
		$orders['yinh'] = M('bank_name')->where(array('q_id'=>$uinfomsg['bank_id']))->getField('banq_genre');
		$orders['bname'] = $uinfomsg['bank_uname'];
		$orders['openrds'] = $uinfomsg['open_card'];
		$orders['card_number'] = $uinfomsg['card_number'];
		$orders['uname'] = $uinfomsg['username'];
		$orders['umobile'] = $uinfomsg['mobile'];
		$orders['zfb'] = $uinfomsg['zfb'];
		$orders['wx'] = $uinfomsg['wx'];
		$orders['usdt'] = $uinfomsg['usdt'];
		$orders['house'] = $v['house'];
		$orders['min'] = $v['min'];
		$orders['seconds'] = $v['seconds'];
	 //var_dump($orders);die();
		
		$this->assign('page',$page);
		$this->assign('orders',$orders);
		$this->display();
	}

	// 投诉
	public function tousu(){
		$id = I('post.id');
		$check = M('trans')->where('id='.$id.' and payout_id='.session('userid'))->getField('pay_state');
		if ($check!=5) {
			$res = M('trans')->where('id='.$id)->save(array('pay_state'=>4));
			if ($res) {
				ajaxReturn('投诉成功,已提交后台处理',1,'/Growth/Conpay');
			}else{
				ajaxReturn('投诉失败,您已经被投诉',0);
			}
		}else{
			ajaxReturn('参数错误',0);
		}
	}

	//取消订单
 public function quxiao_order(){

	$id = (int)I('id','intval',0);
	$uid = session('userid');
	$mydeal = M('trans')->where(array("id"=>$id,"payin_id|payout_id"=>$uid,"pay_state"=>array("lt",2)))->find();

	 if(!$mydeal)ajaxReturn('订单不存在~',0);

	$type=$mydeal["trans_type"];
	M('trans_quxiao')->add($mydeal);//把记录复制到另一个表


	if($type==0){//卖出单，删除订单


			//var_dump($res1);die;

			$res1 = M('trans')->delete($id); 
  

			if($res1){
				$sellnums = $mydeal["pay_nums"] + $mydeal['pay_nums']*0.1;

				$doDec = M('store')->where(array('uid'=>$uid))->setInc('cangku_num',$sellnums);

				//增加自己的余额记录

				$pay_n = M('store')->where(array('uid' => $uid))->getfield('cangku_num');
				$jifen_dochange['now_nums'] = $pay_n;
				$jifen_dochange['now_nums_get'] = $pay_n;
				$jifen_dochange['is_release'] = 1;
				$jifen_dochange['pay_id'] = 0;
				$jifen_dochange['get_id'] = $uid;
				$jifen_dochange['get_nums'] = $sellnums;
				$jifen_dochange['get_time'] = time();
				$jifen_dochange['get_type'] = 10;
				$res_addres = M('tranmoney')->add($jifen_dochange);
  
			}
		 


	}elseif($type==1){//为购买单，自己是卖出方，清空payout_id和改变pay_state为0并返回全部余额


				$sellnums = $mydeal["pay_nums"] + $mydeal['pay_nums']*0.1;

				$doDec = M('store')->where(array('uid'=>$uid))->setInc('cangku_num',$sellnums);

				//增加自己的余额记录

				$pay_n = M('store')->where(array('uid' => $uid))->getfield('cangku_num');
				$jifen_dochange['now_nums'] = $pay_n;
				$jifen_dochange['now_nums_get'] = $pay_n;
				$jifen_dochange['is_release'] = 1;
				$jifen_dochange['pay_id'] = 0;
				$jifen_dochange['get_id'] = $uid;
				$jifen_dochange['get_nums'] = $sellnums;
				$jifen_dochange['get_time'] = time();
				$jifen_dochange['get_type'] = 10;
				$res_addres = M('tranmoney')->add($jifen_dochange);

			$payout['payout_id'] =0;
			$payout['pay_state'] =0;
			$res1 = M('trans')->where(array('id'=>$id))->save($payout); 


	}

		if($res1){       
		ajaxReturn('取消成功',1);
		}else{
		ajaxReturn('操作失败',1);
		}
}




	//已完成订单
	public function Dofinsh(){
		//查询我买入的
		$uid = session('userid');
		$traInfo = M('trans');
		$banks = M('ubanks');
		$where['payout_id'] = $uid;
		$where['pay_state'] = 3;
		//分页
		$p=getpage($traInfo,$where,20);
		$page=$p->show();
		$orders = $traInfo->where($where)->order('id desc')->select();
		//收款人
		foreach($orders as $k =>$v){
			//银行卡号.开户支行.开户银行
			$bankinfos = $banks ->where(array('id'=>$v['card_id']))->field('hold_name,card_number,card_id,open_card')->find();
			$uinfomsg = M('user')->where(array('userid'=>$v['payin_id']))->Field('username,mobile')->find();
			$orders[$k]['cardnum'] = $bankinfos['card_number'];
			$orders[$k]['bname'] = M('bank_name')->where(array('q_id'=>$bankinfos['card_id']))->getfield('banq_genre');
			$orders[$k]['openrds'] = $bankinfos['open_card'];
			$orders[$k]['uname'] = $uinfomsg['username'];
			$orders[$k]['umobile'] = $uinfomsg['mobile'];
		}
		$this->assign('page',$page);
		$this->assign('orders',$orders);
		$this->display();
	}


 
	public function Buyrecords(){
		$traInfo = M('trans');
		$uid = session('userid');
		$where['payin_id'] = $uid;
		//分页
		$p=getpage($traInfo,$where,20);
		$page=$p->show();
		$Chan_info = $traInfo->where($where)->order('id desc')->select();
		foreach ($Chan_info as $k =>$v){
			$Chan_info[$k]['username'] = M('user')->where(array('userid'=>$v['payout_id']))->getField('username');
			$Chan_info[$k]['get_timeymd'] = date('Y-m-d',$v['pay_time']);
			$Chan_info[$k]['get_timedate'] = date('H:i:s',$v['pay_time']);
		}
		if(IS_AJAX){
			if(count($Chan_info) >= 1) {
				ajaxReturn($Chan_info,1);
			}else{
				ajaxReturn('暂无记录',0);
			}
		}
		$this->assign('page',$page);
		$this->assign('Chan_info',$Chan_info);
		$this->assign('uid',$uid);
		$this->display();
	}


//卖出记录
	public function Sellerrecords(){
		$traInfo = M('trans');
		$uid = session('userid');
		$where['payout_id'] = $uid;
		//分页
		$p=getpage($traInfo,$where,20);
		$page=$p->show();
		$Chan_info = $traInfo->where($where)->order('id desc')->select();
		foreach ($Chan_info as $k =>$v){
			$Chan_info[$k]['username'] = M('user')->where(array('userid'=>$v['payin_id']))->getField('username');
			$Chan_info[$k]['get_timeymd'] = date('Y-m-d',$v['pay_time']);
			$Chan_info[$k]['get_timedate'] = date('H:i:s',$v['pay_time']);
		}
		if(IS_AJAX){
			if(count($Chan_info) >= 1) {
				ajaxReturn($Chan_info,1);
			}else{
				ajaxReturn('暂无记录',0);
			}
		}
		$this->assign('page',$page);
		$this->assign('Chan_info',$Chan_info);
		$this->assign('uid',$uid);
		$this->display();
	}


	//确认收到款
	public function Con_Getmoney(){
		$uid = session('userid');
		$trid = I('trid','intval',0);
		$traninfo = M('trans');
		if($trid < 1){
			ajaxReturn('请选择证正确的订单',0);
		}
		$order_info = $traninfo->where(array('id'=>$trid))->find();
		$config = M('config')->where(array('name'=>'djkg'))->Field('name,value')->find();

		if($config['value']==0){
			$guan = $traninfo->where(array('id'=>$trid))->save(array('is_dj'=>0));
		}else{
			$kai = 1;
		}//dump($guan);dump($kai);exit;
		if($order_info['pay_state'] != 2){
			ajaxReturn('该订单状态不正确',0);
		}

		if ($kai) {
			$t = time();
			$dj_time = $order_info['dj_time'];
			$lingqu = M('trans')->where(array('id'=>$trid))->getField('is_linqu');
			if($t<$dj_time){
				$r = $traninfo->where(array('id'=>$trid))->save(array('pay_state'=>3,'is_linqu'=>1));
				$res_pay = true;
			}else{
				$shengyu = $t%60*60*24;
				if ($shengyu>20) {
					$shengyu=20;
				}
				$interest = M('config')->where(array('name'=>'interest'))->getField('value');
				$lixi = $order_info['pay_nums']*$shengyu*$interest/100;
				//给用户加余额
				$paynums = $order_info['pay_nums']+$lixi+$order_info['jiangli'];
				$datapay['cangku_num'] = $paynums;
				$res_pay = M('trans')->where(array('id'=>$trid))->setInc('cunchu_num',$paynums);
			}
			
		}else{
			$rr = $traninfo->where(array('id'=>$trid))->save(array('pay_state'=>3));
			$paynums = $order_info['pay_nums'];
			$datapay['cangku_num'] = $paynums;
			$res_pay = M('store')->where(array('uid'=>$order_info['payin_id']))->setInc('cangku_num',$paynums);
		}

		// 推荐人动态积分增加
		$aa = M('trans')->where(array('payin_id'=>$uid))->order('id desc')->find();
		$shang = M('trans')->where(array('pay_no'=>$aa['pay_no']))->order('id desc')->sum('pay_nums');
		$smai = M('trans')->where(array('pay_state'=>3,'payout_id'=>$uid,'dt_jifen'=>0))->sum('pay_nums');
		$userid = M('user')->field('userid,pid')->where('userid='.$uid)->find();
		// dump($shang);dump($smai);
		if ($smai>=$shang) {
			$mail = 5/100*$smai;
			M('store')->where('uid='.$userid['pid'])->setInc('dt_jifen',$mail);
			M('trans')->where(array('pay_state'=>3,'payout_id'=>$uid,'dt_jifen'=>0))->setField('dt_jifen',1);
		}

		$tramsg['pay_state'] = 3;
		$tramsg['get_moneytime'] = time();
		$res_suc = $traninfo->where(array('id'=>$trid))->save($tramsg);

		if($res_suc && $res_pay){
			ajaxReturn('确认收款成功',1);
		}else{
			ajaxReturn('确认收款失败',0);
		}
	}


	//卖出中心
	public function Selldets(){
		if(IS_AJAX){
			$pricenum = I('mvalue');
			if($pricenum == ''){
				ajaxReturn('请选择正确的订单价格',0);
			}
			$order_info = M('trans as tr')->join('LEFT JOIN  ysk_user as us on tr.payin_id = us.userid')->where(array('tr.pay_state'=>0,'tr.trans_type'=>1,'tr.pay_nums'=>$pricenum))->order('id desc')->select();

			foreach($order_info as $k => $v){
				$order_info[$k]['cardinfo'] = M('bank_name')->where(array('q_id'=>$v['card_id']))->getfield('banq_genre');
				// $order_info[$k]['spay'] = $v['pay_nums'] * 0.9;
			}
			if(count($order_info) <= 0){
				ajaxReturn('没找到相关记录',0);
			}else{
				ajaxReturn($order_info,1);
			}
		}
		$this->display();
	}

	//执行卖出
	public function Dosells(){
		if(IS_AJAX){
			$uid = session('userid');
			$trid = I('trid',1,'intval');
			$pwd = trim(I('pwd'));
			$sellnums = M('trans')->where(array('id'=>$trid))->field('pay_nums,payin_id,pay_state')->find();

			$sellAll = array(500,1000,3000,5000,10000,30000);
			if (!in_array($sellnums['pay_nums'], $sellAll)) {
				ajaxReturn('您选择购买的金额不正确',0);
			}
			if($sellnums['payin_id'] == $uid){
				ajaxReturn('您不能自己购买哦~',0);
			}
			if($sellnums['pay_state'] != 0){
				ajaxReturn('该订单存在异常,暂时无法购买哦~',0);
			}
			//验证交易密码
			$minepwd = M('user')->where(array('userid'=>$uid))->Field('account,mobile,safety_pwd,safety_salt')->find();
			$user_object = D('Home/User');
			$user_info = $user_object->Trans($minepwd['account'], $pwd);
				//自己是否有足够余额
				$is_enough = M('store')->where(array('uid'=>$uid))->getField('cangku_num');
				$shouldpay = $sellnums['pay_nums'] + 100;
				if($shouldpay > $is_enough){
					ajaxReturn('您当前账户暂无这么多余额~',0);
				}
				//是否绑定银行卡
				$id_setcards = M('ubanks')->where(array('user_id'=>$uid,'is_default'=>1))->count('1');
				if($id_setcards < 1){
					$id_setcards = M('ubanks')->where(array('user_id'=>$uid))->limit(1)->find();
				}
				if(empty($id_setcards)){
					ajaxReturn('对不起,您还没用绑定银行卡哦~',0);
				}
				$doDec = M('store')->where(array('uid'=>$uid))->setDec('cangku_num',$shouldpay);
	  
				$pay_n = M('store')->where(array('uid' => $uid))->getfield('cangku_num');
				$jifen_dochange['now_nums'] = $pay_n;
				$jifen_dochange['now_nums_get'] = $pay_n;
				$jifen_dochange['is_release'] = 1;
				$jifen_dochange['pay_id'] = $uid;
				$jifen_dochange['get_id'] = 0;
				$jifen_dochange['get_nums'] = $shouldpay;
				$jifen_dochange['get_time'] = time();
				$jifen_dochange['get_type'] = 9;
				$jifen_dochange['status'] = 2;
				$res_addres = M('tranmoney')->add($jifen_dochange);

				//记录买入会员
				$res_Buy = M('trans')->where(array('id'=>$trid))->setField(array('payout_id'=>$uid,'pay_state'=>1,'card_id'=>$id_setcards['id'],'fee_nums'=>100));
				if($res_Buy){
					ajaxReturn('卖出成功',1);
				}
		}
		$this->display();
	}
	public function freebuy(){
		if(!IS_AJAX){
			return false;
		}
		$userid=session('userid');
		$table=D('TraingFree');
		$where['sell_id']=array('neq',$userid);
		$where['status']=0;
		$p = I('p','0','intval');
		$page=$p*10;
		$info=$table->field('FROM_UNIXTIME(create_time,"%Y-%m-%d") tt,num sellnum,id,sell_account u_account,sell_username u_username,status zhuangtai')->where($where)->order('id desc')->limit($page,10)->select();
		if(empty($info)){
		   $info=null; 
		}
		$this->ajaxReturn($info);
	}


	//定向交易待收款
	 public function directwait(){

		$table=D('Trading');
		$userid=session('userid');
		$where='(sell_id = '.$userid.' AND status IN (0,1)) OR (buy_id ='.$userid.' AND status IN (0,1))';

		$p = I('p','0','intval');
		$page=$p*10;
		$info=$table->field('id,num,sell_id s_id,sell_account s_account,sell_username s_username,buy_id b_id,buy_account b_account,buy_username b_username,FROM_UNIXTIME(create_time,"%Y-%m-%d") tt,status,img')->where($where)->order('id desc')->limit($page,10)->select();
		if(empty($info)){
		   $info=null; 
		}
		$this->ajaxReturn($info);
	}

}