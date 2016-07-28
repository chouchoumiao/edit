<?php

/**
 * 验证方法类
 */
namespace Admin\Model;

	class ValidateModel {
		/**
		 * 姓名合法性检查，只能输入中文英文
		 * @param $val
		 * @return bool
		 */
		public static function isName($val)
		{
			if( preg_match("/^[\x80-\xffa-zA-Z0-9]{3,60}$/", $val) )//2008-7-24
			{
				return true;
			}
			return false;
		}

		/**
		 * 是否为空值
		 */
		public static function isEmpty($str){
			$str = trim($str);
			return !empty($str) ? true : false;
		}
		/**
		 * 数字验证
		 * param:$flag : int是否是整数，float是否是浮点型
		 */
		public static function isNum($str,$flag = 'float'){
			if(!self::isEmpty($str)) return false;
			if(strtolower($flag) == 'int'){
				return ((string)(int)$str === (string)$str) ? true : false;
			}else{
				return ((string)(float)$str === (string)$str) ? true : false;
			}
		}

		/**
		 * 邮箱验证
		 */
		public static function isEmail($str){
			if(!self::isEmpty($str)) return false;
			return preg_match("/([a-z0-9]*[-_\.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[\.][a-z]{2,3}([\.][a-z]{2})?/i",$str) ? true : false;
		}


		/**
		 * 手机号码验证
		 * @param $str
		 * @return bool
		 */
		public static function isMobile($str){
			$exp = "/^13[0-9]{1}[0-9]{8}$|15[012356789]{1}[0-9]{8}$|18[012356789]{1}[0-9]{8}$|14[57]{1}[0-9]$/";
			if(preg_match($exp,$str)){
				return true;
			}else{
				return false;
			}
		}
		/**
		 * URL验证，纯网址格式，不支持IP验证
		 */
        function isUrl($url){

            if(!preg_match('/http:\/\/[\w.]+[\w\/]*[\w.]*\??[\w=&\+\%]*/is',$url)){
                return false;
            }
            return true;
        }



        /**
		 * 验证长度
		 * @param $str
		 * @param int $type (方式，默认min <= $str <= max)
		 * @param int $min 最小值;
		 * @param int $max 最大值;
		 * @param string $charset $charset 字符
		 * @return bool
		 */
		public static function length($str,$type=3,$min=0,$max=0,$charset = 'utf-8'){
			if(!self::isEmpty($str)) return false;
			$len = mb_strlen($str,$charset);
			switch($type){
				case 1: //只匹配最小值
					return ($len >= $min) ? true : false;
					break;
				case 2: //只匹配最大值
					return ($max >= $len) ? true : false;
					break;
				default: //min <= $str <= max
					return (($min <= $len) && ($len <= $max)) ? true : false;
			}
		}

		/**
		 * 验证密码
		 * @param $value
		 * @param int $minLen
		 * @param int $maxLen
		 * @return bool|int
		 */
		public static function isPWD($value,$minLen=6,$maxLen=16){
			$match='/^[\\~!@#$%^&*()-_=+|{},.?\/:;\'\"\d\w]{'.$minLen.','.$maxLen.'}$/';
			$v = trim($value);
			if(empty($v))
				return false;
			return preg_match($match,$v);
		}

		/**
		 * 验证用户名
		 * @param $value
		 * @param int $minLen
		 * @param int $maxLen
		 * @param string $charset
		 * @return bool|int
		 */
		public static function isNames($value, $minLen=2, $maxLen=16, $charset='ALL'){
			if(empty($value))
				return false;
			switch($charset){
				case 'EN': $match = '/^[_\w\d]{'.$minLen.','.$maxLen.'}$/iu';
					break;
				case 'CN':$match = '/^[_\x{4e00}-\x{9fa5}\d]{'.$minLen.','.$maxLen.'}$/iu';
					break;
				default:$match = '/^[_\w\d\x{4e00}-\x{9fa5}]{'.$minLen.','.$maxLen.'}$/iu';
			}
			return preg_match($match,$value);
		}
		/**
		 * 匹配日期
		 * @param $str
		 * @return bool
		 */
		public static function checkDate($str){
			$dateArr = explode("-", $str);
			if (is_numeric($dateArr[0]) && is_numeric($dateArr[1]) && is_numeric($dateArr[2])) {
				if (($dateArr[0] >= 1000 && $dateArr[0] <= 10000) && ($dateArr[1] >= 0 && $dateArr[1] <= 12) && ($dateArr[2] >= 0 && $dateArr[2] <= 31))
					return true;
				else
					return false;
			}
			return false;
		}

		/**
		 * 匹配时间
		 * @param $str
		 * @return bool
		 */
		public static function checkTime($str){
			$timeArr = explode(":", $str);
			if (is_numeric($timeArr[0]) && is_numeric($timeArr[1]) && is_numeric($timeArr[2])) {
				if (($timeArr[0] >= 0 && $timeArr[0] <= 23) && ($timeArr[1] >= 0 && $timeArr[1] <= 59) && ($timeArr[2] >= 0 && $timeArr[2] <= 59))
					return true;
				else
					return false;
			}
			return false;
		}

        /**
         * 时间相比,后一个时间等于前一个时间则返回0
         * 后一个时间大于前一个时间则返回1
         * 后一个时间小于前一个时间则返回-1
         * @param $from_date
         * @param $to_time
         * @return bool
         */
        public static function dateDiff($from_date,$to_time){
            if(strtotime($to_time) - strtotime($from_date)  == 0 ){
                return 0;
            }
            if(strtotime($to_time) - strtotime($from_date) >0 ){
                return 1;
            }
            return -1;

        }
	}