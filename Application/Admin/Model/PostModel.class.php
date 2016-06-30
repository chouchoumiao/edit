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
                return M('posts')->join('INNER JOIN ccm_m_user ON ccm_m_user.id = ccm_posts.post_author')->select();
            }else{
                return M('posts')->join('INNER JOIN ccm_m_user ON ccm_m_user.id = ccm_posts.post_author')->limit($limit)->select();
            }

        }

        public function getCount(){
            return M('posts')->join('INNER JOIN ccm_m_user ON ccm_posts.post_author = ccm_m_user.id')->count();
        }

        public function setNewData(){
            $this->post_author = $_SESSION['uid'];
            $this->post_dept = $_POST['dept'];
            $this->post_title = $_POST['title'];
            $this->post_content = $_POST['data'];
        }

        public function addNewPost(){
            $now = date('Y/m/d H:i:s',time());

            $dataArr = array(
                'post_author' => $this->post_author,
                'post_date' => $now,
                'post_content'=>$this->post_content,
                'post_title'=>$this->post_title,
                'post_dept'=>$this->post_dept,
                'post_status'=>'publish',
                'post_name'=>'',
                'post_modified'=> $now,
                'post_parent'=>0
            );

            if( false === M('posts')->add($dataArr)){
                return false;
            }else{
                return true;
            }
        }
        
    }