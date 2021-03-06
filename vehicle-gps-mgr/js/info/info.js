initStatus();

function initStatus(){
	$(".info_select").show();
	$("#info_Commit").show();
	$("#da_user").show();
	
	$("#area_Commit").hide();
	$("#area_begin_time").hide();
	$("#area_end_time").hide();
	$("#issue_map").hide();
	$("#add_input").hide();
	$("#city_mode").hide();
	
	if($("#is_hidden").val()==1){
		$("#car").hide();
		$("#area_issue").hide();
		$("#add_user").hide();
		$("#delete_user").hide();
	}
}

function returnInfo(){
	resetDialogSize(300,390);
	initStatus();
}

//填充邮箱列表
function fillMailList(str){
str = str.substr(0,str.length-1);
	if(str.length!=0){
		$.post("index.php?a=203&character="+str,function(data){
			if(data != "|"){
				var old_array=data.split("|");
				array = undulpicate(old_array);//去除数组中的重复元素
				for(var i=0;i<array.length-1;i++){
					if(check_email(array[i])){
						$("#email_list")[0].options.add(new Option(array[i],array[i])); 
					}else{
						alert("邮箱:"+array[i]+" 格式错误")
					}
				}
			}
		});
  	}
}

//去除数组中重复的元素
function undulpicate(array){  
    for(var i=0;i<array.length;i++) {  
        for(var j=i+1;j<array.length;j++) {  
            //注意 ===  
            if(array[i]===array[j]) {  
                array.splice(j,1);  
                j--;  
            }  
        }  
    }  
    return array;  
} 

function resetDialogSize(width,height){
	$( "#operation" ).dialog( "option", "width", width );
	$( "#operation" ).dialog( "option", "height", height );
	$( "#operation" ).dialog({ position : "center" });
}

//载入所有车辆
$("#car").click(function(event) {
	$(".info_select").show();
	$("#info_Commit").show();
	$("#area_Commit").hide();
	$("#issue_map").hide();
	$("#infodiv").show();
	$("#list_user").show();
	$("#da_user").show();
	$("#area_begin_time").hide();
	$("#area_end_time").hide();
	
	resetDialogSize(800,400);
	$("#sendNews").hide();
	$("#operation").mask("车辆信息载入中...");
	
	$.post("index.php?a=1",function(data){
		$("#operation" ).unmask();
		$("#infodiv").html(data);
		$("#sel_vehicle_commit").click(function(){
				 	var vehicles = $(".vehicle:checked");
			     	var str="";
			     	vehicles.each(function(i){
			         	str = str+$(this).val()+",";
			      	});
			     	
			      	fillMailList(str);
			      	$("#infodiv").html("");
			      	$("#infodiv").css("height","1");
			      	
			     	resetDialogSize(300,390);
			     	$("#sendNews").show();
			     	initStatus();
		});
	});
});

//加载区域发布时的地图
$("#area_issue").click(function() {
	$("#city_mode").show();
	$("#info_Commit").hide();
	$(".info_select").hide();
	$("#da_user").hide();
	$("#add_input").hide();
	$("#area_Commit").show();
	$("#infodiv").hide();
	$("#issue_map").show();
	$("#list_user").hide();
	$("#area_begin_time").show();
	$("#area_end_time").show();
	$("#begin_time_area").val(getNowFormatDate()); //生效时间赋默认值
	$("#end_time_area").val(day_date()); //失效时间赋默认值
	$("#issue_frame").attr("src","templates/51map.html?a=3"); //iframe加载地图
});

$("#addUser").click(function(){
	resetDialogSize(300,440);
	$("#add_input").show();
});

$("#cancle_commit").click(function(){
	resetDialogSize(300,390);
	$("#add_input").hide();
});

