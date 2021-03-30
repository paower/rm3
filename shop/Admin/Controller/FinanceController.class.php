<?php

namespace Admin\Controller;

use Think\Page;

/**
 * 用户控制器
 *
 */
class FinanceController extends AdminController
{

	// 驳回
	public function quxiao()
	{
		$id = I('id');
		$a['id'] = $id;
		$data['state'] = 0;
		$data['src'] = "";
		M('upgrade')->where($a)->save($data);
		$this->redirect('finance/list2');
	}
	/**
	 * 交易匹配
	 *
	 */
	public function index()
	{
		// 获取所有用户
		$map['status'] = array('egt', '0'); // 禁用和正常状态
		$map['trans_type'] = array('eq', '1'); 
		$map['pay_state'] = array('in', '0');
		$map['pay_nums'] = array('neq',0);
		$user_object   = M('trans as a');
		

		
		$num = $user_object
		->where($map)
		->join("ysk_user as b on b.userid = a.payout_id")
		->join("ysk_store as c on c.uid = a.payout_id")
		->order('pay_time asc')->count();
		// dump($user_object->getLastSql());
		// exit;

		//分页
		$p=getpage($user_object,$map,$num,15);
		$page=$p->show();
		$data_list = $user_object
			->where($map)
			->join("ysk_user as b on b.userid = a.payout_id")
			->join("ysk_store as c on c.uid = a.payout_id")
			->order('pay_time asc')->select();
		$this->assign('list',$data_list);
		$this->assign('table_data_page',$page);
		$this->display();

	}
	
	/**
	 *sv闪兑
	 */
	public function shan(){
		$res = M('shandui')->order('status asc,id desc')->select();

		$this->assign('res',$res);
		$this->display();
	}
	/**
	 * list
	 */
	public function list2(){
		$m = M('upgrade')->where('state!=2')->select();
		$this->assign('list',$m);
		$this->display();
	}
	
