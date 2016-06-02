<?php
/**
 * Created by wujiayu.
 * User: Administrator
 * Date: 2016/5/19
 * Time: 13:59
 */

namespace Admin\Controller;

use Think\Controller;

class LoginController extends Controller{

    private $actionName;

    public function index(){

        if((isset($_POST['action'])) && ('' != $_POST['action'])){
            $this->actionName = I('post.action');

            switch ($this->actionName){
                case 'login':
                    $this->login();
                    break;
                case 'reg':
                    $this->reg();
                    break;
                case 'lostpass':
                    $this->lostPass();
                    break;
                default:
                    break;

            }
        }else{
            if( (!isset($_SESSION['username'])) || ('' == $_SESSION['username']) ){
                //无session则进入后台主页面
                $this->redirect('Login/login');
            }else{
                //有session则进入后台主页面
                $this->redirect('Index/index');
            }
        }

    }
    private function login(){

        //检查表单
        $msg = checkForm( $this->actionName );

        if( '' != $msg ){

            $arr['success'] = 'NG';
            $arr['msg'] = $msg;
            echo json_encode($arr);
            exit;
        }

        //检查登录用户名密码是否正确
        if(D('login')->checklogin()){
            //进入后台主页
            //$this->redirect('Index/index');
            //exit;
            $arr['success'] = 'OK';
            echo json_encode($arr);
            exit;


        }else{
            $arr['success'] = 'NG';
            $arr['msg'] = '用户名或密码错误，请重新输入！';
            echo json_encode($arr);
            exit;
        }

    }

    /**
     * 退出登录
     *
     */
    public function logout(){
        unset($_SESSION['username']);
        unset($_SESSION['uid']);
        $this->display('login');
    }

    /***********************************************用户注册***********************************************/
    /**
     * 注册
     */
    private function reg()
    {


        $msg = checkForm( $this->actionName );
        if( '' != $msg ){

            $arr['success'] = 'NG';
            $arr['msg'] = $msg;
            echo json_encode($arr);
            exit;
        }

        //判断新注册的用户名和邮箱地址是否已经存在
        $register = D('Register');

        //判断数据库中是否已经存在对应的用户和邮箱地址
        if (!$register->checkUserIsExist()) {
            $arr['success'] = 'NG';
            $arr['msg'] = '用户名或者邮箱地址已存在，请换个其他的用户名或者邮箱地址';
            echo json_encode($arr);
            exit;
        }

        //将新注册的用户信息追加入数据表User，并且生成Session
        if (!$register->addToUser()) {
            $arr['success'] = 'NG';
            $arr['msg'] = '新用户追加到数据库失败';
            echo json_encode($arr);
            exit;
        }

        //发送邮件给注册用户的邮箱地址
        if($register->sendMailToUser()){
            $arr['success'] = 'OK';
            $arr['msg'] = '恭喜您，注册成功！<br/>请登录到您的邮箱及时激活您的帐号！！';
        }else{
            $arr['success'] = 'OK';
            $arr['msg'] = '注册失败，请联系wu_jy1984@126.com';
        }
        echo json_encode($arr);
        exit;
    }

    /**
     * 激活注册邮件
     */
    public function activeEamil(){

        echo D('Register')->activeUserEamil();
        exit;

    }

    /***********************************************用户注册***********************************************/

    /***********************************************忘记密码***********************************************/
    /**
     * 忘记密码时候执行操作
     */
    private function lostPass(){
        echo json_encode(D('Lostpass')->lostPassDo());
        exit;
    }
    /***********************************************忘记密码***********************************************/
}