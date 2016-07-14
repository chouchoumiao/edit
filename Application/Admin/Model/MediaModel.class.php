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