	public function list3(){
		$where['pay_state'] = array('eq',3);
		$where['is_lin'] = array('eq',0);
		$where['dj_time'] = array('gt',time());
		$where2['dj_time'] = array('elt',time());
		$m = M('trans')->where($where)->order('dj_time desc')->select();
		$jiedong = M('trans')->where($where2)->order('dj_time desc')->select();
		foreach($jiedong as $k=>$v){
			$jiedong[$k]['pay_state'] = 100; 
		}
		$list = array_merge($m,$jiedong);
		$this->assign('list',$list);
		$this->display();
	}
	public function jiedong(){
		$where['id'] = I('id');
		$m = M('trans')->where($where)->find();
		$time = $m['dj_time']-time();
		if($m['is_dj']==1||$time<0){
			$data = ['code'=>2,'msg'=>'error'];
			echo json_encode($data);
		}
		$data['dj_time'] = time();
		$data['is_dj'] = 0;
		$res = M('trans')->where($where)->save($data);
		if($res){
			$data = ['code'=>1,'msg'=>'解冻成功'];
			echo json_encode($data);
		}else{
			$data = ['code'=>0,'msg'=>'解冻失败'];
			echo json_encode($data);
		}
	}
	/**
	*卖出取消
	*/
	public function mcquxiao(){
		$where['id'] = I('id');
		$uid = I('uid');
		$m = M('trans')->where($where)->find();
		// var_dump($m);die();
		$data = $m['pay_nums'];
		$res = M('store')->where(array('uid'=>$uid))->setInc('sv',$data);
		if($res){
			M('trans')->where($where)->delete();
			echo "<meta charset='utf-8'/>";
			echo "<script>alert('取消成功');location.href='/admin/Finance/index.html';</script>";
		}else{
			echo "<meta charset='utf-8'/>";
			echo "<script>alert('取消失败');location.href='/admin/Finance/index.html';</script>";
		}
    } 
	public function shandetail(){
		$id = I('id');
		$res = M('shandui')->where(array('id'=>$id))->find();
		if(IS_POST){
			$id = I('id');
			$type = I('type');

			if($type==1){
				$data['status'] = I('hes');
				if($data['status']==1){
					$chaxun = M('shandui')->where(array('id'=>$id))->field('is_lin,uid,pay_num')->find();
					if($chaxun['is_lin']==0){
						$data['is_lin'] = 1;

						$res1 = M('store')->where(array('uid'=>$chaxun['uid']))->setInc('sc',$chaxun['pay_num']);
						$res2 = M('shandui')->where(array('id'=>$id))->save($data);
						//记录
						$jifen_dochange['pay_id'] = $chaxun['uid'];
						$jifen_dochange['get_id'] = $chaxun['uid'];
						$jifen_dochange['get_nums'] = +$chaxun['pay_num'];
						$jifen_dochange['get_time'] = time();
						$jifen_dochange['get_type'] = 34;
						$jifen_dochange['my'] = 2;
						$jifen_dochange['status'] = 1;
						$res_addres = M('tranmoney')->add($jifen_dochange);
						if($res1&&$res2&&$res_addres){
							ajaxReturn('确认成功');
						}
					}
					 
				}else{
					$res2 = M('shandui')->where(array('id'=>$id))->save($data);
					ajaxReturn('确认成功',0);
				}
			}else{
				$data['status'] = I('hes');
				//var_dump($data['status']);die();
				if($data['status']==2){
					$chaxun = M('shandui')->where(array('id'=>$id))->field('is_lin,uid,pay_num')->find();
					if($chaxun['is_lin']==0){
						$data['is_lin'] = 1;
						$res1 = M('store')->where(array('uid'=>$chaxun['uid']))->setInc('sc',$chaxun['pay_num']);
						$res2 = M('shandui')->where(array('id'=>$id))->save($data);
						//记录
						$jifen_dochange['pay_id'] = $chaxun['uid'];
						$jifen_dochange['get_id'] = $chaxun['uid'];
						$jifen_dochange['get_nums'] = +$chaxun['pay_num'];
						$jifen_dochange['get_time'] = time();
						$jifen_dochange['get_type'] = 34;
						$jifen_dochange['my'] = 2;
						$jifen_dochange['status'] = 1;
						$res_addres = M('tranmoney')->add($jifen_dochange);
						if($res1&&$res2&&$res_addres){
							ajaxReturn('审核失败',0);
						}
					}
					 
				}else{
					$res2 = M('shandui')->where(array('id'=>$id))->save($data);
						ajaxReturn('确认成功',1);
				}
			}
			
		}
		$this->assign('res',$res);
		$this->display();
	}

	/**
	 * 匹配完成
	 *
	 */
	public function list1()
	{
		// 获取所有用户
		// $map['status'] = array('egt', '0'); // 禁用和正常状态
		$user_object   = M('upgrade');
		  

		
		$num = $user_object
		->where('state=2')
		->count();
			// var_dump($data_list);exit();
		//分页
		$p=getpage($user_object,$map,$num,10);
		$page=$p->show(); 
		$data_list = $user_object
		->where($map)
		->where(array('pay_state'=>2))
		->select();
		$this->assign('indent',$data_list);
		$this->assign('table_data_page',$page);

		// $indent = M('upgrade')->where('state=2')->select();
		// $num = M('upgrade')->where('state=2')->count();
		// $this->assign('indent',$indent);
		// $this->assign('num',$num);

		$this->display();
	}

	/**
	 * 匹配未完成
	 *
	 */
	public function cannot()
	{
		// 获取所有用户
		$map['status'] = array('egt', '0'); // 禁用和正常状态
		$map['pay_state'] = array('in','0,1,2,4,5,6,7');
		//$map['trans_type'] = array('eq','0');
		$map['payin_id'] = array('neq','0');
		$map['payout_id'] = array('eq',0);
		$user_object = M('trans as a');

		// $chongxin = M('trans')
		// 			->where('pay_state'=>4)
		// 			->save($data);

		//update 表名 set 字段=null where 字段=某值 --即将表中字段为某值的替换为null
		 
		// $where="pay_state in(0,1,2)";
		
		// dump($user_object->getLastSql());
		// exit;
		$num = $user_object
		->where($map)
		->join("ysk_user as b on b.userid = a.payin_id")
		->join("ysk_store as c on c.uid = a.payin_id")
		->order('id asc')->count();
		$zong = M('trans')->where(array('payout_id'=>0))->sum('pay_nums');
		$this->assign('zong',$zong);
		//分页
		$p=getpage($user_object,$map,$num,25);
		
		$page=$p->show();
		$data_list = $user_object
			->where($map)
			->join("ysk_user as b on b.userid = a.payin_id")
			->join("ysk_store as c on c.uid = a.payin_id")
			->order('id asc')->select();
		$this->assign('list',$data_list);
		$this->assign('table_data_page',$page);
		$this->display();
	}

