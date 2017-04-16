
//定义常量

//全局变量角色
var BAOLIAOZHE = 1;       //爆料者
var XIAOBIAN   = 2;       //编辑
var ZONGBIAN   = 3;       //总编
var DEPT_ADMIN = 4;       //部门管理员
var ADMIN      = 88;      //管理员

//定义可上传的文件类型
var FILE_EXT = '*.zip;*.7z;*.rar;*.jpg;*.jpge;*.gif;*.txt;*.xls;*.pdf;*.doc;*.xlsx;*.docx;*.pptx;*.png';
var FILE_SIZE = 5242880;    //5M


/**
 *删除数组指定下标或指定对象
 */
Array.prototype.remove=function(obj){
    for(var i =0;i <this.length;i++){
        var temp = this[i];
        if(!isNaN(obj)){
            temp=i;
        }
        if(temp == obj){
            for(var j = i;j <this.length;j++){
                this[j]=this[j+1];
            }
            this.length = this.length-1;
        }
    }
}



/*
 用途：检查输入字符串是否为空或者全部都是空格
 输入：str
 返回：
 如果全是空返回true,否则返回false
 */
function isNull( str ){
    if ( str == "" ) return true;
    var regu = "^[ ]+$";
    var re = new RegExp(regu);
    return re.test(str);
}


/*
 用途：检查输入对象的值是否符合整数格式
 输入：str 输入的字符串
 返回：如果通过验证返回true,否则返回false

 */
function isInteger( str ){
    var regu = /^[-]{0,1}[0-9]{1,}$/;
    return regu.test(str);
}

/*
 用途：检查输入手机号码是否正确
 输入：
 s：字符串
 返回：
 如果通过验证返回true,否则返回false

 */
function isMobile( s ){
//var regu =/^[1][3][0-9]{9}$/;
    var regu =/^[1][0-9]{10}$/;
    var re = new RegExp(regu);
    if (re.test(s)) {
        return true;
    }else{
        return false;
    }
}


/*
 用途：检查输入字符串是否符合正整数格式
 输入：
 s：字符串
 返回：
 如果通过验证返回true,否则返回false

 */
function isNumber( s ){
    var regu = "^[0-9]+$";
    var re = new RegExp(regu);
    if (s.search(re) != -1) {
        return true;
    } else {
        return false;
    }
}

/*
 用途：检查输入字符串是否是带小数的数字格式,可以是负数
 输入：
 s：字符串
 返回：
 如果通过验证返回true,否则返回false

 */
function isDecimal( str ){
    if(isInteger(str)) return true;
    var re = /^[-]{0,1}(\d+)[\.]+(\d+)$/;
    if (re.test(str)) {
        if(RegExp.$1==0&&RegExp.$2==0) return false;
        return true;
    } else {
        return false;
    }
}

/*
 用途：检查输入对象的值是否符合端口号格式
 输入：str 输入的字符串
 返回：如果通过验证返回true,否则返回false

 */
function isPort( str ){
    return (isNumber(str) && str<65536);
}

/*
 用途：检查输入对象的值是否符合E-Mail格式
 输入：str 输入的字符串
 返回：如果通过验证返回true,否则返回false

 */
function isEmail( str ){
    var myReg = /^[-_A-Za-z0-9]+@([_A-Za-z0-9]+\.)+[A-Za-z0-9]{2,3}$/;
    if(myReg.test(str)) return true;
    return false;
}

/*
 用途：检查输入字符串是否符合金额格式
 格式定义为带小数的正数，小数点后最多三位
 输入：
 s：字符串
 返回：
 如果通过验证返回true,否则返回false

 */
function isMoney( s ){
    var regu = "^[0-9]+[\.][0-9]{0,3}$";
    var re = new RegExp(regu);
    if (re.test(s)) {
        return true;
    } else {
        return false;
    }
}
/*
 用途：检查输入字符串是否只由英文字母和数字和下划线组成
 输入：
 s：字符串
 返回：
 如果通过验证返回true,否则返回false

 */
function isNumberOr_Letter( s ){//判断是否是数字或字母

    var regu = "^[0-9a-zA-Z\_]+$";
    var re = new RegExp(regu);
    if (re.test(s)) {
        return true;
    }else{
        return false;
    }
}
/*
 用途：检查输入字符串是否只由英文字母和数字组成
 输入：
 s：字符串
 返回：
 如果通过验证返回true,否则返回false

 */
