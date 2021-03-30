<?php
namespace Home\Controller;
use Think\Controller;
class UserController extends CommonController
{
    //明细
    public function detail(){
        $uid = session('userid');
        $map['pay_id|get_id'] = $uid;
        $map['get_type'] = array('in','3,4');
        $info = M('tranmoney')->where($map)->select();
        $this->assign('info',$info);
        $this->assign('uid',$uid);
        $this->display();
    }
    //我的好友
    public function teamdetails()
    {
        $id = session('userid');
        //上级信息
        $pid = M('user')->where("userid = $id")->getField('pid');
        if($pid != 0){
            $pname = M('user')->where("userid = $pid")->getField('username');
            $this->assign('pname',$pname);
        }
        //推荐人数
        $tj = M('user')->where("pid = $id")->count();
        $this->assign('tj',$tj);
        //激活人数
        $jh = M('user')->where(array('pid'=>$id,'is_jihuo'=>1))->count();
        $this->assign('jh',$jh);
        //已结算收益
        $yjs = M('user')->where("userid = $id")->getField('yjs');
        $this->assign('yjs',$yjs);
        //冷却倒计时
        $level = M('user')->where("userid = $id")->getField('userlevel');
        $map['uid'] = $id;
        $map['level'] = $level;
		$map['status'] = 0;
        $lengque = M('lengque')->where($map)->getField('time');
        $daojishi = $lengque+6*60*60;
        $lengque =$daojishi;
        $this->assign('lengque',$lengque);
        // dump($lengque);die;

        $a = D('user')->where("userid=$id")->field('userlevel')->find();
        $level = $a['userlevel'];
        $this->assign('level',$level);
        //累计收入
        $sum = M('user')->where("userid = $id")->getField("sj_money");
        $this->assign('sum',$sum);

        //层数
        $son[] = $id;
        $res = "<a href='/User/Teamdets?layer=1'><li class='sharelist'><p>第1层</p><p>1</p><p>0</p><p>0</p></li></a>";
        for ($i=2; $i < 22; $i++) {
            $snum = 0;
            $order_num = 0;
            $lou_num = 0;
            foreach ($son as $k => $v) {
                $id = $v;
                $num = M('user')->where("cid = $id")->count();
                $snum += $num;
                $sinfo = M('user')->where("cid = $id")->select();
                foreach ($sinfo as $key => $value) {
                    $sid[] = $value['userid'];
                    $order_num += M('tranmoney')->where(array("pay_id"=>$value['userid'],"get_id"=>session('userid'),"get_type"=>4))->count();
                    $lou_num += M('user')->where(array('userid'=>$value['userid']))->getField('loudan');
                }
            }
            $son = $sid;
            $sid = [];
            $res .= "<a href='/User/Teamdets?layer=".$i."'><li class='sharelist'><p>第".$i."层</p><p>".$snum."</p><p>".$order_num."</p><p>".$lou_num."</p></li></a>";
        }
        $this->assign('res',$res);
        // var_dump($res);die;

        $son1 = M('user')->where("pid=$id")->count();
        $sonid1 = M('user')->where("pid=$id")->field('userid')->select();
        $indent1 = 0;
        $lou1 = 0;
        $ziji = session('userid');
        foreach ($sonid1 as $k => $v) {
            $id = $v['userid'];
            $indent1+= M('upgrade')->where("shouid=$ziji and uid=$id and state=2")->count();
            $i = M('loudan')->where("shouid=$ziji and uid=$id")->count();
            $lou1 += $i['loudan'];   
        }
        $this->assign('lou1',$lou1);
        $this->assign('indent1',$indent1);
        $this->assign('son1',$son1);


        $son2 = 0;
        foreach ($sonid1 as $k => $v) {
            $id = $v['userid'];
            $son2+=M('user')->where("pid=$id")->count();
            $sonid2[] = M('user')->where("pid=$id")->field('userid')->select();
        }
        $indent2 = 0;
        $lou2 = 0;
        foreach ($sonid2 as $k => $v) {
            foreach ($v as $k1 => $v1) {
                $id = $v1['userid'];
                $a['shouid']=$id;
                $indent2+= M('upgrade')->where("shouid=$ziji and uid=$id and state=2")->count();
                $b['userid']=$id;
                $i = M('loudan')->where("shouid=$ziji and uid=$id")->count();
                $lou2 += $i['loudan'];   
            }
        }
        $this->assign('lou2',$lou2);
        $this->assign('indent2',$indent2);
        $this->assign('son2',$son2);


        $son3 = 0;
        foreach ($sonid2 as $k => $v) {
            foreach ($v as $k1 => $v1) {
                $id = $v1['userid'];
                $son3+=M('user')->where("pid=$id")->count();
                $sonid3[] = M('user')->where("pid=$id")->field('userid')->select();
            }
        }
        $indent3 = 0;
        $lou3 = 0;
        foreach ($sonid3 as $k => $v) {
            foreach ($v as $k1 => $v1) {
                $id = $v1['userid'];
                $a['shouid']=$id;
                $indent3+= M('upgrade')->where("shouid=$ziji and uid=$id and state=2")->count();
                $b['userid']=$id;
                $i = M('loudan')->where("shouid=$ziji and uid=$id")->count();
                $lou3 += $i['loudan'];   
            }
        }
        $this->assign('lou3',$lou3);
        $this->assign('indent3',$indent3);
        $this->assign('son3',$son3);

        $son4 = 0;
        foreach ($sonid3 as $k => $v) {
            foreach ($v as $k1 => $v1) {
                $id = $v1['userid'];
                $son4+=M('user')->where("pid=$id")->count();
                $sonid4[] = M('user')->where("pid=$id")->field('userid')->select();
            }
        }
        $indent4 = 0;
        $lou4 = 0;
        foreach ($sonid4 as $k => $v) {
            foreach ($v as $k1 => $v1) {
                $id = $v1['userid'];
                $a['shouid']=$id;
                $indent4+= M('upgrade')->where("shouid=$ziji and uid=$id and state=2")->count();
                $b['userid']=$id;
                $i = M('loudan')->where("shouid=$ziji and uid=$id")->count();
                $lou4 += $i['loudan'];   
            }
        }
        $this->assign('lou4',$lou4);
        $this->assign('indent4',$indent4);
        $this->assign('son4',$son4);



        $son5 = 0;
        foreach ($sonid4 as $k => $v) {
            foreach ($v as $k1 => $v1) {
                $id = $v1['userid'];
                $son5+=M('user')->where("pid=$id")->count();
                $sonid5[] = M('user')->where("pid=$id")->field('userid')->select();
            }
        }
        $indent5 = 0;
        $lou5 = 0;
        foreach ($sonid5 as $k => $v) {
            foreach ($v as $k1 => $v1) {
                $id = $v1['userid'];
                $a['shouid']=$id;
                $indent5+= M('upgrade')->where("shouid=$ziji and uid=$id and state=2")->count();
                $b['userid']=$id;
                $i = M('loudan')->where("shouid=$ziji and uid=$id")->count();
                $lou5 += $i['loudan'];   
            }
        }
        $this->assign('lou5',$lou5);
        $this->assign('indent5',$indent5);
        $this->assign('son5',$son5);


        $son6 = 0;
        foreach ($sonid5 as $k => $v) {
            foreach ($v as $k1 => $v1) {
                $id = $v1['userid'];
                $son6+=M('user')->where("pid=$id")->count();
                $sonid6[] = M('user')->where("pid=$id")->field('userid')->select();
            }
        }
        $indent6 = 0;
        $lou6 = 0;
        foreach ($sonid6 as $k => $v) {
            foreach ($v as $k1 => $v1) {
                $id = $v1['userid'];
                $a['shouid']=$id;
                $indent6+= M('upgrade')->where("shouid=$ziji and uid=$id and state=2")->count();
                $b['userid']=$id;
                $i = M('loudan')->where("shouid=$ziji and uid=$id")->count();
                $lou6 += $i['loudan'];   
            }
        }
        $this->assign('lou6',$lou6);
        $this->assign('indent6',$indent6);
        $this->assign('son6',$son6);


        $son7 = 0;
        foreach ($sonid6 as $k => $v) {
            foreach ($v as $k1 => $v1) {
                $id = $v1['userid'];
                $son7+=M('user')->where("pid=$id")->count();
                $sonid7[] = M('user')->where("pid=$id")->field('userid')->select();
            }
        }
        $indent7 = 0;
        $lou7 = 0;
        foreach ($sonid7 as $k => $v) {
            foreach ($v as $k1 => $v1) {
                $id = $v1['userid'];
                $a['shouid']=$id;
                $indent7+= M('upgrade')->where("shouid=$ziji and uid=$id and state=2")->count();
                $b['userid']=$id;
                $i = M('loudan')->where("shouid=$ziji and uid=$id")->count();
                $lou7 += $i['loudan'];   
            }   
        }
        $this->assign('lou7',$lou7);
        $this->assign('indent7',$indent7);
        $this->assign('son7',$son7);


        $son8 = 0;
        foreach ($sonid7 as $k => $v) {
            foreach ($v as $k1 => $v1) {
                $id = $v1['userid'];
                $son8+=M('user')->where("pid=$id")->count();
                $sonid8[] = M('user')->where("pid=$id")->field('userid')->select();
            }
        }
        $indent8 = 0;
        $lou8 = 0;
        foreach ($sonid8 as $k => $v) {
            foreach ($v as $k1 => $v1) {
                $id = $v1['userid'];
                $a['shouid']=$id;
                $indent8+= M('upgrade')->where("shouid=$ziji and uid=$id and state=2")->count();
                $b['userid']=$id;
                $i = M('loudan')->where("shouid=$ziji and uid=$id")->count();
                $lou8 += $i['loudan'];   
            } 
        }
        $this->assign('lou8',$lou8);
        $this->assign('indent8',$indent8);
        $this->assign('son8',$son8);

        $son9 = 0;
        foreach ($sonid8 as $k => $v) {
            foreach ($v as $k1 => $v1) {
                $id = $v1['userid'];
                $son9+=M('user')->where("pid=$id")->count();
                $sonid9[] = M('user')->where("pid=$id")->field('userid')->select();
            }
        }
        $indent9 = 0;
        $lou9 = 0;
        foreach ($sonid9 as $k => $v) {
            foreach ($v as $k1 => $v1) {
                $id = $v1['userid'];
                $a['shouid']=$id;
                $indent9+= M('upgrade')->where("shouid=$ziji and uid=$id and state=2")->count();
                $b['userid']=$id;
                $i = M('loudan')->where("shouid=$ziji and uid=$id")->count();
                $lou9 += $i['loudan'];   
            }  
        }
        $this->assign('lou9',$lou9);
        $this->assign('indent9',$indent9);
        $this->assign('son9',$son9);


        $son10 = 0;
        foreach ($sonid9 as $k => $v) {
            foreach ($v as $k1 => $v1) {
                $id = $v1['userid'];
                $son10+=M('user')->where("pid=$id")->count();
                $sonid10[] = M('user')->where("pid=$id")->field('userid')->select();
            }
        }
        $indent10 = 0;
        $lou10 = 0;
        foreach ($sonid10 as $k => $v) {
            foreach ($v as $k1 => $v1) {
                $id = $v1['userid'];
                $a['shouid']=$id;
                $indent10+= M('upgrade')->where("shouid=$ziji and uid=$id and state=2")->count();
                $b['userid']=$id;
                $i = M('loudan')->where("shouid=$ziji and uid=$id")->count();
                $lou10 += $i['loudan'];   
            }   
        }
        $this->assign('lou10',$lou10);
        $this->assign('indent10',$indent10);
        $this->assign('son10',$son10);



        $son11 = 0;
        foreach ($sonid10 as $k => $v) {
            foreach ($v as $k1 => $v1) {
                $id = $v1['userid'];
                $son11+=M('user')->where("pid=$id")->count();
                $sonid11[] = M('user')->where("pid=$id")->field('userid')->select();
            }
        }
        $indent11 = 0;
        $lou11 = 0;
        foreach ($sonid11 as $k => $v) {
            foreach ($v as $k1 => $v1) {
                $id = $v1['userid'];
                $a['shouid']=$id;
                $indent11+= M('upgrade')->where("shouid=$ziji and uid=$id and state=2")->count();
                $b['userid']=$id;
                $i = M('loudan')->where("shouid=$ziji and uid=$id")->count();
                $lou11 += $i['loudan'];   
            } 
        }
        $this->assign('lou11',$lou11);
        $this->assign('indent11',$indent11);
        $this->assign('son11',$son11);

        $son12 = 0;
        foreach ($sonid11 as $k => $v) {
            foreach ($v as $k1 => $v1) {
                $id = $v1['userid'];
                $son12+=M('user')->where("pid=$id")->count();
                $sonid12[] = M('user')->where("pid=$id")->field('userid')->select();
            }
        }
        $indent12 = 0;
        $lou12 = 0;
        foreach ($sonid12 as $k => $v) {
            foreach ($v as $k1 => $v1) {
                $id = $v1['userid'];
                $a['shouid']=$id;
                $indent12+= M('upgrade')->where("shouid=$ziji and uid=$id and state=2")->count();
                $b['userid']=$id;
                $i = M('loudan')->where("shouid=$ziji and uid=$id")->count();
                $lou12 += $i['loudan'];   
            }
        }
        $this->assign('lou12',$lou12);
        $this->assign('indent12',$indent12);
        $this->assign('son12',$son12);


        $son13 = 0;
        foreach ($sonid12 as $k => $v) {
            foreach ($v as $k1 => $v1) {
                $id = $v1['userid'];
                $son13+=M('user')->where("pid=$id")->count();
                $sonid13[] = M('user')->where("pid=$id")->field('userid')->select();
            }
        }
        $indent13 = 0;
        $lou13 = 0;
        foreach ($sonid13 as $k => $v) {
            foreach ($v as $k1 => $v1) {
                $id = $v1['userid'];
                $a['shouid']=$id;
                $indent13+= M('upgrade')->where("shouid=$ziji and uid=$id and state=2")->count();
                $b['userid']=$id;
                $i = M('loudan')->where("shouid=$ziji and uid=$id")->count();
                $lou13 += $i['loudan'];   
            }   
        }
        $this->assign('lou13',$lou13);
        $this->assign('indent13',$indent13);
        $this->assign('son13',$son13);



        $son14 = 0;
        foreach ($sonid13 as $k => $v) {
            foreach ($v as $k1 => $v1) {
                $id = $v1['userid'];
                $son14+=M('user')->where("pid=$id")->count();
                $sonid14[] = M('user')->where("pid=$id")->field('userid')->select();
            }
        }
        $indent14 = 0;
        $lou14 = 0;
        foreach ($sonid14 as $k => $v) {
            foreach ($v as $k1 => $v1) {
                $id = $v1['userid'];
                $a['shouid']=$id;
                $indent14+= M('upgrade')->where("shouid=$ziji and uid=$id and state=2")->count();
                $b['userid']=$id;
                $i = M('loudan')->where("shouid=$ziji and uid=$id")->count();
                $lou14 += $i['loudan'];   
            }   
        }
        $this->assign('lou14',$lou14);
        $this->assign('indent14',$indent14);
        $this->assign('son14',$son14);


        $son15 = 0;
        foreach ($sonid14 as $k => $v) {
            foreach ($v as $k1 => $v1) {
                $id = $v1['userid'];
                $son15+=M('user')->where("pid=$id")->count();
                $sonid15[] = M('user')->where("pid=$id")->field('userid')->select();
            }
        }
        $indent15 = 0;
        $lou15 = 0;
        foreach ($sonid15 as $k => $v) {
            foreach ($v as $k1 => $v1) {
                $id = $v1['userid'];
                $a['shouid']=$id;
                $indent15+= M('upgrade')->where("shouid=$ziji and uid=$id and state=2")->count();
                $b['userid']=$id;
                $i = M('loudan')->where("shouid=$ziji and uid=$id")->count();
                $lou15 += $i['loudan'];   
            }  
        }
        $this->assign('lou15',$lou15);
        $this->assign('indent15',$indent15);
        $this->assign('son15',$son15);


        $son16 = 0;
        foreach ($sonid15 as $k => $v) {
            foreach ($v as $k1 => $v1) {
                $id = $v1['userid'];
                $son16+=M('user')->where("pid=$id")->count();
                $sonid16[] = M('user')->where("pid=$id")->field('userid')->select();
            }
        }
        $indent16 = 0;
        $lou16 = 0;
        foreach ($sonid16 as $k => $v) {
            foreach ($v as $k1 => $v1) {
                $id = $v1['userid'];
                $a['shouid']=$id;
                $indent16+= M('upgrade')->where("shouid=$ziji and uid=$id and state=2")->count();
                $b['userid']=$id;
                $i = M('loudan')->where("shouid=$ziji and uid=$id")->count();
                $lou16 += $i['loudan'];   
            }   
        }
        $this->assign('lou16',$lou16);
        $this->assign('indent16',$indent16);
        $this->assign('son16',$son16);



        $this->display();
    }
	// 上级信息
    public function superior(){
        $id = session('userid');
        $pid = M('user')->where("userid = $id")->getField('pid');
        $info = M('user')->where("userid = $pid")->find();
        $this->assign('info',$info);
        $this->display();
    }
	
