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
        $where['login_name'] = I('post.user_login');
		$where['password'] = md5(I('post.user_pass'));

		$userInfo = M('m_user')->where( $where )->find();
		if( $userInfo ){

			//检查通过后将必要的内容写入Session，方便调用
			$_SESSION['username'] = $userInfo['username'];
			$_SESSION['uid'] = $userInfo['id'];
			$_SESSION['img'] = $userInfo['head_img'];

			return true;
		}else{
			return false;
		}
	}

}