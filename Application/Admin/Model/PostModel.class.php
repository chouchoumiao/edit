<?php

/**
 * 登录Model
 */
namespace Admin\Model;

	class PostModel {

        private $post_author;
        private $post_content;
        private $post_title ;
        private $post_dept ;
        private $post_status;
        private $post_modified;
        private $post_parent;


        //文章一览众当原则部门后点击查询,显示符合部门的所有文章个数
        public function getPostByDeptCount($dept){
            return M('posts')->join("INNER JOIN ccm_m_user ON ccm_posts.post_author = ccm_m_user.id AND ccm_posts.post_dept LIKE '%$dept%'" )->count();
        }


        /**
         * 根据传入的部门id,取得属于该部门的文章个数,用于分页(只用于小编和总编角色)
         * @param $dept
         * @return mixed
         */
        public function getAllDeptCount($dept){
            return M('posts')->join("INNER JOIN ccm_m_user ON ccm_posts.post_author = ccm_m_user.id AND ccm_posts.post_status <> 'save' AND ccm_posts.post_dept LIKE '%$dept%'" )->count();
        }


        public function getAllBaoliaozheCount(){


            $id = $_SESSION['uid'];
            return M('posts')->join("INNER JOIN ccm_m_user ON ccm_posts.post_author = ccm_m_user.id AND ccm_posts.post_author = '$id'" )->count();
        }


        /**
         * 根据传入的部门id 和 文章状态取得对应状态的文章个数(只用于小编和总编角色)
         * @param $dept
         * @return mixed
         */
        public function getDeptStatusCount($dept,$status){
            return M('posts')->join("INNER JOIN ccm_m_user ON ccm_posts.post_author = ccm_m_user.id AND ccm_posts.post_status <> 'save' AND ccm_posts.post_dept LIKE '%$dept%' AND ccm_posts.post_status = '$status'" )->count();
        }



        /**
         * 当前用户是爆料者,根据传入的文章状态取得对应状态的文章个数(只用于爆料者)
         * @return mixed
         */
        public function getBaoliaozheStatusCount($status){
            $id = $_SESSION['uid'];
            return M('posts')->join("INNER JOIN ccm_m_user ON ccm_posts.post_author = ccm_m_user.id AND ccm_posts.post_author = '$id' AND ccm_posts.post_status = '$status'" )->count();
        }


        /**
         * 根据传入的状态取得符合该状态的文章个数
         * @return mixed
         */
        public function getStatusCount($status){
            return M('posts')->join("INNER JOIN ccm_m_user ON ccm_posts.post_author = ccm_m_user.id AND ccm_posts.post_status = '$status'" )->count();
        }


        /**
         * 取得所有文章个数
         * @return mixed
         */
        public function getAllStatusCount(){
            return M('posts')->join("INNER JOIN ccm_m_user ON ccm_posts.post_author = ccm_m_user.id" )->count();
        }


        /**
         * 当前用户是爆料者,则取得所有该爆料者提交的文章(只用于爆料者)
         * @return mixed
         */
        public function getBaoliaozheCount(){


            $id = $_SESSION['uid'];
            if( ( isset($_GET['status'] ) ) && ( '' != I('get.status')) ){
                $where['post_status'] = I('get.status');
                return M('posts')->join("INNER JOIN ccm_m_user ON ccm_posts.post_author = ccm_m_user.id AND ccm_posts.post_author = '$id'" )->where($where)->count();
            }
            return M('posts')->join("INNER JOIN ccm_m_user ON ccm_posts.post_author = ccm_m_user.id AND ccm_posts.post_author = '$id'" )->count();
        }

        public function showBaoliaozhePostList($limit){

            //取得用户信息
            $obj = $this->allBaoliaozhePost($limit);
            if($obj) {
                //是二维数组则进行数据格式修正并返回
                if(ToolModel::isTwoArray($obj)){
                    return $this->dataFormart($obj);
                }
            }

        }

        private function allBaoliaozhePost($limit){
            //多表联合查询
            $id = $_SESSION['uid'];
            if('' == $limit){

                if( ( isset($_GET['status'] ) ) && ( '' != I('get.status')) ){
                    $where['post_status'] = I('get.status');
                    return M('posts')->field('ccm_posts.*,ccm_m_user.id as uid,ccm_m_user.username')->join("INNER JOIN ccm_m_user ON ccm_m_user.id = ccm_posts.post_author AND ccm_posts.post_author = '$id'")->where($where)->select();
                }

                return M('posts')->field('ccm_posts.*,ccm_m_user.id as uid,ccm_m_user.username')->join("INNER JOIN ccm_m_user ON ccm_m_user.id = ccm_posts.post_author AND ccm_posts.post_author = '$id'")->select();
            }else{

                if( ( isset($_GET['status'] ) ) && ( '' != I('get.status')) ){
                    $where['post_status'] = I('get.status');
                    return M('posts')->field('ccm_posts.*,ccm_m_user.id as uid,ccm_m_user.username')->join("INNER JOIN ccm_m_user ON ccm_m_user.id = ccm_posts.post_author AND ccm_posts.post_author = '$id'")->where($where)->limit($limit)->select();
                }

                return M('posts')->field('ccm_posts.*,ccm_m_user.id as uid,ccm_m_user.username')->join("INNER JOIN ccm_m_user ON ccm_m_user.id = ccm_posts.post_author AND ccm_posts.post_author = '$id'")->limit($limit)->select();
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
            //多表联合查询
            if('' == $limit){

                if( ( isset($_GET['status'] ) ) && ( '' != I('get.status')) ){
                    $where['post_status'] = I('get.status');
                    return M('posts')->field('ccm_posts.*,ccm_m_user.id as uid,ccm_m_user.username')->join("INNER JOIN ccm_m_user ON ccm_m_user.id = ccm_posts.post_author AND ccm_posts.post_status <> 'save' AND ccm_posts.post_dept LIKE '%$dept%'")->where($where)->select();
                }

                return M('posts')->field('ccm_posts.*,ccm_m_user.id as uid,ccm_m_user.username')->join("INNER JOIN ccm_m_user ON ccm_m_user.id = ccm_posts.post_author AND ccm_posts.post_status <> 'save' AND ccm_posts.post_dept LIKE '%$dept%'")->select();
            }else{
                if( ( isset($_GET['status'] ) ) && ( '' != I('get.status')) ){
                    $where['post_status'] = I('get.status');
                    return M('posts')->field('ccm_posts.*,ccm_m_user.id as uid,ccm_m_user.username')->join("INNER JOIN ccm_m_user ON ccm_m_user.id = ccm_posts.post_author AND ccm_posts.post_status <> 'save' AND ccm_posts.post_dept LIKE '%$dept%'")->where($where)->limit($limit)->select();
                }
                return M('posts')->field('ccm_posts.*,ccm_m_user.id as uid,ccm_m_user.username')->join("INNER JOIN ccm_m_user ON ccm_m_user.id = ccm_posts.post_author AND ccm_posts.post_status <> 'save' AND ccm_posts.post_dept LIKE '%$dept%'")->limit($limit)->select();
            }

        }

        /**
         * 根据传入的部门id,取得属于该部门的文章个数,用于分页(只用于小编和总编角色)
         * @param $dept
         * @return mixed
         */
        public function getDeptCount($dept){
            if( ( isset($_GET['status'] ) ) && ( '' != I('get.status')) ){
                $where['post_status'] = I('get.status');
                return M('posts')->join("INNER JOIN ccm_m_user ON ccm_posts.post_author = ccm_m_user.id AND ccm_posts.post_status <> 'save' AND ccm_posts.post_dept LIKE '%$dept%'" )->where($where)->count();
            }

            return M('posts')->join("INNER JOIN ccm_m_user ON ccm_posts.post_author = ccm_m_user.id AND ccm_posts.post_status <> 'save' AND ccm_posts.post_dept LIKE '%$dept%'" )->count();
        }




        /**
         * 根据传入的id删除文章
         * @param $id
         * @return bool
         */
        public function delThePost($id){
            //删除主表，错误的情况下返回
            if( false === M('posts')->where("id=$id")->delete()){
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

            return M('posts')->field('post_content')->where($where)->find();
        }

        /**
         * 根据传入的id查找是否存在记录
         * @param $id
         * @return bool
         */
        public function idIsExist($id){
            $where['id'] = $id;

            if(M('posts')->where($where)->count() > 0){
                return true;
            }
            return false;

        }


        /**
         * 根据传入的文章ID取得文章
         * @return mixed
         */
        public function getThePost($id){
            $where['id'] = intval($id);
            return M('posts')->where($where)->find();

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


        private function dataFormart($obj)
        {
            $deptArr = C('DEPT_ARRAY');   //取得自定义常量部门数组
            $statusArr = C('POST_STATUS');   //取得自定义常量文章状态数组

            for ($i = 0; $i < count($obj); $i++) {

                //如果传入的数据库取得结果集中含有部门信息,则转化数据
                if($obj[$i]['post_dept']){
                    //处理部门数字转化为文字 start
                    $dept = json_decode($obj[$i]['post_dept']);            //json转化为数字

                    $obj[$i]['post_dept'] = '';                            //先清空原来的数组

                    //将json转化的数组循环判断并显示名称
                    for ($j = 0; $j < count($dept); $j++) {

                        //为空则不输出
                        if ('' != $dept[$j]) {
                            //最后一个不需要输出间隔符
                            if ((count($dept) - 1) == $j) {
                                $obj[$i]['post_dept'] .= $deptArr[$dept[$j]];
                            } else {
                                $obj[$i]['post_dept'] .= $deptArr[$dept[$j]] . '，';
                            }
                        }
                    }
                }
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
            //多表联合查询
            if('' == $limit){

                if( ( isset($_GET['status'] ) ) && ( '' != I('get.status')) ){
                    $where['post_status'] = I('get.status');
                    return M('posts')->field('ccm_posts.*,ccm_m_user.id as uid,ccm_m_user.username')->join('INNER JOIN ccm_m_user ON ccm_m_user.id = ccm_posts.post_author')->where($where)->select();
                }
                return M('posts')->field('ccm_posts.*,ccm_m_user.id as uid,ccm_m_user.username')->join('INNER JOIN ccm_m_user ON ccm_m_user.id = ccm_posts.post_author')->select();

            }else{
                if( ( isset($_GET['status'] ) ) && ( '' != I('get.status')) ){
                    $where['post_status'] = I('get.status');
                    return M('posts')->field('ccm_posts.*,ccm_m_user.id as uid,ccm_m_user.username')->join('INNER JOIN ccm_m_user ON ccm_m_user.id = ccm_posts.post_author')->limit($limit)->where($where)->select();
                }
                return M('posts')->field('ccm_posts.*,ccm_m_user.id as uid,ccm_m_user.username')->join('INNER JOIN ccm_m_user ON ccm_m_user.id = ccm_posts.post_author')->limit($limit)->select();
            }

        }

        public function getCount(){

            if( ( isset($_GET['status'] ) ) && ( '' != I('get.status')) ){
                $where['post_status'] = I('get.status');
                return M('posts')->join('INNER JOIN ccm_m_user ON ccm_posts.post_author = ccm_m_user.id')->where($where)->count();
            }

            return M('posts')->join('INNER JOIN ccm_m_user ON ccm_posts.post_author = ccm_m_user.id')->count();
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

            if( false === M('posts')->add($dataArr)){
                return false;
            }else{
                return true;
            }
        }
        
    }