    public function activation(){
        $uid = session('userid');
        $res = M('store')->where(array('uid'=>$uid))->getField('sv');
        $userjihuo = M('user')->where(array('userid'=>$uid))->getField('is_jihuo');
        $grade = M('user')->where(array('userid'=>$uid))->getField('vip_grade');
        $huan = M('coindets')->where(array('id'=>1))->find();
        $gradelist = M('grade')->select();
        $this->assign('gradelist',$gradelist);
        $this->assign('huan',$huan);
        $this->assign('grade',$grade);
        $this->assign(array('res'=>$res,'userjihuo'=>$userjihuo));
        $this->display();  
    }
   public function Personal()
    {
        $uid = session('userid');
        $uinfo = M('user')->where(array('userid' => $uid))->field('username,is_dailishang,userid,img_head,use_grade,user_credit,vip_grade')->find();



        //判断当前语言
        $lang = LANG_SET;
        if (preg_match("/zh-C/i", $lang))
            $lantype = 1;//简体中文
        if (preg_match("/en/i", $lang))
            $lantype = 2;//English

        $this->assign('uid', $uid);
        $this->assign('uinfo', $uinfo);
        $this->assign('lantype', $lantype);
        $this->display();
    }

    public function Imgup()
    {
        $uid = session('userid');
        $picname = $_FILES['uploadfile']['name'];
        $picsize = $_FILES['uploadfile']['size'];
        if ($uid != "") {
            if ($picsize > 2014000) { //限制上传大小
                ajaxReturn('图片大小不能超过2M', 0);
            }
            $type = strstr($picname, '.'); //限制上传格式
            if ($type != ".gif" && $type != ".jpg" && $type != ".png" && $type != ".jpeg") {
                ajaxReturn('图片格式不对', 0);
            }
            $rand = rand(100, 999);
            $pics = uniqid() . $type; //命名图片名称
            //上传路径
            $pic_path = "./Public/home/wap/heads/" . $pics;
            move_uploaded_file($_FILES['uploadfile']['tmp_name'], $pic_path);
        }
        $size = round($picsize / 1024, 2); //转换成kb
        $pic_path = trim($pic_path, '.');
        if ($size) {
            $res = M('user')->where(array('userid' => $uid))->setField('img_head', $pics);
            ajaxReturn($pic_path, 1);
        }
    }

