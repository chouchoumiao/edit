$(function(){

    //点击评分时候获得评分的个数
    $(".scoreBtn").on('click',function (e) {

        //点击时候如果存在active和lastActive先清空原有的class
        $('.scoreBtn').each(function () {
            if($(this).hasClass('active')){
                $(this).removeClass('active');
            }
            if($(this).hasClass('lastActive')){
                $(this).removeClass('lastActive');
            }
        });

        //取得当前的数值,默认就是个数
        var val = $(this).attr("value");

        //点击的前几个都给附上class
        for(var i= 1;i<=val;i++){
            $('#scoreBtn'+i).addClass('active');
        }
        //最后一个追加一个特有class,用于提交时判断
        $('#scoreBtn'+val).addClass('lastActive');

    }),

    /**
     * 富文本编辑器初始化
     * 最小长度400
     * 不聚焦
     * 设置为中文提示
     * 如果是显示特定文章的情况，则初始化显示code内容
     * 图片上传至服务器 路径：upload/post/日期/
     */
    $( '#summernote' ).summernote({
        minHeight: 400,
        focus:false,
        lang:'zh-CN',

        //回调函数
        callbacks : {
            //初始化
            onInit: function() {
                var content = $('#content').val();
                if(content != ''){
                    $('#summernote').summernote('code', content);
                }else {
                    $('#summernote').summernote('code', '');
                }
            },

            //删除图片时候同时删除已经上传的图片
            onMediaDelete : function($target, editor, $editable) {

                var imgPath = $target[0].src;
                $.ajax({
                    url:ROOT+"/Admin/Post/doAction/action/deleteImg"//改为你的动态页
                    ,type:"POST"
                    ,data:{
                        'imgPath':imgPath
                    }
                    ,dataType: "json"
                    ,success:function(json){
                        $target.remove();
                    }
                    ,error:function(xhr){alert('PHP页面有错误！'+xhr.responseText);}
                });

            },

            // onImageUpload的参数为files，summernote支持选择多张图片
            onImageUpload : function(files) {

                var $files = $(files);

                // 通过each方法遍历每一个file
                $files.each(function() {
                    var file = this;

                    var data = new FormData();

                    // 将文件加入到file中，后端可获得到参数名为“file”
                    data.append("file", file);

                    // ajax上传
                    $.ajax({
                        data : data,
                        type : "POST",
                        url : ROOT+"/Admin/Post/doAction/action/upload",
                        cache : false,
                        contentType : false,
                        processData : false,
                        dataType : "json",

                        // 成功时调用方法，后端返回json数据
                        success : function(json) {
                            if(json.success){
                                var imgNode = $('<img>').attr('src',PUBLIC+json.msg);
                                $( '#summernote' ).summernote('insertNode', imgNode[0]);
                            }else{
                                alert(json.msg);
                                return false;
                            }
                        }
                    });
                });
            }
        }

    });

});

/**
 * 提交新增画面信息
 * ajax
 */
function addFormSubmit(flag) {

    //前端验证
    var title = $("#newTitle").val();

    if(title == ''){
        alert('文章标题不能空');
        return;
    }

    //判断文章内容是否为空
    if($("#summernote").summernote('isEmpty')){
        alert('文章内容不能空');
        return;
    }

    var data = $("#summernote").summernote('code');

    //判断部门复选框是否都没有选中
    if( 0 == ($("input[class='checkbox-purple']:checked").length)){
        alert('至少选中一个部门');
        return;
    }

    var deptArr = [];
    $("#dept [type=checkbox]:checked").each(function(i){
        deptArr.push($(this).val());
    });


    //将数组转化为json格式
    var deptJson = JSON.stringify(deptArr);

    $.ajax({
        url:ROOT+"/Admin/Post/doAction/action/addNew"//改为你的动态页
        ,type:"POST"
        ,data:{
            'dept':deptJson,
            'title':title,
            'data':data,
            'flag':flag
        }
        ,dataType: "json"
        ,success:function(json){
           if(json.success){
               alert(json.msg);
               location = ROOT+"/Admin/Post/doAction/action/all";
           }else{
               alert(json.msg);
               return false;
           }
        }
        ,error:function(xhr){alert('PHP页面有错误！'+xhr.responseText);}
    });
}

