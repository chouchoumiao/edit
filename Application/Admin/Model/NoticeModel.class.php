<?php

/**
 * 通知Model
 */
namespace Admin\Model;

	class NoticeModel {

        private $object;
        private $order;         //默认排序
        private $data;          //数据
        private $join;
        private $field;         //字段

        public function __construct(){
            if(!$this->object){
                $this->join = 'INNER JOIN ccm_m_user ON ccm_notice.author = ccm_m_user.id';
                $this->object = M('notice');
                $this->order = 'time DESC';    //默认排序为修改文章的时间降序
                $this->field = 'ccm_m_user.username,ccm_notice.*';
            }
        }

        /**
         * 取得当前有效的通知
         * @return mixed
         */
        public function getActivedNotice(){

            //先获取当前用户的部门和角色

            $userData = D('User')->getNowUserDetailInfo();
            $auto = $userData['udi_auto_id'];
            $deptArr = json_decode($userData['udi_dep_id']);

            $nowDate = date('Y-m-d');

            $where['from_date'] = array('elt',$nowDate);
            $where['_logic'] = 'AND';
            $where['to_date'] = array('egt',$nowDate);

            //管理员和超级管理员全部进行部门条件
            if( (intval($auto) != ADMIN) && (intval($auto) != SUPPER_ADMIN) ){
                $where['_logic'] = 'AND';
                $where['auto'] = array('like','%'.$auto.'%');
                $where['_logic'] = 'AND';

                if(count($deptArr) == 1){
                    $where['dept'] = array('like','%'.$deptArr[0].'%');
                }else{
                    $arr = array();
                    for ($i = 0;$i<count($deptArr);$i++){

                        $arr[] = array('like','%'.$deptArr[$i].'%');
                    }
                    $arr[] = 'or';

                    $where['dept'] = $arr;

                }
            }
            return $this->object->where($where)->select();

        }

        /**
         * 根据传入的id删除对应的通知
         * @param $id
         * @return bool
         */
        public function delById($id){

            $where['id'] = $id;

            if( false === $this->object->where($where)->delete()){
                return false;
            }
            return true;

        }

        /**
         * 根据id取得对应的通知
         * @param $id
         * @return mixed
         */
        public function getTheNoticeById($id){

            $where['ccm_notice.id'] = $id;
            return $this->object->where($where)->join($this->join)->field($this->field)->find();

        }

        /**
         * 取得所有通知个数
         * @return mixed
         */
        public function getAllNoticeCount(){

            return $this->object->join($this->join)->count();
        }

        /**
         * 字段检查
         * @param $data
         */
        public function checkData($data){

            if($data['title'] == '') ToolModel::goBack('标题不能为空');

            if(strlen($data['title']) > 200) ToolModel::goBack('标题长度不能超过200');

            if( ('' !=$data['link']) && (ValidateModel::isUrl($data['link']) == false) ){
                ToolModel::goBack('不是正确的网址格式');
            }

            if($data['content'] == '') ToolModel::goBack('内容不能为空');

            if($data['from_date'] == '') ToolModel::goBack('开始日期不能为空');

            if($data['to_date'] == '') ToolModel::goBack('结束日期不能为空');
            
            
            if(ValidateModel::dateDiff($data['from_date'],$data['to_date']) == -1) ToolModel::goBack('结束日期不能小于开始日期');

            $deptArr = json_decode($data['dept']);
            if(empty($deptArr)) {
                ToolModel::goBack('至少要选择一个部门');
            }

            $autoArr = json_decode($data['auto']);
            if(empty($autoArr)) {
                ToolModel::goBack('至少要选择一个角色');
            }


            $this->data = $data;
        }



        /**
         * 新增通知
         * 成功则返回新ID,失败则返回false
         * @return bool
         */
        public function addNewData(){

            $newID = $this->object->add($this->data);
            if( false === $newID ){
                return false;
            }
            return $newID;

        }

        public function updateData($id){

            $where['id'] = $id;

            if( false === $this->object->where($where)->save($this->data) ){
                return false;
            }
            return true;

        }

        /**
         * 取得所有通知信息
         * @return mixed
         */
        public function getAllNotice($limit){

            if( '' == $limit){
                return $this->object->order($this->order)->join($this->join)->field($this->field)->select();
            }
            return $this->object->order($this->order)->join($this->join)->limit($limit)->field($this->field)->select();


        }


    }