//删除驾驶员邮箱
$("#deleteUser").click(function(){
	//如果没有选择邮箱则提示
	if($("#email_list")[0].selectedIndex==null || $("#email_list")[0].selectedIndex==-1){
		alert("请选择驾驶员邮箱");
		return false;
	}
	
	while($("#email_list")[0].selectedIndex!=null && $("#email_list")[0].selectedIndex!=-1){
		var index = $("#email_list")[0].selectedIndex;
		$("#email_list")[0].options.remove(index);
	}
});

//手动添加手机邮箱
$("#add_commit").click(function(){
	var email = $("#addInput").val();
	
	if(email==null || email==""){
		alert("邮箱不能为空");
		return false;
	}
	
	if(check_email(email)==false){
		alert("格式错误");
		return false;
	}
	
	for(var i = 0;i < $("#email_list")[0].length;i++){
		if(email == $("#email_list")[0].options[i].text){
			alert("已有该邮箱");
			return false;
		}
	}
	
	$("#add_input").hide();
	$("#email_list")[0].options.add(new Option(email,email));
});

$(":button").button();

/**
 * 正则表达式检验邮箱地址格式
 */
function check_email(email){
	var reg = /^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{2,3}){1,2})$/;
    return reg.test(email);
}

/*发布信息*/
function subInfo(){	
	var content=document.getElementById("info_content").value;
	if(""==content) {
		alert("请输入信息内容");
		return false;
	}
	var str="";
	var datalist=document.getElementById('email_list');
    for (i=0;i<datalist.length;i++) {
		if(i!=0){
         str=str+datalist.options[i].value+"~";
		}
    }
  
    str=str+content+"~[["+content+"]]~";
    $.post("index.php",
		  {"a":202,"email_data":str,"content":content,"is_area_info":0},
		  function(data){
			  if("success"==data){
				  alert("信息已发出");
			  }else{
				  alert("信息发出失败");
				  return;
			  }
		  },"text");

  //保存已发布的信息
    $.post("index.php",{"a":204,"is_area_info":0,"content":content},function(data){},"text");
}

/*区域发布信息*/
function subArea(){	
	$("#operation" ).mask("处理中...");
	var content=document.getElementById("info_content").value; //获取信息内容
	var begin = document.getElementById("begin_time_area").value; //获取生效时间
	var end = document.getElementById("end_time_area").value; //获取失效时间
	
	if(""==content){
		$("#operation" ).unmask();
		alert("请输入信息内容");
		return false;
	}
	
	if(begin>=end){
		alert("失效时间不能小于生效时间");
		$("#operation" ).unmask();
		return false;
	}

	/*获取经纬度范围*/
	var lonMin = document.getElementById("issue_frame").contentWindow.document.getElementById("lonMin").value;
	var latMin = document.getElementById("issue_frame").contentWindow.document.getElementById("latMin").value;
	var lonMax = document.getElementById("issue_frame").contentWindow.document.getElementById("lonMax").value;
	var latMax = document.getElementById("issue_frame").contentWindow.document.getElementById("latMax").value;

	$.post("index.php",{"a":204,"content":content,"is_area_info":1,"begin_time":begin,"end_time":end},function(data){
		 $.post("index.php",{"a":205,"info_id":data,"lon":lonMin,"lat":latMin},function(data_one){
				$.post("index.php",{"a":205,"info_id":data,"lon":lonMax,"lat":latMax},function(data_two){
					$.post("index.php",{"a":206,"first_id":data_one,"second_id":data_two},function(result){
						if("ok"==result){
								$("#operation" ).unmask();
								alert("发布成功");
							}else{
								$("#operation" ).unmask();
								alert("发布失败");
							}
						});
					});
		        });
	});
}

//区域发布信息时开始日期显示
$("#begin_time_area").datetimepicker({
	 ampm: false,//上午下午是否显示  
	 timeFormat: 'hh:mm:ss',//时间模式  
	 stepHour: 1,//拖动时间时的间隔  
	 dateFormat:"yy/mm/dd", //日期格式设定  
	 showHour: true,//是否显示小时，默认是true  
	 showMinute:false,
	 showSecond:false,
	 createButton:false
});	

