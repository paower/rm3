<?php
/**
 * 本程序仅供娱乐开发学习，如有非法用途与本公司无关，一切法律责任自负！
 */
namespace Home\Controller;
use Think\Controller;
class CommonController extends Controller {
	public function _initialize(){
		
		
		$starttime1 = session('caozuo');
        // dump($starttime);
		$endtime = $starttime1 + 24 * 3600;
		if(time()>$endtime){
			session(null);
		}
		session('caozuo',time());
        $where = array(
            'is_linqu' => 0,
            'pay_state' =>3,
            'dj_time'=>array('lt',time())
        );
        $res = M('trans')->field('pay_nums,payin_id,dj_time,id,dj_set_time,cunchu_num')->where($where)->select();
        foreach ($res as $key => $value) {/*dump($key);exit;*/
            
            //给用户加余额
            $gai = M('trans')->where(array('id'=>$value['id']))->save(array('is_linqu'=>1,'dongjie'=>0));
            // 推荐人动态积分增加
            // $userid = M('user')->field('pid,gid,ggid')->where('userid='.$value['payin_id'])->find();
            // foreach ($userid as $k => $v) {
            //     $cangku_num = M('store')->where(array('userid'=>$v))->getField('cangku_num');
            //     if($cangku_num<$value['pay_nums']){
            //         $save_num = $cangku_num*0.05;
            //     }else{
            //         $save_num = $value['pay_nums']*0.05;
            //     }
            //     $cangku_num = M('store')->where(array('userid'=>$v))->save(array('dt_jiden'=>$save_num));
            // }

            //添加买入余额记录
            // $pay_n = M('store')->where(array('uid' => $value["payin_id"]))->getfield('cangku_num');
            // $jifen_dochange['now_nums'] = $pay_n;
            // $jifen_dochange['now_nums_get'] = $pay_n;
            // $jifen_dochange['is_release'] = 1;
            // $jifen_dochange['pay_id'] = 0;
            // $jifen_dochange['get_id'] = $value["payin_id"];
            // $jifen_dochange['get_nums'] = $paynums;
            // $jifen_dochange['get_time'] = $value['dj_time'];
            // $jifen_dochange['get_type'] = 8;
            // $res_addres = M('tranmoney')->add($jifen_dochange);
        }

        //判断网站是否关闭
        $close=is_close_site();
        if($close['value']==0){
          success_alert($close['tip'],U('Login/logout'));
        }
        //验证用户登录
        $this->is_user();
    }


 protected function is_user(){
        $userid=user_login();
        $user=M('user');
        if(!$userid){
            $this->redirect('Login/login');
            exit();
        }

        //判断12小时后必须重新登录
        $in_time=session('in_time');
        $time_now=time();
        $between=$time_now-$in_time;
        if($between > 3600*24*5){
            $this->redirect('Login/logout');
        }

        $where['userid']=$userid;
        $u_info=$user->where($where)->field('status,session_id')->find();
        //判断用户是否锁定
        $login_from_admin=session('login_from_admin');//是否后台登录
        if($u_info['status']==0 && $login_from_admin!='admin'){
            if(IS_AJAX){
                ajaxReturn('你账号已锁定，请联系管理员',0);
            }else{
                success_alert('你账号已锁定，请联系管理员',U('Login/logout'));
                exit();
            }
        }

        //判断用户是否在他处已登录
        // $session_id=session_id();
        // if($session_id != $u_info['session_id'] && empty($login_from_admin)){

        //     if(IS_AJAX){
        //         ajaxReturn('您的账号在他处登录，您被迫下线',0);
        //     }else{
        //         success_alert('您的账号在他处登录，您被迫下线',U('Login/logout'));
        //         exit();
        //     }
        // }
        //记录操作时间
        // session('in_time',time());
    }

    // 获取上级用户
    protected function getParents($userid){
        $data = M('user')->where(array('vip_grade'=>array('egt',1)))->field('pid,userid')->select();
        $list = $this->getTree($data,$userid);
        return $list;
    }

    private function getTree($data,$userid,$level=0){
        static $list = array();
        foreach($data as $k => $v){
            if($userid == $v['userid']){
                $v['level'] = $level;
                $v['child_count'] = M('user')->where(array('pid'=>$v['userid'],'vip_grade'=>array('egt',1)))->count();
                $list[] = $v;
                $this->getTree($data,$v['pid'],$level+1);
            }
        }
        return $list;
    }


}

