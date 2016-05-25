<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

    <title>微信管理后台-测试环境</title>
    <link rel="stylesheet" href="/edit/Public/css/admin/index.css?v=20150436" type="text/css" media="screen" />

    <!-- script标签放在底下，刷新时候会有跳动，所以需要放在页面加载前-->
    <script type="text/javascript" src="http://apps.bdimg.com/libs/jquery/1.8.3/jquery.min.js"></script>
    <script type="text/javascript" src="/edit/Public/js/Common/tendina.min.js?v=20150103"></script>
    <script type="text/javascript" src="/edit/Public/js/Admin/index.js?v=20150123" charset="utf-8"></script>
</head>
<body>

<!--顶部-->
<header>
    <div class="layout_top_header">
        <div style="float: left">
            <span style="font-size:20px;line-height:45px;padding-left:20px;color:#8d8d8d">微信管理后台</span>
        </div>

        <div id = "ad_setting" class="ad_setting2">
            <a class="ad_setting_a">
                <i class="icon-user glyph-icon" style="font-size: 20px"></i>
                <span>管理员: &nbsp <?php echo ($aaa); ?></span>
                <i class="icon-chevron-down glyph-icon"></i>
            </a>
            <ul class="dropdown-menu-uu" style="display: none" id="ad_setting_ul">
                <li class="ad_setting_ul_li"> <a href="tpl/admin/adminEdit.html" target="menuFrame">
                        <i class="icon-user glyph-icon"></i> 修改密码 </a> </li>
                <li class="ad_setting_ul_li"> <a href="admin.php?controller=admin&method=logout">
                        <i class="icon-signout glyph-icon" ></i>
                        <span class="font-bold" id='logout'>退出</span></a>
                </li>
            </ul>
        </div>
        <div class="ad_setting">
            <a class="ad_setting_a" >
                <span>
                <select style="font-size:15px;color:#8d8d8d" id = "weiIDSelect" onchange="getWeiID();">
                    {foreach from=$weixinInfo item = foo}
                    <option value=<?php echo ($foo["id"]); ?>><?php echo ($foo["weixinName"]); ?></option>
                    {/foreach}
                </select>
                </span>
            </a>
        </div>
        <div class="ad_setting">
            <a class="ad_setting_a" href="javascript:; ">
                <i style="font-size: 16px"></i>
                <span >当前公众号:</span>
            </a>
        </div>
    </div>
