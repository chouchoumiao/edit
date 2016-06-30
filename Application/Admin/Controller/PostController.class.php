<?php
namespace Admin\Controller;
use Admin\Model\ToolModel;
use Think\Controller;

header("Content-type: text/html;charset=utf-8");

class PostController extends CommonController {

    public function doAction(){

        $action = $_GET['action'];
        if( isset($action) && '' != $action ){
            switch($action){

                //取得所有用户(分页)
                case 'all':

                    $this->assign('all',true);

                    $obj = D('Post');

                    //取得所有用户信息总条数，用于分页
                    $count = $obj->getCount();

                    //分页
                    import('ORG.Util.Page');// 导入分页类
                    $Page = new \Org\Util\Page($count,PAGE_SHOW_COUNT);// 实例化分页类 传入总记录数
                    $limit = $Page->firstRow.','.$Page->listRows;

                    //取得指定条数的信息


                    $user = $obj->showPostList($limit);

                    $show = $Page->show();// 分页显示输出


                    $this->assign('allPost',$user); //用户信息注入模板
                    $this->assign('page',$show);    //赋值分页输出

                    $this->display('post');
                    break;

                //取得当前用户
                case 'the':
                    $where['post_author'] = $_SESSION['uid'];
                    $data = M('posts')->where($where)->find();

                    dump($data);exit;

                    $content = $data['post_content'];
                    $this->assign('content',$content);

                    $this->assign('the',true);
                    $this->display('post');

                    break;

                //删除用户
                case 'del':

                    break;
                //追加用户
                case 'add':

                    //追加部门设置
                    $this->assign('dept',$this->dept());

                    $this->assign('add',true);
                    $this->display('post');

                    break;
                case 'upload':
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

                    $retArr = $this->uploadImg($config);

                    if($retArr['success']){
                        $arr['success'] = 1;
                        $arr['msg'] = $retArr['msg'];
                    }else{
                        $arr['success'] = 0;
                        $arr['msg'] = $retArr['msg'];
                    }

                    echo json_encode($arr);
                    exit;
                    break;

                //追加用户
                case 'addNew':

                    $obj = D('Post');
                    $obj->setNewData();

                    if( $obj->addNewPost()){
                        $arr['success'] = 1;
                        $arr['msg'] = '新增成功！';
                    }else{
                        $arr['success'] = 0;
                        $arr['msg'] = '新增失败，请重试！';
                    }
                    echo json_encode($arr);
                    exit;
                    break;

                default:
                    break;
            }
        }

    }

    /**
     * 上传图片
     * @param $config
     * @return mixed   正确则返回路径名称 错误则返回错误信息
     */
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
     * 拼接部门列表显示
     * @return string
     */
    private function dept(){

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


}