    public function test()
    {
        $this->display();
    }

    public function imgUps()
    {
        if (IS_AJAX) {
            $uid = session('userid');
            $dataflow = trim(I('dataflow'));
            $base64 = str_replace('data:image/jpeg;base64,', '', $dataflow);
            $img = base64_decode($base64);
            //保存地址
            $imgDir = './Public/home/wap/heads/';
            //要生成的图片名字
            $filename = md5(time() . mt_rand(10, 99)) . ".png"; //新图片名称
            $newFilePath = $imgDir . $filename;
            $res = file_put_contents($newFilePath, $img);//返回的是字节数
            if ($res > 1000) {
                //修改头像
                $res_change = M('user')->where(array('userid' => $uid))->setField('img_head', $filename);
                if ($res_change) {
                    ajaxReturn('头像修改成功', 1);
                } else {
                    ajaxReturn('头像修改失败', 0);
                }
            } else {
                ajaxReturn('头像修改失败', 0);
            }
        }
    }


    public function Setuname()
    {
        $uid = session('userid');
        $uname = M('user')->where(array('userid' => $uid))->getField('username');
        if (IS_AJAX) {
            $uname = trim(I('uname'));
            if ($uname == '') {
                ajaxReturn('请填写昵称', 0);
            } else {
                $res_Save = M('user')->where(array('userid' => $uid))->setField('username', $uname);
                if ($res_Save) {
                    ajaxReturn('昵称修改成功', 1);
                } else {
                    ajaxReturn('昵称修改失败', 0);
                }
            }
        }
        $this->assign('uname', $uname);
        $this->display();
    }

