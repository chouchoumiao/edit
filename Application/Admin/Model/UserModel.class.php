<?php

/**
 * 登录Model
 */
namespace Admin\Model;

	use Think\Log;

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
        private $img;
        private $status;
        private $area;  //拼接后地区字符串
        private $sel3;  //地区三级内容
        private $sel4;  //地区四级内容
        private $address;
        private $tel;
        private $description;
        private $workPlace;

        private $areaObj;   //area表

        private $dataArray; //整合后主表数据
        private $detailDataArray; //整合后明细数据

        private $join;  //join条件
        private $order; //排序条件

        public function __construct(){
            $this->join = 'INNER JOIN ccm_user_detail_info ON ccm_user_detail_info.uid = ccm_m_user.id';
            $this->order ='ccm_m_user.regtime desc';

            $this->areaObj = M('area');
        }

        /**
         * 根据传入的用户id取得用户明细表中的DEPT
         * @param $uid
         * @return bool
         */
        public function getTheDept($uid){
            $field = 'udi_dep_id';
            $where['uid'] = $uid;
            $data = M('user_detail_info')->field($field)->where($where)->find();
            if($data){
                return $data['udi_dep_id'];
            }
            return false;
        }

        public function getNowUserDetailInfo(){
            $where['uid'] = $_SESSION['uid'];

            return M('user_detail_info')->where($where)->find();
        }


        /**
         * 根据传入的id判读是否存在对应的用户，防止错误的id输入
         * @param $id
         * @return bool
         */
        public function idIsExist($id){
            $where['id'] = $id;

            if(M('m_user')->where($where)->count() > 0){
                return true;
            }
            return false;

        }

        //更新用户
        public function updateUser(){

            //判断是否有取到修改用户的id
            if( (isset($_POST['uid'])) && ('' != $_POST['uid'])){
                $this->id = intval(I('post.uid'));
            }else{
                ToolModel::goBack('传值错误');
            }

            //取得该用户的姓名
            if( I('post.oldUsername') == I('post.user_name') ){
                $this->username = I('post.oldUsername');      //无修改则取原先的值
            }else{
                //判断新姓名长度是否超过30位
                if(!ValidateModel::length(I('post.user_name'),2,0,30)){
                    ToolModel::goBack('警告，姓名不能超过30位');
                }
                $this->username = I('post.user_name');      //修改了则取新的姓名
            }

            //取得该用户的性别
            if( intval(I('post.oldSex')) == intval(I('post.sex')) ){
                $this->sex = I('post.oldSex');      //无修改则取原先的值
            }else{
                $this->sex = I('post.sex');      //修改了则取新的性别
            }

            //取得密码 如果没有设置新密码，则使用旧密码，否则使用md5加密新密码
            if('' == I('post.user_pass')){
                $this->password = I('post.oldMd5Pass');
            }else{
                //判断新密码格式和长度
                if(!ValidateModel::isPWD(I('post.user_pass'),6,20)){
                    ToolModel::goBack('警告，密码不能含有特殊字符并且大于6位小于20位');
                }
                $this->password = md5(I('post.user_pass'));     //密码加密
            }

            //做成地区数据
            $sel1 = I('post.sel1');
            $sel2 = I('post.sel2');
            $sel3 = I('post.sel3');
            $sel4 = I('post.sel4');

            //都是空则表明没有选择，地址为数据库取得的老数据
            if(($sel3 == '') && ($sel4 == '') && ($sel2 == '') && ($sel1 == '')){
                $this->area = I('post.oldArea');
            }else{
                //四级有数据
                if($sel4 != ''){
                    $this->sel4 = $sel4;
                    $this->area =  $this->get3thSelName();
                }else{
                    //四级为空，三级有值，继续判断三级是否为末尾(只有一个数字)
                    if( ($sel3 != '') && !strpos($sel3,',')){
                        $this->sel3 = $sel3;
                        $this->area = $this->get2thSelName();
                    }else{
                        $this->area = I('post.oldArea');
                    }
                }
            }

            //取得该用户的具体地址
            if( I('post.oldAddress') == I('post.address') ){

                $this->address = I('post.oldAddress');      //无修改则取原先的值(int型)
            }else{

                //判断地址长度是否超过30位
                if(!ValidateModel::length(I('post.address'),2,0,200)){
                    ToolModel::goBack('警告，地址不能超过200位');
                }
                $this->address = I('post.address');      //修改了则取新的具体地址(int型)
            }

            //取得该用户的个人说明
            if( strval(I('post.oldDescription')) == strval(I('post.description')) ){
                $this->description = I('post.oldDescription');      //无修改则取原先的值(int型)
            }else{
                $this->description = I('post.description');      //修改了则取新的个人说明(int型)
            }

            //取得该用户的工作单位
            if( strval(I('post.oldWorkplace')) == strval(I('post.workPlace')) ){
                $this->workPlace = I('post.oldWorkplace');      //无修改则取原先的值(int型)
            }else{
                $this->workPlace = I('post.workPlace');      //修改了则取新的个人说明(int型)
            }


            //取得该用户的手机，如果新旧号码一样，则设为旧号码
            if( intval(I('post.oldTel')) == intval(I('post.tel')) ){
                $this->tel = I('post.oldTel');      //无修改则取原先的值(int型)
            }else{
                //判断新号码是否是手机格式，如果是则设置为新号码
                if( ValidateModel::isMobile(I('post.tel'))){
                    $this->tel = I('post.tel');
                }else{
                    //不是则弹出对话框并返回
                    ToolModel::goBack('手机格式错误');
                }
            }

            //取得部门信息组成json格式
           if ( '' != I('post.dept1')){
                $deptArr[] = I('post.dept1');
            }
            if ( '' != I('post.dept2')){
                $deptArr[] = I('post.dept2');
            }
            if ( '' != I('post.dept3')){
                $deptArr[] = I('post.dept3');
            }
            if ( '' != I('post.dept4')){
                $deptArr[] = I('post.dept4');
            }

            $this->dept = json_encode($deptArr);


            //取得角色信息
            $this->auto = I('post.auto');


            //$this->description = I('post.description');

            if((isset($_SESSION['editImg'])) && ( '' != $_SESSION['editImg'])){
                $this->img = $_SESSION['editImg'];

                //然后将session清空
                $_SESSION['editImg'] = '';
                unset($_SESSION['editImg']);


            }else{
                $this->img = '';
            }

            if( '' != $this->username ){
                $data['username'] = $this->username;
            }

            if('' != $this->password){
                $data['password'] = $this->password;
            }

            if('' != $this->img){
                $data['img'] = $this->img;
            }

            //如果是审核爆料者的情况下下，同时要将状态设置为1：激活
            if(isset($_POST['auditSend']) && '' != I('post.auditSend')){
                $data['status'] = 1;
            }


            //明细表数据做成
            if('' != $this->sex){
                $detailData['udi_sex'] = intval($this->sex);  //转化为数字
            }

            if('' != $this->tel){
                $detailData['udi_tel'] = $this->tel;
            }

            if('' != $this->area){
                $detailData['udi_area'] = $this->area;
            }

            if('' != $this->address){
                $detailData['udi_address'] = $this->address;
            }

            if('' != $this->description){
                $detailData['udi_description'] = $this->description;
            }

            if('' != $this->workPlace){
                $detailData['udi_workplace'] = $this->workPlace;
            }

            if('' != $this->dept){
                $detailData['udi_dep_id'] = $this->dept;
            }
            if('' != $this->auto){
                $detailData['udi_auto_id'] = intval($this->auto);
            }
            if( false !== M('m_user')->where(array('id'=>$this->id))->save($data)){
                //删除upload文件夹中原先用户设置的头像，避免脏数据
                //上传了图片则删除原图
                if( '' != $this->img ){
                    if( 'default.jpg' != I('post.oldImg' )){
                        //删除旧图片，防止垃圾数据
                        ToolModel::delImg(PROFILE_PATH.'/'.I('post.oldImg'));
                    }
                }
                if( false === M('user_detail_info')->where(array('uid'=>$this->id))->save($detailData)){

                    ToolModel::goBack('修改明细表出错');
                }
                return true;
            }
            return false;
        }

        public function getOldImg($id){

            $where['id'] = $id;

            return M('m_user')->field('img')->where($where)->find();
        }

        //根据sel4数值取得前三级的值
        private function get3thSelName(){

            //根据四级内容取得四级的父级ID
            $sel4 = $this->areaObj->where("areaid = $this->sel4")->find();

            //四级的名称
            $sel4name = $sel4['areaname'];

            //父级ID转化为数组
            $sel4iArr = explode(',',$sel4['arrparentid']);

            //取得数组个数
            $count = count($sel4iArr);

            //数组的最后一个为三级的ID
            $sel3id = $sel4iArr[$count-1];

            //根据三级ID取得三级的名称
            $sel3 = $this->areaObj->field('areaname')->where("areaid = $sel3id")->find();
            $sel3name = $sel3['areaname'];

            //数组的最后二个为二级的ID
            $sel2id = $sel4iArr[$count - 2];
            //根据三级ID取得二级的名称
            $sel2 = $this->areaObj->field('areaname')->where("areaid = $sel2id")->find();
            $sel2name = $sel2['areaname'];

            //数组的最后二个为一级的ID
            $sel1id = $sel4iArr[$count - 3];
            //根据三级ID取得一级的名称
            $sel1 = $this->areaObj->field('areaname')->where("areaid = $sel1id")->find();
            $sel1name = $sel1['areaname'];

            //返回拼接的地址
//            return $sel1name.'  (省)  '.$sel2name.'  (市)  '.$sel3name.'  (镇)  '.$sel4name.'  (乡)  ';
            return $sel1name.'  (省)  '.$sel2name.'  (市)  '.$sel3name.'  (区/县)';
        }

        //根据sel3数值取得前两级的值
        private function get2thSelName(){

            //根据四级内容取得三级的父级ID
            $sel3 = $this->areaObj->where("areaid = $this->sel3")->find();

            //三级的名称
            $sel3name = $sel3['areaname'];

            //父级ID转化为数组
            $sel3iArr = explode(',',$sel3['arrparentid']);

            //取得数组个数
            $count = count($sel3iArr);

            //数组的最后二个为二级的ID
            $sel2id = $sel3iArr[$count - 1];
            //根据三级ID取得二级的名称
            $sel2 = $this->areaObj->field('areaname')->where("areaid = $sel2id")->find();
            $sel2name = $sel2['areaname'];

            //数组的最后二个为一级的ID
            $sel1id = $sel3iArr[$count - 2];
            //根据三级ID取得一级的名称
            $sel1 = $this->areaObj->field('areaname')->where("areaid = $sel1id")->find();
            $sel1name = $sel1['areaname'];

            //返回拼接的地址
            return $sel1name.'  (省)  '.$sel2name.'  (市)  '.$sel3name.'  (区/县)  ';

        }




        /**
         * 判断是否是管理员和超级管理员
         * @return bool
         */
        public function isAdmin(){
            $where['uid'] = $_SESSION['uid'];
            $ret =  M('user_detail_info')->field('udi_auto_id')->where($where)->find();
            if(($ret['udi_auto_id'] == ADMIN) || ($ret['udi_auto_id'] == SUPPER_ADMIN) || ($ret['udi_auto_id'] == DEPT_ADMIN)){
                return true;
            }
            return false;
        }
        /**
         * 登录用户名密码判定
         * @return mixed
         */
        public function checklogin(){

            $emailOrName = I('post.user_login');

            //判断是否是email，如果是则设置给email字段，如果不是则设置给用户名字段
            if(ValidateModel::isEmail($emailOrName)){
                $where['email'] = $emailOrName;
            }else{
                $where['login_name'] = $emailOrName;
            }

            $where['password'] = md5(I('post.user_pass'));

            return M('m_user')->where($where)->find();
        }

        /**
         * 也显示所有用户一览表使用后(管理员)
         * 取得关联表的用户数据，并通过转化生出页面可显示的数据
         * @param $limit
         * @return mixed
         */
        public function showUserList($limit){

            //取得用户信息
            $obj = $this->allUser($limit);

            if(!$obj) ToolModel::goBack('未能取到数据');
            //返回格式化好的数据，用于显示

            //是二维数组则进行数据格式修正并返回
            if(ToolModel::isTwoArray($obj)){
                return $this->dataFormart($obj);
            }
        }

        /**
         * 也显示所有用户一览表使用后(部门管理员)
         * 取得关联表的用户数据，并通过转化生出页面可显示的数据
         * @param $limit
         * @param $dept
         * @return mixed
         */
        public function showDeptAdminUserList($limit,$dept){

            $join = "INNER JOIN ccm_user_detail_info 
                        ON ccm_user_detail_info.uid = ccm_m_user.id
                        AND ccm_user_detail_info.udi_dep_id = '$dept'";
            //多表联合查询
            if('' == $limit){
                $obj =  M('m_user')->join($join)->order($this->order)->select();
            }else{
                $obj =  M('m_user')->join($join)->order($this->order)->limit($limit)->select();
            }

            if(!$obj) ToolModel::goBack('未能取到数据');
            //返回格式化好的数据，用于显示

            //是二维数组则进行数据格式修正并返回
            if(ToolModel::isTwoArray($obj)){
                return $this->dataFormart($obj);
            }
        }


        /**
         * 性别需要转化
         * 部门需要转化
         * 角色需要转化
         * 时间戳转化为时间
         * @param $obj
         * @return mixed
         */
        private function dataFormart($obj)
        {
            $sexArr = C('SEX_ARRAY');    //取得自定义常量性别数组
            $deptArr = C('DEPT_ARRAY');   //取得自定义常量部门数组
            $autoArr = C('AUTO_ARRAY');   //取得自定义常量角色数组
            $statusArr = C('STATUS_ARRAY'); //取得自定义常量激活状态数组

            for ($i = 0; $i < count($obj); $i++) {

                $obj[$i]['udi_sex'] = $sexArr[$obj[$i]['udi_sex']];     //处理sex数字转为为文字

                //处理部门数字转化为文字 start
                $dept = json_decode($obj[$i]['udi_dep_id']);            //json转化为数字

                $obj[$i]['udi_dep_id'] = '';                            //先清空原来的数组

                //将json转化的数组循环判断并显示名称
                for ($j = 0; $j < count($dept); $j++) {

                    //为空则不输出
                    if ('' != $dept[$j]) {
                        //最后一个不需要输出间隔符
                        if ((count($dept) - 1) == $j) {
                            $obj[$i]['udi_dep_id'] .= $deptArr[$dept[$j]];
                        } else {
                            $obj[$i]['udi_dep_id'] .= $deptArr[$dept[$j]] . '，';
                        }
                    }
                }
                //处理部门数字转化为文字 end

                //处理角色数字转为为文字
                $obj[$i]['udi_auto_id'] = $autoArr[$obj[$i]['udi_auto_id']];

                //追加id，用于判断，提高速度
                $obj[$i]['statusId'] = $obj[$i]['status'] ;
                //处理激活状态数字转为为文字
                $obj[$i]['status'] = $statusArr[$obj[$i]['status']];

                //创建时间戳转化为时间
                $obj[$i]['regtime'] = ToolModel::formartTime($obj[$i]['regtime']) ;

                //更新时间戳转化为时间
                $obj[$i]['udi_update_time'] = ToolModel::formartTime($obj[$i]['udi_update_time']);
            }

            return $obj;
        }

        /**
         * 取得当前用户详细信息
         * 公有方法
         * @param $id
         * @return mixed
         */
		public function getTheUserInfo($id){
			return $obj = $this->theUser($id);
		}


        /**
         * 取得所有用户总数
         * 公用方法
         * @return mixed
         */
        public function getAllUserCount(){
            return $this->getCount();
        }

        public function getDeptAdminAllUserCount($dept){
            $join = "INNER JOIN ccm_user_detail_info 
                        ON ccm_user_detail_info.uid = ccm_m_user.id
                        AND udi_dep_id = '$dept'";
            return M('m_user')->join($join)->count();
            //return $this->getCount();
        }

        public function delTheUserInfo($id){
            return $this->delTheUser($id);
        }


        /**
         * 取得所有用户信息总数(多表查询)
         * 私有方法
         * @return mixed
         */
        private function getCount(){
            //多表联合查询
            return M('m_user')->join($this->join)->count();
        }

        /**
         * 取得当前用户的详细信息(多表查询)
         * 私有方法
         * @param $id
         * @return mixed
         */
		private function theUser($id){
			//多表联合查询
            $where['ccm_m_user.id'] = $id;
			return M('m_user')->join($this->join)->where($where)->find();
		}

        /**取得所有用户信息(多表查询)
         * 私有方法
         * @param $limit
         * @return mixed
         */
		private function allUser($limit){
			//多表联合查询
            if('' == $limit){
                return M('m_user')->join($this->join)->order($this->order)->select();
            }else{
                return M('m_user')->join($this->join)->order($this->order)->limit($limit)->select();
            }

		}

        private function delTheUser($id){

            //删除主表，错误的情况下返回
            if( false === M('m_user')->where("id=$id")->delete()){
                return false;
            }

            //继续删除明细表，错误则返回
            if( false === M('user_detail_info')->where("uid=$id")->delete()){
                return false;
            }
            //都正确删除后返回
            return true;
        }


        public function doReg(){

            $this->setData();           //设置主表信息



            //判断是否已经存在该用户信息
            if(!$this->checkUserIsExist()){
                return 1;               //存在用户
            }
            //注册新用户
            if($this->addToUser() <= 0){
                return 2;              //主表追加错误
            }

            //必须再追加主表后生成新id后才能设置明细数据,不然id无法获取,会出错
            $this->setDetailData();     //设置明细表数据

            if($this->addToUserDetail()<= 0){
                //失败的情况下，要将主表的新增用户也删除，避免脏数据
                $this->delUserMain();
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
                'username' => $this->username,	//注册时候默认姓名和用户名设置为一样
                'autopass' => $this->autopass,
                'password'=> $this->password,
                'img'=> 'default.jpg',
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

            //默认注册者都是爆料者，默认对所有部门都可以爆料
            $idArr = D('Dept')->getAllID();

            $arr = array();

            for($i =0;$i<count($idArr);$i++){
                $arr[] = $idArr[$i]['id'];
            }

            $jsoID = json_encode($arr);

            $this->detailDataArray = array(
                'uid' => $this->id,
                'udi_sex'=>1,
                'udi_tel' => '',
                'udi_area' => '',
                'udi_address' => '',
                'udi_dep_id'=>$jsoID,
                'udi_auto_id'=>BAOLIAOZHE,
                'udi_description'=> '',
                'udi_workplace'=> '',
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
        private function delUserMain(){
            $where['id'] = $this->id;
            return M('m_user')->where($where)->delete();
        }


        /*********************************************注册相关***************************************************/
		/**
		 * 确认注册用户是否已经存在相同用户名或者邮箱地址
		 * @return bool
		 */
		private function checkUserIsExist(){

			$where['login_name'] = $this->loginName;
			$where['email'] = $this->email;
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
					$data['status'] = -1;
					$autopass = $data['autopass'];
					$where['id']= $data['id'];
					if(M('m_user')->where($where)->save($data)){
						$msg = "激活成功！<br/>您的初始密码为：$autopass<br/>请及时修改您的密码,并完善您的个人信息，并等待管理员审核";
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

            //新增成功后需要情况图片的session中
            $_SESSION['newImg'] = '';
            unset($_SESSION['newImg']);

            //向新用户发送邮件
            if( 0 == $this->status ){
                if(!$this->sendMailToUser()){
                    ToolModel::goBack('发生邮件失败,请联系wu_jy1984@126.com');
                    exit;
                }else{
                    ToolModel::goToUrl('新增用户成功,邮件已发送给新用户邮箱地址！','all');
                    exit;
                }
            }else{
                ToolModel::goToUrl('新增用户成功,请联系新用户！','all');
                exit;
            }


        }

        /**
         * 获得提交的数据
         */
        private function setNewUserData(){

            $this->loginName = I('post.user_login');    //登录用户名
            $this->username = I('post.user_name');      //姓名
            $this->email = I('post.user_email');        //邮件地址
            $this->sex = I('post.sex');                 //性别
            $this->autopass = I('post.user_pass');      //初始密码
            $this->password = md5($this->autopass);     //密码加密

            //部门需要特殊处理 start (可优化)
            $deptList = array();

            $deptCount = D('Dept')->getDeptCount();

            //取得所有的dept个数，然后根据上传的dept进行确认，做成数组
            for($i = 1;$i<=$deptCount;$i++){
                $name = 'dept'.$i;
                if(I("post.$name")){
                    $deptList[] = I("post.$name");
                }
            }

            //数组转化为json格式
            $this->dept = json_encode($deptList);
            //部门需要特殊处理 end

            $this->auto = I('post.auto');

            //是否发生邮件(如果需要发送邮件则将状态设置为未激活状态，反正则未激活状态，不用邮件激活)
            if ( 'on' == I('post.isSend')){
                $this->status = 0;
            }else{
                $this->status = 1;
            }

            $this->regtime = time();
            $this->updateTime = $this->regtime;

            $this->token = md5($this->username.$this->password.$this->regtime); //创建用于激活识别码
            $this->token_exptime = time()+60*60*24*7;//过期时间为一周后

            if((isset($_SESSION['newImg'])) && ( '' != $_SESSION['newImg'])){
                $this->img = $_SESSION['newImg'];
            }else{
                $this->img = 'default.jpg';
            }

            $this->dataArray  = array(
                'login_name' => $this->loginName,
                'username' => $this->username,	//注册时候默认姓名和用户名设置为一样
                'autopass' => $this->autopass,
                'password'=> $this->password,
                'img'=> $this->img,
                'email' => $this->email,
                'token' => $this->token,
                'status' => $this->status,
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
                'udi_area' => '',
                'udi_address' => '',
                'udi_dep_id'=>$this->dept,
                'udi_auto_id'=>$this->auto,
                'udi_description'=> '',
                'udi_workplace'=> '',
                'udi_update_time'=> time()
            );
        }

        /**
         * 私有方法
         * 新增用户时验证表单
         */
        private function checkAddNewUser(){

            //判断loginname
            if('' == $this->loginName) ToolModel::goBack('警告，用户名不能为空！');

            if(strlen($this->loginName) < 6) ToolModel::goBack('警告，用户名长度不能小于6位！');

            if(strlen($this->loginName) > 20) ToolModel::goBack('警告，用户名长度不能大于20位！');

            //提交的默认生成初始是否为空
            if('' == $this->autopass) ToolModel::goBack('警告，初始密码不能为空！');

            if(strlen($this->autopass) > 20) ToolModel::goBack('警告，密码长度不能大于20位！');

            //判断提交的邮件地址正确性
            if('' == $this->email) ToolModel::goBack('警告，邮箱地址不能为空！');

            if(!is_mail($this->email)) ToolModel::goBack('警告，邮箱格式错误！');

            //判断是否存在相同的用户名或者密码了
            if(!$this->checkUserIsExist()) ToolModel::goBack('已存在相同用户名或者邮箱地址了');

            //判断提交的部门复选框是否都为空
            //array_filter函数是去除数组内空内容，如果剩下为空数组
            $dept = json_decode($this->dept);
            $arr = array_filter($dept);
            if( empty($arr) ){
                ToolModel::goBack('警告，至少选择一个部门！');
            }

            //如果是小编或者总编,或者部门管理员,则只能选择一个部门
            if( ($this->auto == XIAOBIAN) || ($this->auto == ZONGBIAN) || ($this->auto == DEPT_ADMIN)){
                if(count($dept) > 1){
                    ToolModel::goBack('该角色只能选择一个部门');
                }
            }

            //取得所有部门的个数
            $deptCount = D('Dept')->getDeptCount();
            //如果是管理员一定要选择全部部门
            if( $this->auto == ADMIN ){
                if(count($dept) != $deptCount){
                    ToolModel::goBack('管理员需要选择全部部门进行管理');
                }
            }

        }
        /*********************************************新增用户***************************************************/

        /**
         * 取得当前用户的状态
         * @return bool
         */
        public function getNowUserStatus(){
            $where['id'] = $_SESSION['uid'];
            $field = 'status';
            $data = M('m_user')->where($where)->field($field)->find();
            if($data){
                return $data['status'];
            }
            return false;
        }

        /**
         * 发送邮件到新注册用户邮箱
         * @return string
         */
        public function afterAuditToUser($email){

            $emailTitle= "恭喜！您的注册信息已被审核通过";//邮件标题
            //邮件主体内容
            $emailContent = "您的注册信息已被管理员审核通过，现在您可以登录系统使用更多功能了...";

            return SendMail($email,$emailTitle,$emailContent)?  true: false;

        }

    }