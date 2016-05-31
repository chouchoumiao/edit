<?php

/**
 * 登录Model
 */
namespace Admin\Model;

	class UserModel {

		/**
		 * 取得当前用户详细信息
		 * 公有方法
		 * @return mixed
		 */
		public function getTheUserInfo(){
			return $this->getTheUser();
		}

		/**
		 * 取得所有用户
		 * 公用方法
		 * @return mixed
		 */
		public function getAllUserInfo(){
			return $this->getAllUser();
		}


		/**
		 * 取得当前用户的详细信息(多表查询)
		 * 私有方法
		 * @return mixed
		 */
		private function getTheUser(){
			//多表联合查询
			$where['ccm_m_user.id'] = $_SESSION['uid'];
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

	}