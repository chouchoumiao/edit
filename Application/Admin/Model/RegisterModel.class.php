<?php
/**
 * Created by PhpStorm.
 * User: wujiayu
 * Date: 16/5/17
 * Time: 21:38
 */

namespace Admin\Model;

use Think\App;

header("Content-Type:text/html; charset=utf-8");
class RegisterModel {
    /**
     * 确认注册用户是否已经存在相同用户名或者邮箱地址
     * @return bool
     */
    public function checkUserIsExist(){

        $where['username'] = I('post.user_login');
        $where['email'] = I('post.user_email');
        $where['_logic'] = 'OR';

        if(M('m_user')->field('id')->where($where)->find()){
            return false;
        }

        return true;
    }

    /**
     * 将新用户的信息写入数据表User中，并且缓存到Session中
     * @return mixed
     */
    public function addToUser(){

        $username = I('post.user_login');

        $autopass = make_password(); //自动做成密码
        $password =  md5(trim($autopass));//加密密码
        $email = trim($_POST['user_email']); //邮箱
        $regtime = time();

        $token = md5($username.$password.$regtime); //创建用于激活识别码
        $token_exptime = time()+60*60*24*7;//过期时间为24小时后

        $data = array(
            'username' => $username,
            'autopass' => $autopass,
            'password'=> $password,
            'email' => $email,
            'token' => $token,
            'token_exptime' => $token_exptime,
            'regtime' => $regtime
        );


        $ret = M('m_user')->add($data);

        //如果做成成功将数据写入Session中
        if($ret){
            $_SESSION['userInfo'] = $data;
        }

        return $ret;

    }

    /**
     * 发送邮件到新注册用户邮箱
     * @return string
     */
    public function sendMailToUser(){

        $username = $_SESSION['userInfo']['username'];
        $email    = $_SESSION['userInfo']['email'];
        $token    = $_SESSION['userInfo']['token'];

        $emailTitle= "用户帐号激活";//邮件标题
        //邮件主体内容

        $emailContent = "亲爱的".$username."：<br/>感谢您在我站注册了新帐号。<br/>请点击链接激活您的帐号。<br/>
    <a href='http://".MY_SITE."/edit/index.php/Admin/Login/activeEamil/verify/".$token."' target=
'_blank'>http://".MY_SITE."/edit/index.php/Admin/Login/activeEamil/verify/".$token."</a><br/>
    如果以上链接无法点击，请将它复制到你的浏览器地址栏中进入访问，该链接一周内有效。";

        return SendMail($email,$emailTitle,$emailContent)?  true: false;

    }

    /**
     * 接受用户发送过来的激活邮箱的请求，并完成验证过程
     * @return string
     */
    public function activeUserEamil(){

        $verify = I('get.verify');

        $nowtime = time();

        $where = array(
            'status' => '0',
            'token' => $verify
        );

        $data = M('m_user')->field('id,autoPass,token_exptime')->where($where)->find();

        if($data){
            if($nowtime > $data['token_exptime']){ //一周
                $msg = '您的激活有效期已过，请登录您的帐号重新发送激活邮件.';
            }else{
                $data['status'] = 1;
                $autopass = $data['autopass'];
                $where['id']= $data['id'];
                if(M('m_user')->where($where)->save($data)){
                    $msg = "激活成功！<br/>您的初始密码为：$autopass<br/>请及时修改您的密码";
                }
            }
        }else{
            $msg = '取得用户信息失败';
        }
        return $msg;
    }

}