   public function Mobile()
    {
        $uid = session('userid');
        $uname = M('user')->where(array('userid' => $uid))->getField('mobile');
        // if (IS_AJAX) {
        //     $uname = trim(I('uname'));
        //     if ($uname == '') {
        //         ajaxReturn('请填写姓名', 0);
        //     } else {
        //         $res_Save = M('user')->where(array('userid' => $uid))->setField('username', $uname);
        //         if ($res_Save) {
        //             ajaxReturn('昵称修改成功', 1, '/User/Personal');
        //         } else {
        //             ajaxReturn('昵称修改失败', 0, '/User/Personal');
        //         }
        //     }
        // }
        $this->assign('uname', $uname);
        $this->display();
    }
       

    public function Setpwd()
    {
        $type = trim(I('type'));

        if ($type == 1) {
            $title = '登录密码修改';
        } else {
            $title = '支付密码修改';
        }
        if (IS_AJAX) {
            $user = D('Home/User');
            $user_object = D('Home/User');
            $uid = session('userid');
            $pwd = trim(I('pwd'));
            $pwdrpt = trim(I('pwdrpt'));
            $type = trim(I('pwdtype'));
            if ($pwdrpt == '') {
                ajaxReturn('新密码不能为空哦', 0);
            }
            $account = M('user')->where(array('userid' => $uid))->Field('account,mobile,login_pwd')->find();
            //验证初始密码
            $user_info = $user_object->Savepwd($account['mobile'], $pwd, $type);
            $salt = substr(md5(time()), 0, 3);
            if ($type == 1) {
                //密码加密
                $data['login_pwd'] = $user->pwdMd5($pwdrpt, $salt);
                $data['login_salt'] = $salt;
            } else {
                $data['safety_pwd'] = $user->pwdMd5($pwdrpt, $salt);
                $data['safety_salt'] = $salt;
            }
            $res_Sapwd = M('user')->where(array('userid' => $uid))->save($data);
            if ($res_Sapwd) {
                ajaxReturn('密码修改成功', 1, '/Inex/index');
            } else {
                ajaxReturn('密码修改失败', 0);
            }
        }
        $this->assign('title', $title);
        $this->assign('type', $type);
        $this->display();
    }