function isNumberOrLetter( s ){//判断是否是数字或字母

    var regu = "^[0-9a-zA-Z]+$";
    var re = new RegExp(regu);
    if (re.test(s)) {
        return true;
    }else{
        return false;
    }
}
/*
 用途：检查输入字符串是否只由汉字、字母、数字组成
 输入：
 value：字符串
 返回：
 如果通过验证返回true,否则返回false

 */
function isChinaOrNumbOrLett( s ){//判断是否是汉字、字母、数字组成

    var regu = "^[0-9a-zA-Z\u4e00-\u9fa5]+$";
    var re = new RegExp(regu);
    if (re.test(s)) {
        return true;
    }else{
        return false;
    }
}

/*
 用途：判断是否是日期
 输入：date：日期；fmt：日期格式
 返回：如果通过验证返回true,否则返回false
 */
function isDate( date, fmt ) {
    if (fmt==null) fmt="yyyy-MM-dd";
    var yIndex = fmt.indexOf("yyyy");
    if(yIndex==-1) return false;
    var year = date.substring(yIndex,yIndex+4);
    var mIndex = fmt.indexOf("MM");
    if(mIndex==-1) return false;
    var month = date.substring(mIndex,mIndex+2);
    var dIndex = fmt.indexOf("dd");
    if(dIndex==-1) return false;
    var day = date.substring(dIndex,dIndex+2);
    if(!isNumber(year)||year>"2100" || year< "1900") return false;
    if(!isNumber(month)||month>"12" || month< "01") return false;
    if(day>getMaxDay(year,month) || day< "01") return false;
    return true;
}

function getMaxDay(year,month) {
    if(month==4||month==6||month==9||month==11)
        return "30";
    if(month==2)
        if(year%4==0&&year%100!=0 || year%400==0)
            return "29";
        else
            return "28";
    return "31";
}

/*
 用途：字符1是否以字符串2结束
 输入：str1：字符串；str2：被包含的字符串
 返回：如果通过验证返回true,否则返回false

 */
function isLastMatch(str1,str2)
{
    var index = str1.lastIndexOf(str2);
    if(str1.length==index+str2.length) return true;
    return false;
}


/*
 用途：字符1是否以字符串2开始
 输入：str1：字符串；str2：被包含的字符串
 返回：如果通过验证返回true,否则返回false

 */
function isFirstMatch(str1,str2)
{
    var index = str1.indexOf(str2);
    if(index==0) return true;
    return false;
}

/*
 用途：字符1是包含字符串2
 输入：str1：字符串；str2：被包含的字符串
 返回：如果通过验证返回true,否则返回false

 */
function isMatch(str1,str2)
{
    var index = str1.indexOf(str2);
    if(index==-1) return false;
    return true;
}


/*
 用途：检查输入的起止日期是否正确，规则为两个日期的格式正确，
 且结束如期>=起始日期
 输入：
 startDate：起始日期，字符串
 endDate：结束如期，字符串
 返回：
 如果通过验证返回true,否则返回false

 */
function checkTwoDate( startDate,endDate,messStar,messEnd) {
    if( !isDate(startDate) ) {
        alert(messStar+"日期不正确!");
        return false;
    } else if( !isDate(endDate) ) {
        alert(messEnd+"日期不正确!");
        return false;
    } else if( startDate > endDate ) {
        alert(messStar+"日期不能大于"+messEnd+"日期!");
        return false;
    }
    return true;
}

function checkTwoDate2(startDate,endDate,str1,str2) {
    var msg = "";
    if( !isDate(startDate) ) {
        msg = str1+"格式不正确!";
    } else if( !isDate(endDate) ) {
        msg = str2+"格式不正确!";
    } else if( startDate > endDate ) {
        msg = str1+"不能大于"+str2+"!";
    }
    return msg;
}

/****************************************************
 function:cTrim(sInputString,iType)
 description:字符串去空格的函数
 parameters:iType：1=去掉字符串左边的空格

 2=去掉字符串左边的空格
 0=去掉字符串左边和右边的空格
 return value:去掉空格的字符串
 ****************************************************/
