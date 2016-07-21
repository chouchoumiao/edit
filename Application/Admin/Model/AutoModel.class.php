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

        private $_model;

        public function __construct(){
            $this->_model = M('auto');
        }

        public function getAllAuto(){
            return $this->_model->order('id')->select();
        }

        public function getAutoCount(){
            return $this->_model->count();
        }

	}