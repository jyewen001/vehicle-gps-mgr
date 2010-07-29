<?php
/** 
* 车辆处理
* @copyright		vehicle, 2010
* @author			李少杰
* @create date		2010.07.24
* @modify			修改人
* @modify date		修改日期
* @modify describe	修改内容
*/
$act = $GLOBALS["all"]["operate"];

$page = $_REQUEST['page']; // get the requested page
$limit = $_REQUEST['rows']; // get how many rows we want to have into the grid
$sidx = $_REQUEST['sidx']; // get index row - i.e. user click to sort
$sord = $_REQUEST['sord']; // get the direction
$searchfil = $_REQUEST['searchField']; // get the direction
$searchstr = $_REQUEST['searchString']; // get the direction

$par = $_REQUEST["par"];
$child = $_REQUEST["child"];

if(!$sidx) $sidx =1;

switch($act)
{
	case "list":			//加载车辆管理的html页面
		echo $db->display(null,"list");
		break;
	case "list_data":		//车辆管理html中，js文件会加载这个case，取得并输出数据
		$vehicle	= new Vehicle();
		$count = $vehicle->get_vehicle_count();

		if( $count >0 ) {
			$total_pages = ceil($count/$limit);
		} else {
			$total_pages = 0;
		}
		if ($page > $total_pages) $page=$total_pages;
		$start = $limit*$page - $limit;
		if ($start<0) $start = 0;

		//得到字段类型
		if(empty($searchfil) or empty($searchstr))
			$wh = '';
		else
		{
			$type = $vehicle->get_type($searchfil);
			$searchstr = $db->prepare_value($searchstr,$type);
			$wh = "where ".$searchfil." = ".$searchstr;
		}
		
		//得到所有车辆
		$result = $vehicle->get_all_vehicles($wh,$sidx,$sord,$start,$limit);

		$responce->page	= $page;
		$responce->total = $total_pages;
		$responce->records = $count;

		foreach($result as	$key => $val)
		{
			//对指定字段进行翻译
			$vehicle2	= new Vehicle($val['id']);
			$vehicle_group_name = $vehicle2->get_data("vehicle_group_name");
			$driver_name = $vehicle2->get_data("driver_name");
			$type_name = $vehicle2->get_data("type_name");
			$alert_state = $vehicle2->get_data("v_alert_state");
			$responce->rows[$key]['id']=$val['id'];
			$responce->rows[$key]['cell']=array($val['id'],$val['number_plate'],
																					$val['gps_id'],$vehicle_group_name,
																					$driver_name,$type_name,$val['cur_longitude'],
																					$val['cur_latitude'],$val['cur_speed'],
																					$val['cur_direction'],$alert_state,$val['color'],
																					$val['running_time']
																					//$val['backup1'],$val['backup2'],
																					//$val['backup3'],$val['backup4'],$val['create_id'],
																					//$val['create_time'],$val['update_id'],$val['update_time']
																					);
		}

		//打印json格式的数据
		echo json_encode($responce);
		break;
		
	case "operate":		//车辆修改、添加、删除
		$oper = $_REQUEST['oper'];
		//file_put_contents("a.txt",implode(',',array_keys($_REQUEST)).'--'.implode(',',$_REQUEST));exit;
		$arr["number_plate"] = $db->prepare_value($_REQUEST['number_plate'],"VARCHAR");
		$arr["gps_id"] = $db->prepare_value($_REQUEST['gps_id'],"VARCHAR");
		$arr["vehicle_group_id"] = $db->prepare_value($_REQUEST['vehicle_group_id'],"INT");
		$arr["driver_id"] = $db->prepare_value($_REQUEST['driver_id'],"INT");
		$arr["type_id"] = $db->prepare_value($_REQUEST['type_id'],"INT");
		$arr["cur_longitude"] = $db->prepare_value($_REQUEST['cur_longitude'],"INT");
		$arr["cur_latitude"] = $db->prepare_value($_REQUEST['cur_latitude'],"INT");
		$arr["cur_speed"] = $db->prepare_value($_REQUEST['cur_speed'],"INT");
		$arr["cur_direction"] = $db->prepare_value($_REQUEST['cur_direction'],"INT");
		$arr["alert_state"] = $db->prepare_value($_REQUEST['alert_state'],"INT");
		$arr["color"] = $db->prepare_value($_REQUEST['color'],"VARCHAR");
		$arr["running_time"] = $db->prepare_value($_REQUEST['running_time'],"INT");
//		$arr["backup1"] = $db->prepare_value($_REQUEST['backup1'],"VARCHAR");
//		$arr["backup2"] = $db->prepare_value($_REQUEST['backup2'],"VARCHAR");
//		$arr["backup3"] = $db->prepare_value($_REQUEST['backup3'],"VARCHAR");
//		$arr["backup4"] = $db->prepare_value($_REQUEST['backup4'],"VARCHAR");
//		$arr["create_id"] = $db->prepare_value($_REQUEST['create_id'],"INT");
//		$arr["create_time"] = $db->prepare_value($_REQUEST['create_time'],"DATETIME");
//		$arr["update_id"] = $db->prepare_value($_REQUEST['update_id'],"INT");
//		$arr["update_time"] = $db->prepare_value($_REQUEST['update_time'],"DATETIME");
		$vehicle = new Vehicle($_REQUEST['id']);
		switch($oper)
		{
			case "add":		//增加
				$vehicle->add_vehicle($arr);
				break;
			case "edit":		//修改
				$vehicle->edit_vehicle($arr);
				break;
			case "del":		//删除
				$vehicle->del_vehicle($arr);
				break;
		}
		break;
	case "select":		//下拉列表
		$p = $_REQUEST["p"];		//获得需要翻译的字段
		$vehicle = new Vehicle();
		switch($p)
		{
			case "vehicle_group_id":
				$html = $vehicle->get_select("vehicle_group","name");
				break;
			case "driver_id":
				$html = $vehicle->get_select("driver_manage","name");
				break;
			case "type_id":
				$html = $vehicle->get_select("vehicle_type_manage","name");
				break;
			case "alert_state":
				if(!$par or !$child)
				{
					$par = "vehicle_manage";
					$child = "alert_state";
				}
				$xml = new Xml($par,$child);
				$html = $xml->get_html_xml();
				break;
		}
		echo $html;
		break;
}


?>