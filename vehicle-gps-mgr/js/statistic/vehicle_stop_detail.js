var vel=$("#vs_stop_id").val();
var begin_date = $("#vehicle_begin_data").val();
var end_date = $("#vehicle_end_data").val();

jQuery("#vehicle_stop_detail").jqGrid( {
		url : "index.php?a=415&vehicle_id="+vel+"&begin_data="+begin_date+"&end_data="+end_date,
		datatype : "json",
		colNames : ['id','开始时间','结束时间','停车时间间隔(分钟)'],
		colModel : [{
			name : 'id',
			index : 'id',
			width : 0,
			editable : false,
			hidden : true
		},{
			name : 'start_time',
			index : 'start_time',
			width : 100,
			align : "center",
			editable : false
		},{
			name : 'end_time',
			index : 'end_time',
			width : 100,
			align : "center",
			editable : false
		}, {
			name : 'stop_time',
			index : 'stop_time',
			width : 50,
			align : "center",
			editable : false
		}],
		rowNum : 10,// 初始化每页10条数据
		rowList : [ 10, 20, 30 ],// 设置每页多少条数据
		mtype : "GET",
		gridview : true,
		pager : '#vehicle_stop_detail_page',
		viewrecords : true,
		sortorder : "asc",
		caption : "车辆停用信息",
		height : 230,
		width :  800
	});
	
	jQuery("#vehicle_stop_detail").jqGrid('navGrid', '#vehicle_stop_detail_page', {
		del : false,
		add : false,
		edit : false,
		search:false,
		alerttext : "请选择需要操作的数据行!"
	});
