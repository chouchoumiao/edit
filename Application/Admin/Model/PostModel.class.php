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


        /**
         * 当前用户是爆料者,则取得所有该爆料者提交的文章(只用于爆料者)
         * @return mixed
         */
        public function getBaoliaozheCount(){
            $id = $_SESSION['uid'];
            return M('posts')->join("INNER JOIN ccm_m_user ON ccm_posts.post_author = ccm_m_user.id AND ccm_posts.post_author = '$id'" )->count();
        }

        public function showBaoliaozhePostList($limit){

            //取得用户信息
            $obj = $this->allBaoliaozhePost($limit);
            if(!$obj) ToolModel::goBack('未能取到数据');
            //返回格式化好的数据，用于显示

            //是二维数组则进行数据格式修正并返回
            if(ToolModel::isTwoArray($obj)){
                return $this->dataFormart($obj);
            }
        }

        private function allBaoliaozhePost($limit){
            //多表联合查询
            $id = $_SESSION['uid'];
            if('' == $limit){
                return M('posts')->field('ccm_posts.*,ccm_m_user.id as uid,ccm_m_user.username')->join("INNER JOIN ccm_m_user ON ccm_m_user.id = ccm_posts.post_author AND ccm_posts.post_author = '$id'")->select();
            }else{
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

            if(!$obj) ToolModel::goBack('未能取到数据');
            //返回格式化好的数据，用于显示

            //是二维数组则进行数据格式修正并返回
            if(ToolModel::isTwoArray($obj)){
                return $this->dataFormart($obj);
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
                return M('posts')->field('ccm_posts.*,ccm_m_user.id as uid,ccm_m_user.username')->join("INNER JOIN ccm_m_user ON ccm_m_user.id = ccm_posts.post_author AND ccm_posts.post_dept LIKE '%$dept%'")->select();
            }else{
                return M('posts')->field('ccm_posts.*,ccm_m_user.id as uid,ccm_m_user.username')->join("INNER JOIN ccm_m_user ON ccm_m_user.id = ccm_posts.post_author AND ccm_posts.post_dept LIKE '%$dept%'")->limit($limit)->select();
            }

        }

        /**
         * 根据传入的部门id,取得属于该部门的文章个数,用于分页(只用于小编和总编角色)
         * @param $dept
         * @return mixed
         */
        public function getDeptCount($dept){
            return M('posts')->join("INNER JOIN ccm_m_user ON ccm_posts.post_author = ccm_m_user.id AND ccm_posts.post_dept LIKE '%$dept%'" )->count();
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
        public function getThePost(){
            $where['id'] = I('get.id');
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

            if(!$obj) ToolModel::goBack('未能取到数据');
            //返回格式化好的数据，用于显示

            //是二维数组则进行数据格式修正并返回
            if(ToolModel::isTwoArray($obj)){
                return $this->dataFormart($obj);
            }
        }


        private function dataFormart($obj)
        {
            $deptArr = C('DEPT_ARRAY');   //取得自定义常量部门数组

            for ($i = 0; $i < count($obj); $i++) {

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
                return M('posts')->field('ccm_posts.*,ccm_m_user.id as uid,ccm_m_user.username')->join('INNER JOIN ccm_m_user ON ccm_m_user.id = ccm_posts.post_author')->select();
            }else{
                return M('posts')->field('ccm_posts.*,ccm_m_user.id as uid,ccm_m_user.username')->join('INNER JOIN ccm_m_user ON ccm_m_user.id = ccm_posts.post_author')->limit($limit)->select();
            }

        }

        public function getCount(){
            return M('posts')->join('INNER JOIN ccm_m_user ON ccm_posts.post_author = ccm_m_user.id')->count();
        }


        /**
         * ajax上传的数据进行检查并赋值
         */
        public function checkAndSetNewData(){

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


        }


        public function addNewPost(){
            $now = date('Y/m/d H:i:s',time());

            $dataArr = array(
                'post_author'  => $this->post_author,
                'post_date'    => $now,
                'post_content' => $this->post_content,
                'post_title'   => $this->post_title,
                'post_dept'    => $this->post_dept,
                'post_status'  => 'publish',
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