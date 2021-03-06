/**
 * commpany  秦运恒  
 * date  2010-08-25 15:02
 * function 首页脚本函数库
 * author 叶稳
 * update 
 * modifier
 */
	var map = null; //历史轨迹地图对象 
	var vehicle_id = -1; //车辆ID
	var speed = 1000;  //速度/ms
	var progress_length = 0; //历史轨迹时间队列数组长度
	var cur_progress = 0; //当前进度
	var data_queue_state = 1;  //数据队列状态，0 非新数据状态  1 新数据状态 
	var data_queue_point_second = 0; //数据队列是否是第二个点  0 非   1 真
	var second_longitude = -1; //数据队列第二个点经度
	var second_latitude = -1; //数据队列第二个点纬度
	var old_longitude = -1;//旧点经度
	var old_latitude = -1;//旧点纬度
	/**
	 * 区域自动匹配用户查看设置
	 * 0  非匹配
	 * 1  匹配
	 */
	var chanage_state = 0; 
	
	/***
	 * 操作状态 :处理画历史轨迹操作状态    
	 * 	 'normal' 正常  
	 * 	 'suspend' 暂停 
	 * 	 'stop' 停止 
	 */
	var state = "stop"; 
	var marker;   //地图标记对象
	
	
	var leftOffsetRatio = 0.05;  //	矩形左间距
	var rightOffsetRatio = 0.1;  //	矩形右间距
	var upOffsetRatio = 0.05;    //	矩形上间距
	var downOffsetRatio = 0.1;   //	矩形下间距
	
	history_map();
	  
	/**
	 * 初始化历史轨迹
	 */
	function history_map(){
		
		//因为地图上的进度条可能会影响折线的事件触发，因此先禁止进度条的显示
		window._LT_map_disableProgressBar=true;	
		map=new LTMaps("51map");
		map.cityNameAndZoom( "beijing" , 13);
		var standControl = new LTSmallMapControl();
		map.addControl(standControl);
	 	map.handleMouseScroll();
		//绑定事件注册
		LTEvent.addListener(map,"dblclick",onDblHistoryClick);
		
		var pre_id = parent.document.getElementById("pre_vehicle_id").value;
		
		if(pre_id != ""){
			vehiclePosition(pre_id);
		}
	}
	
	var moveLsitener;
	
	/**
	 * 定义在双击的时候执行的函数
	 */
	function onDblClick(){
		//因为系统默认双击的时候会将地图定位到中心，因此，只需要定义地图在定位到中心完成之后放大地图即可
		moveLsitener=LTEvent.addListener(map,"moveend",onMoveEnd);
	}
	/**
	 * 定义地图在定位到中心完成之后执行的函数
	 */
	function onMoveEnd(){
		LTEvent.removeListener(moveLsitener);//删除事件注册
		map.zoomIn();//放大地图
	}
	 
	
	/**
	 * 定义在双击的时候执行的函数
	 */
	function onDblHistoryClick(){
		//因为系统默认双击的时候会将地图定位到中心，因此，只需要定义地图在定位到中心完成之后放大地图即可
		moveLsitener=LTEvent.addListener(map,"moveend",onMoveEnd);
	}
	
	
	/**
	 * 运行历史轨迹
	 */
	function runHistoryTrack(){  
		//如果当前画线数组还存在数据，继续执行画线
		if(window.parent.drawLine_arr != null  && state === "normal"){
			if(window.parent.drawLine_arr.length > 0 ){ 
				newDrawLine();
			} 
		}else{ //当画线队列数组不存在数据时，执行时间段取点  
			 
			if(window.parent.arr_history.length>0){ //当时间数组还存在时间，连续查询 
				cur_progress ++;
				//按比例计算进度条进度数值
				var progress_val = round((cur_progress/progress_length)*100,0);
				window.parent.progress_assignment(progress_val);
				var time = window.parent.arr_history[0];  
				window.parent.arr_history.shift();//删除已查询日期
				//查询
				drawHistoryTrack(time,vehicle_id);
			}
			
		}
	}
	
	/**
	 * 清除所有标点，重新加载新数据
	 */
	function clearOverLay(){
		map.clearOverLays(); 
	}
	 
	//等待线程
	function wait(){
		
		setTimeout(function(){
			if(window.parent.drawLine_arr!=null){//唤醒线程
				if(window.parent.drawLine_arr.length<=0){ 
					window.parent.drawLine_arr = null;
					runHistoryTrack(); 
				}else{ //等待
					wait();
				} 
			}
			},1000);
	}
	 
	/**
	 * 画历史轨迹路径
	 * @param {Object} time  画数据时间点
	 * @param {Object} vehicle_id 车辆编号
	 */
	function drawHistoryTrack(time,vehicle_id){   
		var space = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";   
		$("#51mask").mask(space+"查询中...<br>"+format_time(time,"yyyy/MM/DD/HH"));  

		$.ajax({
			type:"POST",
			url:window.parent.host+"/index.php?a=353&time="+time+"&vehicle_id="+vehicle_id, 
			dataType:"json",
			success:function(data){  
			if(state == "stop") return false; //停止状态不能运行
			 
			$("#51mask").unmask();
			
			/**
			 * 日期请求查询路线为空操作
			 */
			if(data==0 || data == null || data == ""){ 
			   if(window.parent.arr_history == null) return false; //查询时间队列为空 
			   if(window.parent.arr_history.length>0){ //如果日期数组存在日期数据，连续请求查询
				   runHistoryTrack();
					return false;
				}else //否则进入停止状态
					return end_history_line();
			}
			/**
			 * 日期请求查询路线存在操作
			 */	
			if(data!=null) 
				data_queue_state = 1; //设置新数据状态
			
			//路线赋值
			window.parent.drawLine_arr = data; 
			 
			//新画路线
			newDrawLine();
			
			//线程等待
			wait();
			
		 }
		}); 
	}
	/**
	 * 画新路线
	 */
	function newDrawLine(){ 
		 
		if(window.parent.drawLine_arr!=null && state==="normal"){ //等于‘正常’状态
			var length = window.parent.drawLine_arr.length; 
			if(length>0){
				 	
			 		var points = new Array();
			 		 
			 		var longitude = window.parent.drawLine_arr[0][0];  //经度
			 		var latitude = window.parent.drawLine_arr[0][1];  //纬度
			 		var direction = window.parent.drawLine_arr[0][2]; //方向
			 		var vehicle_speed = window.parent.drawLine_arr[0][3]; //车辆速度
			 		var color = window.parent.drawLine_arr[0][4]; //颜色
					var img_path =  window.parent.drawLine_arr[0][5]; //图片路径
					var location_time = window.parent.drawLine_arr[0][6]; //定位时间
			 		var newLongitude = -1; //新线点经度
			 		var newLatitude = -1; //新线点纬度
			 		var point_index = -1; //数据点下标
			 		 
			 		//初始始画线点1
			 		switch(parseInt(data_queue_state)){
						case 0: //非新数据队列状态
							
							point_index = 1;//非新数据队列状态，队列点2为终点线坐标
							
							if(data_queue_point_second==1){ 
								points.push( new LTPoint(second_longitude,second_latitude));//画线开始点
								data_queue_point_second = 0;
							}else
								points.push( new LTPoint(longitude,latitude));//画线开始点
							break;
						case 1: //新数据队列状态
							
							point_index = 0;//新数据队列状态 ，队列点1为终点线坐标
							data_queue_point_second = 1;
							
							if(old_longitude === -1 && old_latitude === -1)  
								points.push( new LTPoint(longitude,latitude));//新路线开始点
							else
								points.push( new LTPoint(old_longitude,old_latitude));//新路线连接点
							
							break;
			 		}
			 		
			 	/**
			 	 * 设置画线下一个点经纬度
			 	 */	
				if(length>1){ //队列数据大于1
					 
					newLongitude = window.parent.drawLine_arr[point_index][0];//线的终点经度
					newLatitude = window.parent.drawLine_arr[point_index][1]; //线的终点纬度
					
					second_longitude = newLongitude;
					second_latitude = newLatitude;
					
					points.push( new LTPoint(newLongitude,newLatitude));
				}else{ //队列中 只存在最后一个点坐标
					
					//当前起始点为终点
					newLongitude = longitude;
					newLatitude = latitude;
					points.push( new LTPoint(newLongitude,newLatitude));
					
					//保存最后经纬度点
					old_longitude = newLongitude;
					old_latitude = newLatitude; 
					
					//当历史轨迹画完之后，回到初始状态
					if(window.parent.arr_history.length <=0 || window.parent.arr_history == null || window.parent.arr_history == ""){
						return end_history_line();
					}
				} 
				
				//改变数据队列状态 为非新数据队列状态
				if(data_queue_state ===1)
						data_queue_state = 0;
				
				//删除并返回数组的第一个元素
				window.parent.drawLine_arr.shift(); 
				//调用画线函数 
				drawRunLine(points,newLongitude,newLatitude,direction,color,vehicle_speed,img_path,location_time,newLongitude,newLatitude);
			} 
	 }
}
	/**
	 * 历史轨迹请求画完，返回初始状态
	 * @return 停止运行 返回上一级操作
	 */
	function  end_history_line(){
		window.parent.empty_cur_vhicle_history();
		
		state = "stop";
		return false;
	}
	/**
	 * 运行画线函数
	 * @param {Object} points 画线点
	 * @param {Object} longitude 第一个经度
	 * @param {Object} latitude 第一个纬度
	 * @param {Object} direction 方向
	 * @param {Object} color 颜色
	 * @param {Object} vehicle_speed 车辆速度 
	 * @param {Object} img_path 图片路径
	 * @param {Object} location_time 定位时间
	 * @param {Object} newLongitude 第二个经度
	 * @param {Object} newLatitude 第二个纬度
	 */
	function drawRunLine(points,longitude,latitude,direction,color,vehicle_speed,img_path,location_time,newLongitude,newLatitude){  
		 //居中点
		var point = new Array();
		point.push(new LTPoint(newLongitude,newLatitude)); 
		
		/**
		 * 区域自动匹配用户查看设置
		 * 1 匹配
		 * 0 非匹配
		 */
		switch (parseInt(chanage_state)) {
			case 1: //匹配
				
				var bound = map.getBoundsLatLng(); //矩形范围对象 
				var xmin = bound.getXmin(); // 最小经度
				var ymin = bound.getYmin(); // 最小纬度
				var xmax = bound.getXmax(); // 最大经度
				var ymax = bound.getYmax(); // 最大纬度 
				
				var longitudeRange = xmax - xmin; // 矩形经度范围
				var latitudeRange = ymax - ymin;  // 矩形纬度范围
				var current_zoom = map.getCurrentZoom();  //获取当前用户操作地图比例  
				
				//验证车辆当前位置是否超出范围
				var isOutofMapRange = (((longitude - xmin) / longitudeRange) <= leftOffsetRatio) ||
				(((xmax - longitude) / longitudeRange) <= rightOffsetRatio) ||
				(((latitude - ymin) / latitudeRange) <= downOffsetRatio) ||
				(((ymax - latitude) / latitudeRange) <= upOffsetRatio);
				
				if (isOutofMapRange) {//超出范围
					//重新获得最佳位置
					map.getBestMap(point);
					//如果当前重新获得最佳位置比例非等于以前用户操作比较，还原用户操作比例
					if(current_zoom !=map.getCurrentZoom())
						map.zoomTo(current_zoom==0?1:current_zoom);
				}
				break;
			case 0:	//非匹配
				chanage_state = 1;
				map.getBestMap(point);
				map.zoomTo(map.getCurrentZoom()==0?1:map.getCurrentZoom()); 
				break;
		} 
		
		var polyLine = new LTPolyLine(points);
		polyLine.setLineColor("#"+color);	//设置折线颜色 
		polyLine.setLineStroke(3);	//设置折线线宽
		
		polyLine.setLineArrow("None","None");
		//将折线添加到地图
		map.addOverLay( polyLine );
		
		//删除车辆显示上一个点对象
		map.removeOverLay(marker);
		
		//新建车辆显示新一个点对象(注：实现车辆当前开动效果)
		marker = new LTMarker( new LTPoint(longitude,latitude),new LTIcon(window.parent.host+"/"+img_path));
		
		/**
		 * 将最新点的信息数据载入‘定位信息’窗口中。
		 */
		$("#location_info").css("display","inline");

		$("#direction",parent.document).html(direction_change(direction));
		$("#speed",parent.document).html(vehicle_speed);
		$("#longitude",parent.document).html(around(longitude));
		$("#latitude",parent.document).html(around(latitude));
		$("#location_time",parent.document).html(format_time(location_time,"yyyy/MM/DD/HH:mm:ss"));
		$("#address",parent.document).html("<a id='more' name="+longitude+" rel="+latitude+" href='javascript:history_track_frame.details();'>查看详情</a>")

		//点添入地图中
		map.addOverLay(marker); 

		setTimeout("newDrawLine();",speed);
	}
	/**
	 *  根据经纬度查看具体地址	
	 */
	function details(){ 
		  
		var long = $("#more",parent.document).attr("name");
		var lat = $("#more",parent.document).attr("rel");
		$.get(window.parent.host+"/index.php?a=503",{"longitude":long,"latitude":lat},function(data){
			 
			$("#address",parent.document).html(data.replace("[null,null]",""));
			});
	}
	
	 /**
	  *  将51地图经纬度转换成真正的经纬度	
	  *  @param v 经度或纬度
	  */
	 function around(v){
			v = v/100000;
			return v;
		}

	 /**
	  *  转换方向
	  *  @param direction 方向
	  */
	 function direction_change(direction){
		 var directions = new Array(8);
		 
		 directions['north']="北";
		 directions['east']="东";
		 directions['west']="西";
		 directions['south']="南";
		 directions['northeast']="东北";
		 directions['northwest']="西北";
		 directions['southeast']="东南";
		 directions['southwest']="西南";
		 
		return directions[direction];
			
		}  
	 /**
	  * 车辆请求定位
	  * @param {Object} str 车辆ID集合 格式"ID1,ID2,ID3,"
	  */
	function vehiclePosition(str){ 
		 $.ajax({
				type:"POST",
				url:window.parent.host+"/index.php?a=2&vehicleIds="+str, 
				dataType:"json",
				success:function(data){ 
					var length = data.length; 
					var points = new Array();
					 
					if(length>0)clearOverLay();
					
					for(var i=0;i<length;i++){        
						 
						 vehicle_id = data[0]['id']; //车辆id
						 number_plate = data[0]['number_plate']; //车牌号
						 point_longitude = data[0]['cur_longitude']; //当前经度
						 point_latitude = data[0]['cur_latitude']; //当前纬度 
						 alert_state = data[0]['alert_state'];// 告警状态
						 img_name = data[0]['cur_direction']; //图片名
						 file_path = data[0]['file_path']; //文件路径 
						
						//车辆点位置添入数组中，地图视图显示
						points.push( new LTPoint(point_longitude,point_latitude));

						//创建点对象
						marker =new LTMarker(new LTPoint(point_longitude,point_latitude),
										 	  new LTIcon(window.parent.host+"/"+file_path+"/"+img_name+".png"));

						//点添入地图中
						map.addOverLay(marker);
					}
					map.getBestMap(points);
					map.zoomTo(map.getCurrentZoom()==0?1:map.getCurrentZoom()); 
				 }
		 	});
		}
		
		/**
		 * 取消遮罩效果。
		 * 为了父窗口控制用。
		 * @return
		 */
		function cancle_mask(){
			$("#51mask").unmask();
		}