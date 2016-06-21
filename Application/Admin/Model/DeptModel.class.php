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

        /**
         * 返回所有的id一览
         * @return mixed
         */
        public function getAllID(){
            return $this->_model->field('id')->select();
        }

        public function getAllDept(){
            return $this->_model->select();
        }

        public function getDeptCount(){
            return $this->_model->count();
        }

	}