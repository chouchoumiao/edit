<?php

/**
 * 评分Model
 */
namespace Admin\Model;

	class ScoreModel {

        private $object;
        private $order;         //默认排序
        private $data;          //数据
        private $join;
        private $field;         //字段

        public function __construct(){
            if(!$this->object){
                $this->join = 'INNER JOIN ccm_user_detail_info ON ccm_user_detail_info.uid = ccm_m_user.id AND ccm_user_detail_info.udi_auto_id = 1 INNER JOIN ccm_score ON ccm_score.author = ccm_m_user.id';
                $this->object = M('m_user');
                $this->order = 'regtime DESC';    //默认排序为修改文章的时间降序
                $this->field = 'ccm_m_user.username,ccm_user_detail_info.udi_auto_id,ccm_score.*';
            }
        }

        /**
         * 取得所有
         * @return mixed
         */
        public function getAllScore(){

            return $this->object
                        ->join($this->join)
                        ->field($this->field)
                        ->order($this->order)
                        ->select();
//            echo $this->object->getLastSql();exit;
        }

    }