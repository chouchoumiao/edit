<?php
/**
 * Created by PhpStorm.
 * User: wujiayu
 * Date: 16/5/17
 * Time: 21:38
 */

namespace Admin\Model;
use Think\Model;

header("Content-Type:text/html; charset=utf-8");
class LostpassModel {

    private $username;
    private $email;
    private $newPass;

    /**
     * 根据填写的用户名或者邮箱地址重新生成密码并发送邮箱
     * @return mixed
     */
    public function lostPassDo(){

        //检查Form表单
        $msg = checkForm( 'lostpass' );

        //检查表单
        if('' != $msg ){
            //如果检查表单返回的是username或者email，则表示输入的是用户名或者邮箱
            if( ('username' == $msg) ||('email' == $msg) ){

                //根据username或者邮箱查找数据表中是否存在对应的用户
                $arr = $this->getUser( $msg );

                if( $arr['username']){
                    //生成新的自动密码，写入数据库，并且发送给用户邮箱告知新密码
                    $this->username = $arr['username'];
                    $this->email = $arr['email'];

                    //生成新密码
                    $this->newPass = make_password();

                    $newMd5Pass = md5($this->newPass);

                    //需要更新的字段

                    $data['autopass'] = $this->newPass;
                    $data['password'] = $newMd5Pass;
                    $data['updateTime'] = time();

                    //根据用户名更新
                    $where['username'] = $this->username;

                    //更新数据(密码，MD5密码，时间)
                    if (!M('m_user')->where($where)->save($data)){
                        //更新失败
                        $arr['success'] = 'NG';
                        $arr['msg'] = '重置密码出错，请重试！';
                        return $arr;
                    }

                    //发送邮件
                    if(!$this->sendNewPassToMail()){
                        $arr['success'] = 'NG';
                        $arr['msg'] = '重置密码后发送邮件出错，请重试！';
                        return $arr;
                    }

                    //发送成功
                    $arr['success'] = 'OK';
                    $arr['msg'] = '重置密码已发送，请登录邮箱确认! ';
                    return $arr;

                }else{

                    $arr['success'] = 'NG';
                    $arr['msg'] = '不存在您输入的用户名或者邮箱';
                    return $arr;
                }

            }else{
                $arr['success'] = 'NG';
                $arr['msg'] = $msg;
                return $arr;
            }
        }
    }


    /**
     * 根据用户名或者邮箱地址取得用户信息
     * @param $flag
     * @return mixed
     */
    private function getUser( $flag ){

       $data = I('post.user_login');
       if( 'username' == $flag ){
           $where['username'] = $data;

       }

       if( 'email' == $flag ){
           $where['email'] = $data;
       }

       return M('m_user')->where($where)->find();

    }

    /**
     * 重置密码发送邮件
     * @return bool
     */
    private function sendNewPassToMail(){

        $emailTitle= "重置密码";//邮件标题
        //邮件主体内容

        $emailContent = "亲爱的".$this->username."：<br/>您的重置密码为：".$this->newPass."<br/>重置密码登录后请自行修改！";

        return SendMail($this->email,$emailTitle,$emailContent)?  true: false;
    }

}