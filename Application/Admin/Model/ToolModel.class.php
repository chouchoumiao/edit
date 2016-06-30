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

        /**
         * 更新session
         */
        static function setSession(){
            $where['id'] = $_SESSION['uid'];
            $obj = M('m_user')->where($where)->find();
            $_SESSION['username'] = $obj['username'];
            $_SESSION['img']      = $obj['img'];
        }

        /**
         * 上传图片
         * @param $config
         * @return mixed   正确则返回路径名称 错误则返回错误信息
         */
        static function uploadImg($config){

            if (!empty($_FILES)) {

                $upload = new \Think\Upload($config);// 实例化上传类
                $info = $upload->upload();

                //判断是否有图
                $pathName = '';
                if($info){
                    foreach($info as $file){
                        $pathName .= $file['savepath'].$file['savename'];
                    }
                    $retArr['success'] = 1;
                    $retArr['msg'] = $pathName;
                    return $retArr;
                }
                else{
                    $retArr['success'] = 0;
                    $retArr['msg'] = $upload->getError();
                    return $retArr;
                }
            }
        }

        /**
         * 默认取得所有的部门,并组成html的checkbook形式返回
         * @return string
         */
        static function showAllDept(){

            $obj = D('Dept')->getAllDept();

            $html = '';
            for($i=0;$i<count($obj);$i++){

                $html .= '<div class="checkbox inline-block">';
                $html .= '<div class="custom-checkbox">';
                $html .= '<input type="checkbox" id="dept'.$obj[$i]['id'].'" value="'.$obj[$i]['id'].'" name="dept'.$obj[$i]['id'].'" class="checkbox-purple" checked>';
                $html .= '<label for="dept'.$obj[$i]['id'].'"></label>';
                $html .= '</div>';
                $html .= '<div class="inline-block vertical-top">'.$obj[$i]['name'];
                $html .= '</div> &nbsp &nbsp';
                $html .= '</div>';
            }

            return $html;

        }


        /**
         * 默认取得所有的角色,拼接角色列表显示
         * @return string
         */
        static function showAllAuto(){

            $obj = D('Auto')->getAllAuto();
            $html = '';
            //(count($obj) - 1) 超级管理员不予显示
            for($i=0;$i<(count($obj) - 1);$i++){

                $html .= '<div class="radio inline-block">';
                $html .= '<div class="custom-radio m-right-xs">';

                if( 1 == $obj[$i]['id']){
                    $html .= '<input type="radio" id="auto'.$obj[$i]['id'].'" value="'.$obj[$i]['id'].'" checked name="auto">';
                }else{
                    $html .= '<input type="radio" id="auto'.$obj[$i]['id'].'" value="'.$obj[$i]['id'].'" name="auto">';
                }
                $html .= '<label for="auto'.$obj[$i]['id'].'"></label>';
                $html .= '</div>';
                $html .= '<div class="inline-block vertical-top">'.$obj[$i]['name'];

                $html .= '</div> &nbsp &nbsp';
                $html .= '</div>';

            }

            return $html;
        }


        /**
         * 从数据库中取得json格式的部门信息,
         * 取得对应用户的部门信息并组成html进行判断输出,用于页面显示
         * @return string
         */
        static function theDept($dept){

            //取得数据库中的deptjson格式后，转化为数组格式
            $deptArr = json_decode($dept);

            //取得数据库中的部门表
            $deptDefineArr = D('Dept')->getAllDept();

            //拼接成html
            $html = '';

            //显示所有的部门信息，如果该用户选过的则显示打勾，不然则不打勾
            for($i=1;$i<=count($deptDefineArr);$i++){

                $html .= '<div class="checkbox inline-block">';
                $html .= '<div class="custom-checkbox">';

                //用于判断没有选择的次数（如果没有选择的次数等于总部门数，则表示没有选中）
                $x = 0;

                //循环判断数据库中部门表在该用户的数组中是否存在，存在则表示选中状态
                for($j=0;$j<count($deptArr);$j++){
                    //如果该用户的部门id在数据表中存在，则改部门为选中状态
                    if($deptArr[$j] == $i){
                        $html .= '<input type="checkbox" id="dept'.$i.'" value="'.$i.'" name="dept'.$i.'" class="checkbox-purple" checked>';
                    }else{
                        //不存在数据表，数值加一
                        $x++;

                    }
                }
                //都不存在，则表示该用户没有选中该部门
                if($x == count($deptArr)){
                    $html .= '<input type="checkbox" id="dept'.$i.'" value="'.$i.'" name="dept'.$i.'" class="checkbox-purple">';
                }
                $html .= '<label for="dept'.$i.'"></label>';
                $html .= '</div>';
                $html .= '<div class="inline-block vertical-top">'.$deptDefineArr[$i -1 ]['name'];
                $html .= '</div> &nbsp &nbsp';
                $html .= '</div>';
            }

            return $html;

        }

        /**
         * 从数据库中取得json格式的角色信息,
         * 取得对应用户的角色信息并组成html进行判断输出,用于页面显示
         * @return string
         */
        static function theAuto($auto){

            $obj = D('Auto')->getAllAuto();
            $html = '';

            //count($obj) - 2 最后两个是管理员和超级管理员，不予显示
            for($i=0;$i<(count($obj) - 2);$i++){

                $html .= '<div class="radio inline-block">';
                $html .= '<div class="custom-radio m-right-xs">';

                if( $auto == $obj[$i]['id']){
                    $html .= '<input type="radio" id="auto'.$obj[$i]['id'].'" value="'.$obj[$i]['id'].'" checked name="auto">';
                }else{
                    $html .= '<input type="radio" id="auto'.$obj[$i]['id'].'" value="'.$obj[$i]['id'].'" name="auto">';
                }
                $html .= '<label for="auto'.$obj[$i]['id'].'"></label>';
                $html .= '</div>';
                $html .= '<div class="inline-block vertical-top">'.$obj[$i]['name'];

                $html .= '</div> &nbsp &nbsp';
                $html .= '</div>';

            }

            return $html;
        }
    }