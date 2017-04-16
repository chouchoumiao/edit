$(function(){

    var ALL_DEPT_COUNT = 4;   //部门的总个数
    
    $('#file_upload').uploadifive({
        'uploadScript' : './Admin/User/doAction/action/upload',   //上传的方法
        'buttonText' : '头像上传',
        'fileTypeExts'  : '*.jpg;*.jpge;*.png',
        'fileSizeLimit' : 5242880,
        'removeCompleted':true,
        'onUploadComplete' : function(file, data) {

            doUpload(data);
        }
    });

    $('#edit_file_upload').uploadifive({
        'multi' : false,
        'uploadScript' : './Admin/User/doAction/action/editimg',   //上传的方法
        'buttonText' : '头像上传',
        'fileTypeExts'  : '*.jpg;*.jpge;*.png',
        'fileSizeLimit' : 5242880,
        'onUploadComplete' : function(file, data) {

            doUpload(data);
        }
    });

    $('#upload').change(function(){
        $("#upload-img").attr("src",$('#upload').val());
    });

    //新增用户表单验证
    if($('#addForm').length > 0) {
        //文本框失去焦点后
        $('form :input').blur(function(){
            var $parent = $(this).parent();
            $parent.find(".formtips").remove();
            //验证用户名
            if( $(this).is('#user_login') ){
                var idname = 'user_loginID';
                if( this.value=="" || this.value.length < 6 || this.value.length > 20 ){
                    var errorMsg = '用户名不能为空,并且在6-20位之间.';

                    doError($parent,errorMsg,idname);
                }else{
                    doOK($parent,idname);
                }
            }

            //验证昵称
            if( $(this).is('#user_name') ){
                var idname = 'user_name';
                if( this.value.length > 30 ){
                    var errorMsg = '昵称不能大于30位';
                    doError($parent,errorMsg,idname);
                }else{
                    doOK($parent,idname);
                }
            }
            //验证邮件
            if( $(this).is('#user_email') ){
                var idname = 'user_email';
                if( this.value=="" || ( !isEmail(this.value) ) ){
                    var errorMsg = '请输入正确的E-Mail地址';

                    doError($parent,errorMsg,idname);
                }else{
                    doOK($parent,idname);

                }
            }

            //验证空密码
            if( $(this).is('#user_pass') ){
                var idname = 'user_pass';
                if( this.value=="" || this.value.length < 6 || this.value.length > 20 ){
                    var errorMsg = '密码不能为空,并且在6-20位之间';
                    doError($parent,errorMsg,idname);
                }else{
                    doOK($parent,idname);

                }
            }


        }).keyup(function(){
            $(this).triggerHandler("blur");
        }).focus(function(){
            $(this).triggerHandler("blur");
        });//end blur


        //提交，最终验证。
        $('#send').click(function(){
            

            //提交时验证
            if($('#user_login').val() == ''){
                $('#user_login').focus();
                return false;
            }

            if($('#user_email').val() == ''){
                $('#user_email').focus();
                return false;
            }

            if($('#user_pass').val() == ''){
                $('#user_pass').focus();
                return false;
            }

            //判断部门复选框是否都没有选中
            var deptCount = $("input[class='checkbox-purple']:checked").length; 
            if( 0 == deptCount){
                alert('至少选中一个部门');
                return false;
            }

            //判断新增的角色为编辑或者总编时，或者部门管理员,只能选择一个部门,管理员必全选部门
            var auto = $("input[name='auto']:checked").val();

            if( auto == XIAOBIAN || auto == ZONGBIAN || auto == DEPT_ADMIN){
                if(deptCount > 1){
                    alert('该角色只能选择一个部门');
                    return false;
                }
            }else if( auto == ADMIN ){
                if(deptCount != ALL_DEPT_COUNT){
                    alert('管理员需要选择全部部门进行管理');
                    return false;
                }
            }

            $("form :input.required").trigger('blur');
            var numError = $('#showError').length;
            if(numError){
                return false;
            }
        });

        //重置
        $('#res').click(function(){

            $("div").removeClass('glyphicon');
            $("div").removeClass('has-success');
            $("div").removeClass('has-error');
            $("div").removeClass('has-feedback');
            $('.glyphicon').remove();
            $('.showMsg').remove();
        });
    }


    //修改用户表单验证
    if($('#updateForm').length > 0) {
        //文本框失去焦点后
        $('form :input').blur(function(){
            var $parent = $(this).parent();
            $parent.find(".formtips").remove();

            //验证昵称
            if( $(this).is('#user_name') ){
                var idname = 'user_name';
                if( this.value.length > 30 ){
                    var errorMsg = '昵称不能大于30位sss';
                    doError($parent,errorMsg,idname);
                }else{
                    doOK($parent,idname);
                }
            }
            //验证密码(可以为空,不修改)
            if( $(this).is('#user_pass') ){

                var idname = 'user_pass';
                if( (this.value != '') && (this.value.length < 6 || this.value.length > 20) ){

                    $('#comfirm_pass').val('');  //隐藏钱先清空原先输入的确认密码内容
                    $('#comfirm').hide();   //如果密码不符合规则,则不显示确认密码

                    var errorMsg = '密码必须在6-20位之间';
                    doError($parent,errorMsg,idname);
                }else if (this.value == ''){
                    doOK($parent,idname);
                }else { //如果输入了新密码 则显示出确认密码
                    $('#comfirm').fadeIn();
                    doOK($parent,idname);
                }
            }

            //验证确认密码(可以为空,不修改)
            if( $(this).is('#comfirm_pass') ){

                var idname = 'comfirm_pass';
                if( this.value != $('#user_pass').val() ){

                    var errorMsg = '确认密码必须与修改密码一致';
                    doError($parent,errorMsg,idname);
                }else{
                    doOK($parent,idname);
                }
            }

            //验证手机
            if( $(this).is('#tel') ){
                var idname = 'tel';
                if( !isMobile(this.value) ){
                    var errorMsg = '手机格式错误';
                    doError($parent,errorMsg,idname);
                }else{
                    doOK($parent,idname);

                }
            }


        }).keyup(function(){
            $(this).triggerHandler("blur");
        }).focus(function(){
            $(this).triggerHandler("blur");
        });//end blur


        //提交，最终验证。
        $('#updateSend').click(function(){


            if($('#address').val().length > 200){
                alert('地址内容不得超过200位');
                $('#address').focus();
                return false;
            }

            //如果是管理员或者超级管理员的时候则不验证部门
            if($('#isShow').val()){

                //判断部门复选框是否都没有选中
                if( 0 == ($("input[class='checkbox-purple']:checked").length)){
                    alert('至少选中一个部门');
                    return false;
                }
            }

            $("form :input.required").trigger('blur');

            //通过判定含有错误class的个数来验证是否都通过
            var numError = $('.glyphicon-remove').length;
            
            if(numError){
                return false;
            }
        });

        //管理员审核时。
        $('#auditSend').click(function(){

            if($('#address').val().length > 200){
                alert('地址内容不得超过200位');
                $('#address').focus();
                return false;
            }

            //如果是管理员或者超级管理员的时候则不验证部门
            if($('#isShow').val()){

                //判断部门复选框是否都没有选中
                if( 0 == ($("input[class='checkbox-purple']:checked").length)){
                    alert('至少选中一个部门');
                    return false;
                }
            }

            $("form :input.required").trigger('blur');

            //通过判定含有错误class的个数来验证是否都通过
            var numError = $('.glyphicon-remove').length;

            if(numError){
                return false;
            }
        });

        //管理员审核时。
        $('#auditNoSend').click(function(){

            location.href = history.back();
        });

        //重置
        $('#updateRes').click(function(){


            $("div").removeClass('glyphicon');
            $("div").removeClass('has-success');
            $("div").removeClass('has-error');
            $("div").removeClass('has-feedback');
            $('.glyphicon').remove();
            $('.showMsg').remove();
        });
    }




});