</header>
<!--顶部结束-->
<!--菜单-->
<div class="layout_left_menu">
    <ul id="menu">
        <li class="childUlLi">
            <a href="tpl/admin/main.html"  target="menuFrame"><i class="glyph-icon icon-home"></i>首页</a>
            <ul>
                <li><a target="menuFrame" href="javascript:void(0)"><i class="glyph-icon icon-chevron-right"></i>管理公众号</a>
                    <ul>
                        {if $weixinID}
                        <li><a target="menuFrame" href="javascript:void(0)">
                                <i class="glyph-icon icon-chevron-right2"></i><?php echo ($weixinName); ?>
                            </a>
                            <ul>
                                <li><a href="../../Admin/forWexinID/weixinIDAddNew.php?weixinID=<?php echo $weiInfo['id'];?>" target="menuFrame">
                                        <i class="glyph-icon icon-chevron-right3"></i>编辑公众号基本设置</a>
                                </li>
                                <li><a href="../../Admin/forEventReply/eventReplySet.php?weixinID=<?php echo $weiInfo['id'];?>" target="menuFrame">
                                        <i class="glyph-icon icon-chevron-right3"></i>进入活动相关设置</a>
                                </li>
                                <li><a href="../../Admin/forWexinID/menuSet.php?weixinID=<?php echo $weiInfo['id'];?>" target="menuFrame">
                                        <i class="glyph-icon icon-chevron-right3"></i>自定义菜单设置</a>
                                </li>
                            </ul>
                        </li>
                        {else}
                                <li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;尚未设置公众号信息!</a></li>
                        {/if}
                    </ul>
                    <li><a href="../../Admin/forWexinID/weixinIDAddNew.php" target="menuFrame">
                            <i class="glyph-icon icon-chevron-right"></i><font color="#FF0000">添加新公众号</font> </a>
                    </li>
                    <li><a href="../../Admin/forWexinID/editWeixinID.php" target="menuFrame">
                            <i class="glyph-icon icon-chevron-right"></i>编辑公众号</a>
                    </li>

                    <li><a href="tpl/admin/adminSetMuneClass.html" target="menuFrame"><i class="glyph-icon icon-chevron-right"></i>菜单分类设置</a>
                    </li>
            </ul>
        </li>
        <li class="childUlLi">
            <a target="menuFrame" href="javascript:void(0)"> <i class="glyph-icon icon-reorder"></i>会员管理</a>
            <ul>
                {if $isEventListExist }

                    <li><a href="admin.php?controller=weixin&method=showVipBaseInfo" target="menuFrame">
                            <i class="glyph-icon icon-chevron-right"></i>初始化设置</a></li>
                    <li><a href="admin.php?controller=weixin&method=showBaseInfo" target="menuFrame">
                            <i class="glyph-icon icon-chevron-right"></i>签到<?php echo ($weixinName); ?></a></li>

                {else}

                    <li><a target="menuFrame">
                            <i class="glyph-icon icon-chevron-right"></i>尚未设置活动，请设置！</a></li>
                {/if}

            </ul>
        </li>
        <li class="childUlLi">
            <a target="menuFrame" href="javascript:void(0)"> <i class="glyph-icon  icon-location-arrow"></i>活动设置</a>
            <ul>
                {if $isEventListExist}
                    {foreach from=$eventNameArr item = foo}
                        {if strstr($foo,"答题") }
                            <li><a target="menuFrame" href="javascript:void(0)">
                                    <i class="glyph-icon icon-chevron-right"></i><?php echo ($foo); ?></a>
                                    <ul>
                                        <li><a href="../../Admin/forAnswer/question_search.php" target="menuFrame">
                                                <i class="glyph-icon icon-chevron-right2"></i>结果查询</a>
                                        </li>
                                        <li><a href="../../Admin/forAnswer/question_master_manager.php" target="menuFrame">
                                                <i class="glyph-icon icon-chevron-right2"></i>主题信息</a>
                                        </li>
                                        <li><a href="../../Admin/forAnswer/question_manager.php" target="menuFrame">
                                                <i class="glyph-icon icon-chevron-right2"></i>题目信息</a>
                                        </li>
                                    </ul>
                            </li>
                        {else}
                        <?php echo ($index = $smarty["foreach"]["foo"]["index"]); ?>
                        <li><a href=<?php echo ($smarty["foreach"]["foo"]["index"]); ?> target="menuFrame">
                                <i class="glyph-icon icon-chevron-right"></i><?php echo ($foo); ?></a>
                        </li>
                        {/if}
                    {/foreach}
                {else}
                    <li><a target="menuFrame" href="javascript:void(0)">
                            <i class="glyph-icon icon-chevron-right"></i>尚未设置活动，请设置！</a>
                    </li>
                {/if}
            </ul>
        </li>
        <li class="childUlLi">
            <a target="menuFrame" href="javascript:void(0)"> <i class="glyph-icon icon-reorder"></i>用户管理</a>
            <ul>
                {if $userName eq "gokayuwu"}
                    <li><a href="admin.php?controller=admin&method=showUserInfo" target="menuFrame">
                        <i class="glyph-icon icon-chevron-right"></i>管理员权限用户查询</a>
                    </li>
                    <li><a href="tpl/admin/addUserByAdmin.html" target="menuFrame">
                        <i class="glyph-icon icon-chevron-right"></i>管理员权限用户添加</a>
                    </li>
                {/if}
                <li><a href="tpl/admin/adminEdit.html" target="menuFrame">
                        <i class="glyph-icon icon-chevron-right"></i>修改密码</a></li>
            </ul>
        </li>
        <li class="childUlLi">
            <a target="menuFrame" href="javascript:void(0)"> <i class="glyph-icon icon-reorder"></i>查询功能</a>
            <ul>
                {if $isEventListExist}
                    <li><a href="admin.php?controller=forSearchInfo&method=showVipInfoList" target="menuFrame">
                            <i class="glyph-icon icon-chevron-right"></i>会员信息查询</a></li>
                    <li><a href="tpl/admin/forWeixin/getDailyCode.html" target="menuFrame">
                            <i class="glyph-icon icon-chevron-right"></i>查询签到码</a></li>
                    <li><a href="admin.php?controller=forSearchInfo&method=showQAClassInfoList" target="menuFrame">
                        <i class="glyph-icon icon-chevron-right"></i>答题分类查询</a></li>
                    <li><a href="admin.php?controller=forSearchInfo&method=showQuestionOkCountList" target="menuFrame">
                            <i class="glyph-icon icon-chevron-right"></i>全答对时间排行</a></li>
                    <li><a href="admin.php?controller=forSearchInfo&method=getExchangeListCon" target="menuFrame">
                            <i class="glyph-icon icon-chevron-right"></i>奖品兑换情况查询</a>
                    <li><a href="tpl/admin/forExchange/exchange.html" target="menuFrame">
                            <i class="glyph-icon icon-chevron-right"></i>兑奖信息查询</a>
                    <li><a href="../../Admin/forForwardingGift/forwardingGiftInfoSearch.php" target="menuFrame">
                            <i class="glyph-icon icon-chevron-right"></i>转发有礼查询</a>
                    <li><a href="../../Admin/forHongbao/hongbaoInfoSearch.php" target="menuFrame">
                            <i class="glyph-icon icon-chevron-right"></i>红包测试</a>
                    <li><a href="../../Admin/forFlowerCity/flowerCityManger.php" target="menuFrame">
                            <i class="glyph-icon icon-chevron-right"></i>印章商城</a>
                    </li>
                {else}
                    <li><a target="menuFrame" href="javascript:void(0)">
                            <i class="glyph-icon icon-chevron-right"></i>尚未设置活动，请设置！</a>
                    </li>
                {/if}
            </ul>
        </li>
    </ul>
</div>
<!--菜单-->
<div id="layout_right_content" class="layout_right_content">
    <div class="mian_content">
        <div id="page_content">
            <iframe id="menuFrame" name="menuFrame" src="tpl/admin/main.html" style="overflow:visible;"
                scrolling="yes" frameborder="no" width="100%" height="100%"></iframe>
        </div>
    </div>
</div>
<footer>
    <div class="layout_footer">
        <p>Copyright © 2015-2016 - 臭臭喵工作室</p>
    </div>
</footer>

<script>
$(document).ready(function(){
    var weiID = "<?php echo ($weixinID); ?>";
    if (weiID){
        $("#weiIDSelect ").val(weiID);
    }
});
</script>
</body>
</html>