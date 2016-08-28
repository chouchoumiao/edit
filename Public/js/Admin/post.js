$(function(){

    /**
     * 爆料者新增文章时候上传附件的时候进行以下方法
     */
    $('#file_upload').uploadifive({
        'multi' : false,
        'uploadScript' : './Admin/Post/doAction/action/uploadAttachment',   //上传的方法
        'buttonText' : '上传资源',
        'fileTypeExts'  : FILE_EXT,
        'fileSizeLimit' : FILE_SIZE * 4,   //附件默认20M
        'removeCompleted':true,
        'onUploadComplete' : function(file, data) {

            doUpload(data,0);

        },
        'onError': function(errorType) {
            alert('The error was: ' + errorType);
        }
    });

    /**
     * 爆料者编辑文字时候,如果原先有上传附件的情况下们可以删除后再上传,上传的时候进行以下方法
     */
    $('#the_file_upload').uploadifive({
        'multi' : false,
        'uploadScript' : './Admin/Post/doAction/action/uploadAttachment',   //上传的方法
        'buttonText' : '上传资源',
        'fileTypeExts'  : FILE_EXT,
        'fileSizeLimit' : FILE_SIZE * 4,   //附件默认20M
        'removeCompleted':true,
        'onUploadComplete' : function(file, data) {

            doUpload(data,1);

        },
        'onError': function(errorType) {
            alert('The error was: ' + errorType);
        }
    });

    //编辑文章的时候,如果有附件则显示附件
    if($('#postid').length > 0){
        if( ($('#theAuto').val() == BAOLIAOZHE) || ($('#theAuto').val() == XIAOBIAN) ) {
            doAttachment();
        }
    }

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
        focus:true,
        lang:'zh-CN',
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['fontname', ['fontname']],
            ['color', ['color']],
            ['fontsize', ['fontsize']],
            ['para', ['ul', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture']]
        ],

        //回调函数
        callbacks : {
            //初始化
            onInit: function() {

                var content = $('#content');

                if( (content.length > 0) && (content.val() != '') ){
                    $('#summernote').summernote('code', content.val()+' ');
                }else {
                    $('#summernote').summernote('code', '');
                }
            },

            //小编的情况，并且是编辑文章的时候，输入时默认是红色字显示，其他为黑色字
            onKeydown: function() {

                if($('#theAuto').length > 0 && $('#theAuto').val() == XIAOBIAN ){
                    $('#summernote').summernote('foreColor', 'red');
                }else {
                    $('#summernote').summernote('foreColor', 'black');
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

    //焦点定位到第一个input
    $('#theTitle').focus();

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
            'flag':flag,
            'attachment':$('#path').text(),     //单独附件路径
            'saveName':$('#saveName').text(),   //单独附件保存文件名
            'fileName':$('#fileName').text()    //单独附件文件名

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

    //三次弹出确认对话框
    if( !confirmThree() ){
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
    if((flag == 4 || flag == 6 ) && ($("#dismiss_msg").is(":hidden"))){
        if(!$("#scoreBtn").is(":hidden")){
            $('#scoreBtn').hide();
        }
        $('#dismiss_msg').fadeIn();
        return false;
    }

    var dismissMsg = $('#dismiss_msg').val();

    if((flag == 4 || flag == 6 ) && (dismissMsg == '')){
        alert('必须填写不通过审核的原因');
        return false;
    }


    if((flag == 3 || flag == 5 ) && ($("#scoreBtn").is(":hidden"))){
        if(!$("#dismiss_msg").is(":hidden")){
            $('#dismiss_msg').hide();
        }
        $('#scoreBtn').fadeIn();
        return false;
    }

    //初始化评分
    var score = 0;

    //只有在总编最终审核文章的情况下才进行获取数据
    if((flag == 3 || flag == 5 )){

        //获取对应class的数值
        score = $('.lastActive').val();

        //没有没有点击的情况下,不能获取该class,则默认将评分设为1
        if (typeof(score) == "undefined"){
            score = 1;

        }
    }

    //小编审核不通过或者总编打回给小编的情况下都需要写原因
    if( (flag == 4 || flag == 6 ) && (dismissMsg == '')){
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
            'score':score,
            'attachment':$('#path').text(),     //单独附件路径
            'saveName':$('#saveName').text(),   //单独附件保存文件名
            'fileName':$('#fileName').text()    //单独附件文件名
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
 * 编辑文章页面进行刷新，离开则会触发
 * 文章防止多人编辑，当刷新页面或者离开页面的时候触发，清除cache
 */
window.addEventListener("beforeunload", function(event) {


    //当前页面是编辑文章页面，不在编辑页面才会触发清空cache
    if($('#thePostForLock').length == 1){

        $.ajax({
            url:ROOT+"/Admin/Post/doAction/action/unlockPost"//改为你的动态页
            ,type:"POST"
            ,data:{
                'postid':$("#postid").val()
            }
            ,async: false
            // ,dataType: "json"
            ,success:function(json){
                if(json.success == 1){
                    console.log(json.msg);
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log('失败');
            },
        });
    }
});

/**
 *
 * @param flag  0:表示新增文章时上传附件,1:编辑文章时候修改附件
 * @returns {boolean}
 */
function doUpload(data,flag) {

    var json = jQuery.parseJSON(data);
    if(json.success == 1){

        if(flag === 0){
            var html = '<div class="gallery-item" id="attachmentID">';
        }else if(flag === 1){
            var html = '<div class="gallery-item" id="attachmentTheID">';
        }else {
            alert('参数错误,请重试');return false;
        }

        html += '<span id="path" style="display: none;">'+json.path+'</span>';
        html += '<span id="saveName" style="display: none;">'+json.saveName+'</span>';
        html += '<span id="fileName" style="display: none;">'+json.fileName+'</span>';
        html += '<div class="gallery-wrapper">';
        if(flag === 0){
            html += '<a class="gallery-remove" onclick="return removeAttachment(\''+json.path+'\',\''+json.saveName+'\',0)"><i class="fa fa-times"></i></a>';
        }else if(flag === 1){
            if($('#theAuto').length > 0 && $('#theAuto').val() == BAOLIAOZHE ) {    //爆料和可以编辑附件,可以删除
                html += '<a class="gallery-remove" onclick="return removeAttachment(\''+json.path+'\',\''+json.saveName+'\',1)"><i class="fa fa-times"></i></a>';
            }
        }else {
            alert('参数错误,请重试');return false;
        }
        html += '<a target="_blank" href='+PUBLIC+json.path+'> <img class = textAttachmenthow src='+PUBLIC+'/img/Admin/media/textAttachment.png></a>';
        html += '<div class="gallery-title" id="title'+json.saveName+'">';
        html += '<a href = "#">'+json.fileName+'</a>';
        html += '</div>';
        html += '</div>';
        html += '</div>';

        if(flag === 0){
            $("#imgs").append(html);
            $("#attachmentDiv").fadeIn();
            $("#upLoadBtn").hide();
        }else if (flag === 1){

            $("#theImgs").append(html);
            $("#theAttachmentDiv").fadeIn();
            $("#theAttachmentTextDiv").fadeIn();
            $("#theUpLoadBtn").hide();

        }else {
            alert('参数错误,请重试');return false;
        }
    }else{
        alert(json.msg);
        return false;
    }
}


/**
 * 编辑文章时,取得附件内容,如果存在附件则显示在页面上
 */
function doAttachment() {

    var attachmentStr = $('#attachmentList').val();
    var saveNameStr = $('#saveNameList').val();
    var fileNameStr = $('#fileNameList').val();

    if(attachmentStr != 0 && saveNameStr !=0 &&fileNameStr !=0 ){

        var html = '<div class="gallery-item" id="attachmentTheID">';
        html += '<span id="path" style="display: none;">'+attachmentStr+'</span>';
        html += '<span id="saveName" style="display: none;">'+saveNameStr+'</span>';
        html += '<span id="fileName" style="display: none;">'+fileNameStr+'</span>';
        html += '<div class="gallery-wrapper">';
        if($('#theAuto').length > 0 && $('#theAuto').val() == BAOLIAOZHE ) {    //爆料和可以编辑附件,可以删除
            html += '<a class="gallery-remove" onclick="return removeAttachment(\'' + attachmentStr + '\',\'' + saveNameStr + '\',1) "><i class="fa fa-times"></i></a>';
        }
        html += '<a target="_blank" href='+PUBLIC+attachmentStr+'> <img class = textAttachmenthow src='+PUBLIC+'/img/Admin/media/textAttachment.png></a>';
        html += '<div class="gallery-title" id="title'+saveNameStr+'">';
        html += '<a href = "#">'+fileNameStr+'</a>';
        html += '</div>';
        html += '</div>';
        html += '</div>';

        $("#theImgs").append(html);
        $("#theAttachmentDiv").fadeIn();
        $("#theAttachmentTextDiv").fadeIn();

    }
}

/**
 * 删除附件
 * @param path      文件路径
 * @param saveName  物件保存名
 * @param flag      0:表示与数据库无关,1:表示数据库也需要删除
 */
function removeAttachment (path,saveName,flag) {

    var postid = 0,
        pathNameStr = '',
        saveNameStr = '',
        fileNameStr = '';

    if(flag === 1){
        postid = $('#postid').val();
        pathNameStr = $('#path').text();
        saveNameStr = $('#saveName').text();
        fileNameStr = $('#fileName').text();
    }

    $.ajax({
        url:ROOT+"/Admin/Post/doAction/action/delAttachment"//改为你的动态页
        ,type:"POST"
        ,data:{
            'postid':postid,
            'path':path,
            'attachment':pathNameStr,     //单独附件路径
            'saveName':saveNameStr,   //单独附件保存文件名
            'fileName':fileNameStr    //单独附件文件名
        }
        ,dataType: "json"
        ,beforeSend: function(){
            $('#title'+saveName).html('正在删除...');
        }
        ,success:function(json){
            if(json.success == 1){
                if(flag === 1){
                    $('#attachmentTheID').remove();
                    $('#theUpLoadBtn').fadeIn();
                }else
                {
                    $('#attachmentID').remove();
                    $('#upLoadBtn').fadeIn();
                }
            }else{
                alert(json.msg);
                return false;
            }

            //判断是否都删除完毕了,则将img的div隐藏起来
            if($('.gallery-item').length == 0){
                if(flag === 1){
                    $("#theAttachmentDiv").hide();
                }else {
                    $("#attachmentDiv").hide();
                }
            }

        }
        ,error:function(xhr){alert('PHP页面有错误！'+xhr.responseText);}
    });
}