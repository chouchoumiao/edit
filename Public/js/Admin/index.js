/**
 * Created by Administrator on 14-11-16.
 */
$(document).ready(function(){

    $('#menu').tendina({
        openCallback: function(clickedEl) {
            clickedEl.addClass('opened');
        },
        closeCallback: function(clickedEl) {
            clickedEl.addClass('closed');
        }
    });

});
$(function(){

    $("#ad_setting").click(function(){
        $("#ad_setting_ul").show();
    });
    $("#ad_setting_ul").mouseleave(function(){
        $(this).hide();
    });
    $("#ad_setting_ul li").mouseenter(function(){
        $(this).find("a").attr("class","ad_setting_ul_li_a");
    });
    $("#ad_setting_ul li").mouseleave(function(){
        $(this).find("a").attr("class","");
    });
});

function getWeiID(){
    var weixinID =  $("#weiIDSelect").val();

    $.ajax({
        url:"admin.php?controller=admin&method=changeWeixinID"
        ,type:"POST"
        ,data:{"weixinID":weixinID}
        ,dataType: "json"
        ,success:function(data){
            if(data.success == 1){
                self.location='admin.php?controller=admin&method=index';
            }else{
                alert('获取公众号信息失败！');
                return;
            }

        }
        ,error:function(xhr){alert('页面有错误'+xhr.responseText);}
    });
};