	/**
	 * 重新匹配
	 */
	public function chongxin()
	{

		$id = I('id');
		$chongxin = M('trans')
				->where('id='.$id)
				->save(array('payin_id'=>'','pay_state'=>0,'trans_type'=>1,'trans_img'=>'','pipeitime'=>''));

		if($chongxin){
				echo "<meta charset='utf-8'/>";
				echo "<script>alert('重新匹配成功');location.href='/admin/Finance/index.html';</script>";
			}else{
				echo "<meta charset='utf-8'/>";
				echo "<script>alert('重新匹配失败');location.href='/admin/Finance/index.html';</script>";
			}
	}

	/**
	 * 重新匹配卖家
	 */
	public function mchongxin()
	{

		$id = I('id');
		$chongxin = M('trans')
				->where('id='.$id)
				->save(array('payout_id'=>'','pay_state'=>0,'trans_type'=>0,'trans_img'=>'','pipeitime'=>''));

		if($chongxin){
				echo "<meta charset='utf-8'/>";
				echo "<script>alert('重新匹配成功');location.href='/admin/Finance/index.html';</script>";
			}else{
				echo "<meta charset='utf-8'/>";
				echo "<script>alert('重新匹配失败');location.href='/admin/Finance/index.html';</script>";
			}
	}
	/**
	 * 拆单
	 */
	public function chaidan()
	{

		$user_object   = M('trans as a');
		$id = I('get.id');

		$data_list = $user_object
			->where($map)
			->where(array('id'=>$id))
			->order('payout_id desc')
			->find();
		$this->assign('list',$data_list);
		$this->assign('table_data_page',$page);
		$this->display();

	}



