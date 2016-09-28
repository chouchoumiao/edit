<?php

/**
 * 登录Model
 */
namespace Admin\Model;

    use Think\Log;

    class PostModel {

        private $post_id;
        private $post_author;
        private $post_judge;        //审批者 新加
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
         * 根据传入的打分者的文章ID(不是爆料者最先的文章ID)
         * @param $scorePostID
         * @return bool
         */
        public function getTheScore($scorePostID){

            $ret = D('Score')->getScoreInfoByPostId($scorePostID);
            if($ret){
                return $ret['score'];
            }
            return false;


        }

        /**
         * 更新附件表
         * @param $data
         * @return bool
         */
        public function updatetAttachmentData($data){
            $where['post_id'] = $data['post_id'];

            $newAttacnment['post_attachment'] = $data['post_attachment'];
            $newAttacnment['post_save_name'] = $data['post_save_name'];
            $newAttacnment['post_file_name'] = $data['post_file_name'];
            $newAttacnment['time'] = date('Y-m-d H:i:s', time());

            if( false === M('post_attachment')->where($where)->save($newAttacnment)){
                return false;
            }
            return true;

        }
        /**
         * 根据传入的文章ID取得对应的附件
         * @param $postID
         * @return bool
         */
        public function getAttachmentData($postID){

            $where['post_id'] = $postID;

            $data = M('post_attachment')->where($where)->find();
            if(!$data){
                return false;
            }
            return $data;

        }

        /**
         * 根据传入的postid来删除对应的单独上传的附件
         * @param $postID 文章ID
         * @return bool
         */
        public function delAttachmentData($postID){
            $where['post_id'] = $postID;

            if( false === M('post_attachment')->where($where)->delete()){
                return false;
            }
            return true;

        }

        /**
         * 数据库中追加的单独上传附件
         * @param $data
         * @return bool
         */
        public function insertAttachment($data){
            $ret = M('post_attachment')->add($data);
            if( false === $ret){
                return false;
            }
            return $ret;
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
                            //$post_status = POST_SAVE;
                            $join = "INNER JOIN ccm_m_user 
                                        ON ccm_posts.post_author = ccm_m_user.id 
                                        AND ccm_posts.post_author = $userid 
                                        AND ccm_posts.post_dept LIKE '%$dept%'";
                            break;
                        case ZONGBIAN:  //总编只显示提交给自己最终审核的文章
                            $join = "INNER JOIN ccm_m_user 
                                        ON ccm_posts.post_author = ccm_m_user.id 
                                        AND ccm_posts.post_author = $userid 
                                        AND ccm_posts.post_status in ('pending2','dismiss','pended','return')
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

                        //如果取得的status不在信息的状态数组中则显示全部
                        if(!in_array($status,C('POST_ALLOW_STATUS'))){
                            $status = 'all';
                        }

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
                            $theID = $_SESSION['uid'];
                            //$post_status = POST_SAVE;
                            if($status == 'all'){
                                $join = "INNER JOIN ccm_m_user 
                                            ON ccm_m_user.id = ccm_posts.post_author 
                                            AND ccm_posts.post_dept LIKE '%$dept%' 
                                            AND ( ccm_posts.post_name LIKE '%$dept%' 
                                            OR (ccm_posts.post_status = 'pending' 
                                            AND ccm_posts.post_parent = 0))" ;       //追加判断未被编辑部门才显示

                            }else{

                                if($status == POST_SAVE){

                                    $join = "INNER JOIN ccm_m_user 
                                            ON ccm_posts.post_author = ccm_m_user.id 
                                            AND ccm_posts.post_dept LIKE '%$dept%' 
                                            AND ccm_posts.post_author = '$theID'
                                            AND ccm_posts.post_status = '$status'";
                                }else {
                                    $join = "INNER JOIN ccm_m_user 
                                            ON ccm_posts.post_author = ccm_m_user.id 
                                            AND ccm_posts.post_dept LIKE '%$dept%' 
                                            AND ccm_posts.post_status = '$status'
                                            AND ccm_posts.post_name LIKE '%$dept%'" ;       //追加判断未被编辑部门才显示
                                }
                            }
                            break;
                        case ZONGBIAN:

                            if($status == 'all'){
                                $join = "INNER JOIN ccm_m_user 
                                            ON ccm_posts.post_author = ccm_m_user.id 
                                            AND ccm_posts.post_status in ('pending2','dismiss','pended','return') 
                                            AND ccm_posts.post_dept LIKE '%$dept%'" ;

                            }else{
                                $join = "INNER JOIN ccm_m_user 
                                            ON ccm_posts.post_author = ccm_m_user.id 
                                            AND ccm_posts.post_status in ('pending2','dismiss','pended','return') 
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
                            //$post_status = POST_SAVE;
                            $join = "INNER JOIN ccm_m_user 
                                        ON ccm_m_user.id = ccm_posts.post_author 
                                        AND ccm_posts.post_author = $userid 
                                        AND ccm_posts.post_dept LIKE '%$dept%'";
                            break;
                        case ZONGBIAN:

                            $join = "INNER JOIN ccm_m_user 
                                        ON ccm_m_user.id = ccm_posts.post_author 
                                        AND ccm_posts.post_author = $userid 
                                        AND ccm_posts.post_status in ('pending2','dismiss','pended','return') 
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
                        //如果取得的status不在信息的状态数组中则显示全部
                        if(!in_array($status,C('POST_ALLOW_STATUS'))){
                            $status = 'all';
                        }
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
                            //$post_status = POST_SAVE;
                            $theID = $_SESSION['uid'];
                            if($status == 'all'){
                                $join = "INNER JOIN ccm_m_user 
                                            ON ccm_m_user.id = ccm_posts.post_author 
                                            AND ccm_posts.post_dept LIKE '%$dept%' 
                                            AND ( ccm_posts.post_name LIKE '%$dept%' 
                                            OR (ccm_posts.post_status = 'pending' 
                                            AND ccm_posts.post_parent = 0))" ;       //追加判断未被编辑部门才显示
                            }else{

                                if($status == POST_SAVE){

                                    $join = "INNER JOIN ccm_m_user 
                                            ON ccm_m_user.id = ccm_posts.post_author 
                                            AND ccm_posts.post_dept LIKE '%$dept%'
                                            AND ccm_posts.post_author = '$theID'
                                            AND ccm_posts.post_status = '$status'";
                                }else {
                                    $join = "INNER JOIN ccm_m_user 
                                            ON ccm_m_user.id = ccm_posts.post_author 
                                            AND ccm_posts.post_dept LIKE '%$dept%'
                                            AND ccm_posts.post_status = '$status'
                                            AND ccm_posts.post_name LIKE '%$dept%'" ;       //追加判断未被编辑部门才显示
                                }
                            }
                            break;
                        case ZONGBIAN:

                            if($status == 'all'){
                                $join = "INNER JOIN ccm_m_user 
                                            ON ccm_m_user.id = ccm_posts.post_author 
                                            AND ccm_posts.post_status in ('pending2','dismiss','pended','return') 
                                            AND ccm_posts.post_dept LIKE '%$dept%'";
                            }else{
                                $join = "INNER JOIN ccm_m_user 
                                            ON ccm_m_user.id = ccm_posts.post_author 
                                            AND ccm_posts.post_status in ('pending2','dismiss','pended','return') 
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


//            $field = 'ccm_posts.*,ccm_m_user.id as uid,ccm_m_user.username';
            $field = 'ccm_posts.*,ccm_m_user.id as uid,ccm_m_user.username,ccm_score.score';

            $join1 = "LEFT JOIN ccm_score 
                    ON ccm_score.score_post_id = ccm_posts.id
                    AND score_flag = 1";

            //多表联合查询
            if('' == $limit){
                return $this->object->field($field)->join($join)->join($join1)->order($this->order)->select();
            }else{
                return $this->object->field($field)->join($join)->join($join1)->order($this->order)->limit($limit)->select();
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
                    //$post_status = POST_SAVE;
                    $theID = $_SESSION['uid'];
                    if($status == 'all'){
                        $join = "INNER JOIN ccm_m_user 
                                ON ccm_m_user.id = ccm_posts.post_author 
                                AND ccm_posts.post_dept LIKE '%$dept%' 
                                AND ( ccm_posts.post_name LIKE '%$dept%' 
                                OR (ccm_posts.post_status = 'pending' 
                                AND ccm_posts.post_parent = 0))" ;       //追加判断未被编辑部门才显示

                    }else{
                        if($status == POST_SAVE){

                            $join = "INNER JOIN ccm_m_user 
                                    ON ccm_posts.post_author = ccm_m_user.id 
                                    AND ccm_posts.post_dept LIKE '%$dept%' 
                                    AND ccm_posts.post_author = '$theID' 
                                    AND ccm_posts.post_status = '$status'" ;
                        }else{
                            $join = "INNER JOIN ccm_m_user 
                                    ON ccm_posts.post_author = ccm_m_user.id 
                                    AND ccm_posts.post_dept LIKE '%$dept%' 
                                    AND ccm_posts.post_status = '$status'
                                    AND ccm_posts.post_name LIKE '%$dept%'" ;       //追加判断未被编辑部门才显示
                        }

                    }
                    break;
                case ZONGBIAN:

                    if($status == 'all'){
                        $join = "INNER JOIN ccm_m_user 
                                    ON ccm_posts.post_author = ccm_m_user.id 
                                    AND ccm_posts.post_status in ('pending2','dismiss','pended','return') 
                                    AND ccm_posts.post_dept LIKE '%$dept%'" ;

                    }else{
                        $join = "INNER JOIN ccm_m_user 
                                    ON ccm_posts.post_author = ccm_m_user.id 
                                    AND ccm_posts.post_status in ('pending2','dismiss','pended','return')  
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

            $this->post_author = '';


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
            //            6 : 打回给小编flag

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
                    $this->post_judge = intval($_SESSION['uid']);   //追加审批者信息

                    break;
                case 5:
                    $this->post_status = 'pended';
                    $this->post_judge = intval($_SESSION['uid']);   //追加审批者信息
                    break;
                case 6:
                    $this->post_status = 'return';
                    //审核不通过检查是否填写了原因
                    if(I('post.dismissMsg') == ''){
                        ToolModel::goBack('必须填写不通过原因');
                    }
                    $this->dismissMsg = I('post.dismissMsg');
                    $this->post_judge = intval($_SESSION['uid']);   //追加审批者信息
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
//                'post_author'           => $this->post_author,
                'post_judge'            => $this->post_judge,       //追加审批者信息
                'post_content'          => $this->post_content,
                'post_title'            => $this->post_title,
                'post_dept'             => $this->post_dept,
                'post_name'             => $this->post_dept,        //为了判定被继承个数,用此字段
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
         * 小编或者总编都可以进行打分,将评分记录计入评分表
         * @param $auto 权限 是小编还是总编
         * @param $dept 部门
         * @return mixed
         */
        public function insertScore($auto,$dept){
            $now = date('Y/m/d H:i:s',time());

            $parentid = $this->getParentPostid($this->post_id);

            $scoreData['postid'] = $parentid;

            $author = $this->getAuthorByPostID($parentid);

            $scoreData['author'] = $author;

            //因为小编也需要打分所以新增两个字段
            $scoreData['score_post_id'] = $this->post_id;

            //小编的flag=0，总编=1
            if($auto == XIAOBIAN){
                $scoreData['score_flag'] = 0;
            }else{
                $scoreData['score_flag'] = 1;
            }

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

        /**
         * 取得原文章的未被小编编辑的(未被继承的)部门数组
         * @param $postid       原文章ID
         * @return bool
         */
        public function getPostName($postid){
            $field = 'ccm_posts.post_name';
            $where['id'] = $postid;

            $data = $this->object->field($field)->where($where)->find();
            if($data){
                return $data['post_name'];
            }
            return false;

        }
        
        public function updatePostName($postid,$postName){
            
            $data['post_name'] = $postName;
            $where['id'] = $postid;

            return $this->object->where($where)->save($data);
            
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
         * 根据传入的文章ID取得文章(与user联合查询)
         * @param $id
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
         * @param $id
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
                        case 'return':  //又可以编辑又可以查看原因
                            $spanColor = '<span style="color: #3278b3">';
                            $obj[$i]['post_canEdit'] = 10;
                            break;

                    }

                    //取得当前用户的详细信息，用于判断当前用户的权限等
                    $nowUserInfo = ToolModel::getNowXioabianUserInfo();

                    //如果当前用户的权限是小编则要继续进行判断，否则不需要进行多余判断
                    if($nowUserInfo['udi_auto_id'] == XIAOBIAN) {
                        $obj[$i] = $this->doXioabianListShow($obj[$i], $spanColor, $statusArr);

                    }else if( ($nowUserInfo['udi_auto_id'] == BAOLIAOZHE) && ($obj[$i]['post_status'] == 'pending') ){
                            //$spanColor = '<span style="color: #23b7e5">';
                            $obj[$i]['post_canEdit'] = 2;
                            $obj[$i]['post_status'] = $spanColor.$statusArr[$obj[$i]['post_status']].'[不能修改]</span>';
                    }else{

                        $obj[$i]['post_status'] = $spanColor.$statusArr[$obj[$i]['post_status']].'</span>';
                    }

                }

            }

            return $obj;
        }

        /**
         *
         * 先判断当前的文章是不是爆料者发布的，并且没有父级文章，并且已经被继承(至少被一个小编拷贝)   0：则进行普通的显示
         *                                                                            1；继续判断(看下记说明)
         * 判断被继承的字段中是单个还是多个     0：单个直接传入判断
         *                                1：多个则进行循环在传入判断
         *
         * @param $objArr       需要返回出去的文章obj
         * @param $spanColor    显示的文章颜色
         * @param $statusArr    文章状态列表数组
         * @return mixed
         */
        private function doXioabianListShow($objArr,$spanColor,$statusArr){
            $xiaobianInfo = ToolModel::getNowXioabianUserInfo();
            

            //对于爆料者的文章显示 根据是否被小编继承 再做相应判断
            if( ($objArr['post_status'] == 'pending') && (intval($objArr['post_parent']) == 0) &&(intval($objArr['post_child']) != 0) ) {

                $objArr['post_status'] = $spanColor . $statusArr[$objArr['post_status']] . '</span>';

                //取得被继承的部门的对应文章列表
                $childList = $objArr['post_child'];

                $objArr = $this->showPostListByXioabianDept($childList, $objArr);

            //小编的文章 判断
            }else{

                //小编的文章，分当前是本小编和不是本小编
                if($objArr['post_author'] != $xiaobianInfo['uid']){
                    $objArr['post_canEdit'] = 2;                        //当前小编不是文章的作者，则显示预览，不能编辑 （默认是可以编辑的状态）
                }

                $objArr['post_status'] = $spanColor.$statusArr[$objArr['post_status']].'</span>';
            }
            return $objArr;
        }


        /**
         * 根据childId来查询该小编拷贝文章的部门
         * 拷贝文章部门是否为本部门   0：则显示最普通的，并返回
         *                        1：继续进行判断(看下判断内容)
         *
         * 拷贝文档本部门的是不是当前小编   0：则显示被谁认领（本部门的其他人）
         *                            1； 显示被本人认领
         *
         * @param $childList    被继承的postID（小编拷贝文章后的ID）
         * @param $objArr       需要返回出去的文章obj
         * @return mixed
         */
        private function showPostListByXioabianDept($childList,$objArr){

            $xiaobianInfo = ToolModel::getNowXioabianUserInfo();

            $childArr = explode(',', $childList);

            for ($j = 0; $j < count($childArr); $j++) {

                $childPostDeptAndUserName = $this->getChildDeptAndUsernameByChildPostID($childArr[$j]);

                if ($childPostDeptAndUserName['post_dept'] == $xiaobianInfo['udi_dep_id'] ) {
                    //文章列表中显示被谁认领，默认为本人
                    $userName = '本人';
                    $word = '认领了文章';


                    //如果当前用户不是认领该文章的小编则显示被认领状态，否则现正常显示
                    if($childPostDeptAndUserName['username'] != $xiaobianInfo['username'] ){
                        //不是本人则显示对应的人名
                        $userName = $childPostDeptAndUserName['username'];

                        switch ($childPostDeptAndUserName['post_status']){
                            case 'pended':
                                $word = '审核通过';
                                break;
                            case 'dismiss':
                                $word = '审核不通过';
                                break;
                            case 'return':
                                $word = '文章打回';
                                break;
                            case 'pending2':
                                $word = '认领了文章';
                                break;
                            default:
                                $word = '未知状态';
                                break;
                        }

                    }
                    $spanColor = '<span style="color: #ccc">';
                    $objArr['post_canEdit'] = 2;
                    $objArr['post_status'] = $spanColor.'['.$userName.']'.$word.'</span>';

                }

            }
            return $objArr;
        }

        /**
         * 根据被继承的postID查询该文章(被小编拷贝)的属于小编部门和该小编的姓名
         * @param $childPostId
         * @return bool
         */
        private function getChildDeptAndUsernameByChildPostID($childPostId){
            $field = 'post_dept,username,post_status';
            $where['ccm_posts.id'] = $childPostId;
            $join = "INNER JOIN ccm_m_user 
                        ON ccm_posts.post_author = ccm_m_user.id";

            $ret = $this->object->join($join)->field($field)->where($where)->find();
            if( false === $ret){
                return false;
            }
            return $ret;
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
                'post_name'    => $this->post_dept,     //为了判定被继承个数,用此字段
                'post_modified'=> $now,
                'post_parent'  => 0
            );

            $newId = $this->object->add($dataArr);

            if(false === $newId ){
                return false;
            }else{

                //判断是否存在单独附件上传,有则存入数据库
                $attachment = htmlspecialchars_decode(I('attachment'));
                $saveName = htmlspecialchars_decode(I('saveName'));
                $fileName = htmlspecialchars_decode(I('fileName'));
                if('' != $attachment){

                    $data['post_id'] = $newId;
                    $data['post_attachment'] = $attachment;
                    $data['post_save_name'] = $saveName;
                    $data['post_file_name'] = $fileName;
                    $data['time'] = $now;

                    if( false == $this->insertAttachment($data)){
                        return false;
                    }
                }

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