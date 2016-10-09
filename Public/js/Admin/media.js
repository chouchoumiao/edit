$(function(){

    $('#media_file_upload').uploadifive({
        'uploadScript' : './Admin/Media/doAction/action/upload',   //上传的方法
        'buttonText' : '上传资源',
        'fileTypeExts'  : '*.jpg;*.jpge;*.gif;*.txt;*.xls;*.pdf;*.doc;*.xlsx;*.docx;*.pptx;*.png',
        'fileSizeLimit' : 5242880,
        'removeCompleted':true,
        'uploadLimit':5,
        'onUploadComplete' : function(file, data) {

            try {
                var json = jQuery.parseJSON(data);
            }catch(e) {
                alert('未知错误，请重试！');
                return false;
            }

            if(json.success == 1){

                if(json.msg.indexOf('jpg') > -1
                    || json.msg.indexOf('jpge') > -1
                    || json.msg.indexOf('png') > -1
                    || json.msg.indexOf('gif') > -1){
                    var flag = '<img id= imgId src='+PUBLIC+json.msg+'>';
                }else{
                    var flag = '<img class = textshow src='+PUBLIC+'/img/Admin/media/text.png>';
                }
                doUpload(json,flag);
            }else{
                alert(json.msg);
                return false;
            }

        },
        'onFallback' : function() {
            alert("该浏览器无法使用!");
        },
        'onError': function(errorType) {
            return false;
        }
    });

});

/**
 * 如果点击删除图片按钮则先获取图片的名称,将名称传入后删除对应的ID
 * @param data
 */
function removeImg (id,day,name,ext) {

    $.ajax({
        url:ROOT+"/Admin/Media/doAction/action/delImg"//改为你的动态页
        ,type:"POST"
        ,data:{
            'id':id,
            'img':day+'/'+ name + '.' + ext
        }
        ,dataType: "json"
        ,beforeSend: function(){
            $('#title'+name).html('正在删除...');
        }
        ,success:function(json){
            if(json.success == 1){
                $('#'+name).remove();
            }else{

                alert(json.msg);
                return false;
            }
        }
        ,error:function(xhr){alert('PHP页面有错误！'+xhr.responseText);}
    });
}

function doUpload(json,flag) {
    var html = '<div class="gallery-item" id="'+json.name+'">';
    html += '<div class="gallery-wrapper">';
    html += '<a class="gallery-remove" onclick="return removeImg(\''+json.id+'\',\''+json.day+'\',\''+json.name+'\',\''+json.ext+'\') "><i class="fa fa-times"></i></a>';
    html += flag;
    html += '<div class="gallery-title" id="title'+json.name+'">';
    html += '<a href = '+ROOT+'/Admin/Media/doAction/action/getStatus/status/me>前往资源库修改</a>';
    html += '</div>';
    html += '<div class="gallery-overlay">';
    html += '<a target="_blank" href="'+PUBLIC+json.msg+'" class="gallery-action enlarged-photo">';
    html += '<i class="fa fa-search-plus fa-lg"></i>';
    html += '</a>';
    html += '</div>';
    html += '</div>';
    html += '</div>';

    $("#imgs").append(html);
}