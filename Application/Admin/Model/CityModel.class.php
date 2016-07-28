<?php

/**
 * 登录Model
 */
namespace Admin\Model;
header("Content-Type:text/html; charset=utf-8");
	class CityModel {

        private $obj;
        private $parentid;
        private $arrchildid;
        private $idarrs;
        private $field;

        public function __construct(){
            $this->obj = M('area');
            $this->field = array(
                'arrchildid',
                'areaname'
            );
        }

        public function get4thCity(){


            if(isset($_POST['parentid'])){
                $this->parentid = I('post.parentid');

                $result = $this->getCityByParentid();
                $str="";

                for($i=0;$i<count($result);$i++){
                    $str.=$result[$i]['arrchildid']."|".$result[$i]['areaname']."-";
                }

                return $str;
            }

            if(isset($_POST['arrchildid'])){

                $this->arrchildid = $_POST['arrchildid'];

                $str="";
                $idarr=rtrim($this->arrchildid,",");
                $this->idarrs=explode(",",$idarr);

                $result = $this->getCityByArrchildid();
                for($i=0;$i<count($result);$i++){
                    $str.=$result[$i]['arrchildid']."|".$result[$i]['areaname']."-";
                }

                return $str;
            }
        }

        private function getCityByParentid(){

            $where['parentid'] = $this->parentid;
            return $this->obj->field($this->field)->where($where)->order('areaid asc')->select();
        }

        private function getCityByArrchildid(){
            $where['parentid'] = $this->idarrs[0];
            $where['areaid'] = array('in',$this->arrchildid);
            return $this->obj->field($this->field)->where($where)->order('areaid')->select();
        }

    }