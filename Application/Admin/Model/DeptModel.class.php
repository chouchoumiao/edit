<?php

/**
 * 登录Model
 */
namespace Admin\Model;

/**
 * 部门类
 * Class DeptModel
 * @package Admin\Model
 */
	class DeptModel {

        public function getAllDept(){
            return M('dept')->select();
        }

	}