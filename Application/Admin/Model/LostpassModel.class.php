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
   public function isUserExist( $flag ){

       $data = I('post.user_login');
       if( 'username' == $flag ){
           $where['username'] = $data;

       }

       if( 'email' == $flag ){
           $where['email'] = $data;
       }

       return M('m_user')->where($where)->find();
       //return M('m_user')->getLastSql();

   }

}