    public function News()
    {
        $newinfo = M('news')->where(array('type'=>1))->order('id desc')->limit(8)->select();
        $this->assign('newinfo', $newinfo);
        $this->display();
    }

    public function Newsdetail()
    {
        $nid = I('nid', 'intval', 0);
        $newdets = M('news')->where(array('id' => $nid))->find();
        $this->assign('newdets', $newdets);
        $this->display();
    }

    //个人二维码
    public function Sharecode()
    {
        $time = time();
        $userid = session('userid');

//        $u_ID = M('user')->where(array('userid'=>$userid))->getField('mobile');
        $u_ID = $userid;
        $drpath = './Uploads/Scode';
        $imgma = 'codes' . $userid . '.png';
        $urel = './Uploads/Scode/' . $imgma;
       if (!file_exists($drpath . '/' . $imgma)) {
            sp_dir_create($drpath);
            vendor("phpqrcode.phpqrcode");
            $phpqrcode = new \QRcode();
            $hurl ="http://".$_SERVER['SERVER_NAME']. U('Login/register/mobile/' . $u_ID);
            $size = "7";
            //$size = "10.10";
            $errorLevel = "L";
            $phpqrcode->png($hurl, $drpath . '/' . $imgma, $errorLevel, $size);

            
            $phpqrcode->scerweima1($hurl,$urel,$hurl);

         
       }
        $aurl = "http://".$_SERVER['SERVER_NAME']. U('Login/register/mobile/' . $u_ID);

        //查询直推人数
        $num = M('user')->where("pid = $userid")->count();
        $this->assign('num',$num);

        $this->urel = ltrim($urel,".");
        $this->aurl = $aurl;
        $a = M('user')->where("userid=$userid")->field('userlevel')->find();
        $level = $a['userlevel'];
        // dump($level);die;

        $this->assign('userid',$userid);
        
        $this->assign('level',$level);
        $this->display();
    }

