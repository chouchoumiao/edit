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

	}