/**
 * 重置内容
 */
function resetAddForm () {
    $('#title').val('');
    $('#summernote').summernote('code', '');

}

/**
 * 根据传入的id删除文章
 * @param id
 */
function delPost(id) {
    if (!confirm('确定要删除吗？')){
        return;
    }

    $.ajax({
        url:ROOT+"/Admin/Post/doAction/action/del"//改为你的动态页
        ,type:"POST"
        ,data:{
            'id':id
        }
        ,dataType: "json"
        ,success:function(json){
            if(json.success == "OK"){
                alert('删除成功');
                location = ROOT+"/Admin/Post/doAction/action/all";

            }else{
                alert('删除失败');
                return false;
            }
        }
        ,error:function(xhr){alert('PHP页面有错误！'+xhr.responseText);}
    });

}

/**
 * 修改文章
 * @param flag
 * @constructor
 */
function UpdateFormSubmit(flag) {

    //前端验证
    var title = $("#theTitle").val();

    if(title == ''){
        alert('文章标题不能空');
        return;
    }

    //判断文章内容是否为空
    if($("#summernote").summernote('isEmpty')){
        alert('文章内容不能空');
        return;
    }

    var data = $("#summernote").summernote('code');

    //判断部门复选框是否都没有选中
    if( 0 == ($("input[class='checkbox-purple']:checked").length)){
        alert('至少选中一个部门');
        return;
    }

    var deptArr = [];
    $("#dept [type=checkbox]:checked").each(function(i){
        deptArr.push($(this).val());
    });


    //将数组转化为json格式
    var deptJson = JSON.stringify(deptArr);
    if((flag == 4) && ($("#dismiss_msg").is(":hidden"))){
        if(!$("#scoreBtn").is(":hidden")){
            $('#scoreBtn').hide();
        }
        $('#dismiss_msg').fadeIn();
        return false;
    }

    var dismissMsg = $('#dismiss_msg').val();

    if(flag == 4 && (dismissMsg == '')){
        alert('必须填写不通过审核的原因');
        return false;
    }


    if((flag == 5) && ($("#scoreBtn").is(":hidden"))){
        if(!$("#dismiss_msg").is(":hidden")){
            $('#dismiss_msg').hide();
        }
        $('#scoreBtn').fadeIn();
        return false;
    }

    //初始化评分
    var score = 0;

    //只有在总编最终审核文章的情况下才进行获取数据
    if(flag == 5){

        //获取对应class的数值
        score = $('.lastActive').val();

        //没有没有点击的情况下,不能获取该class,则默认将评分设为1
        if (typeof(score) == "undefined"){
            score = 1;

        }
    }


    if(flag == 4 && (dismissMsg == '')){
        alert('必须填写不通过审核的原因');
        return false;
    }



    $.ajax({
        url:ROOT+"/Admin/Post/doAction/action/update"//改为你的动态页
        ,type:"POST"
        ,data:{
            'postid':$("#postid").val(),
            'dept':deptJson,
            'title':title,
            'data':data,
            'flag':flag,
            'dismissMsg':dismissMsg,
            'score':score
        }
        ,dataType: "json"
        ,success:function(json){
            if(json.success){
                alert(json.msg);
                location = ROOT+"/Admin/Post/doAction/action/all";
            }else{
                alert(json.msg);
                return false;
            }
        }
        ,error:function(xhr){alert('PHP页面有错误！'+xhr.responseText);}
    });
}

window.addEventListener("beforeunload", function(event) {

    if( (typeof ($("#postid").val()) != 'undefined') && ($("#postid").val() != '') ){

        $.ajax({
            url:ROOT+"/Admin/Post/doAction/action/unlockPost"//改为你的动态页
            ,type:"POST"
            ,data:{
                'postid':$("#postid").val()
            }
            ,dataType: "json"
            ,success:function(json){
                if(json.success == "OK"){
                    alert('删除成功');
                    location = ROOT+"/Admin/Post/doAction/action/all";

                }else{
                    alert('删除失败');
                    return false;
                }
            }
            ,error:function(xhr){alert('PHP页面有错误！'+xhr.responseText);}
        });
    }

});