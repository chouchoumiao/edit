<?php
namespace Admin\Model;
use Think\Model;

class AdminModel {

	private $_table = 'adminuser';

	function findOne_by_username($username){

        return M($this->_table)->where( array( 'username' => $username) )->find();
		//$sql = "select * from ".$this->_table." where username='$username' and isdeleted = 0";
		//return DB::findOne($sql);
	}

	/**
	 * 根据分页取得所有后台用户信息
	 * public
	 * @return array
	 */
	function getUserByAdmin(){
		//取得所用用户的总数
		$count = $this->getUserCountByAdmin();
		//如果数据表里有数据
		if($count){
			//每页显示记录数
			$multiArr = parent::getMulti(); //取得分页信息(公共Model类里抽取)

			$class_list = $this->getUserWithMulti($multiArr); //根据分页信息取得相关人员信息
			$retArr = array(
				'count' => $count,
				'page_num' => $multiArr['showCount'],
				'page' => $multiArr['page'],
				'showCount' => $multiArr['showCount'],
				'class_list' => $class_list,
			);
			return $retArr;
		}else{
			return array();
		}
	}

	/**
	 * 取得所用用户的总数
	 * private
	 * @return mixed
	 */
	private function getUserCountByAdmin(){
		$sql="select COUNT(*) from ".$this->_table." where isdeleted = 0 ";
		return $count = DB::findResult($sql);
	}

	/**
	 * 根据分页信息取得相关人员信息
	 * private
	 * @param $arr
	 * @return mixed
	 */
	private function getUserWithMulti($arr){

		//获取符合条件的数据
		$sql = "select * from AdminUser
					where isdeleted = 0
					order by id asc
					limit $arr[from_record],$arr[showCount]";
		return DB::findAll($sql);
	}

	function delUserByID($id){

		$nowtime=date("Y/m/d H:i:s",time());
		$sql = "update AdminUser set isdeleted = 1,editTime = '$nowtime' where id = $id";
		$errno = DB::query($sql);
		if(!$errno){
			return false;
		}
		return true;
	}
}