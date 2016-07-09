<?php

/**
 * 登录Model
 */
namespace Admin\Model;

	class PostModel {

        private $post_id;
        private $post_author;
        private $post_content;
        private $post_title ;
        private $post_dept ;
        private $post_status;
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
                        case XIAOBIAN:
                        case ZONGBIAN:
                            //点击用户查询(管理员和小编总编可以),管理员默认取得全部,小编总编条件中需要加入部门和不显示保存的
                            $join = "INNER JOIN ccm_m_user 
                        ON ccm_posts.post_author = ccm_m_user.id 
                        AND ccm_posts.post_author = $userid 
                        AND ccm_posts.post_status <> 'save'
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
                                $join = "INNER JOIN ccm_m_user 
                                ON ccm_posts.post_author = ccm_m_user.id 
                                AND ccm_posts.post_author = '$id' 
                                AND ccm_posts.post_status = '$status'" ;
                            }
                            break;
                        case XIAOBIAN:
                        case ZONGBIAN:
                            if($status == 'all'){
                                $join = "INNER JOIN ccm_m_user 
                            ON ccm_posts.post_author = ccm_m_user.id 
                            AND ccm_posts.post_status <> 'save' 
                            AND ccm_posts.post_dept LIKE '%$dept%'" ;

                            }else{
                                $join = "INNER JOIN ccm_m_user 
                            ON ccm_posts.post_author = ccm_m_user.id 
                            AND ccm_posts.post_status <> 'save' 
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
                        case ZONGBIAN:
                            $join = "INNER JOIN ccm_m_user 
                                        ON ccm_m_user.id = ccm_posts.post_author 
                                        AND ccm_posts.post_author = $userid 
                                        AND ccm_posts.post_status <> 'save'
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
                                $join = "INNER JOIN ccm_m_user 
                                            ON ccm_m_user.id = ccm_posts.post_author 
                                            AND ccm_posts.post_author = '$id'
                                            AND ccm_posts.post_status = '$status'";
                            }
                            break;
                        case XIAOBIAN:
                        case ZONGBIAN:
                            if($status == 'all'){
                                $join = "INNER JOIN ccm_m_user 
                                            ON ccm_m_user.id = ccm_posts.post_author 
                                            AND ccm_posts.post_status <> 'save' 
                                            AND ccm_posts.post_dept LIKE '%$dept%'";
                            }else{
                                $join = "INNER JOIN ccm_m_user 
                                            ON ccm_m_user.id = ccm_posts.post_author 
                                            AND ccm_posts.post_status <> 'save' 
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
                        $join = "INNER JOIN ccm_m_user 
                                ON ccm_posts.post_author = ccm_m_user.id 
                                AND ccm_posts.post_author = '$id' 
                                AND ccm_posts.post_status = '$status'" ;
                    }
                    break;
                case XIAOBIAN:
                case ZONGBIAN:
                    if($status == 'all'){
                        $join = "INNER JOIN ccm_m_user 
                            ON ccm_posts.post_author = ccm_m_user.id 
                            AND ccm_posts.post_status <> 'save' 
                            AND ccm_posts.post_dept LIKE '%$dept%'" ;

                    }else{
                        $join = "INNER JOIN ccm_m_user 
                            ON ccm_posts.post_author = ccm_m_user.id 
                            AND ccm_posts.post_status <> 'save' 
                            AND ccm_posts.post_dept LIKE '%$dept%' 
                            AND ccm_posts.post_status = '$status'";
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
                    break;
                case 5:
                    $this->post_status = 'pended';
                    break;
            }

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
         * 更新文章内容
         * @return bool
         */
        public function updatePost(){
            $now = date('Y/m/d H:i:s',time());

            $where['id'] = $this->post_id;

            $dataArr = array(
                'post_author'  => $this->post_author,
                'post_content' => $this->post_content,
                'post_title'   => $this->post_title,
                'post_dept'    => $this->post_dept,
                'post_status'  => $this->post_status,
                'post_modified'=> $now
            );

            if( false === $this->object->where($where)->save($dataArr)){
                return false;
            }else{
                return true;
            }
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
                    $obj[$i]['post_status'] = $statusArr[$obj[$i]['post_status']];
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