//区域发布信息时结束日期显示
$("#end_time_area").datetimepicker({
	 ampm: false,//上午下午是否显示  
	 timeFormat: 'hh:mm:ss',//时间模式  
	 stepHour: 1,//拖动时间时的间隔  
	 dateFormat:"yy/mm/dd", //日期格式设定  
	 showHour: true,//是否显示小时，默认是true  
	 showMinute:false,
	 showSecond:false,
	 createButton:false
});

//获取系统当前时间并格式化为yyyy/mm/dd hh:mm:ss
function getNowFormatDate()
{
   	var day = new Date();

   	var Year = 0;
   	var Month = 0;
   	var Day = 0;
	var CurrentDate = "";
   //初始化时间
   	Year= day.getFullYear();
   	Month= day.getMonth()+1;
   	Day = day.getDate();
   	Hour = day.getHours();
 	Minute = day.getMinutes();
	Second = day.getSeconds();
   	CurrentDate += Year + "/";
   	if (Month >= 10){
    	CurrentDate += Month + "/";
   	}else{
    	CurrentDate += "0" + Month + "/";
   	}
   	if (Day >= 10 ){
    	CurrentDate += Day +" ";
   	}else{
    	CurrentDate += "0" + Day+" " ;
   	}
   	if(Hour>=10){
		CurrentDate+=Hour+":";
	}else{
		CurrentDate+="0"+Hour+":";
   	}if (Minute >= 10 ){
    	CurrentDate += Minute +":";
   	}else{
    	CurrentDate += "0" + Minute+":" ;
   	}
   	if(Second>=10){
		CurrentDate+=Second;
	}else{
		CurrentDate+="0"+Second;
   	}
   	return CurrentDate;
}

	//获取失效天数
	$("#issue_day").change(function(){
		$("#end_time_area").val(day_date());
	});

	/**
	 * 失效时间天数转换成当时的时间
	 */
	function day_date(){
		var day = parseFloat($("#issue_day option:selected").val());
		var begin_time = $("#begin_time_area").val();
		var begin_day = parseFloat(begin_time.substr(8,2));
		var differ = begin_day+day;

		var year = parseFloat(begin_time.substr(0,4));
		var month = parseFloat(begin_time.substr(5,2));

		var more_months = new Array(1,3,5,7,8,10,12);

		var end_day= 0;
		var end_month = 0;
		var end_year = 0;

		if(month==2){
			end_year = year;
			if(differ>29){
				end_month = 3;
				if(year%4!=0){
					end_day = differ-28;
					}else{
					end_day = differ-29;				
					}
				}else if(differ==29){
					if(year%4==0){
						end_day = differ;
						end_month = 2;
						}else{
							end_day = 1;
							end_month = 3;	
							}
					}else{
						end_day = differ;
						end_month = 2;
						}
			}else{	
				if(differ>31){

					for(var i =0;i<more_months.length;i++){
						if(month==more_months[i]){
							end_day = differ-31;
							if(month<12){
								end_month = month+1;
								end_year = year;
								}else{
									end_month = 1;
									end_year = year+1;
								}
							}
						}

					if(end_month==0){
							end_day = differ-30;
							end_month = month+1;	
							end_year = year;						
					}
				}else if(differ==31){
					for(var i =0;i<more_months.length;i++){
						if(month==more_months[i]){
							end_day = differ;
							end_month = month;
							end_year = year;
							}
						}

					if(end_month==0){
						end_day = differ-30;
						end_month = month+1;	
						end_year = year;						
				  }
				}else{
					end_day = differ;
					end_month = month;
					end_year = year;
					}
		}	

		if(end_month<10){
			end_month = "0"+end_month;
			}
		if(end_day<10){
			end_day = "0"+end_day;
			}
		var end_time = end_year+"/"+end_month+"/"+end_day+" "+begin_time.substr(11);
		return end_time;
		}
