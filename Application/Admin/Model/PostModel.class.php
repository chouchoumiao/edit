<?php

/**
 * 登录Model
 */
namespace Admin\Model;

	class PostModel {

        private $post_id;
        private $post_author;
        private $post_parent_author;
        private $post_content;
        private $post_title ;
        private $post_dept ;
        private $post_status;
        private $dismissMsg = '';
        private $post_modified;
        private $post_parent;
        private $object;
        private $order;         //默认排序

        public function __construct(){
            if(!$this->object){
                $this->object = M('posts');
                $this->order = 'post_modified DESC';    //默认排序为修改文章的时间降序
            }
        }


        /**
         * 根据传入的flag来判断是执行查询(部门或者用户),还是单纯的查询,显示相应的文章个数
         * @param $flag
         * @param $auto
         * @param string $dept
         * @return mixed
         */
        public function getCountWithAutoAndSearch($flag,$auto,$dept=''){

            switch ($flag){
                case 'userSearch':
                    $userid = I('get.userSearch');
                    switch ($auto){
                        case ADMIN:
                        case SUPPER_ADMIN:
                            //传入的是部门名称,需要转化为部门id
                            $join = "INNER JOIN ccm_m_user 
                            ON ccm_posts.post_author = ccm_m_user.id 
                            AND ccm_posts.post_author = $userid";
                            break;
                        case DEPT_ADMIN:
                            //传入的是部门名称,需要转化为部门id
                            $join = "INNER JOIN ccm_m_user 
                            ON ccm_posts.post_author = ccm_m_user.id 
                            AND ccm_posts.post_author = $userid  
                            AND ccm_posts.post_dept LIKE '%$dept%'";
                            break;
                        case XIAOBIAN:
                            $post_status = POST_SAVE;
                            $join = "INNER JOIN ccm_m_user 
                                        ON ccm_posts.post_author = ccm_m_user.id 
                                        AND ccm_posts.post_author = $userid 
                                        AND ccm_posts.post_status <> '$post_status'
                                        AND ccm_posts.post_child = 0 
                                        AND ccm_posts.post_dept LIKE '%$dept%'";
                            break;
                        case ZONGBIAN:  //总编只显示提交给自己最终审核的文章
                            $join = "INNER JOIN ccm_m_user 
                                        ON ccm_posts.post_author = ccm_m_user.id 
                                        AND ccm_posts.post_author = $userid 
                                        AND ccm_posts.post_status in ('pending2','dismiss','pended')
                                        AND ccm_posts.post_dept LIKE '%$dept%'";
                            break;
                    }
                    break;
                case 'deptSearch':
                    $deptID = $this->getDeptIDByName(I('get.deptSearch'));
                    switch ($auto){
                        case ADMIN:
                        case SUPPER_ADMIN:
                            $join = "INNER JOIN ccm_m_user 
                                        ON ccm_posts.post_author = ccm_m_user.id 
                                        AND ccm_posts.post_dept LIKE '%$deptID%'";
                            break;
                        case BAOLIAOZHE:
                            $id = $_SESSION['uid'];

                                $join = "INNER JOIN ccm_m_user 
                                        ON ccm_posts.post_author = ccm_m_user.id 
                                        AND ccm_posts.post_author = '$id' 
                                        AND ccm_posts.post_dept LIKE '%$deptID%'";
                            break;

                    }

                    break;
                case 'noSearch':
                    if( ( isset($_GET['status'] ) ) && ( '' != I('get.status')) ){
                        $status = I('get.status');
                    }else{
                        $status = 'all';
                    }
                    switch ($auto){
                        case ADMIN:
                        case SUPPER_ADMIN:
                            if($status == 'all'){
                                $join = "INNER JOIN ccm_m_user 
                                            ON ccm_posts.post_author = ccm_m_user.id" ;
                            }else{
                                $join = "INNER JOIN ccm_m_user 
                                            ON ccm_posts.post_author = ccm_m_user.id 
                                            AND ccm_posts.post_status = '$status'" ;
                            }
                            break;
                        case BAOLIAOZHE:
                            if($status == 'all'){
                                $id = $_SESSION['uid'];
                                $join = "INNER JOIN ccm_m_user 
                                            ON ccm_posts.post_author = ccm_m_user.id 
                                            AND ccm_posts.post_author = '$id'";
                            }else{
                                $id = $_SESSION['uid'];

                                if($status == 'dismiss' || $status == 'pended'){
                                    $join = "INNER JOIN ccm_m_user 
                                            ON ccm_posts.post_author = ccm_m_user.id 
                                            AND ccm_posts.post_parent_author = '$id' 
                                            AND ccm_posts.post_status = '$status'" ;
                                }else{

                                    $join = "INNER JOIN ccm_m_user 
                                            ON ccm_posts.post_author = ccm_m_user.id 
                                            AND ccm_posts.post_author = '$id' 
                                            AND ccm_posts.post_status = '$status'" ;
                                }

                            }
                            break;
                        case XIAOBIAN:
                            $post_status = POST_SAVE;
                            if($status == 'all'){
                                $join = "INNER JOIN ccm_m_user 
                                            ON ccm_posts.post_author = ccm_m_user.id 
                                            AND ccm_posts.post_status <> '$post_status' 
                                            AND ccm_posts.post_child = 0 
                                            AND ccm_posts.post_dept LIKE '%$dept%'" ;

                            }else{
                                $join = "INNER JOIN ccm_m_user 
                                            ON ccm_posts.post_author = ccm_m_user.id 
                                            AND ccm_posts.post_status <> '$post_status' 
                                            AND ccm_posts.post_child = 0 
                                            AND ccm_posts.post_dept LIKE '%$dept%' 
                                            AND ccm_posts.post_status = '$status'";
                            }
                            break;
                        case ZONGBIAN:

                            if($status == 'all'){
                                $join = "INNER JOIN ccm_m_user 
                                            ON ccm_posts.post_author = ccm_m_user.id 
                                            AND ccm_posts.post_status in ('pending2','dismiss','pended') 
                                            AND ccm_posts.post_dept LIKE '%$dept%'" ;

                            }else{
                                $join = "INNER JOIN ccm_m_user 
                                            ON ccm_posts.post_author = ccm_m_user.id 
                                            AND ccm_posts.post_status in ('pending2','dismiss','pended') 
                                            AND ccm_posts.post_dept LIKE '%$dept%' 
                                            AND ccm_posts.post_status = '$status'";
                            }
                            break;
                        case DEPT_ADMIN:

                            if($status == 'all'){
                                $join = "INNER JOIN ccm_m_user 
                                            ON ccm_posts.post_author = ccm_m_user.id
                                            AND ccm_posts.post_dept LIKE '%$dept%'" ;

                            }else{
                                $join = "INNER JOIN ccm_m_user 
                                            ON ccm_posts.post_author = ccm_m_user.id
                                            AND ccm_posts.post_dept LIKE '%$dept%' 
                                            AND ccm_posts.post_status = '$status'";
                            }
                            break;
                    }
                    break;
            }


            return $this->object->join($join)->count();
        }

        /**
         * 根据传入的flag来判断是执行查询(部门或者用户),还是单纯的查询,显示相应的文章内容
         * 并对得到的数据进行过滤
         * @param $flag
         * @param $auto
         * @param $limit
         * @param string $dept
         * @return mixed
         */
        public function showPostListWithAutoAndSearch($flag,$auto,$limit,$dept=''){
            $obj = $this->PostListWithAutoAndSearch($flag,$auto,$limit,$dept);

            if($obj){
                //是二维数组则进行数据格式修正并返回
                if(ToolModel::isTwoArray($obj)){
                    return $this->dataFormart($obj);
                }
            }

        }

        /**
         * 根据传入的flag来判断是执行查询(部门或者用户),还是单纯的查询,显示相应的文章内容 (具体执行函数)
         * @param $flag
         * @param $auto
         * @param $limit
         * @param $dept
         * @return mixed
         */
        private function PostListWithAutoAndSearch($flag,$auto,$limit,$dept){
            switch ($flag){
                case 'userSearch':
                    $userid = I('get.userSearch');
                    switch ($auto){
                        case ADMIN:
                        case SUPPER_ADMIN:
                            $join = "INNER JOIN ccm_m_user 
                                        ON ccm_m_user.id = ccm_posts.post_author 
                                        AND ccm_posts.post_author = $userid";
                            break;
                        case XIAOBIAN:
                            $post_status = POST_SAVE;
                            $join = "INNER JOIN ccm_m_user 
                                        ON ccm_m_user.id = ccm_posts.post_author 
                                        AND ccm_posts.post_author = $userid 
                                        AND ccm_posts.post_status <> '$post_status'
                                        AND ccm_posts.post_child = 0 
                                        AND ccm_posts.post_dept LIKE '%$dept%'";
                            break;
                        case ZONGBIAN:

                            $join = "INNER JOIN ccm_m_user 
                                        ON ccm_m_user.id = ccm_posts.post_author 
                                        AND ccm_posts.post_author = $userid 
                                        AND ccm_posts.post_status in ('pending2','dismiss','pended') 
                                        AND ccm_posts.post_dept LIKE '%$dept%'";
                            break;
                        case DEPT_ADMIN:

                            $join = "INNER JOIN ccm_m_user 
                                        ON ccm_m_user.id = ccm_posts.post_author 
                                        AND ccm_posts.post_author = $userid 
                                        AND ccm_posts.post_dept LIKE '%$dept%'";
                            break;
                    }
                    break;
                case 'deptSearch':
                    //传入的是部门名称,需要转化为部门id
                    $deptID =  $this->getDeptIDByName(I('get.deptSearch'));

                    switch ($auto){
                        case ADMIN:
                        case SUPPER_ADMIN:
                            $join = "INNER JOIN ccm_m_user 
                                        ON ccm_m_user.id = ccm_posts.post_author 
                                        AND ccm_posts.post_dept LIKE '%$deptID%'";
                            break;
                        case BAOLIAOZHE:
                            $id = $_SESSION['uid'];
                            $join = "INNER JOIN ccm_m_user 
                                        ON ccm_m_user.id = ccm_posts.post_author 
                                        AND ccm_posts.post_author = '$id' 
                                        AND ccm_posts.post_dept LIKE '%$deptID%'";
                            break;
                    }

                    break;
                case 'noSearch':
                    if( ( isset($_GET['status'] ) ) && ( '' != I('get.status')) ){
                        $status = I('get.status');
                    }else{
                        $status = 'all';
                    }
                    switch ($auto){
                        case ADMIN:
                        case SUPPER_ADMIN:
                            if($status == 'all'){
                                $join = 'INNER JOIN ccm_m_user 
                                            ON ccm_m_user.id = ccm_posts.post_author';
                            }else{
                                $join = "INNER JOIN ccm_m_user 
                                            ON ccm_m_user.id = ccm_posts.post_author
                                            AND ccm_posts.post_status = '$status'";
                            }
                            break;

                        case BAOLIAOZHE:
                            $id = $_SESSION['uid'];

                            if($status == 'all'){
                                $join = "INNER JOIN ccm_m_user 
                                            ON ccm_m_user.id = ccm_posts.post_author 
                                            AND ccm_posts.post_author = '$id'";
                            }else{

                                if($status == 'dismiss' || $status == 'pended'){
                                    $join = "INNER JOIN ccm_m_user 
                                            ON ccm_m_user.id = ccm_posts.post_author 
                                            AND ccm_posts.post_parent_author = '$id'
                                            AND ccm_posts.post_status = '$status'";
                                }else{
                                    $join = "INNER JOIN ccm_m_user 
                                            ON ccm_m_user.id = ccm_posts.post_author 
                                            AND ccm_posts.post_author = '$id'
                                            AND ccm_posts.post_status = '$status'";
                                }

                            }
                            break;
                        case XIAOBIAN:
                            $post_status = POST_SAVE;
                            if($status == 'all'){
                                $join = "INNER JOIN ccm_m_user 
                                            ON ccm_m_user.id = ccm_posts.post_author 
                                            AND ccm_posts.post_status <> '$post_status'
                                             AND ccm_posts.post_child = 0 
                                            AND ccm_posts.post_dept LIKE '%$dept%'";
                            }else{
                                $join = "INNER JOIN ccm_m_user 
                                            ON ccm_m_user.id = ccm_posts.post_author 
                                            AND ccm_posts.post_status <> '$post_status' 
                                            AND ccm_posts.post_child = 0 
                                            AND ccm_posts.post_dept LIKE '%$dept%'
                                            AND ccm_posts.post_status = '$status'";
                            }
                            break;
                        case ZONGBIAN:

                            if($status == 'all'){
                                $join = "INNER JOIN ccm_m_user 
                                            ON ccm_m_user.id = ccm_posts.post_author 
                                            AND ccm_posts.post_status in ('pending2','dismiss','pended') 
                                            AND ccm_posts.post_dept LIKE '%$dept%'";
                            }else{
                                $join = "INNER JOIN ccm_m_user 
                                            ON ccm_m_user.id = ccm_posts.post_author 
                                            AND ccm_posts.post_status in ('pending2','dismiss','pended') 
                                            AND ccm_posts.post_dept LIKE '%$dept%'
                                            AND ccm_posts.post_status = '$status'";
                            }
                            break;
                        case DEPT_ADMIN:

                            if($status == 'all'){
                                $join = "INNER JOIN ccm_m_user 
                                            ON ccm_m_user.id = ccm_posts.post_author 
                                            AND ccm_posts.post_dept LIKE '%$dept%'";
                            }else{
                                $join = "INNER JOIN ccm_m_user 
                                            ON ccm_m_user.id = ccm_posts.post_author 
                                            AND ccm_posts.post_dept LIKE '%$dept%'
                                            AND ccm_posts.post_status = '$status'";
                            }
                            break;
                    }

                    break;
            }


            $field = 'ccm_posts.*,ccm_m_user.id as uid,ccm_m_user.username';

            //多表联合查询
            if('' == $limit){
                return $this->object->field($field)->join($join)->order($this->order)->select();
            }else{
                return $this->object->field($field)->join($join)->order($this->order)->limit($limit)->select();
            }

        }


        /**
         * 根据传入的角色和文章状态取得对应的文章个数(用于显示文章列表一览中的各个状态的文章个数)
         * @param string $auto
         * @param string $status
         * @param string $dept
         * @return mixed
         */
        public function getStatusCountByFlag($auto='',$status='',$dept=''){

            switch ($auto){
                case ADMIN:
                case SUPPER_ADMIN:
                    if($status == 'all'){
                        $join = "INNER JOIN ccm_m_user 
                                    ON ccm_posts.post_author = ccm_m_user.id" ;
                    }else{
                        $join = "INNER JOIN ccm_m_user 
                                ON ccm_posts.post_author = ccm_m_user.id 
                                AND ccm_posts.post_status = '$status'" ;
                    }
                    break;
                case BAOLIAOZHE:
                    if($status == 'all'){
                        $id = $_SESSION['uid'];
                            $join = "INNER JOIN ccm_m_user 
                            ON ccm_posts.post_author = ccm_m_user.id 
                            AND ccm_posts.post_author = '$id'";
                    }else{
                        $id = $_SESSION['uid'];

                        if($status == 'dismiss' || $status == 'pended'){
                            $join = "INNER JOIN ccm_m_user 
                                ON ccm_posts.post_author = ccm_m_user.id 
                                AND ccm_posts.post_parent_author = '$id' 
                                AND ccm_posts.post_status = '$status'" ;
                        }else{
                            $join = "INNER JOIN ccm_m_user 
                                ON ccm_posts.post_author = ccm_m_user.id 
                                AND ccm_posts.post_author = '$id' 
                                AND ccm_posts.post_status = '$status'" ;

                        }
                    }
                    break;
                case XIAOBIAN:
                    $post_status = POST_SAVE;
                    if($status == 'all'){
                        $join = "INNER JOIN ccm_m_user 
                                    ON ccm_posts.post_author = ccm_m_user.id 
                                    AND ccm_posts.post_status <> '$post_status' 
                                    AND ccm_posts.post_child = 0 
                                    AND ccm_posts.post_dept LIKE '%$dept%'" ;

                    }else{
                        $join = "INNER JOIN ccm_m_user 
                                    ON ccm_posts.post_author = ccm_m_user.id 
                                    AND ccm_posts.post_status <> '$post_status' 
                                    AND ccm_posts.post_child = 0 
                                    AND ccm_posts.post_dept LIKE '%$dept%' 
                                    AND ccm_posts.post_status = '$status'" ;
                    }
                    break;
                case ZONGBIAN:

                    if($status == 'all'){
                        $join = "INNER JOIN ccm_m_user 
                                    ON ccm_posts.post_author = ccm_m_user.id 
                                    AND ccm_posts.post_status in ('pending2','dismiss','pended') 
                                    AND ccm_posts.post_dept LIKE '%$dept%'" ;

                    }else{
                        $join = "INNER JOIN ccm_m_user 
                                    ON ccm_posts.post_author = ccm_m_user.id 
                                    AND ccm_posts.post_status in ('pending2','dismiss','pended')  
                                    AND ccm_posts.post_dept LIKE '%$dept%' 
                                    AND ccm_posts.post_status = '$status'" ;
                    }
                    break;
                case DEPT_ADMIN:

                    if($status == 'all'){
                        $join = "INNER JOIN ccm_m_user 
                                    ON ccm_posts.post_author = ccm_m_user.id 
                                    AND ccm_posts.post_dept LIKE '%$dept%'" ;

                    }else{
                        $join = "INNER JOIN ccm_m_user 
                                    ON ccm_posts.post_author = ccm_m_user.id 
                                    AND ccm_posts.post_dept LIKE '%$dept%' 
                                    AND ccm_posts.post_status = '$status'" ;
                    }
                    break;
            }



            return $this->object->join($join)->count();
        }
        

        /**
         * 更新文章时候数据检查以及做成
         */
        public function checkAndSetUpdateData(){

            //获取需要修改的文章的id
            if(!isset($_POST['postid'])) ToolModel::goBack('警告,未能取得本文章的ID!');
            $this->post_id = I('post.postid');

            if(!isset($_SESSION['uid'])) ToolModel::goBack('警告,session出错,请重新登录!');

            $this->post_author = intval($_SESSION['uid']);


            if(!isset($_POST['dept'])) ToolModel::goBack('警告,部门传参错误!');
            if( '' == $_POST['dept']) ToolModel::goBack('警告,部门参数不能为空!');

            //存入数据库中取出转义（默认I函数会转义）
            $this->post_dept = htmlspecialchars_decode(I('post.dept'));

            if(!isset($_POST['title'])) ToolModel::goBack('警告,文章标题传参错误!');
            if( '' == $_POST['title']) ToolModel::goBack('警告,文章标题不能为空!');

            //存入数据库中取出转义（默认I函数会转义）
            $this->post_title = htmlspecialchars_decode(I('post.title'));

            if(!isset($_POST['data'])) ToolModel::goBack('警告,文章内容传参错误!');
            if( '' == $_POST['data']) ToolModel::goBack('警告,文章内容不能为空!');

            //存入数据库中取出转义（默认I函数会转义）
            $this->post_content = htmlspecialchars_decode(I('post.data'));


            //            1 ：保存flag
            //            2 ：提交审核
            //            3 : 继续提交审核
            //            4 : 审核不通过flag
            //            5 : 审核通过flag

            switch (I('post.flag')){
                case 1:
                    $this->post_status = 'save';
                    break;
                case 2:
                    $this->post_status = 'pending';
                    break;
                case 3:
                    $this->post_status = 'pending2';
                    break;
                case 4:
                    $this->post_status = 'dismiss';

                    //审核不通过检查是否填写了原因
                    if(I('post.dismissMsg') == ''){
                        ToolModel::goBack('必须填写不通过原因');
                    }

                    $this->dismissMsg = I('post.dismissMsg');

                    break;
                case 5:
                    $this->post_status = 'pended';
                    break;
            }
        }


        /**
         * 更新文章内容
         * @return bool
         */
        public function updatePost(){
            $now = date('Y/m/d H:i:s',time());

            $where['id'] = $this->post_id;

            $dataArr = array(
                'post_author'           => $this->post_author,
                'post_content'          => $this->post_content,
                'post_title'            => $this->post_title,
                'post_dept'             => $this->post_dept,
                'post_status'           => $this->post_status,
                'post_dismiss_msg'      => $this->dismissMsg,
                'post_modified'         => $now
            );

            if( false === $this->object->where($where)->save($dataArr)){
                return false;
            }else{

                //如果是审核通过或者不通过的情况下,还需要判断是否提交的部门都已经做出审核,
                //如果是,额需要将元文章的状态改变为审核结束

                if(($this->post_status == 'dismiss') || ($this->post_status == 'pended')){

                    //取得该拷贝文章的父文章的id
                    $parentid = $this->getParentPostid($this->post_id);

                    //根据父文章id取得提交给部门的个数
                    $count = $this->getDeptCountByParentid($parentid);

                    //查询父文章id是当前id的所有文章(状态是审核通过或者未审核通过的)
                    $count2 = $this->getParentIdCount($parentid);

                    //如果当前文章是审核通过或者不通过,并且满足了原文章提交的所有部门都最终审核的条件
                    //则将原文章的状态改为[所有部门都确认完毕]
                    if($count == $count2){
                        $where['id'] = $parentid;
                        $data['post_status'] = 'close';
                        $data['post_modified'] = date('Y/m/d H:i:s',time());

                        //更新原文章状态
                        $this->object->where($where)->save($data);
                    }

                }

                return true;
            }
        }


        /**
         * 最终审核文章提交成功后,将评分记录计入评分表
         * @return mixed
         */
        public function insertScore($dept){
            $now = date('Y/m/d H:i:s',time());

            $parentid = $this->getParentPostid($this->post_id);

            $scoreData['postid'] = $parentid;

            $author = $this->getAuthorByPostID($parentid);

            $scoreData['author'] = $author;

            $scoreData['score_author'] = $_SESSION['uid'];
            $scoreData['score'] = I('post.score');
            $scoreData['time'] = $now;
            $scoreData['dept'] = $dept;

            return D('Score')->newScoreInsert($scoreData);

        }

        private function getAuthorByPostID($id){
            $where['id'] = $id;
            $field = 'post_author';

            $data = $this->object->field($field)->where($where)->find();
            if($data){
                return $data['post_author'];
            }
            return false;
        }

        /**
         * 查询父文章id是当前id的所有文章(状态是审核通过或者未审核通过的)
         * @param $id
         * @return mixed
         */
        private function getParentIdCount($id){

            $where = "post_parent = $id AND post_status IN ('pended','dismiss')";
            return $this->object->where($where)->count();
        }

        /**
         * 根据父文章id取得提交给部门的个数
         * @param $parentid
         * @return bool|int
         */
        private function getDeptCountByParentid($parentid){
            $where['id'] = $parentid;
            $field = 'post_dept';

            $arr = $this->object->field($field)->where($where)->find();

            if($arr){
                $dept = json_decode($arr['post_dept']);
                return count($dept);
            }
            return false;

        }

        /**
         * 取得该拷贝文章的父文章的id
         * @param $id
         * @return bool
         */
        private function getParentPostid($id){
            $where['id'] = $id;
            $field = 'post_parent';
            $arr = $this->object->field($field)->where($where)->find();
            if($arr){
                return $arr['post_parent'];
            }
            return false;

        }

        /**
         * 判断是否小编已经点击过改文章生成备份文件了
         * @param $id
         * @return mixed
         */
        public function isCopiedByXIAOBIAN($id){
            $where['post_author'] = $_SESSION['uid'];
            $where['post_parent'] = $id;
            $field = 'id';

            return $this->object->field($field)->where($where)->find();
            
        }

        public function getPostChild($postid){
            $field = 'ccm_posts.post_child';
            $where['id'] = $postid;

            $data = $this->object->field($field)->where($where)->find();
            if($data){
                return $data['post_child'];
            }
            return false;

        }
        
        public function updatePostChild($postid,$newid){
            
            $data['post_child'] = $newid;
            $where['id'] = $postid;

            return $this->object->where($where)->save($data);
            
        }


        public function updatePostByPar($data,$where){
            return $this->object->where($where)->sava($data);

        }

        /**
         * 小编点击审核后默认生成一份备份文件
         * @param $data
         * @param $dept
         * @return mixed
         */
        public function copyPostByXIAOBIAN($data,$dept){
            $now = date('Y/m/d H:i:s',time());
            //$id = intval(I('get.id'));
            $dataArr = array(
                'post_author'  => $_SESSION['uid'],
                'post_date'    => $now,
                'post_content' => $data['post_content'],
                'post_title'   => $data['post_title'],
                'post_dept'    => $dept,
                'post_status'  => 'pending',    //待审核
                'post_dismiss_msg'  => '',    //待审核
                'post_name'    => '',
                'post_modified'=> $now,
                'post_parent'  => $data['id'],   //父节点是提交过来的文章ID
                'post_parent_author'  => $data['post_author']   //父节点是提交过来的文章作者
            );

            //新增小编拷贝文件
            return $this->object->add($dataArr);
        }


        /**
         * 根据传入的id删除文章
         * @param $id
         * @return bool
         */
        public function delThePost($id){
            //删除主表，错误的情况下返回
            if( false === $this->object->where("id=$id")->delete()){
                return false;
            }
            //都正确删除后返回
            return true;
        }


        /**
         * 根据传入的id查询code（用于删除图片）
         * @param $id
         * @return mixed
         */
        public function getTheContent($id){

            $where['id'] = $id;

            return $this->object->field('post_content')->where($where)->find();
        }

        /**
         * 根据传入的id查找是否存在记录
         * @param $id
         * @return bool
         */
        public function idIsExist($id){
            $where['id'] = $id;

            if($this->object->where($where)->count() > 0){
                return true;
            }
            return false;

        }


        /**
         * 根据传入的文章ID取得文章
         * @return mixed
         */
        public function getThePostAndUser($id){
            $where['ccm_posts.id'] = intval($id);
            $field = 'ccm_m_user.username,
                    ccm_posts.post_title,
                    ccm_posts.post_content,
                    ccm_posts.post_dept,
                    ccm_posts.post_name,
                    ccm_posts.post_modified';
            $join = "INNER JOIN ccm_m_user 
                        ON ccm_posts.post_author = ccm_m_user.id";
            return $this->object->field($field)->where($where)->join($join)->find();
        }


        /**
         * 根据传入的文章ID取得文章
         * @return mixed
         */
        public function getThePost($id){
            $where['id'] = intval($id);
            return $this->object->where($where)->find();
        }



        /**
         * 对部门过滤,返回部门的name数组和id数组
         * 对文章状态进行过滤
         * @param $obj
         * @return mixed
         */
        private function dataFormart($obj)
        {
            $deptArr = C('DEPT_ARRAY');   //取得自定义常量部门数组
            $statusArr = C('POST_STATUS');   //取得自定义常量文章状态数组

            for ($i = 0; $i < count($obj); $i++) {

                //如果传入的数据库取得结果集中含有部门信息,则转化数据
                if($obj[$i]['post_dept']){
                    //处理部门数字转化为文字 start
                    $dept = json_decode($obj[$i]['post_dept']);            //json转化为数字

                    //给obj新增dept数组,给文章列表中显示部门可点击用
                    $obj[$i]['post_dept_id'] = $dept;

                    $obj[$i]['post_dept'] = '';                            //先清空原来的数组

                    $arr = array();
                    //将json转化的数组循环判断并显示名称
                    for ($j = 0; $j < count($dept); $j++) {

                        //为空则不输出
                        if ('' != $dept[$j]) {
                            $arr[] = $deptArr[$dept[$j]];
                        }
                    }
                }
                $obj[$i]['post_dept'] = $arr;

                //对文章状态进行过滤
                if($obj[$i]['post_status']) {
                    $spanColor = '';
                    switch ($obj[$i]['post_status']){
                        case 'pending':
                            $spanColor = '<span style="color: #e36159">';
                            $obj[$i]['post_canEdit'] = 1;
                            break;
                        case 'pending2':
                            $spanColor = '<span style="color: #edbc6c">';
                            $obj[$i]['post_canEdit'] = 1;
                            break;
                        case 'save':
                            $spanColor = '<span style="color: #edbc6c">';
                            $obj[$i]['post_canEdit'] = 1;
                            break;
                        case 'dismiss':
                            $spanColor = '<span style="color: #7266ba">';
                            $obj[$i]['post_canEdit'] = 0;
                            break;
                        case 'pended':
                            $spanColor = '<span style="color: #23b7e5">';
                            $obj[$i]['post_canEdit'] = 2;
                            break;
                        case 'close':
                            $spanColor = '<span style="color: #3278b3">';
                            $obj[$i]['post_canEdit'] = 9;
                            break;

                    }

                    $obj[$i]['post_status'] = $spanColor.$statusArr[$obj[$i]['post_status']].'</span>';
                }

            }

            return $obj;
        }


        /**
         * ajax上传的数据进行检查并赋值
         */
        public function checkAndSetNewData(){

            if(!isset($_SESSION['uid'])) ToolModel::goBack('警告,session出错,请重新登录!');

            $this->post_author = intval($_SESSION['uid']);

            $this->post_status = I('post.flag');

            if(!isset($_POST['dept'])) ToolModel::goBack('警告,部门传参错误!');
            if( '' == $_POST['dept']) ToolModel::goBack('警告,部门参数不能为空!');

            //存入数据库中取出转义（默认I函数会转义）
            $this->post_dept = htmlspecialchars_decode(I('post.dept'));

            if(!isset($_POST['title'])) ToolModel::goBack('警告,文章标题传参错误!');
            if( '' == $_POST['title']) ToolModel::goBack('警告,文章标题不能为空!');

            //存入数据库中取出转义（默认I函数会转义）
            $this->post_title = htmlspecialchars_decode(I('post.title'));

            if(!isset($_POST['data'])) ToolModel::goBack('警告,文章内容传参错误!');
            if( '' == $_POST['data']) ToolModel::goBack('警告,文章内容不能为空!');

            //存入数据库中取出转义（默认I函数会转义）
            $this->post_content = htmlspecialchars_decode(I('post.data'));
        }

        /**
         * 追加新文章
         * @return bool
         */
        public function addNewPost(){
            $now = date('Y/m/d H:i:s',time());

            $dataArr = array(
                'post_author'  => $this->post_author,
                'post_date'    => $now,
                'post_content' => $this->post_content,
                'post_title'   => $this->post_title,
                'post_dept'    => $this->post_dept,
                'post_status'  => $this->post_status,
                'post_dismiss_msg'  => '',
                'post_name'    => '',
                'post_modified'=> $now,
                'post_parent'  => 0
            );

            if( false === $this->object->add($dataArr)){
                return false;
            }else{
                return true;
            }
        }

        /**
         * 根据传入的部门名称返回出对应的id(只允许传入一个)
         * @param $deptName
         * @return int
         */
        private function getDeptIDByName($deptName){
            $deptArr = C('DEPT_ARRAY');

            for ($i = 1; $i<=count($deptArr);$i++){
                if($deptArr[$i] == $deptName){
                    return $i;
                }
            }
        }
        
    }