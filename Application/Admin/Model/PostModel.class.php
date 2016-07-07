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
         * 文章一览中除了爆料者角色以外的都可以通过点击作者来获取文章个数
         * @param $userid
         * @param string $dept
         * @return mixed
         */
        public function getUserSearchCount($userid,$dept=''){

            if($dept == ''){
                //传入的是部门名称,需要转化为部门id
                $join = "INNER JOIN ccm_m_user 
                        ON ccm_posts.post_author = ccm_m_user.id 
                        AND ccm_posts.post_author = $userid";
            }else{
                //点击用户查询(管理员和小编总编可以),管理员默认取得全部,小编总编条件中需要加入部门和不显示保存的
                $join = "INNER JOIN ccm_m_user 
                        ON ccm_posts.post_author = ccm_m_user.id 
                        AND ccm_posts.post_author = $userid 
                        AND ccm_posts.post_status <> 'save'
                        AND ccm_posts.post_dept LIKE '%$dept%'";
            }

            return $this->object->join($join)->count();
        }

        /**
         * 文章一览中除了爆料者角色以外的都可以通过点击作者来筛选文章列表
         * @param $userid
         * @param $limit
         * @param string $dept
         * @return mixed
         */
        public function showUserSearchPostList($userid,$limit,$dept=''){

            //点击用户查询(管理员和小编总编可以),管理员默认取得全部,小编总编条件中需要加入部门和不显示保存的
            $obj = $this->allUserSearchPost($userid,$limit,$dept);

            if($obj){
                //是二维数组则进行数据格式修正并返回
                if(ToolModel::isTwoArray($obj)){
                    return $this->dataFormart($obj);
                }
            }

        }

        /**
         * 文章一览中除了爆料者角色以外的都可以通过点击作者来筛选文章列表
         * @param $userid
         * @param $limit
         * @param $dept
         * @return mixed
         */
        private function allUserSearchPost($userid,$limit,$dept){

            //点击用户查询(管理员和小编总编可以),管理员默认取得全部,小编总编条件中需要加入部门和不显示保存的
            if($dept == ''){
                $join = "INNER JOIN ccm_m_user 
                        ON ccm_m_user.id = ccm_posts.post_author 
                        AND ccm_posts.post_author = $userid";
            }else{
                $join = "INNER JOIN ccm_m_user 
                        ON ccm_m_user.id = ccm_posts.post_author 
                        AND ccm_posts.post_author = $userid 
                        AND ccm_posts.post_status <> 'save'
                        AND ccm_posts.post_dept LIKE '%$dept%'";
            }
            $field = 'ccm_posts.*,
                        ccm_m_user.id as uid,
                        ccm_m_user.username';

            //多表联合查询
            if('' == $limit){
                return $this->object->field($field)->join($join)->order($this->order)->select();
            }else{
                return $this->object->field($field)->join($join)->order($this->order)->limit($limit)->select();
            }

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
         * 根据点击的部门名称先取得id,进一步取得该部门的所有文章条数
         * @return mixed
         */
        public function getdeptSearchCount($auto,$userid=''){

            //传入的是部门名称,需要转化为部门id
            $deptID = $this->getDeptIDByName(I('get.deptSearch'));

            if($userid == ''){
                $join = "INNER JOIN ccm_m_user 
                        ON ccm_posts.post_author = ccm_m_user.id 
                        AND ccm_posts.post_dept LIKE '%$deptID%'";
            }else{
                $join = "INNER JOIN ccm_m_user 
                        ON ccm_posts.post_author = ccm_m_user.id 
                        AND ccm_posts.post_author = '$userid' 
                        AND ccm_posts.post_dept LIKE '%$deptID%'";
            }


            if($auto == BAOLIAOZHE){
                $where['ccm_m_user.id'] = $_SESSION['uid'];
                return $this->object->join($join)->where($where)->count();
            }
            return $this->object->join($join)->count();
        }

        /**
         * 根据点击的部门名称先取得id,进一步取得该部门的所有文章列表,并过滤字段
         * @param $limit
         * @return mixed
         */
        public function showdeptSearchPostList($auto,$limit,$userid = ''){

            //取得该部门的所有文章列表
            $obj = $this->alldeptSearchPost($auto,$limit,$userid);

            if($obj){
                //是二维数组则进行数据格式修正并返回
                if(ToolModel::isTwoArray($obj)){
                    return $this->dataFormart($obj);
                }
            }

        }

        /**
         * 根据点击的部门名称先取得id,进一步取得该部门的所有文章列表
         * @param $limit
         * @return mixed
         */
        private function alldeptSearchPost($auto,$limit,$userid){

            //传入的是部门名称,需要转化为部门id
            $deptID =  $this->getDeptIDByName(I('get.deptSearch'));

            $field = 'ccm_posts.*,
                        ccm_m_user.id as uid,
                        ccm_m_user.username';

            if($userid == ''){
                $join = "INNER JOIN ccm_m_user 
                        ON ccm_m_user.id = ccm_posts.post_author 
                        AND ccm_posts.post_dept LIKE '%$deptID%'";
            }else{
                $join = "INNER JOIN ccm_m_user 
                        ON ccm_m_user.id = ccm_posts.post_author 
                        AND ccm_posts.post_author = '$userid' 
                        AND ccm_posts.post_dept LIKE '%$deptID%'";
            }

            //多表联合查询
            if('' == $limit){
                if($auto == BAOLIAOZHE){
                    $where['ccm_m_user.id'] = $_SESSION['uid'];
                    return $this->object->field($field)->join($join)->where($where)->order($this->order)->select();
                }
                return $this->object->field($field)->join($join)->order($this->order)->select();
            }else{
                if($auto == BAOLIAOZHE){
                    $where['ccm_m_user.id'] = $_SESSION['uid'];
                    return $this->object->field($field)->join($join)->where($where)->order($this->order)->limit($limit)->select();
                }
                return $this->object->field($field)->join($join)->order($this->order)->limit($limit)->select();
            }

        }

        /**
         * 根据传入的部门id,取得属于该部门的文章个数,用于分页(只用于小编和总编角色)
         * @param $dept
         * @return mixed
         */
        public function getAllDeptCount($dept){
            $join = "INNER JOIN ccm_m_user 
                        ON ccm_posts.post_author = ccm_m_user.id 
                        AND ccm_posts.post_status <> 'save' 
                        AND ccm_posts.post_dept LIKE '%$dept%'" ;
            return $this->object->join($join)->count();
        }


        /**
         * 取得保该爆料者发布的所有文章个数
         * @return mixed
         */
        public function getAllBaoliaozheCount(){
            $id = $_SESSION['uid'];
            $join = "INNER JOIN ccm_m_user 
                        ON ccm_posts.post_author = ccm_m_user.id 
                        AND ccm_posts.post_author = '$id'";
            return $this->object->join($join)->count();
        }


        /**
         * 根据传入的部门id 和 文章状态取得对应状态的文章个数(只用于小编和总编角色)
         * @param $dept
         * @return mixed
         */
        public function getDeptStatusCount($dept,$status){
            $join = "INNER JOIN ccm_m_user 
                        ON ccm_posts.post_author = ccm_m_user.id 
                        AND ccm_posts.post_status <> 'save' 
                        AND ccm_posts.post_dept LIKE '%$dept%' 
                        AND ccm_posts.post_status = '$status'";
            return $this->object->join($join)->count();
        }


        /**
         * 当前用户是爆料者,根据传入的文章状态取得对应状态的文章个数(只用于爆料者)
         * @return mixed
         */
        public function getBaoliaozheStatusCount($status){
            $id = $_SESSION['uid'];
            $join = "INNER JOIN ccm_m_user 
                        ON ccm_posts.post_author = ccm_m_user.id 
                        AND ccm_posts.post_author = '$id' 
                        AND ccm_posts.post_status = '$status'" ;
            return $this->object->join($join)->count();
        }


        /**
         * 根据传入的状态取得符合该状态的文章个数
         * @return mixed
         */
        public function getStatusCount($status){
            $join = "INNER JOIN ccm_m_user 
                        ON ccm_posts.post_author = ccm_m_user.id 
                        AND ccm_posts.post_status = '$status'" ;
            return $this->object->join($join)->count();
        }


        /**
         * 取得所有文章个数
         * @return mixed
         */
        public function getAllStatusCount(){
            $join = "INNER JOIN ccm_m_user 
                        ON ccm_posts.post_author = ccm_m_user.id" ;

            return $this->object->join($join)->count();
        }


        /**
         * 当前用户是爆料者,则取得所有该爆料者提交的文章个数(只用于爆料者)
         * @return mixed
         */
        public function getBaoliaozheCount(){

            $id = $_SESSION['uid'];
            $join = "INNER JOIN ccm_m_user 
                        ON ccm_posts.post_author = ccm_m_user.id 
                        AND ccm_posts.post_author = '$id'" ;

            if( ( isset($_GET['status'] ) ) && ( '' != I('get.status')) ){
                $where['post_status'] = I('get.status');
                return $this->object->join($join)->where($where)->count();
            }
            return $this->object->join($join)->count();
        }

        /**
         * 当前用户是爆料者,则取得所有该爆料者提交的文章(只用于爆料者)
         * @param $limit
         * @return mixed
         */
        public function showBaoliaozhePostList($limit){

            $obj = $this->allBaoliaozhePost($limit);
            if($obj) {
                //是二维数组则进行数据格式修正并返回
                if(ToolModel::isTwoArray($obj)){
                    return $this->dataFormart($obj);
                }
            }

        }

        /**
         * 当前用户是爆料者,则取得所有该爆料者提交的文章列表(只用于爆料者)
         * @param $limit
         * @return mixed
         */
        private function allBaoliaozhePost($limit){
            //多表联合查询
            $id = $_SESSION['uid'];
            $field = 'ccm_posts.*,ccm_m_user.id as uid,ccm_m_user.username';
            $join = "INNER JOIN ccm_m_user 
                        ON ccm_m_user.id = ccm_posts.post_author 
                        AND ccm_posts.post_author = '$id'";
            if('' == $limit){

                if( ( isset($_GET['status'] ) ) && ( '' != I('get.status')) ){
                    $where['post_status'] = I('get.status');
                    return $this->object->field($field)->join($join)->order($this->order)->where($where)->select();
                }

                return $this->object->field($field)->join($join)->order($this->order)->select();
            }else{

                if( ( isset($_GET['status'] ) ) && ( '' != I('get.status')) ){
                    $where['post_status'] = I('get.status');
                    return $this->object->field($field)->join($join)->where($where)->order($this->order)->limit($limit)->select();
                }

                return $this->object->field($field)->join($join)->order($this->order)->limit($limit)->select();
            }

        }


        /**
         * 也显示所有文章一览表使用后
         * 取得关联表的用户数据，并通过转化生出页面可显示的数据

         * @return mixed
         */
        public function showDeptPostList($dept,$limit){

            //取得用户信息
            $obj = $this->allDeptPost($dept,$limit);

            if($obj) {
                //是二维数组则进行数据格式修正并返回
                if(ToolModel::isTwoArray($obj)){
                    return $this->dataFormart($obj);
                }
            }

        }

        /**
         * 根据传入的部门id,取得属于该部门的文章(用于小编和总编角色,默认只能属于一个部门)
         * @param $dept
         * @param $limit
         * @return mixed
         */
        private function allDeptPost($dept,$limit){

            $field = 'ccm_posts.*,ccm_m_user.id as uid,ccm_m_user.username';
            $join = "INNER JOIN ccm_m_user 
                        ON ccm_m_user.id = ccm_posts.post_author 
                        AND ccm_posts.post_status <> 'save' 
                        AND ccm_posts.post_dept LIKE '%$dept%'";
            
            //多表联合查询
            if('' == $limit){

                if( ( isset($_GET['status'] ) ) && ( '' != I('get.status')) ){
                    $where['post_status'] = I('get.status');
                    return $this->object->field($field)->join($join)->order($this->order)->where($where)->select();
                }

                return $this->object->field($field)->join($join)->order($this->order)->select();
            }else{
                if( ( isset($_GET['status'] ) ) && ( '' != I('get.status')) ){
                    $where['post_status'] = I('get.status');
                    return $this->object->field($field)->join($join)->where($where)->order($this->order)->limit($limit)->select();
                }
                return $this->object->field($field)->join($join)->limit($limit)->order($this->order)->select();
            }

        }

        /**
         * 根据传入的部门id,取得属于该部门的文章个数,用于分页(只用于小编和总编角色)
         * @param $dept
         * @return mixed
         */
        public function getDeptCount($dept){
            $join = "INNER JOIN ccm_m_user 
                        ON ccm_posts.post_author = ccm_m_user.id 
                        AND ccm_posts.post_status <> 'save' 
                        AND ccm_posts.post_dept LIKE '%$dept%'";

            if( ( isset($_GET['status'] ) ) && ( '' != I('get.status')) ){
                $where['post_status'] = I('get.status');
                return $this->object->join($join)->where($where)->count();
            }

            return $this->object->join($join)->count();
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
         * 也显示所有文章一览表使用后
         * 取得关联表的用户数据，并通过转化生出页面可显示的数据

         * @return mixed
         */
        public function showPostList($limit){

            //取得用户信息
            $obj = $this->allPost($limit);

            if($obj){
                //是二维数组则进行数据格式修正并返回
                if(ToolModel::isTwoArray($obj)){
                    return $this->dataFormart($obj);
                }
            }

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
         * 取得所有用户信息(多表查询)
         * 私有方法
         * @return mixed
         */
        private function allPost($limit){

            $field = 'ccm_posts.*,ccm_m_user.id as uid,ccm_m_user.username';
            $join = 'INNER JOIN ccm_m_user 
                        ON ccm_m_user.id = ccm_posts.post_author';
            
            //多表联合查询
            if('' == $limit){

                if( ( isset($_GET['status'] ) ) && ( '' != I('get.status')) ){
                    $where['post_status'] = I('get.status');
                    return $this->object->field($field)->join($join)->order($this->order)->where($where)->select();
                }
                return $this->object->field($field)->join($join)->order($this->order)->select();

            }else{
                if( ( isset($_GET['status'] ) ) && ( '' != I('get.status')) ){
                    $where['post_status'] = I('get.status');
                    return $this->object->field($field)->join($join)->limit($limit)->order($this->order)->where($where)->select();
                }
                return $this->object->field($field)->join($join)->order($this->order)->limit($limit)->select();
            }

        }

        public function getCount(){

            $join = 'INNER JOIN ccm_m_user 
                        ON ccm_posts.post_author = ccm_m_user.id';

            if( ( isset($_GET['status'] ) ) && ( '' != I('get.status')) ){
                $where['post_status'] = I('get.status');
                return $this->object->join($join)->where($where)->count();
            }

            return $this->object->join($join)->count();
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