<?php

/**
 * 登录Model
 */
namespace Admin\Model;

	class UserModel {

		private $id;
		private $username;
		private $loginName;
		private $autopass;
		private $password;
		private $email;
		private $token;
		private $token_exptime;
		private $regtime;
		private $updateTime;

		/**
		 * 取得当前用户详细信息
		 * 公有方法
		 * @return mixed
		 */
		public function getTheUserInfo($id){
			return $this->getTheUser($id);
		}

		/**
		 * 取得所有用户
		 * 公用方法
		 * @return mixed
		 */
		public function getAllUserInfo(){
			return $this->getAllUser();
		}

        public function delTheUserInfo($id){
            return $this->delTheUser($id);
        }

		/**
		 * 取得当前用户的详细信息(多表查询)
		 * 私有方法
		 * @return mixed
		 */
		private function getTheUser($id){
			//多表联合查询
            $where['ccm_m_user.id'] = $id;
			return M('m_user')->join('RIGHT JOIN ccm_user_detail_info ON ccm_user_detail_info.uid = ccm_m_user.id')->where($where)->find();
		}

		/**
		 * 取得所有用户信息(多表查询)
		 * 私有方法
		 * @return mixed
		 */
		private function getAllUser(){
			//多表联合查询
			return M('m_user')->join('RIGHT JOIN ccm_user_detail_info ON ccm_user_detail_info.uid = ccm_m_user.id')->select();
		}

        private function delTheUser($id){

            //新删除主表，成功的情况下删除明细表
            if(M('m_user')->where("id=$id")->delete()){
                //返回删除明细表的结果
                return M('user_detail_info')->where("uid=$id")->delete();
            }else{
                return false;
            }
        }

		/**
		 * 将新用户的信息写入数据表User中，并且缓存到Session中
		 * @return mixed
		 */
		public function addToUser($pass='auto'){

			//新增用户主表
			$this->id = M('m_user')->add($this->setData($pass));

			//新增主表成功后，追加明细表初始值
			if($this->id > 0){
				//追加明细表
				if($this->addToUserDetail()){
					return true;
				}else{

					//失败的情况下，要将主表的新增用户也删除，避免脏数据
					$this->delUserDetail();
					return false;
				}
			}else{
				return false;
			}
		}

		/**
		 * 新增用户时整理数据（分以下两种情况进行）
		 * 1.注册时密码是后台新增
		 * 2.管理员新增用户时提交时已经生成了密码
		 * @param string $pass
		 * @return array
		 */
		private function setData($pass='auto'){
			$this->loginName = I('post.user_login');

			//用户自己注册的情况
			if('auto' == $pass){
				$this->autopass = make_password(); //自动做成密码
				$this->username = $this->loginName;
			//管理员进行追加用户时候
			}else{
				$this->autopass = $pass; //接受传入密码
				$this->username = I('post.user_name');
			}

			$this->password =  md5($this->autopass);//加密密码
			$this->email = trim($_POST['user_email']); //邮箱
			$this->regtime = time();
			$this->updateTime = $this->regtime;

			$this->token = md5($this->username.$this->password.$this->regtime); //创建用于激活识别码
			$this->token_exptime = time()+60*60*24*7;//过期时间为一周后

			return array(
				'login_name' => $this->loginName,
				'username' => $this->username,	//注册时候默认昵称和用户名设置为一样
				'autopass' => $this->autopass,
				'password'=> $this->password,
				'head_img'=> 'profile8.jpg',
				'email' => $this->email,
				'token' => $this->token,
				'token_exptime' => $this->token_exptime,
				'regtime' => $this->regtime,
				'updateTime' => $this->updateTime
			);
		}


		/*********************************************注册相关***************************************************/
		/**
		 * 确认注册用户是否已经存在相同用户名或者邮箱地址
		 * @return bool
		 */
		public function checkUserIsExist(){

			$where['login_name'] = I('post.user_login');
			$where['email'] = I('post.user_email');
			$where['_logic'] = 'OR';

			if(M('m_user')->field('id')->where($where)->find()){
				return false;
			}

			return true;
		}

		/**
		 * 追加用户明细表
		 * @return mixed
		 */
		private function addToUserDetail(){

			$data = array(
				'uid' => $this->id,
				'udi_sex'=>1,
				'udi_tel' => '',
				'udi_address' => '',
				'udi_dep_id'=>0,
				'udi_auto_id'=>0,
				'udi_description'=> '',
				'udi_update_time'=> time()
			);

			return M('user_detail_info')->add($data);
		}

		private function delUserDetail(){
			$where['uid'] = $this->id;
			return M('m_user')->where($where)->delete();
		}


		/**
		 * 发送邮件到新注册用户邮箱
		 * @return string
		 */
		public function sendMailToUser(){

			$emailTitle= "用户帐号激活";//邮件标题
			//邮件主体内容

			$emailContent = "亲爱的".$this->username."：<br/>感谢您在我站注册了新帐号。<br/>请点击链接激活您的帐号。<br/>
    <a href='http://".MY_SITE."/edit/index.php/Admin/Login/activeEamil/verify/".$this->token."' target=
'_blank'>http://".MY_SITE."/edit/index.php/Admin/Login/activeEamil/verify/".$this->token."</a><br/>
    如果以上链接无法点击，请将它复制到你的浏览器地址栏中进入访问，该链接一周内有效。";

			return SendMail($this->email,$emailTitle,$emailContent)?  true: false;

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
		/*********************************************注册相关***************************************************/
	}