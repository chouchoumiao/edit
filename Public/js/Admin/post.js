$(function(){

    /**
     * //Ueditor富文本编辑器操作
     */

    //初始化富文本编辑器
    UE.getEditor('container',{toolbars:[[
        'fullscreen',"undo","redo",'|',"bold","italic","underline",'|',
        'insertorderedlist', 'insertunorderedlist', '|',
        "superscript","subscript",'removeformat','|',
        "justifyleft","justifycenter","justifyright","justifyjustify",
        '|',"indent","rowspacingbottom","fontfamily","fontsize",
        '|',"forecolor","backcolor",'|',"insertimage",'|'
        ,"link","unlink",'|',"inserttable","deletetable",'|',
        "source",'|',
        'searchreplace']],
        initialFrameHeight:500});


    var ue = UE.getEditor("container");
    ue.ready(function() {

        var content = $('#content');
        if( (content.length > 0) && (content.val() != '') ){
            ue.setContent(content.val() + ' ');
        }else {
            ue.setContent(' ');
        }
        ue.focus(true);

        //如果是小编而且是编辑的请情况下则变成红色
        if($('#theAuto').length > 0 && $('#theAuto').val() == XIAOBIAN ){
            ue.execCommand('forecolor', '#FF0000');
        }
    });




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


    if($('#postid').length > 0){

        var theAutoVal = $('#theAuto').val();

        //编辑文章的时候,如果有附件则显示附件
        if( ( theAutoVal== BAOLIAOZHE) || ( theAutoVal == XIAOBIAN) ) {
            doAttachment();
        }

        //如果是小编或者是总编的人情况下，先判断该文章是否有过打分，有的话则取出显示
        if( ( theAutoVal == XIAOBIAN) || ( theAutoVal == ZONGBIAN) ) {

            var theScore = parseInt($('#theScore').val());
            //大于0说明原先该文章已经被打过分了,所有要把分数取得并且绑定在积分DIV上
            if( theScore > 0){
                setScoreActive(theScore);
            }

        }


    }else{  //新增文章时候讲焦点定于第一行
        if($('#newTitle').length > 0){

            $('#newTitle').focus();
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

    });

    //焦点定位到第一个input（并将光标定位到最后位置，火狐不支持）
    var titleVar = $('#theTitle').val();
    $('#theTitle').focus().val(titleVar);

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
    var ue = UE.getEditor("container");

    //判断提交的时候是否有内容输入
    if(!ue.hasContents()){
        alert('文章内容不能空');
        return;
    }

    //取得富文本编辑器的html内容
    var data = ue.getContent();

    //判断部门复选框是否都没有选中
    if( 0 == ($("input[class='checkbox-purple']:checked").length)){
        alert('至少选中一个部门');
        return;
    }

    var deptArr = [];
    $("#dept [type=checkbox]:checked").each(function(i){
        deptArr.push($(this).val());
    });


    //判断附件个数，只能是一个
    if($('.gallery-item').length > 1){
        alert('附件个数有误请确认');
        return;
    }

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
        //防止多次提交
        ,beforeSend: function(){

            $(".submit-btn").attr("disabled","disabled");
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

           //防止多次提交
           $("#submit-btn").removeAttr("disabled");
        }
        ,error:function(xhr){alert('PHP页面有错误！'+xhr.responseText);}
    });
}


/**
 * 重置内容
 */
function resetAddForm () {
    $('#title').val('');

    var ue = UE.getEditor("container");
    ue.setContent('');
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
        //防止多次提交
        ,beforeSend: function(){

            $(".delPost").attr("disabled","disabled");
        }
        ,dataType: "json"
        ,success:function(json){
            if(json.success == "OK"){
                alert('删除成功');
                //防止多次提交
                $("#delPost").removeAttr("disabled");
                location = ROOT+"/Admin/Post/doAction/action/all";

            }else{
                alert('删除失败');
                //防止多次提交
                $("#delPost").removeAttr("disabled");
                return false;
            }

        }
        ,error:function(xhr){alert('PHP页面有错误！'+xhr.responseText);}
    });

}

/**
 * 修改文章
 * @param flag
 *              1:保存
 *              2:提交审核
 *              3:继续提交审核
 *              4:审核不通过
 *              5:审核通过
 *              6:总编打回给小编不通过,小编可以继续修改
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
    var ue = UE.getEditor("container");

    if(!ue.hasContents()){
        alert('文章内容不能空');
        return;
    }

    //取得富文本编辑器的html内容
    var data = ue.getContent();


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

    //判断附件个数，只能是一个
    if($('.gallery-item').length > 1){
        alert('附件个数有误请确认');
        return;
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
        //防止多次提交
        ,beforeSend: function(){

            $(".submit-btn").attr("disabled","disabled");
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
            //防止多次提交
           $("#submit-btn").removeAttr("disabled");
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

    try {
        var json = jQuery.parseJSON(data);
    }catch(e) {
        alert('未知错误，请重试！');
        return false;
    }

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

/**
 * 根据传入的个数,设置爱心激活状态
 * @param num
 */
function setScoreActive(num) {

    if( typeof num === "number"){

        for (var i=1; i<=num; i++){
            $('#scoreBtn'+i).addClass('active');
        }
        //最后一个追加一个特有class,用于提交时判断
        $('#scoreBtn'+num).addClass('lastActive');

    }

}