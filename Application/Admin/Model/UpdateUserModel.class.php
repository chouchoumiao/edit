<?php
//
///**
// * 登录Model
// */
//namespace Admin\Model;
//
//class UpdateUserModel {
//
//    private $id;
//    private $username;
//    private $password;
//    private $sex;
//    private $token;
//    private $updateTime;
//    private $dept;
//    private $auto;
//    private $img;
//    private $sel4;  //地区四级内容
//    private $address;
//    private $tel;
//    private $description;
//
//    private $dataArray; //整合后主表数据
//    private $detailDataArray; //整合后明细数据
//
//    /*********************************************修改用户***************************************************/
//
//    /**
//     * 修改用户
//     */
//    public function updateUser(){
//
//        $this->setUpdateUserData();
//
//        $this->checkAddNewUser();
//
//
//        $this->setNewUserData();
//
//        if($this->addToUser() <= 0){
//            ToolModel::goBack('新增用户主表失败！');
//            exit;
//        }
//
//        $this->setNewUserDetailData();
//
//        if($this->addToUserDetail() <= 0){
//            ToolModel::goBack('新增用户明细表失败！');
//            exit;
//        }
//
//        //追加明细表后将session中的新图片信息清空
//        if((isset($_SESSION['newImg'])) && ( '' != $_SESSION['newImg'])){
//            $_SESSION['newImg'] = '';
//            unset($_SESSION['newImg']);
//        }
//
//        //向新用户发送邮件
//        if( 'on' == $this->isSend ){
//            if(!$this->sendMailToUser()){
//                ToolModel::goBack('发生邮件失败,请联系wu_jy1984@126.com');
//                exit;
//            }else{
//                ToolModel::goToUrl('新增用户成功,邮件已发送给新用户邮箱地址！','all');
//                exit;
//            }
//        }
//    }
//
//    private function setUpdateUserData(){
//        $this->username = I('post.user_name');      //昵称
//        $this->sex = I('post.sex');                 //性别
//        if('' == I('post.user_pass')){
//            $this->password = I('post.oldMd5Pass');
//        }else{
//            $this->password = md5(I('post.user_pass'));     //密码加密
//        }
//
//        $this->sex = I('post.sex');
//        $this->sel4 = I('post.sel4');
//        $this->address = I('post.address');
//        $this->tel = I('post.tel');
//        $this->description = I('post.description');
//
//        if((isset($_SESSION['newImg'])) && ( '' != $_SESSION['newImg'])){
//            $this->img = $_SESSION['newImg'];
//        }else{
//            $this->img = 'default.jpg';
//        }
//
//        $this->dataArray  = array(
//            'username' => $this->username,	//注册时候默认昵称和用户名设置为一样
//            'password'=> $this->password,
//            'img'=> $this->img,
//            'token' => $this->token,
//            'updateTime' => time()
//        );
//
//    }
//
//    private function checkData(){
//
//
//
//        //判断提交的部门复选框是否都为空
//        //array_filter函数是去除数组内空内容，如果剩下为空数组
//        if( empty(array_filter(json_decode($this->dept)))){
//            ToolModel::goBack('警告，至少选择一个部门！');
//        }
//
//    }
//
//    /*********************************************修改用户***************************************************/
//
//
//}