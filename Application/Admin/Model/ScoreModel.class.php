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
                $this->join = 'INNER JOIN ccm_m_user ON ccm_score.author = ccm_m_user.id';
                $this->object = M('score');
                $this->order = 'regtime DESC';    //默认排序为修改文章的时间降序
                $this->field = 'ccm_m_user.username,ccm_score.*';
            }
        }

        /**
         * 根据传入的uid获得该用户的总积分
         * @param $uid
         * @return int
         */
        public function getSumScoreByUid($uid){
            $where['author'] = $uid;
            $field = 'score';

            $data = $this->object->where($where)->sum($field);
            if($data){
                return $data;
            }
            return 0;

        }

        /**
         * 根据传入的评分者的ID获得该昵称
         */
        public function getScoreAuthorName($authorid){

            $where['id'] = $authorid;
            $field = 'username';

            $data = M('m_user')->field($field)->where($where)->find();
            if($data){
                return $data['username'];
            }
            return false;


        }

        /**
         * 追加新记录
         * @param $score
         * @return mixed
         */
        public function newScoreInsert($score){

            return M('score')->add($score);
            
        }

        /**
         * 获取所有评分总记录
         * @return mixed
         */
        public function getAllScoreCount(){
            return $this->object
                ->join($this->join)
                ->count();
        }
        /**
         * 取得所有
         * @return mixed
         */
        public function getAllScore($limit){

            if('' == $limit){

                return $this->object
                    ->join($this->join)
                    ->field($this->field)
                    ->order($this->order)
                    ->select();
            }else{
                return $this->object
                    ->join($this->join)
                    ->field($this->field)
                    ->limit($limit)
                    ->order($this->order)
                    ->select();
            }
//            echo $this->object->getLastSql();exit;
        }

    }