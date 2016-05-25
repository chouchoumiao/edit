<?php

/**
 * 登录Model
 */
namespace Admin\Model;

//自定义Model是不要继承Model类
class LoginModel {

    private $auth;

    /**
     * 登录检查
     * @return mixed
     */
	public function checklogin(){
		if(empty($_POST['user'])||empty($_POST['pass'])){
			$arr['success'] = 0;
			$arr['msg'] = '登录失败！';
            return $arr;
		}

		//使用I内置函数过滤，如果
		$username = I('post.user','');
		$password = I('post.pass','');

		$this->checkauth($username, $password);

		if( $this->auth ){

			$_SESSION['username'] = $this->auth['username'];
            $arr['success'] = $this->updateAdminInfo();
			return $arr;
		}else{
			$arr['success'] = 0;
			$arr['msg'] = '登录失败！';
			return $arr;
		}
	}

	/**
	 * 判断用户密码
	 * @param $username
	 * @param $password
	 * @return bool
	 */
	private function checkauth($username, $password){

        $where['username'] = $username;
        $auth = M('adminuser')->where( $where )->find();

		if( ( !$auth ) && $auth['password'] == md5($password)){
            $this->auth = $auth;
			return $auth;
		}else{
			return false;
		}
	}

    /**
     * 更新改用户的登录信息
     * @return bool
     */
    private function updateAdminInfo(){

		$counts = $this->auth['login_counts'] + 1;
		$user = $this->auth['username'];
		$nowTime  = date("Y-m-d H:i:s",time());
		$ip = get_client_ip();

        $where['username'] = $user;
        $updateArr = array(
            'login_ip' => $ip,
            'login_counts' => $counts,
            'loginTime' => $nowTime
        );
        // 根据条件保存修改的数据
        return M('adminuser')->where( $where )->save( $updateArr );
	}

	/**
	 * 显示主页面的所有信息
	 * @return array
	 */
	function showMain($name){

		//初始化各字段
		$weixinName = '';
		$isWeixinInfoExist = false;
		$isEventListExist = false;
		$msg = '';
		$username = '';
		$eventNameArr = array();
		$eventUrlArr = array();
		$thisWeixinID = '';

		//获取该用户所有可用的公众号的基本信息
		return $weixinInfo = $this->getWeiInfoByName($name);



//		//锟叫断革拷锟矫伙拷锟角凤拷锟斤拷诳锟斤拷锟斤拷玫墓锟斤拷诤锟�
//		if(empty($weixinInfo)){
//			$msg = '当前未设置过公众号，请添加公众号信息！';
//		}else{
//			$isWeixinInfoExist = true;
//			if(!isset($_SESSION['weixinID'])){
//				$thisWeixinID = $weixinInfo[0]['id'];
//				//登录时追加weixinID的session数值
//				$_SESSION['weixinID'] = $thisWeixinID;
//			}else{
//				$thisWeixinID = $_SESSION['weixinID'];
//			}
//			$username = $weixinInfo[0]['username'];
//			$baseInfo = getConfigWithMMC($thisWeixinID);
//			if($baseInfo){
//				$weixinName = $baseInfo['CONFIG_VIP_NAME'];
//				$_SESSION['weixinName'] = $weixinName;
//				$_SESSION['weixinInfo'] = $baseInfo;
//
//			}
//			$info = $this->getEventListByWeiID($thisWeixinID);
//			if($info){
//				$isEventListExist = true;
//				$eventNameArr = explode(",",$info['eventNameList']);
//				$eventUrlArr = explode(",",$info['eventUrlList']);
//			}
//		}

		//返回相关信息
		return array(
//			'eventNameArr'=>$eventNameArr,
//			'eventUrlArr'=>$eventUrlArr,
//			'weixinName'=>$weixinName,
//			'userName'=>$username,
			'weixinInfo'=>$weixinInfo,
//			'weixinID'=>$thisWeixinID,
//			'isWeixinInfoExist'=>$isWeixinInfoExist,
//			'isEventListExist'=>$isEventListExist,
//			'msg'=>$msg
		);
	}

	/**
	 * 获取该用户所有可用的公众号的基本信息
	 * @return mixed
	 */
	private function getWeiInfoByName($userName){

        $arr = array(
            'username' => $userName,
            'weixinStatus' => 1
        );

        return M('admintoweiid')->where( $arr )->select();
//		$sql = "select * from AdminToWeiID
//					where username = '$userName'
//					AND weixinStatus = 1";
//		if(DB::findAll($sql)){
//			return DB::findAll($sql);
//		}else{
//			array();
//		}
	}

	/**
	 * 取得该公众号设置的活动list一览
	 * @param $weiID
	 * @return mixed
	 */
	private function getEventListByWeiID($weiID){
		$sql = "select * from setEventForAdmin where WEIXIN_ID = $weiID";
		return DB::findOne($sql);

	}

}