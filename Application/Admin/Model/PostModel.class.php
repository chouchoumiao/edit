<?php

/**
 * 登录Model
 */
namespace Admin\Model;

    class PostModel {

        private $post_id;
        private $post_author;
        private $post_judge;        //审批者 新加

        private $post_content;
        private $post_title ;
        private $post_dept ;
        private $post_status;
        private $dismissMsg = '';
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


            $join = $this->getPostWithAuto($flag,$auto,$dept);


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

            $join = $this->getPostWithAuto($flag,$auto,$dept);

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

            $join = $this->getPostNosearchWithAuto($auto,$status,$dept);

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

            $this->post_judge = 0;  //默认为0（因为是int类型）


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
            //            6 : 打回给编辑flag

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
         * 编辑或者总编都可以进行打分,将评分记录计入评分表
         * @param $auto 权限 是编辑还是总编
         * @param $dept 部门
         * @return mixed
         */
        public function insertScore($auto,$dept){
            $now = date('Y/m/d H:i:s',time());

            $parentid = $this->getParentPostid($this->post_id);

            $scoreData['postid'] = $parentid;

            $author = $this->getAuthorByPostID($parentid);

            $scoreData['author'] = $author;

            //因为编辑也需要打分所以新增两个字段
            $scoreData['score_post_id'] = $this->post_id;

            //编辑的flag=0，总编=1
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
         * 判断是否编辑已经点击过改文章生成备份文件了
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
         * 根据文章的ID和部门判断是否已经被本部门其他编辑认领了文章
         * @param $id
         * @param $dept
         * @return mixed
         */
        public function isCopiedBySameDeptOtheXIAOBIAN($id,$dept){
            $where['post_parent'] = $id;
            $where['post_dept'] = $dept;
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
         * 取得原文章的未被编辑编辑的(未被继承的)部门数组
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
         * 编辑点击审核后默认生成一份备份文件
         * @param $data
         * @param $dept
         * @return mixed
         */
        public function copyPostByXIAOBIAN($data,$dept){
            $now = date('Y/m/d H:i:s',time());
            //$id = intval(I('get.id'));
            $dataArr = array(
                'post_author'  => $_SESSION['uid'],
                'post_judge'  => 0,     //追加审核者，默认为0
                'post_date'    => $now,
                'post_content' => $data['post_content'],
                'post_title'   => $data['post_title'],
                'post_dept'    => $dept,
                'post_name'    => $dept,        //post_name也需要追加 20161025
                'post_status'  => POST_SAVE,    //待审核 改为 保存 20161025
                'post_dismiss_msg'  => '',    //待审核
                'post_modified'=> $now,
                'post_parent'  => $data['id'],   //父节点是提交过来的文章ID
                'post_parent_author'  => $data['post_author']   //父节点是提交过来的文章作者
            );

            //新增编辑拷贝文件
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

                //取得审批者信息，没有则显示'无'
                if( intval($obj[$i]['post_judge']) != 0){
                    $judgeInfo = D('User')->getTheUserInfo(intval($obj[$i]['post_judge']));
                    $obj[$i]['post_judge'] = $judgeInfo['username'];

                }else{
                    $obj[$i]['post_judge'] = '无';
                }


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
                            $spanColor = '<span style="color: #7F8C8D">';
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

                    //编辑的情况下，取得的文章，如果不是编辑本人是作者的话默认只显示预览按钮 20161025
                    if($nowUserInfo['udi_auto_id'] == XIAOBIAN){
                        if( ($obj[$i]['post_status'] != 'pending') && $obj[$i]['post_author'] != $nowUserInfo['uid'] ){
                            $obj[$i]['post_canEdit'] = 2;
                        }
                    }

                    $temp = '';
                    if( ($nowUserInfo['udi_auto_id'] == BAOLIAOZHE) && ($obj[$i]['post_status'] == 'pending') ){
                        $obj[$i]['post_canEdit'] = 2;
                        $temp = '[不能修改]';
                    }

                    $obj[$i]['post_status'] = $spanColor.$statusArr[$obj[$i]['post_status']].$temp.'</span>';
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
                'post_judge'   => 0,     //追加审核者，默认为0
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


        /**
         * 根据传入的参数判断 并拼接出相应的SQl语句并返回
         * @param $flag     获取 noSearch userSearch deptSearch
         * @param $auto     角色 ADMIN SUPPER_ADMIN XIAOBIAN ZONGBIAN DEPT_ADMIN BAOLIAOZHE
         * @param $dept     部门
         * @return string   拼接后的SQL
         */
        private function getPostWithAuto($flag,$auto,$dept){

            $join = 'INNER JOIN ccm_m_user ON ccm_m_user.id = ccm_posts.post_author ';

            switch ($flag){
                case 'userSearch':
                    $userid = I('get.userSearch');
                    switch ($auto){
                        case ADMIN:
                        case SUPPER_ADMIN:
                            $join .= "AND ccm_posts.post_author = $userid";
                            break;
                        case XIAOBIAN:
                        case DEPT_ADMIN:
                            $join .= "AND ccm_posts.post_author = $userid 
                                        AND ccm_posts.post_dept LIKE '%$dept%'
                                        AND ccm_posts.post_status <> 'save'";
                            break;
                        case ZONGBIAN:

                            $join .= "AND ccm_posts.post_author = $userid 
                                        AND ccm_posts.post_status in ('pending2','dismiss','pended','return') 
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
                            $join .= "AND ccm_posts.post_dept LIKE '%$deptID%'";
                            break;
                        case BAOLIAOZHE:
                            $id = $_SESSION['uid'];
                            $join .= "AND ccm_posts.post_author = '$id' 
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

                    $join = $this->getPostNosearchWithAuto($auto,$status,$dept);

                    break;
            }

            return $join;

        }

        /**
         * 根据传入的角色来拼接对应的SQL语句
         * @param $auto     角色  ADMIN SUPPER_ADMIN XIAOBIAN ZONGBIAN DEPT_ADMIN BAOLIAOZHE
         * @param $status   文章状态
         * @param $dept     部门
         * @return string   拼接后的SQL
         */
        private function getPostNosearchWithAuto($auto,$status,$dept){
            $join = 'INNER JOIN ccm_m_user ON ccm_m_user.id = ccm_posts.post_author ';
            $theID = $_SESSION['uid'];
            switch ($auto){
                case ADMIN:
                case SUPPER_ADMIN:
                    if($status == 'all'){
                        $join = 'INNER JOIN ccm_m_user 
                                            ON ccm_m_user.id = ccm_posts.post_author';
                    }else{
                        $join .= "AND ccm_posts.post_status = '$status'";
                    }
                    break;

                case BAOLIAOZHE:

                    if($status == 'all'){
                        $join .= "AND ccm_posts.post_author = '$theID'";
                    }else{

                        if($status == 'dismiss' || $status == 'pended'){
                            $join .= "AND ccm_posts.post_parent_author = '$theID'
                                            AND ccm_posts.post_status = '$status'";
                        }else{
                            $join .= "AND ccm_posts.post_author = '$theID'
                                            AND ccm_posts.post_status = '$status'";
                        }

                    }
                    break;
                case XIAOBIAN:

                    //编辑的情形下，获取本部门的所有文章(已修正，编辑拷贝文章后默认改为保存),得到的文章再进行分类
                    //如果是当前编辑的则可以编辑删除，不然则只显示预览按钮(要求不同编辑可以查看自己部门的文章，不能编辑)
                    if($status == 'all'){
                        $join .= "AND (ccm_posts.post_name LIKE '%$dept%')" ;
                    }else{
                        //如果是待审核 有可能是爆料者的待审核 或者 是编辑拷贝后未做操作的待审核
                            $join .= "AND ccm_posts.post_name LIKE '%$dept%'
                                    AND ccm_posts.post_status = '$status'";
                    }
                    break;
                case ZONGBIAN:

                    //显示属于本部门的 待最终审核 审核不通过  审核通过 打回的文章
                    if($status == 'all'){
                        $join .= "AND ccm_posts.post_status in ('pending2','dismiss','pended','return') 
                                    AND ccm_posts.post_dept LIKE '%$dept%'";

                    //根据传入状态来显示
                    }else{
                        $join .= "AND ccm_posts.post_status in ('pending2','dismiss','pended','return') 
                                    AND ccm_posts.post_dept LIKE '%$dept%'
                                    AND ccm_posts.post_status = '$status'";
                    }

                    break;
                case DEPT_ADMIN:

                    if($status == 'all'){
                        $join .= "AND ccm_posts.post_dept LIKE '%$dept%'
                                    AND ccm_posts.post_status <> 'save'";       //追加不显示保存的文章
                    }else{
                        $join .= "AND ccm_posts.post_dept LIKE '%$dept%'
                                    AND ccm_posts.post_status = '$status'";
                    }
                    break;
            }

            return $join;
        }


        /**
         * 查询该编辑拷贝的文章的个数(未被审核的)
         *
         */
        public function getCopiedPostCount(){
            $theID = $_SESSION['uid'];
            $where = "post_author = '$theID' AND post_status NOT IN ('pended','dismiss')";
            return $this->object->where($where)->count();

        }
        
    }