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
        private $sex;
		private $token;
		private $token_exptime;
		private $regtime;
		private $updateTime;
        private $dept;
        private $auto;

        private $isSend; //新增用户是是否发送邮件通知

        private $dataArray; //整合后主表数据
        private $detailDataArray; //整合后明细数据

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


        public function doReg(){

            //判断是否已经存在该用户信息
            if(!$this->checkUserIsExist()){
                return 1;               //存在用户
            }

            //整合表单数据

            $this->setData();           //设置主表信息

            //注册新用户
            if($this->addToUser() <= 0){
                return 2;              //主表追加错误
            }

            $this->setDetailData();     //设置明细表数据

            if(!$this->addToUserDetail()){
                //失败的情况下，要将主表的新增用户也删除，避免脏数据
                $this->delUserDetail();
                return 3;
            }

            //向新注册用户发生邮件
            if(!$this->sendMailToUser()){
                return 4;                   //不成功

            }else{
                return 100;                   //成功

            }



        }


        /**
         * 整合成主表信息
         */
        private function setData(){
            $this->loginName = I('post.user_login');

            //用户自己注册的情况
            $this->autopass = make_password(); //自动做成密码
            $this->username = $this->loginName;

            $this->password =  md5($this->autopass);//加密密码
            $this->email = trim($_POST['user_email']); //邮箱
            $this->regtime = time();
            $this->updateTime = $this->regtime;

            $this->token = md5($this->username.$this->password.$this->regtime); //创建用于激活识别码
            $this->token_exptime = time()+60*60*24*7;//过期时间为一周后

            $this->dataArray  = array(
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

		/**
		 * 将新用户的信息写入数据表User中
		 * @return mixed
		 */
		private function addToUser(){

			//新增用户主表
			return $this->id = M('m_user')->add($this->dataArray);

		}


        /**
         * 整合明细表数据
         */
        private function setDetailData(){
            $this->detailDataArray = array(
                'uid' => $this->id,
                'udi_sex'=>1,
                'udi_tel' => '',
                'udi_address' => '',
                'udi_dep_id'=>0,
                'udi_auto_id'=>0,
                'udi_description'=> '',
                'udi_update_time'=> time()
            );
        }

        /**
         * 追加用户明细表
         * @return mixed
         */
        private function addToUserDetail(){

            return M('user_detail_info')->add($this->detailDataArray);
        }

        /**
         * 删除用户主表数据
         * @return mixed
         */
        private function delUserDetail(){
            $where['uid'] = $this->id;
            return M('m_user')->where($where)->delete();
        }


        /*********************************************注册相关***************************************************/
		/**
		 * 确认注册用户是否已经存在相同用户名或者邮箱地址
		 * @return bool
		 */
		private function checkUserIsExist(){

			$where['login_name'] = I('post.user_login');
			$where['email'] = I('post.user_email');
			$where['_logic'] = 'OR';

			if(M('m_user')->field('id')->where($where)->find()){
				return false;
			}

			return true;
		}




		/**
		 * 发送邮件到新注册用户邮箱
		 * @return string
		 */
		private function sendMailToUser(){

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


        /*********************************************新增用户***************************************************/

        /**
         * 新增用户
         */
        public function addNewUser(){

            $this->setNewUserData();

            $this->checkAddNewUser();

            if(!$this->checkUserIsExist()){
                ToolModel::goBack('已存在相同用户名或者邮箱地址了');
                exit;
            }

            $this->setNewUserData();

            if($this->addToUser() <= 0){
                ToolModel::goBack('新增用户主表失败！');
                exit;
            }

            $this->setNewUserDetailData();

            if($this->addToUserDetail() <= 0){
                ToolModel::goBack('新增用户明细表失败！');
                exit;
            }

            //向新用户发送邮件
            if( 'on' == $this->isSend ){
                if(!$this->sendMailToUser()){
                    ToolModel::goBack('发生邮件失败,请联系wu_jy1984@126.com');
                    exit;
                }else{
                    ToolModel::goToUrl('新增用户成功,邮件已发送给新用户邮箱地址！','all');
                    exit;
                }
            }


        }

        /**
         * 获得提交的数据
         */
        private function setNewUserData(){
            $this->loginName = I('post.user_login');    //登录用户名
            $this->username = I('post.user_name');      //昵称
            $this->email = I('post.user_email');        //邮件地址
            $this->sex = I('post.sex');                 //性别
            $this->autopass = I('post.user_pass');      //初始密码
            $this->password = md5($this->autopass);     //密码加密

            //部门需要特殊处理 start
            $deptList = array();

            $deptCount = D('Dept')->getDeptCount();

            //取得所有的dept个数，然后根据上传的dept进行确认，做成数组
            for($i = 1;$i<=$deptCount;$i++){
                $name = 'dept'.$i;
                if($_POST[$name]){
                    $deptList[] = I("post.$name");
                }
            }

            //数组转化为json格式
            $this->dept = json_encode($deptList);
            //部门需要特殊处理 end

            $this->auto = I('post.auto');               //角色
            $this->isSend = I('post.isSend');           //是否发生邮件

            $this->regtime = time();
            $this->updateTime = $this->regtime;

            $this->token = md5($this->username.$this->password.$this->regtime); //创建用于激活识别码
            $this->token_exptime = time()+60*60*24*7;//过期时间为一周后

            $this->dataArray  = array(
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


        private function setNewUserDetailData(){
            $this->detailDataArray = array(
                'uid' => $this->id,
                'udi_sex'=>$this->sex,
                'udi_tel' => '',
                'udi_address' => '',
                'udi_dep_id'=>$this->dept,
                'udi_auto_id'=>$this->auto,
                'udi_description'=> '',
                'udi_update_time'=> time()
            );
        }

        /**
         * 私有方法
         * 新增用户时验证表单
         */
        private function checkAddNewUser(){

            //判断是否存在POST请求发送过来数据
            if(!isset($_POST)){
                ToolModel::goBack('警告，非法操作！');
            }

            //判断loginname
            if('' == $this->loginName){
                ToolModel::goBack('警告，用户名不能为空！');
            }
            if(strlen($this->loginName) < 6){
                ToolModel::goBack('警告，用户名长度不能小于六位！');
            }

            //提交的默认生成初始是否为空
            if('' == $this->autopass){
                ToolModel::goBack('警告，初始密码不能为空！');
            }

            //判断提交的邮件地址正确性
            if('' == $this->email){
                ToolModel::goBack('警告，邮箱地址不能为空！');
            }
            if(!is_mail($this->email)){
                ToolModel::goBack('警告，邮箱格式错误！');
            }

            //判断提交的部门复选框是否都为空
            //array_filter函数是去除数组内空内容，如果剩下为空数组
            if( empty(array_filter(json_decode($this->dept)))){
                ToolModel::goBack('警告，至少选择一个部门！');
            }

        }
        /*********************************************新增用户***************************************************/



    }