//判断用户输入信息

/**
 * 显示验证失败时候应该要显示的样式
 * @param $parent
 * @param msg
 */
function doError($parent,msg,idname) {

    //判断原来是否已经有校验成功过,有的话则删除原来的样式
    if($('#showOK'+idname).length > 0 ){
        $('#showOK'+idname).remove();
        $parent.removeClass('has-success has-feedback');
    }

    //追加失败的样式
    $parent.addClass('has-error has-feedback');

    //如果已经增加过失败样式,则不追加,避免多次追加
    if($('#showError'+idname).length <= 0 ){
        $parent.append('<span id="showError'+idname+'" class="glyphicon glyphicon-remove form-control-feedback"></span>');
        $parent.append('<p id="msg'+idname+'"  class="showMsg text-danger">'+msg+'</p>');
    }
}

/**
 * 显示验证成功时候应该要显示的样式
 * @param $parent
 */
function doOK($parent,idname) {
    //判断原来是否已经有校验失败过,有的话则删除原来的样式
    if($('#showError'+idname).length > 0){

        $('#showError'+idname).remove();
        $('#msg'+idname).remove();
        $parent.removeClass('has-error has-feedback');
    }

    //追加成功的样式
    $parent.addClass('has-success has-feedback');

    //如果已经增加过成功样式,则不追加,避免多次追加
    if($('#showOK'+idname).length <= 0){
        $parent.append('<span id="showOK'+idname+'" class="glyphicon glyphicon-ok form-control-feedback""></span>');
    }
}

//删除用户
function delUser(id){
    
    //三次弹出确认对话框
    if( !confirmThree() ){
        return;
    }

    $.ajax({
    url:ROOT+"/Admin/User/doAction/action/del"//改为你的动态页
    ,type:"POST"
    ,data:{
            'id':id
        }
    ,dataType: "json"
    ,success:function(json){
        if(json.success == "OK"){
            alert('删除成功');
            location = ROOT+"/Admin/User/doAction/action/all";

        }else{
            alert('删除失败');
            return false;
        }
    }
    ,error:function(xhr){alert('PHP页面有错误！'+xhr.responseText);}
    });
}


function doUpload(data) {
    var isOk = false;
    if(data.indexOf("jpg") > -1){
        isOk = true;
    }

    if(data.indexOf("png") > -1){
        isOk = true;
    }

    if(data.indexOf("jpeg") > -1){
        isOk = true;
    }
    if(isOk){
        $("#upload-img").attr("src",PUBLIC+'/Uploads/profile/'+data);
    }else{
        alert(data);
        return false;
    }
}
