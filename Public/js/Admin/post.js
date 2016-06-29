// $(function(){
//
//     $( '#summernote' ).summernote({
//         minHeight: 350,
//         focus:false,
//         lang:'zh-CN',
//         callbacks : {
//             // onImageUpload的参数为files，summernote支持选择多张图片
//             onImageUpload : function(files) {
//                 var $files = $(files);
//
//                 // 通过each方法遍历每一个file
//                 $files.each(function() {
//
//                     var file = this;
//
//                     var data = new FormData();
//
//                     // 将文件加入到file中，后端可获得到参数名为“file”
//                     data.append("file", file);
//
//                     // ajax上传
//                     $.ajax({
//                         data : data,
//                         type : "POST",
//                         url : "__ROOT__/Admin/Post/upload",// div上的action
//                         cache : false,
//                         contentType : false,
//                         processData : false,
//                         dataType : "json",
//
//                         // 成功时调用方法，后端返回json数据
//                         success : function(response) {
//                             var imgNode = $('<img>').attr('src',"__PUBLIC__"+response.msg);
//                             $( '#summernote' ).summernote('insertNode', imgNode[0]);
//                         }
//                     });
//                 });
//             }
//         }
//
//     });
// });