	public function docaidan(){
		$data = I('post.');
		$tran = M('trans')->where(array('id'=>$data['id']))->find();
		if($tran['is_chaijie'] == 0){
			$pricedec = $tran['pay_nums'] - $data['price']; //拆解剩下的钱
			$arr = ['pay_nums'=>$pricedec,'is_chaijie'=>1];
			if($tran['pay_nums'] >0 && $tran['pay_nums'] > $data['price']){
				M('trans')->where(array('id'=>$data['id']))->setField('pay_nums',$pricedec);
			}else{
				echo "<meta charset='utf-8'/>";
				echo "<script>alert('该订单金额不足');location.href='/admin/Finance/index.html';</script>";
			}
			// 删除ID，对数据进行处理
			unset($tran['id']);
			$arr = array(
				'payout_id' =>$tran['payout_id'],
				'payin_id'	=> $tran['payin_id'],
				'pay_nums' => $data['price'],
				'trans_type' => $tran['trans_type'],
			);
			$arr = array_merge($tran,$arr);
			$res = M('trans')->add($arr);
			if($res){
				echo "<meta charset='utf-8'/>";
				echo "<script>alert('拆分成功');location.href='/admin/Finance/index.html';</script>";
			}else{
				echo "<meta charset='utf-8'/>";
				echo "<script>alert('拆分失败');location.href='/admin/Finance/index.html';</script>";
			}
		}else{
			echo "<meta charset='utf-8'/>";
			echo "<script>alert('该订单已拆解过');location.href='/admin/Finance/index.html';</script>";
		}
		
	}

	
	/**
	 * 删除
	 */
	public function delete()
	{

		$id = I('id');
		$res = M('trans')->where('id='.$id)->delete();

		if($res){
			exit(json_encode(array('code'=>1,'msg'=>'删除成功')));
				// echo "<meta charset='utf-8'/>";
				// echo "<script>alert('删除成功');location.href='/admin/Finance/cannot.html';</script>";
			}else{
				exit(json_encode(array('code'=>0,'msg'=>'删除失败')));
				// echo "<meta charset='utf-8'/>";
				// echo "<script>alert('删除失败');location.href='/admin/Finance/cannot.html';</script>";
			}
	}
	/**
	 * 取消匹配
	 */
	public function qupipei(){
		$id = I('id');
		$chongxin = M('trans')->where('id='.$id)->find();
		
		$count = M('trans')->where(array('id'=>$chongxin['match']))->count();
		if($count){
			if($chongxin['trans_type'] == 0){
				// 买入订单
				$arr['pay_nums'] = array('exp','pay_nums +'.$chongxin['pay_nums']);
				$res2 = M('trans')->where(array('id'=>$chongxin['match']))->save($arr);

				$arr2['payout_id'] = 0;
				$arr2['pay_state'] = 0;
				$res = M('trans')->where(array('id'=>$id))->save($arr2);
			}else{
				// 卖出订单
				$arr['payin_id'] = 0;
				$arr['pay_state'] = 0;
				$res = M('trans')->where(array('id'=>$id))->save($arr);

				$arr2['pay_nums'] = array('exp','pay_nums + '.$chongxin['pay_nums']);
				$res2 = M('trans')->where(array('id'=>$chongxin['match']))->save($arr2);
				
			}
		}else{
			if($chongxin['trans_type'] == 0){
				$arr['id'] = $chongxin['match'];
				$arr['payout_id'] = $chongxin['payout_id'];
				$arr['pay_nums'] = $chongxin['pay_nums'];
				$arr['pay_time'] = $chongxin['pay_time'];
				$arr['pay_no'] = build_order_no();
				$arr['trans_type'] = 1;
				$arr['pay_state'] = 0;
				$res = M('trans')->add($arr);

				$arr2['payout_id'] = 0;
				$arr2['pay_state'] = 0;
				$res2 = M('trans')->where(array('id'=>$chongxin['id']))->save($arr2);
			}else{
				$arr['id'] = $chongxin['match'];
				$arr['payin_id'] = $chongxin['payin_id'];
				$arr['pay_nums'] = $chongxin['pay_nums'];
				$arr['pay_time'] = $chongxin['pay_time'];
				$arr['pay_no'] = build_order_no();
				$arr['trans_type'] = 1;
				$arr['pay_state'] = 0;
				$res = M('trans')->add($arr);

				$arr2['payin_id'] = 0;
				$arr2['pay_state'] = 0;
				$res2 = M('trans')->where(array('id'=>$chongxin['id']))->save($arr2);
			}
		}
		// dump($chongxin);
		// $data['payout_id'] = $chongxin['payout_id'];
		// $data['pay_nums'] = $chongxin['pay_nums'];
		// $data['pay_time'] = $chongxin['pay_time'];
		// $data['pay_no'] = build_order_no();
		// $data['trans_type'] = 1;
		// $data['pay_state'] = 0;
		// $res = M('trans')->add($data);

		// $data2['payout_id'] = '';
		// $data2['dakuan_time'] = 0;
		// $data2['cunchu_num'] = 0;
		// $data2['is_lin'] = 0;
		// $data2['pipeitime'] = '';
		// $data2['dj_time'] =0;
		// $data2['dj_set_time'] = 0;
		// $data2['is_dj'] = 0;
		// $data2['get_moneytime'] = '';
		// $data2['trans_img'] = '';
		// $data2['trans_type'] = 0;
		// $data2['pay_state'] = 0;
		// $res2 = M('trans')->where('id='.$id)->save($data2);
		
		if($res&&$res2){
			$data = ['code'=>1,'msg'=>'取消成功'];
			echo json_encode($data);
		}else{
			$data = ['code'=>2,'msg'=>'取消失败'];
			echo json_encode($data);
		}
	}
	/**
	 * 匹配
	 */
	public function pipei()
	{
		$aid = I('get.id');

		$payout_id = I('get.payout_id');
		// 获取所有用户
		$map['status'] = array('egt', '0'); // 禁用和正常状态
		$map['trans_type'] = array('eq', '0'); 
		$map['pay_state'] = array('eq', '0');
		$map['payin_id'] = array('neq',$payout_id);
		$map['payout_id'] = array('eq',0);

		$user_object   = M('trans as a')
		->join("ysk_user as b on b.userid = a.payin_id")
		->join("ysk_store as c on c.uid = a.payin_id");

		
		
		$num = count($data_list);
		//分页 
		$p=getpage($user_object,$map,$num,25);
		$page=$p->show();
		$data_list = $user_object
		->where($map)
		->order('id asc')
		->select();
		$this->assign('list',$data_list);
		$this->assign('aid',$aid);
		$this->assign('table_data_page',$page);
		$this->display();

	}