    public function Teamdets($layer)
    {   
        //查询我的会员
        $uid = session('userid');
        $um = M('user');
        $layer = I('layer');

        if($layer == 1){
            $son = M('user')->where("userid = $uid")->select();
        }else{
            $son[] = M('user')->where("userid = $uid")->find();
            for ($i=1; $i < $layer; $i++) {
                foreach ($son as $k => $v) {
                    $id = $v['userid'];
                    $sinfo = M('user')->where("cid = $id")->select();
                    foreach ($sinfo as $key => $value) {
                        $sid[] = $value;
                    }
                    // dump($sid);die;
                }
                $son = $sid;
                $sid = [];
            }
        }
        $this->assign('son',$son);
        // dump($sinfo);die;
        //一级
        $one = $um->where(array('pid'=>$uid))->count();
        $this->assign('fenxiang',$one);
        //认证
        $renzheng = M('user')->where(array('pid'=>$uid,'renzhen'=>1))->count();
        $this->assign('renzheng',$renzheng);

        $where['pid'] = $uid;

        $muinfo = M('user')->where($where)->order('userid desc')->select();

        $where = "pid={$uid} OR gid={$uid} OR ggid={$uid}";
        $num = M('user')->where($where)->count();

        $this->assign('num', $num);
        $this->assign('muinfo', $muinfo);
        $this->display();
    }
    public function Teamdets_2()
    {
        $uid = session('userid');
        $um = M('user');
        //一级
        $one = $um->where(array('pid'=>$uid))->count();
        $this->assign('fenxiang',$one);
        //认证
        $renzheng = M('user')->where(array('pid'=>$uid,'renzhen'=>1))->count();
        $this->assign('renzheng',$renzheng);

        $where['gid'] = $uid;

        $muinfo = M('user')->where($where)->order('userid desc')->select();
		//var_dump($muinfo);die();
        $where = "pid={$uid} OR gid={$uid} OR ggid={$uid}";
        $num = M('user')->where($where)->count();

        $this->assign('num', $num);
        $this->assign('muinfo', $muinfo);
        $this->display();
    }

    private function getchilduser($pid){
        static $arr;
        $user = M('user')->select();
        foreach($user as $k => $v){
            if($v['pid'] == $pid){
                $arr[] = $v;
                $this->getchilduser($v['userid']);
            }
        }
        return $arr;
    }
	
	/**
     * 短信验证码
     */
    public function sms(){
			$uid = session('userid');
    		$phone = M('user')->where(array('userid'=>$uid))->getField('mobile');
            $randStr = str_shuffle('1234567890');  
            $code = substr($randStr,0,6);
            $msg="你的验证码为：".$code."【77科技】";
            $this->NewSms($phone,$msg);
            
            session('code',$code);
			ajaxReturn('yes');
        
    }
	
