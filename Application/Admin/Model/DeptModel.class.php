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

        private $_model;

        public function __construct(){
            $this->_model = M('dept');
        }

        public function getAllDept(){
            return $this->_model->select();
        }

        public function getDeptCount(){
            return $this->_model->count();
        }

	}