	public function test(){
		$str = trim(I('post.str'),','); //买入tran id
		$payout_id = I('post.id');  //卖出tran id
		
		$where = 'id in ('.$str.')';
		$pay_nums = M('trans')->where($where)->field('id,pay_nums,payin_id,peo_num,tran_grade,pid,com_id,diff_num')->order('id desc')->select();
		$total_pay_nums = 0;
		
		foreach($pay_nums as $v){
			$total_pay_nums = $total_pay_nums + $v['pay_nums'];
			
		}
		//卖出用户的金额和用户ID
		$apay_nums = M('trans')->where(array('id'=>$payout_id))->getField('pay_nums');
		$payout_uid = M('trans')->where(array('id'=>$payout_id))->getField('payout_id');
		$payout_mobile = M('user')->where(array('userid'=>$payout_uid))->getField('mobile');
		$bank_id = M('ubanks')->where(array('user_id'=>$payout_uid,'is_default'=>1))->count();
		if($bank_id > 0){

			$bank_id = M('ubanks')->where(array('user_id'=>$payout_uid,'is_default'=>1))->getField('id');
		}else{

			$bank_id = M('ubanks')->where(array('user_id'=>$payout_uid))->order('id asc')->getFIeld('id');
		}

		foreach($pay_nums as $k => $v){
			
			if($apay_nums > $v['pay_nums'] && $apay_nums != 0){
				// 转让金额大于预约金额
				// echo 1;
				// exit;
				$apay_nums = $apay_nums - $v['pay_nums'];
				$v['peo_num'] = $peo_num + 1;

				$arr = ['payout_id'=>$payout_uid,'pay_state'=>1,'pipeitime'=>time(),'card_id'=>$bank_id,'peo_num'=>$v['peo_num'],'tran_grade'=>$v['tran_grade'],'pid'=>$v['pid'],'match'=>$payout_id,'com_id'=>$v['com_id']];

				$res = M('trans')->where(array('id'=>$v['id']))->save($arr);
				$arr2 = array('pay_nums'=>$apay_nums);
				$res2 = M('trans')->where(array('id'=>$payout_id))->save($arr2);
				$payin_mobile = M('user')->where(array('userid'=>$v['payin_id']))->getField('mobile');
				$this->newMsg($payin_mobile);
				$this->newMsg($payout_mobile,false);
				
			}elseif($apay_nums == $v['pay_nums'] && $apay_nums != 0){
				// 转让金额等于预约金额
				// echo 2;
				// exit;
				$v['peo_num'] = $peo_num + 1;
				$arr = ['payout_id'=>$payout_uid,'pay_state'=>1,'pipeitime'=>time(),'card_id'=>$bank_id,'peo_num'=>$v['peo_num'],'tran_grade'=>$v['tran_grade'],'pid'=>$v['pid'],'match'=>$payout_id,'com_id'=>$v['com_id'],'diff_num'=>$v['diff_num']];
				
				$arr2 = ['payin_id'=>$v['payin_id'],'pay_state'=>1,'pipeitime'=>time(),'tran_grade'=>$v['tran_grade'],'pid'=>$v['pid'],'match'=>$v['id'],'com_id'=>$v['com_id'],'diff_num'=>$v['diff_num']];
				$res2 = M('trans')->where(array('id'=>$v['id']))->save($arr);
				$res = M('trans')->where(array('id'=>$payout_id))->save($arr2);
				$payin_mobile = M('user')->where(array('userid'=>$v['payin_id']))->getField('mobile');
				$this->newMsg($payin_mobile);
				$this->newMsg($payout_mobile,false);
				if($v['id'] > $payout_id){
					M('trans')->where(array('id'=>$payout_id))->delete();
				}else{
					M('trans')->where(array('id'=>$v['id']))->delete();
				}
			}else{
				if($apay_nums != 0){
					// 预约金额大于转让金额
					// echo 3;
					
					$diff = $v['pay_nums'] - $apay_nums;
					$apay_nums = 0;
					$arr = ['pay_nums'=>$diff,'com_id'=>$v['com_id']];
					$arr2 = ['payin_id'=>$v['payin_id'],'pay_state'=>1,'payout_id'=>$payout_uid,'pipeitime'=>time(),'tran_grade'=>$v['tran_grade'],'pid'=>$v['pid'],'match'=>$v['id'],'com_id'=>$v['com_id'],'diff_num'=>$v['diff_num']];
					$res = M('trans')->where(array('id'=>$v['id']))->save($arr);
					$res2 = M('trans')->where(array('id'=>$payout_id))->save($arr2);
					$payin_mobile = M('user')->where(array('userid'=>$v['payin_id']))->getField('mobile');
					$this->newMsg($payin_mobile);
					$this->newMsg($payout_mobile,false);
				}
			}
		}
		if($res && $res2){
			exit(json_encode(array('code'=>1,'msg'=>'匹配成功')));
		}else{
			exit(json_encode(array('code'=>0,'msg'=>'匹配失败')));
		}
	}

