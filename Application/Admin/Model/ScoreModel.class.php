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
         * 部门管理员的情况下，取得自己属于自己部门的总计
         * @param $uid
         * @param $dept
         * @return int
         */
        public function getDeptAdminSumScoreByUid($uid,$dept){
            $where['author'] = $uid;
            $deptArr = json_decode($dept);
            $where['dept'] = array('like',"%{$deptArr[0]}%");
            $field = 'score';

            $data = $this->object->where($where)->sum($field);
            if($data){
                return $data;
            }
            return 0;

        }

        /**
         * 根据传入的评分者的ID获得该姓名
         * @param $authorid
         * @return bool
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
         * 部门管理员时候，取得自己部门的分数记录信息个数
         * @param $dept
         * @return mixed
         */
        public function getDeptAdminAllScoreCount($dept){
            $deptArr = json_decode($dept);
            $where['dept'] = array('like',"%{$deptArr[0]}%");
            return $this->object
                ->where($where)
                ->join($this->join)
                ->count();
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
         * 当前用户为爆料者的情况下，取得该爆料者的评分总数
         * @return mixed
         */
        public function getBaoliaozheScoreCount(){
            $where['author'] = $_SESSION['uid'];
            return $this->object
                ->where($where)
                ->join($this->join)
                ->count();
        }

        /**
         * 当前用户为爆料者的情况下，取得该爆料者的评分记录
         * @param $limit
         * @return mixed
         */
        public function getBaoliaozheScore($limit){
            $where['author'] = $_SESSION['uid'];
            if('' == $limit){

                return $this->object
                    ->where($where)
                    ->join($this->join)
                    ->field($this->field)
                    ->order($this->order)
                    ->select();
            }else{
                return $this->object
                    ->where($where)
                    ->join($this->join)
                    ->field($this->field)
                    ->limit($limit)
                    ->order($this->order)
                    ->select();
            }
        }

        /**
         * 取得自己部门所有评分记录
         * @param $limit
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
        }

        /**
         * 部门管理员，取得自己部门所有评分记录
         * @param $limit
         * @param $dept
         * @return mixed
         */
        public function getDeptAdminAllScore($limit,$dept){
            $deptArr = json_decode($dept);
            $where['dept'] = array('like',"%{$deptArr[0]}%");

            if('' == $limit){

                return $this->object
                    ->where($where)
                    ->join($this->join)
                    ->field($this->field)
                    ->order($this->order)
                    ->select();
            }else{
                return $this->object
                    ->where($where)
                    ->join($this->join)
                    ->field($this->field)
                    ->limit($limit)
                    ->order($this->order)
                    ->select();
            }
        }

    }