var vel=$("#vs_stop_id").val();

jQuery("#vehicle_stop_detail").jqGrid( {
		url : "index.php?a=415&vehicle_id="+vel,
		datatype : "json",
		colNames : ['id','开车时间','停车时间','停车时间间隔'],
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
		rownumbers : true,
		rownumWidth : 60,
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
		alerttext : "请选择需要操作的数据行!"
	});