	// 短信发送接口
	private function newMsg($mobile,$test =true) {
		// if($test){
			// $content="你的订单已匹配 请及时登录查看。【77科技】";//要发送的短信内容
		// }else{
			// $content="你的订单已匹配 请及时登录查看。【77科技】";//要发送的短信内容
		// }
	    
		// $url="http://service.winic.org:8009/sys_port/gateway/index.asp?";
		// $data = "id=%s&pwd=%s&to=%s&content=%s&time=";
		// $id = 'yizhangjin';
		// $pwd = 'yizhangjin1314';
		$to = $mobile;
		$content = iconv("UTF-8","GB2312",$content);
		$rdata = sprintf($data, $id, $pwd, $to, $content);
		
		
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_POST,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$rdata);
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		$result = curl_exec($ch);
		curl_close($ch);
		$result = substr($result,0,3);

	    // ajaxReturn($url,0);
		// $con= substr( file_get_contents($url), 0, 1 );  //获取信息发送后的状态

		if($result == '000'){
			$msg = true;
		}else{
			$msg = false;

		}
		return $msg;
}

	/**
	 * 编辑用户
	 *
	 */
	public function edit($id)
	{
		if (IS_POST) {
			// 提交数据
			$user_object = D('news');
			$data        = I('post.');
			$data['create_time'] = time();
			if(empty($data['title'])){
			  $this->error('标题不能为空');  
			}
		  //  var_dump($data);exit;
			if ($data) {
				$result = $user_object
					->save($data);
				if ($result) {
					$this->success('更新成功', U('index'));
				} else {
					$this->error('更新失败', $user_object->getError());
				}
			} else {
				$this->error($user_object->getError());
			}
		} else {
			// 获取账号信息
			$info = D('news')->find($id);
			$this->assign('info',$info);
			$this->display();
		}
	}

	/**
	 * 设置一条或者多条数据的状态
	 *
	 */
	public function setStatus($model = CONTROLLER_NAME)
	{
		$ids = I('request.ids');
		parent::setStatus($model);
	}

	/**
	 * 设置冻结时间
	 */
	public function do_dj(){
		$id = I('post.id');
		if($id<0){
			$this->ajaxReturn(array('status'=>1));
		}
		$dj_time = I('post.dj_time');
		if(!is_numeric($dj_time) || $dj_time<=0 || $dj_time>20){
			$this->ajaxReturn(array('status'=>2));
		}
		$arr = array(
			'is_dj' =>  1,
			'dj_time' =>  $dj_time,
			'dj_set_time' =>  time(),
		);
		$res = M('trans')->where('id='.$id)->save($arr);
		if($res>0){
			$this->ajaxReturn(array('status'=>3));
		}
	}
}