	/**
     * 短信接口
     */
    public function NewSms($phone,$msg)
    {
            $url="http://service.winic.org:8009/sys_port/gateway/index.asp?";
            $data = "id=%s&pwd=%s&to=%s&content=%s&time=";
            $id = 'yizhangjin';
            $pwd = 'yizhangjin1314';
            $to = $phone; 
            $content = iconv("UTF-8","GB2312",$msg);
            $rdata = sprintf($data, $id, $pwd, $to, $content);
            
            
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST,1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,$rdata);
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
            $result = curl_exec($ch);
            curl_close($ch);
            $result = substr($result,0,3);
            return $result;
    }

    public function jihuo(){
        if(IS_POST){
            $uid = session('userid');
            $number = I('number');
            $parent = M('store')->where('uid = '.$uid)->getField('sv');
            $svnum = M('coindets')->where(array('id'=>1))->find();
            $coin_price = 100/$svnum['coin_price'];
            $res = M('user')->where(array('userid'=>$uid))->getField('vip_grade');
			$renzhen = M('user')->where(array('userid'=>$uid))->getField('renzhen');
            $gradenum = I('khy');
            //var_dump($res);die();
            if($gradenum<=$res){
                exit(json_encode(array('status'=>0,'msg'=>'您不能预选当前级别')));
            }
			if($renzhen!=1){
                exit(json_encode(array('status'=>0,'msg'=>'请先认证')));
            }
            if($parent<$coin_price){
                 exit(json_encode(array('status'=>0,'msg'=>'账户余额不足')));
            }else{
                $res = M('store')->where('uid = '.$uid)->setDec('sv',$coin_price);
                if($res){
                    $is_jihuo['is_jihuo'] = 1;
                    $is_jihuo['vip_grade'] = $gradenum;
                    M('user')->where('userid='.$uid)->save($is_jihuo);
                    $arr = array(
                        'pay_id'=>$uid,
                        'get_nums' => '-'.$coin_price,
                        'get_time' => time(),
                        'get_type'  => '28',
                        'now_nums'  => $parent-$coin_price,
                        'now_nums_get'  => $parent-$coin_price,
                        'status'=>2
                    );
                    M('tranmoney')->add($arr);
                    exit(json_encode(array('status'=>1,'msg'=>'已激活','url'=>'/Growth/Nofinsh')));
                }
            }
        }
        
    }
	
    /**
     * [Friends 我的好友]
     */
    public function FriendsData()
    {
        $userid = session('userid');
        $where['pid'] = $userid;
        $where['gid'] = $userid;
        $where['ggid'] = $userid;
        $where['_logic'] = 'or';
        if (IS_AJAX) {
            $p = I('p', '0', 'intval');
            $page = $p * 10;
            $u_info = M('user a')->join('ysk_user_huafei b ON a.userid=b.uid')->field('username as u_name,account as u_zh,pid as u_fuji,gid as u_yeji,ggid as u_yyj,pid_caimi,gid_caimi,ggid_caimi,datestr,uid')->where($where)->limit($page, 10)->order('userid')->select();


            if (empty($u_info)) {
                $u_info = null;
            }

            $this->ajaxReturn($u_info);
        }
    }

    //判断是否自己好友
    private function isfriend($uid, $fid)
    {
        $user = M('user');
        $c_info = $user->where(array('userid' => $fid))->field('pid,gid,ggid')->find();
        $pid = $c_info['pid'];
        $gid = $c_info['gid'];
        $ggid = $c_info['ggid'];

        if ($pid == $uid) { //一级
            $lv = M('config')->where(array('id' => 24))->getField('value');
            $lv = $lv / 100;
            $data['lever'] = 1;
            $data['lv'] = $lv;
            return $data;
        } elseif ($pid == $gid) { //二级
            $lv = M('config')->where(array('id' => 25))->getField('value');
            $lv = $lv / 100;
            $data['lever'] = 2;
            $data['lv'] = $lv;
            return $data;
        } elseif ($pid == $gid) { //三级
            $lv = M('config')->where(array('id' => 35))->getField('value');
            $lv = $lv / 100;
            $data['lever'] = 3;
            $data['lv'] = $lv;
            return $data;
        } else {
            return false;
        }
    }

    /**
     * 修改密码
     */
    public function updatepassword()
    {
        if (!IS_AJAX)
            return;

        $password_old = I('post.old_pwdt');
        $password = I('post.new_pwd');
        $passwordr = I('post.rep_pwd');
        $two_password = I('post.new_pwdt');
        $two_passwordr = I('post.rep_pwdt');
        if (empty($password_old)) {
            ajaxReturn('请输入登录密码');
            return;
        }
        if ($password != $passwordr) {
            ajaxReturn('两次输入登录密码不一致');
            return;
        }

        if ($two_password != $two_passwordr) {
            ajaxReturn('两次输入交易密码不一致');
        }

        $user = D('User');
        $user->startTrans();
        //验证旧密码
        if (!$user->check_pwd_one($password_old)) {
            ajaxReturn('旧登录密码错误');
        }

        //=============登录密码加密==============
        if ($password) {
            $salt = substr(md5(time()), 0, 3);
            $data['login_salt'] = $salt;
            $data['login_pwd'] = md5(md5(trim($password)) . $salt);
        }

        //=============安全密码加密==============
        if ($two_password) {
            $two_salt = substr(md5(time()), 0, 3);
            $data['safety_salt'] = $two_salt;
            $data['safety_pwd'] = $two_password = md5(md5(trim($two_passwordr)) . $two_salt);
        }
        if (empty($data)) {
            ajaxReturn("请输入要修改的密码");
        }
        $userid = session('userid');
        $where['userid'] = $userid;
        $res = $user->where($where)->save($data);

        if ($res) {
            $user->commit();
            ajaxReturn("修改成功", 1);
        } else {
            $user->rollback();
            ajaxReturn("修改失败");
        }

    }
    //反馈意见
     public function Complaint()
    {
        $uid = session('userid');
        if (IS_POST) {
			$data = I('post.');
			if($data['title'] == ''){
                ajaxReturn('请输入标题');
            }
			if($data['content'] == ''){
                ajaxReturn('请填写您的宝贵意见');
            }
			if(!isMobile($data['phone'])){
                ajaxReturn('手机格式错误');
            }
			if($data['phone'] == ''){
                ajaxReturn('请输入手机号');
            }
            $content = I('post.content');
            $data['content'] = $content;
            $data['send_id'] = $uid;
            $data['time'] = time();
            $data['title'] = I('title');
            $data['phone'] = I('phone');
            $Complaint = M('nzletter');
            $result = $Complaint->add($data);
            if($result){
                ajaxReturn("提交成功", 1,'/User/Complaint');
            }else{
                ajaxReturn("提交失败");
            }
            exit;
        }
        $this->display();
    }
	

    //关于我们
    public function Aboutus()
    {
		$service_qq= D('config')->where("name='service_qq'")->getField("value");
		$service_wx= D('config')->where("name='service_wx'")->getField("value");
		$this->assign('service_qq',$service_qq);
		$this->assign('service_wx',$service_wx);
        $this->display();
    }

    //实名验证
    public function authentication(){
        $bakinfo = M('bank_name')->order('q_id asc')->select();
        $this->assign('bakinfo',$bakinfo);
        $uids = session('userid');
        $reninfo = M('user')->where(array('userid'=>$uids))->field('userid,username,idcard,wx,zfb,usdt,open_card,bank_id,card_number')->find();
        $bankinfo = M('ubanks')->where(array('uid'=>$uids,'is_default'=>1))->find();
        $is_ren = M('user')->where("userid=$uids")->getField('renzhen');
        $this->assign('is_ren',$is_ren);
        // $is_ren = M('user')->where("userid=$uids")->getField('renzhen');
        // $reninfo['khy'] = $bankinfo['card_id'];
        // $reninfo['yhk'] = $bankinfo['card_number'];
        // $reninfo['khzy'] = $bankinfo['open_card'];
        $this->assign('reninfo',$reninfo);
        if(IS_AJAX){
            $uid = session('userid');
            $crkxm = I('crkxm');
            $khy = I('khy');
            $yhk = I('yhk');
            $khzy = I('khzy');
            $wx = I('wx');
            $zfb = I('zfb');
            $usdt = I('usdt');
            $idcard = I('idcard');
            $renstatus = I('renstatus');
            $yancode = I('code');
            $code = session('code');

            if(empty($crkxm)){
                ajaxReturn('请输入真实姓名',0);
            }
            if(empty($idcard)||$idcard.length<18){
                ajaxReturn('请输入正确的身份证号码',0);
            }
            // if(empty($khy)){
               // ajaxReturn('请选择开户行',0);
            // }
            // if(empty($yhk)){
                // ajaxReturn('请输入银行卡号',0);
            // }
            // if(empty($khzy)){
                // ajaxReturn('请输入开户支行',0);
            // }
            //if(empty($wx)){
             //   ajaxReturn('请输入微信账号',0);
            //}
            //if(empty($zfb)){
              //  ajaxReturn('请输入支付宝账号',0);
            //}
            //if(empty($usdt)){
               // ajaxReturn('请输入USDT',0);
            //}
            //if($code!=$yancode){
             //   ajaxReturn('验证码有误');
         //   }
            //$data['hold_name'] = $crkxm;
            //$data['card_id'] = $khy;
            //$data['card_number'] = $yhk;
            //$data['open_card'] = $khzy;
            //$data['add_time'] = time();
            //$data['user_id'] = $uid;
            //$data['is_default'] = 1;
            $data2['username'] = $crkxm;
            $data2['idcard'] = $idcard;
            $data2['wx'] = $wx;
            $data2['zfb'] = $zfb;
            $data2['usdt'] = $usdt;
            $data2['renzhen'] = 1;
			$data2['bank_uname'] = $crkxm;
			$data2['open_card'] = $khzy;$data2['card_number'] = $yhk;$data2['bank_id'] = $khy;
			$renzhen = M('user')->where(array('userid'=>$uid))->getField('renzhen');
            $res_addcard2 = M('user')->where(array('userid'=>$uid))->save($data2);
            
			
            if($res_addcard2){
				$cha = M('store')->where(array('uid'=>$uid))->getField('cangku_num');
				if($renzhen==0){
					//M('store')->where(array('uid'=>$uid))->setInc('cangku_num',100);
					$data12['releas_time'] = time();
					$data12['is_reward'] = 0;
					M('user')->where(array('userid'=>$uid))->save($data12);
					$arr2 = array(
					'pay_id' => 0,
					'get_id' => $uid,
					'get_nums' => '+100',
					'get_time' => time(),
					'get_type'  => 32,
					'now_nums' => $cha+100,
					'now_nums_get' => $cha+100,
					'status'=>1,
					'my'=>3
					);	
					$res_tranmoney  = M('tranmoney')->add($arr2);
				}
                ajaxReturn('添加实名验证成功',1);
            }
        }
        $this->display();
    }
    public function upgrade(){
        $uid = session('userid');
        $res = M('store')->where(array('uid'=>$uid))->getField('sc');
        $grade = M('user')->where(array('userid'=>$uid))->getField('vip_grade');
        $gradelist = M('grade')->select();
        //差额
        $cha = M('grade')->where(array('id'=>$grade+1))->getField('number');
        $chae = $cha-M('grade')->where(array('id'=>$grade))->getField('number');
        if(IS_POST){
			$is_jihuo = M('user')->where(array('userid'=>$uid))->getField('is_jihuo');
			if($is_jihuo == 0){
				exit(json_encode(array('status'=>1,'msg'=>'请先激活','url'=>'/User/activation')));
			}
            $id = I('khy');
            if($id<=$grade){
                exit(json_encode(array('status'=>2,'msg'=>'您已经不能升级当前等级')));
                return;
            }else{
                $data['vip_grade'] = $id;
                $data['q_grade'] = $grade;
                $res2 = M('user')->where(array('userid'=>$uid))->save($data);
                if($res2){
                    exit(json_encode(array('status'=>1,'msg'=>'已升级','url'=>'/Growth/Purchase')));
                }else{
                    exit(json_encode(array('status'=>2,'msg'=>'升级失败')));
                }   
            }

            // $grade1 = M('grade')->where(array('id'=>$id))->find();
            // $kou = $grade1['number']-M('grade')->where(array('id'=>$grade))->getField('number');
            // if($res<$kou){
            //     exit(json_encode(array('status'=>2,'msg'=>'账户余额不足')));
            //     return;
            // }
            // $parent = M('store')->where(array('uid'=>$uid))->getField('sc');
            // $res = M('store')->where(array('uid'=>$uid))->setDec('sc',$kou);
            // $zong = $kou/2*3;
            // $res3 = M('store')->where(array('uid'=>$uid))->setInc('cangku_num',$zong);
            // $res2 = M('user')->where(array('userid'=>$uid))->save(array('vip_grade'=>$id));
            // if($res&&$res2){
                
            //     $arr = array(
            //         'pay_id'=>$uid,
            //         'get_nums' => '-'.$kou,
            //         'get_time' => time(),
            //         'get_type'  => '30',
            //         'now_nums'  => $parent-$kou,
            //         'now_nums_get'  => $parent-$kou,
            //         'status'=>2,
            //         'my'=>2
            //     );
            //     M('tranmoney')->add($arr);
            //     exit(json_encode(array('status'=>1,'msg'=>'已升级','url'=>'/Growth/Purchase')));
            // }
        }
        $this->assign('chae',$chae);
        $this->assign('gradelist',$gradelist);
        $this->assign('grade',$grade);
        $this->assign('res',$res);
        $this->display();
    }
	/**
     * 常见问题
     */
    public function commonproblem(){
        $res = M('news')->where(array('type'=>3))->field('title,content')->order('id desc')->select();
        $this->assign('res',$res);
        $this->display();
    }
    //退出登录
    public function Loginout()
    {
        session_destroy();
        $this->redirect('Login/login');
    }
}