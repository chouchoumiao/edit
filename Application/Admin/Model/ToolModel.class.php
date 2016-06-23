<?php

/**
 * 验证方法类
 */
namespace Admin\Model;

	class ToolModel {

        /**
         * 错误返回
         * @param $msg
         */
		static function goBack($msg){
            echo "<script>alert('$msg');history.go(-1)</script>";
            exit;
        }

        /**
         * 错误关闭
         * @param $msg
         */
        static function goClose($msg){
            echo "<script>alert('$msg');close()</script>";
            exit;
        }

        /**
         * 错误跳转
         * @param $msg
         * @param $url
         */
        static function goToUrl($msg,$url){
            echo "<script>alert('$msg');location='$url'</script>";
            exit;
        }


        /**
         * 删除指定文件
         * @param $img  绝对路径文件
         */
        static function delImg($img){
            if(file_exists($img)){

                unlink($img);
            }
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
            return (is_array($arr[0])) ? true : false;
        }


	}