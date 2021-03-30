<?php
namespace Admin\Controller;
use Think\Controller;
class IndexController extends AdminController {
	public function index(){
		//会员统计
		$this->getUserCount();
		//交易量
		$this->TraingCount();
		$this->Tranje7();
		$this->Tranje();
		// $purchase_price = $this->getToday();
		$getSevenday = $this->getSevenday();
		$time = time();
		for($i=7;$i>0;$i--){
			$res = getTodayTimes($time);
			$list[date('Y-m-d',$res['start'])] = $this->getToday($res['start'],$res['end']);
			$time = $time - 86400;
		}
		$this->assign('list',$list);
		$this->assign('getSevenday',$getSevenday);
		$this->assign('meta_title', "首页");

		$gaoj_line = M('user')->where(array('room'=>3))->count();
		$zhongj_line = M('user')->where(array('room'=>2))->count();
		$dij_line = M('user')->where(array('room'=>1))->count();
		$this->assign('gaoj_line',$gaoj_line);
		$this->assign('zhongj_line',$zhongj_line);
		$this->assign('dij_line',$dij_line);

		$gaoji = M('user')->where("room = 3")->field('line_code')->select();
		foreach ($gaoji as $key => $value) {
			$line = $value['line_code'];
			$gaoji_sum[$line]['line'] = $line;
			$list = M('tranmoney')->where(array('line_code'=>$line,'get_type'=>1))->select();
			$sum = 0;
			foreach ($list as $k => $v) {
				$sum += $v['get_nums'];
			}
			$gaoji_sum[$line]['g'] = $sum;

			$list2 = M('tranmoney')->where(array('line_code'=>$line,'get_type'=>2))->select();
			$sum2 = 0;
			foreach ($list2 as $k => $v) {
				$sum2 += $v['get_nums'];
			}
			$gaoji_sum[$line]['z'] = $sum2;
		}
		$this->assign('gaoji_sum',$gaoji_sum);

		$zhongji = M('user')->where("room = 2")->field('line_code')->select();
		foreach ($zhongji as $key => $value) {
			$line = $value['line_code'];
			$zhongji_sum[$line]['line'] = $line;
			$list = M('tranmoney')->where(array('line_code'=>$line,'get_type'=>1))->select();
			$sum = 0;
			foreach ($list as $k => $v) {
				$sum += $v['get_nums'];
			}
			$zhongji_sum[$line]['g'] = $sum;

			$list2 = M('tranmoney')->where(array('line_code'=>$line,'get_type'=>2))->select();
			$sum2 = 0;
			foreach ($list2 as $k => $v) {
				$sum2 += $v['get_nums'];
			}
			$zhongji_sum[$line]['z'] = $sum2;
		}
		$this->assign('zhongji_sum',$zhongji_sum);

		$diji = M('user')->where("room = 1")->field('line_code')->select();
		foreach ($diji as $key => $value) {
			$line = $value['line_code'];
			$diji_sum[$line]['line'] = $line;
			$list = M('tranmoney')->where(array('line_code'=>$line,'get_type'=>1))->select();
			$sum = 0;
			foreach ($list as $k => $v) {
				$sum += $v['get_nums'];
			}
			$diji_sum[$line]['g'] = $sum;

			$list2 = M('tranmoney')->where(array('line_code'=>$line,'get_type'=>2))->select();
			$sum2 = 0;
			foreach ($list2 as $k => $v) {
				$sum2 += $v['get_nums'];
			}
			$diji_sum[$line]['z'] = $sum2;
		}
		$this->assign('diji_sum',$diji_sum);
		$this->display();
	}
	
	public function getUserCount(){
		$user=D('User');
		$user_total=$user->count(1);
		$start=strtotime(date('Y-m-d'));
		$end=$start+86400;
		$where="reg_date BETWEEN {$start} AND {$end}";
		$user_count=$user->where($where)->count(1);
		$this->assign('user_total', $user_total);
		$this->assign('user_count', $user_count);
	}
	/**
	 * 今日投单数
	 */
	public function Tranje(){
		$traing = M('trans');
		$start = strtotime(date('Y-m-d'));
		$end=$start+86400;
		$where="pay_time BETWEEN {$start} AND {$end}";

		$traing = $traing->where($where)->count();
		$this->assign('traing',$traing);
	}
	/**
	 * 7 day
	 */
	public function Tranje7(){
		$traing = M('trans');
		$start = strtotime(date('Y-m-d'));
		$end=$start+86400*7;
		$where="pay_time BETWEEN {$start} AND {$end}";

		$traing7 = $traing->where($where)->count();
		$this->assign('traing7',$traing7);
	}
	public function TraingCount(){
		$traing=M('trading');
		$trading_free=M('trading_free');

		$start=strtotime(date('Y-m-d'));
		$end=$start+86400;
		$where="create_time BETWEEN {$start} AND {$end}";

		$traing_count=$traing->where($where)->count(1);
		$traing_total=$traing->count(1);

		$traing_count+=$trading_free->where($where)->count(1);
		$traing_total+=$trading_free->count(1);

		$this->assign('traing_count', $traing_count);
		$this->assign('traing_total', $traing_total);
	}

	private function getToday($statr,$end){
		$map['pay_time'] = array(
			array('egt',$statr),
			array('elt',$end),
		);
		$map['trans_type'] = 0;
		$purchase_price = M('trans')->where($map)->sum('pay_nums');
		return $purchase_price;
	}

	private function getSevenday(){
		$statr = strtotime(date('Y-m-d'));
		$end = strtotime(date('Y-m-d 23:59:59'));
		$statr = $statr - 6*86400;
		$map['pay_time'] = array(
			array('egt',$statr),
			array('elt',$end),
		);
		$map['trans_type'] = 0;
		$purchase_price = M('trans')->where($map)->sum('pay_nums');
		return $purchase_price;
	}

	/**
	 * 删除缓存
	 * @author jry <598821125@qq.com>
	 */
	public function removeRuntime()
	{
		$file   = new \Util\File();
		$result = $file->del_dir(RUNTIME_PATH);
		if ($result) {
			$this->success("缓存清理成功1");
		} else {
			$this->error("缓存清理失败1");
		}
	}
	
}