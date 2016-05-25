<?php

/**
 * Created by wujiayu.
 * User: Administrator
 * Date: 2016/4/26
 * Time: 16:35
 */
class commonModel
{

    function getList($_table,$_whereCount,$_whereInfo){
        //如果数据表里有数据

        $count = $this->getCount($_table,$_whereCount);
        if($count){

            $multiArr = $this->getMulti();
            //每页显示记录数
            $infoList =  $this->getListWithMulti($_table,$multiArr,$_whereInfo);
            return array(
                'count' => $count,
                'page' => $multiArr['page'],
                'page_num' => $multiArr['showCount'],
                'showCount' => $multiArr['showCount'],
                'class_list' => $infoList
            );
        }else{
            return array();
        }
    }

    protected function getCount($_table,$where){
        return DB::findResult("select COUNT(*) from ".$_table." where ".$where);
    }
    /**
     * 取得分页信息
     * private
     * @return array
     */
    protected function getMulti(){
        if(!isset($_GET["page"])){
            $page = 1;
        }else{
            $page=intval(addslashes($_GET["page"]));
        }
        if(isset($_GET['showCount'])){
            $showCount = intval(addslashes($_GET['showCount']));
        }else{
            $showCount = 5;
        }
        return array(
            'page' => $page,
            'showCount' =>$showCount,
            'from_record' =>($page - 1) * $showCount  //计算开始的记录序号
        );
    }

    private function getListWithMulti($_table,$arr,$whereInfo){

        //获取符合条件的数据
        $sql = "select * from ".$_table."
				where ".$whereInfo."
				limit $arr[from_record],$arr[showCount]";
        return DB::findAll($sql);
    }

}