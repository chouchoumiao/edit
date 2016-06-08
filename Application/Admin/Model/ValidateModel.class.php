<?php

/**
 * 验证方法类
 */
namespace Admin\Model;

	class ValidateModel {

		/**
		 * 新增用户时检查表单
		 * @return string
		 */
		static function CheckAddUser(){

			if(!isset($_POST)){
				return '未获取到数据';
			}
			if(isset($_POST['user_login'])){
				$user = trim($_POST['user_login']);
				if('' == $user){
					return '用户名不能为空';
				}
				if(strlen($user) < 6){
					return '用户名长度不能小于六位';
				}
			}else{
				return '未获取到数据';
			}

			if(isset($_POST['pass'])){
				$pass = trim($_POST['pass']);
				if('' == $pass){
					return '密码不能为空';
				}
			}else{
				return '未获取到数据';
			}

			if(isset($_POST['user_email'])){
				$email = trim($_POST['user_email']);
				if('' == $email){
					return '邮箱地址不能为空';
				}
				if(!is_mail($email)){
					return '邮箱格式错误';
				}
			}else{
				return '未获取到数据';
			}
			return '';
		}
	}