function cTrim(sInputString,iType)
{
    var sTmpStr = ' ';
    var i = -1;

    if(iType == 0 || iType == 1)
    {
        while(sTmpStr == ' ')
        {
            ++i;
            sTmpStr = sInputString.substr(i,1);
        }
        sInputString = sInputString.substring(i);
    }

    if(iType == 0 || iType == 2)
    {
        sTmpStr = ' ';
        i = sInputString.length;
        while(sTmpStr == ' ')
        {
            --i;
            sTmpStr = sInputString.substr(i,1);
        }
        sInputString = sInputString.substring(0,i+1);
    }
    return sInputString;
}


/**
 * 显示错误
 * @param msg
 */
function showError( msg ){
    $(".message").remove();
    $("#login_error").remove();
    $("form").before(" <div id='login_error'><strong>错误</strong>："+msg+ "<br></div>");
}


/**
 * 显示错误
 * @param msg
 */
function showMSg( msg ){
    $(".message").remove();
    $("#login_error").remove();
    $("form").before(" <p class='message'>	"+msg+ "<br></p>");
}

/**
 * 登录注册页面出错时,摇动对话框
 */
function wp_shake_js(){

    addLoadEvent = function( func ){
        if(typeof jQuery != "undefined")
            jQuery(document).ready( func );
        else if( typeof wpOnload != 'function' ){
            wpOnload = func;
        }else{
            var oldonload=wpOnload;
            wpOnload = function(){
                oldonload();
                func();
            }
        }
    };

    function s(id,pos){
        g(id).left=pos+'px';
    }

    function g(id){
        return document.getElementById(id).style;
    }

    function shake(id,a,d){
        c = a.shift();
        s( id, c );
        if( a.length > 0 ){
            setTimeout( function(){
                shake(id,a,d);
            },d);
        }else{
            try{g(id).position='static';
                wp_attempt_focus();
            }catch(e){

            }
        }
    }

    addLoadEvent(function(){
        var p = new Array(15,30,15,0,-15,-30,-15,0);
        p = p.concat(p.concat(p));
        var i=document.forms[0].id;g(i).position='relative';
        shake(i,p,20);
    });

    //光标停在用户名input上
    try{document.getElementById('user_login').focus();}catch(e){}
    if(typeof wpOnload=='function')wpOnload();
}

/**
 * 验证表单内容
 * @returns {*}
 */
function checkForm( flag ){

    //登录
    if( 'login' == flag){
        var user  = $('#user_login');
        var pass = $('#user_pass');

        if( user.length > 0){

            userval = user.val();

            if( isNull( userval ) ){
                return '用户名不能为空！';
            }

            if( userval.length < 6){
                return '用户名必须大于等于6位！';
            }
        }

        if( pass.length > 0){

            passval = pass.val();

            if( (isNull( passval )) || ('' == cTrim(passval,0)) ){
                return '密码不能为空！';
            }
        }
        return '';
    }
    //注册
    if( 'reg' == flag){
        var user  = $('#user_login');
        var email = $('#user_email');

        if( user.length > 0){

            userval = user.val();

            if( isNull( userval ) ){
                return '用户名不能为空！';
            }

            if( userval.length < 6){
                return '用户名必须大于等于6位！';
            }
        }

        if( email.length > 0){

            emailval = email.val();

            if( (isNull( emailval )) || ('' == cTrim(emailval,0)) ){
                return '邮箱地址不能为空！';
            }
            if( !isEmail( emailval ) ){
                return '请输入正确的邮箱地址！';
            }
        }
        return '';
    }
    if( 'lostpass' == flag ){
        var userORemail  = $('#user_login').val();

        if( (isNull( userORemail )) || ('' == userORemail) ){
            return '用户名或电子邮件地址不能为空！';
        }

         if( !isEmail( userORemail ) ){
            if( userORemail.length < 6){
                return '用户名必须大于等于6位！';
            }
        }
        return '';
    }

}

/**
 * 三次弹出对话框
 * @returns {boolean}
 */
function confirmThree() {
    if (!confirm('确定要删除吗？(3)')){
        return false;
    }
    if (!confirm('确定要删除吗？(2)')){
        return false;
    }
    if (!confirm('确定要删除吗？(1)')){
        return false;
    }
    return true;

}