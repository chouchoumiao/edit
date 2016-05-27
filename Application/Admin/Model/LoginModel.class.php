<?php

/**
 * 登录Model
 */
namespace Admin\Model;

//自定义Model是不要继承Model类
class LoginModel {

    /**
     * 登录检查
     * @return mixed
     */
	public function checklogin(){
		if(empty($_POST['user_login'])||empty($_POST['user_pass'])){
			return false;
		}

		if( $this->checkauth() ){
			//存在对应用户后将用户名放入session中，并返回真
			$_SESSION['username'] = I('post.user_login','');
            return true;
		}else{
			return false;
		}
	}

	/**
	 * 判断用户密码
	 * @return bool|mixed
	 */
	private function checkauth(){

		//使用I内置函数过滤，如果
        $where['username'] = I('post.user_login','');
		$where['password'] = md5(I('post.user_pass',''));

		if( M('m_user')->where( $where )->find() ){
			return true;
		}else{
			return false;
		}
	}

}