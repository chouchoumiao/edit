<?php
/**
 * Created by wujiayu.
 * User: Administrator
 * Date: 2016/5/19
 * Time: 13:43
 */

namespace Admin\Controller;

use Think\Controller;

class CommonController extends Controller{

    /**
     * ���췽��
     */
    public function __construct(){

        //ʹ��__construct����ʱ�����ȵ��ø����__construct������
        parent::__construct();

        //���û��session����sessionΪ0�����ҷ��������� login �� reg ���������ת����¼ҳ��
        if( ( (!isset($_SESSION['username'])) || ('' == $_SESSION['username']) ) && ( 'login') != ACTION_NAME && ( 'reg') != ACTION_NAME){
            $this->redirect('Login/login');
        }
    }

    /**
     * �շ���
     * ���û��session����sessionΪ0��û�ж�Ӧ�ķ������������Ĭ�Ͻ�ȥ��¼ҳ�棬
     * �����session��û�ж�Ӧ�ķ������������Ĭ�Ͻ�ȥ��̨��ҳ
     *
     */
    public function _empty(){
        if( (!isset($_SESSION['username'])) || ('' == $_SESSION['username']) ){
            //��session������̨��ҳ��
            $this->redirect('Login/login');
        }else{
            //��session������̨��ҳ��
            $this->redirect('Index/index');
        }
    }
}