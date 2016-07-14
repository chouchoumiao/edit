<?php

/**
 * 媒体库Model
 */
namespace Admin\Model;

	class MediaModel {

        private $obj;

        public function __construct(){
            $this->obj = M('Media');
        }


        /**
         * 用户修改资源的主题和详细信息
         * @param $data
         * @return bool
         */
        public function updateMedia($data){

            $where['id'] = $data['id'];

            $newdata['title'] = $data['title'];
            $newdata['content'] = $data['content'];
            $newdata['time'] = $data['time'];

            if( false === $this->obj->where($where)->save($newdata)){
                return false;
            }

            return true;


        }

        /**
         * 收藏资源,更新数据库
         * @param $id
         * @return bool
         */
        public function doCollect($id){

            $where['id'] = $id;

            $label = $this->getLabelById($id);

            if($label == ''){
                $data['label'] = $_SESSION['uid'];
            }else{
                $data['label'] = $label.','.$_SESSION['uid'];
            }

            if( false === $this->obj->where($where)->save($data)){
                return false;
            }

            return true;

        }

        /**
         * 根据id来取得Label的值
         * @param $id
         * @return bool
         */
        private function getLabelById($id){
            $where['id'] = $id;

            $label =  $this->obj->field('label')->where($where)->find();

            if(!$label){
                return false;
            }
            return $label['label'];

        }

        /**
         * 根据传入的条件来获取资源数据
         * @param $where
         * @return mixed
         */
        public function getMediaBtStatus($where){

            $order = 'time desc';
            return $this->obj->where($where)->order($order)->select();
        }

        /**
         * 获取所有的资源信息
         * @return mixed
         */
        public function getAllMedia(){

            $where['status'] = 1;
            $order = 'time desc';

            return $this->obj->where($where)->order($order)->select();
        }

        /**
         * 根据传入的data，进行数据表追加
         * @param $data
         * @return bool
         */
        public function insertMedia($data){

            $id = $this->obj->add($data);
            if(false === $id ){
                return false;
            }
            return $id;
            
        }

        /**
         * 根据传入的id删除对应的资源
         * @param $id
         * @return bool
         */
        public function deleteMedia($id){
            $where['id'] = $id;
            if( false === $this->obj->where($where)->delete() ){
                return false;
            }
            return true;

        }

    }