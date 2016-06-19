<?php

/**
 * 验证方法类
 */
namespace Admin\Model;

	class ToolModel {

		static function goBack($msg){
            echo "<script>alert('$msg');history.go(-1)</script>";
            exit;
        }

        static function goClose($msg){
            echo "<script>alert('$msg');close()</script>";
            exit;
        }

        static function goToUrl($msg,$url){
            echo "<script>alert('$msg');location='$url'</script>";
            exit;
        }

        /**
         * 将时间戳转化为正常时间格式
         * @param $data
         * @return bool|string
         */
        static function formartTime($data){
            return date('Y-m-d H:i:s', $data);
        }

        /**
         * 简单判定是否为二维数组
         * @param $arr
         * @return bool
         */
        static function isTwoArray($arr){

            if(is_array($arr[0])){
                return true;
            }
            return false;
        }


	}