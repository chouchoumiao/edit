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
	class AutoModel {

        public function getAllAuto(){
            return M('auto')->select();
        }

	}