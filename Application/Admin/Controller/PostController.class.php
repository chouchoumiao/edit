<?php
namespace Admin\Controller;
use Admin\Model\ToolModel;
use Think\Controller;

header("Content-type: text/html;charset=utf-8");

class PostController extends CommonController {


    //表单接收
    public function subform(){

        dump($_POST);exit;

    }

    //关于上传 请参考我的另一篇记录
    public function upload(){

        //图片上传设置
        $config = array(
            'maxSize'    =>    3145728,
            'rootPath'	 =>    'Public',
            'savePath'   =>    '/Uploads/post/',
            'saveName'   =>    array('uniqid',''),
            'exts'       =>    array('jpg','png','jpeg'),
            'autoSub'    =>    false,
            'subName'    =>    array('date','Ymd'),
        );

        $arr['success'] = 'ok';
        $arr['msg'] = $this->uploadImg($config);
        echo json_encode($arr);
        exit;

    }

    public function doAction(){

        $action = $_GET['action'];
        if( isset($action) && '' != $action ){
            switch($action){

                //取得所有用户(分页)
                case 'all':
                    $this->assign('all',true);
                    $this->display('post');
                    break;

                //取得当前用户
                case 'the':

                    $this->assign('the',true);
                    $this->display('post');

                    break;

                //删除用户
                case 'del':

                    break;
                //追加用户
                case 'add':
                    $this->assign('add',true);
                    $this->display('post');



                    break;

                //追加用户
                case 'addNew':

                    //dump($_FILES);exit;
                    
                    

                    // A list of permitted file extensions
                    $allowed = array('png', 'jpg', 'gif','zip');

                    if(isset($_FILES['file']) && $_FILES['file']['error'] == 0){

                        $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

                        if(!in_array(strtolower($extension), $allowed)){
                            echo '{"status":"error"}';
                            exit;
                        }

                        if(move_uploaded_file($_FILES['file']['tmp_name'], 'uploads/'.$_FILES['file']['name'])){
                            $tmp='uploads/'.$_FILES['file']['name'];
                            echo 'uploads/'.$_FILES['file']['name'];
                            //echo '{"status":"success"}';
                            exit;
                        }
                    }

                    echo '{"status":"error"}';
                    exit;


                    break;

                default:
                    break;
            }
        }

    }

    private function uploadImg($config){

        if (!empty($_FILES)) {

            $upload = new \Think\Upload($config);// 实例化上传类
            $info = $upload->upload();

            //判断是否有图
            $pathName = '';
            if($info){
                foreach($info as $file){
                    $pathName .= $file['savepath'].$file['savename'];
                }
                return $pathName;
            }
            else{
                $this->error($upload->getError());
            }
        }
    }


}
