	
				var id=0;
				/**获得24小时内未处理的告警记录**/
	 			function alertInfo(){
	 				$.post("index.php",{
						 "a":921}
						,function(data){
							
							if("-1" == data){
								$("#lamp").html("<img alt='警灯' src='images/lamp.jpg' style='height:56px; width:46px;'></img>");
								$("#alertInfo").unmask();
								$("#newAlert").html("最新告警记录");
								$("#record").html("没有未处理的告警记录");
								$("#operate").html("<a href='index?a=901'>查看更多</a>");
							}else
							{   
								var array=data.split("|");
							    id=array[0];
								$("#lamp").html("<img alt='警灯' src='images/lamp.gif' style='height:56px; width:46px;'></img>");
								$("#alertInfo").unmask();
								$("#newAlert").html("最新告警记录："+"在"+array[1]+"时间点内");
							    $("#record").html("车牌号为"+array[2]+"的告警记录为："+array[3]);
								document.getElementById("operate").style.display="block";
						     }
						 }
					);
				  setTimeout("alertInfo()",30000);
	 			}
	 			/**动态生成车辆代表的速度**/
				$(document).ready(function() {
					
					$.post("index.php",{
						 "a":5021}
						,function(data){
							
							var array=data.split("|");
							if(array.length==0){
								alert("没有数据");
							}else
							{    
								var image="";
								for(var i=0;i<array.length-1;i++){
									var data_list_min=array[i].split(",");
									
									image=image+"<div style='float:left;heigth:17px;'>" +
											    "&nbsp;&nbsp;&nbsp;<img src="+data_list_min[0]+" style='height:17px;'/>&nbsp;" 
											    +data_list_min[1]+"-"+data_list_min[2]+"</div>";
								}						
								$("#carInfor").html(image);
						    }
						});
			
				
					
					
					
					$('.jsbutton').wrapInner('<span class="hover"></span>').css('textIndent','0')								
					 .each(function () {
						$('span.hover').css('opacity', 0).hover(function () {
							$(this).stop().fadeTo(650, 1);
						}, function () {
							$(this).stop().fadeTo(650, 0);
						});
					});
					$("#vehicle").append("<div class='button_font'>选择车辆</div>");
					$("#sendInfo").append("<div class='button_font'>发布信息</div>");
					$("#searchInfo").append("<div class='button_font'>查询信息</div>");
					$("#statistic").append("<div class='button_font'>统计分析</div>");
					$("#setting").append("<div class='button_font'>设置</div>");

					alertInfo();
					$("#alertInfo").mask("处理中,请耐心等候...");
				});
				 