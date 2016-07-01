$(function(){

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
                }
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
function addFormSubmit() {




    var deptArr = [];
    $("#dept [type=checkbox]:checked").each(function(i){


        deptArr.push($(this).val());

    });


    //将数组转化为json格式
    var deptJson = JSON.stringify(deptArr);

    var title = $("#title").val();
    var data = $("#summernote").summernote('code');

    $.ajax({
        url:ROOT+"/Admin/Post/doAction/action/addNew"//改为你的动态页
        ,type:"POST"
        ,data:{
            'dept':deptJson,
            'title':title,
            'data':data
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