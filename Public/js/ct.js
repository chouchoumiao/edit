/*
	定义一个City类
	并定义一个数组类型的成变量用于指向0-3的select对象
*/
var City = function(){
	//得到#sel 下的所有 select元素
	$select = $("#sel > select");
	//循环将各个元素赋给ajax数组变量
	for(i=0;i<$select.length;i++) {
		this.ajax[i] = $select.eq(i);//eq(i)是取索引的方法
	}
}
City.prototype = {
	/*
		定义ajax成员方法
		参数说明:
			json	Json格式的数据
			index	select下拉框的索引号0表示第一个select
			selectObj	表示第一个select对象
	*/
	ajax : function(json,index,selectObj){
		//开始调用Jquery提供的Ajax方法
		$.post("../action/city",json,function(result){

            //alert(result);
			//分割从服务器端返回的数据使用其成为一个二维数组,[0]为arrchildid,[1]为areaname
			cityArray = result.split("-");
			//selectObj.attr("length","0"); 用此方法select下的option不可以清空
			//selectObj.find("option").remove();//可以清空
			selectObj.empty();//可以清空
			//定义一个type变量用于在select中显示为第几级的标识：省市镇乡
			var	type ="";
			var thisIndex = (parseInt(index));
			switch(thisIndex){
				case 0:type="省";break;
				case 1:type="市";break;
				case 2:type="区(县)";break;
				case 3:type="镇(乡)";break;
				default:type="中华人民共和国";break;	
			}
			//追加显示一个提示下拉的Option
			if( thisIndex != 3){	//第四级直接显示第一个  用于三级显示用 wujiayu
				selectObj.append("<option value=''>请选择--"+type+"</option>");
			}
			//将二维数组中的数据循环的填充到select中
			for(i=0;i<cityArray.length;i++){
				if(cityArray[i]=="") continue;
				selectObj.append("<option value=\""+cityArray[i].split("|")[0]+"\">"+cityArray[i].split("|")[1]+"</option>");	 
			} 
		});	
		
		//以下的各个判断是在页面中选择时显示或隐藏select
		if(index=='1'){
			this.cssDisplay(selectObj,"block");
			this.cssDisplay(this.ajax[2],"none");
			this.cssDisplay(this.ajax[3],"none");
		}else if(index=='2'){
			this.cssDisplay(selectObj,"block");
			this.cssDisplay(this.ajax[1],"block");
			this.cssDisplay(this.ajax[3],"none");	
		}else if(index =='3'){
			this.cssDisplay(selectObj,"block");
			this.cssDisplay(this.ajax[1],"block");
			this.cssDisplay(this.ajax[2],"block");
		}
	},
	//封装一个设置select显示或隐藏的成员方法（为了调用简便）
	cssDisplay : function(OperatorObj,status){
		OperatorObj.css("display",status);
	}
}

function getCity(selectObj){
	selectObj = $(selectObj);//将表单传过来的javascript对象转变成Jquery对象
	selectIndex = parseInt(selectObj.index()+1);//取得selectObj的索引号
	selectText = selectObj.val();//取得selectObj对值
	//创建一个City的实例对象
	$city = new City();
	/*
		如果选择的第一个提示的Option也就是value为空的option时，则后一个Option设置为不可见
		如果selectText等于空，则表示选择的就是第一个option，隐藏后并返回不往下执行了
	*/
	if(selectText=="") {
		switch(selectIndex){
			case 1:
				$city.cssDisplay($city.ajax[1],"none");
				$city.cssDisplay($city.ajax[2],"none");
				$city.cssDisplay($city.ajax[3],"none");
			break;
			case 2:
				$city.cssDisplay($city.ajax[2],"none");
				$city.cssDisplay($city.ajax[3],"none");
			break;
			case 3:
				$city.cssDisplay($city.ajax[3],"none");
			break;
			default:
			break;	
		}
		return;
	}else {
		//selectText不为空则显示下一级
		$city.ajax({arrchildid:selectText},selectIndex,$city.ajax[selectIndex]);
	}
}

$(function(){
	//设置select的样式为向左边浮动
	$("select").css("float","left");
	//创建一个City的实例对象
	$city = new City();
	//初始化时第1 2 3 个select设置为不显示
	for(i=1;i<4;i++){
		$city.cssDisplay($city.ajax[i],"none");
	}
	//调用City对象的ajax方法
	/*
		初始化显示所有一级目录，从父ID为0的开始获取
			{parentid:0}	传给Jquery框架中的Ajax的$.post()方法的Url地址（Json格式的数据）
			0	select下拉框的索引号0表示第一个select
			$city.ajax[0]	表示第一个select对象 ajax是City对象的成员属性为数组类型，$city.ajax[0]则表示第一个
	*/
	$city.ajax({parentid:0},0,$city.ajax[0]);	
})