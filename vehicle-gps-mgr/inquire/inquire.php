<?php
/** 
* 信息查询
* @copyright		秦运恒, 2010
* @author			郭英涛
* @create date		2010.08.02
* @modify			修改人
* @modify date		修改日期
* @modify describe	修改内容
*/

$act = $GLOBALS["all"]["operate"];

$page = $_REQUEST['page'];

switch($act)
{
	case "main":	//填写信息内容页面
		echo $GLOBALS['db']->display(null,$act);
		break;
	case "trace":
		
		$id = $_REQUEST['vehicle_id']; //车辆ID
		$inquire_info = new Inquire();
		$vehicle_list = $inquire_info->get_all_vehicles();
		
		$options = "";
		
		foreach($vehicle_list as $value)
		{
			if($id && $value["id"] == $id)
			{
				$options = $options."<option value=".$value["id"]." selected>".$value["number_plate"]."</option>";
			}
			else
			{
				$options = $options."<option value=".$value["id"].">".$value["number_plate"]."</option>";
			}
			
		}
		
		$data["VEHICLE_LIST"] = $options;
		
		echo $GLOBALS['db']->display($data,$act);
		break;
	case "get_trace_data":
		require_once 'traceInfo.php';
		require_once 'color_mapper.php';
		
		$id = $_REQUEST['vehicle_id']; //车辆ID
		$company_id = get_session("company_id"); //获取当前公司ID  
		$time = $_REQUEST['time'];
		
		//$gps_info_path = $server_path_config["gps_info_path"]."/".$time.".log";
		$gps_info_path = $GLOBALS["all"]["BASE"]."/log/".$time.".log";
		if(!file_exists($gps_info_path)){
			echo json_encode(0);
			break;
		}
			
		
		$parser = new Position_parser($company_id,$gps_info_path,$id,$time);
		//$parser = new Position_parser("1","tracedata/2010080312.log","3"); //测试数据
		$datalist = $parser->getDataList();
		
		$point_info = array();
		$trace_info = array();
		
		$ve_status = new Vehicle_status(); 
		
		foreach($datalist as $k=>$v)
		{
			$long = $ve_status->exact_lon($ve_status->around($v->longitude,0)); //经度
			$lat = $ve_status->exact_lat($ve_status->around($v->latitude,0));//纬度
			
			$point_info[0]= $long; //经度
			$point_info[1]= $lat;  //纬度
			$point_info[2]= resolvingDirection($v->direction); //方向 
			$point_info[3]= $v->speed; //速度
			$point_info[4]= $v->location_desc; //地址
			$point_info[5]= $v->color; //颜色
			$point_info[6]= $v->img_path; //图片路径
			$point_info[7]= $v->location_time; //定位时间

			array_push($trace_info,$point_info);
		}
		
		echo json_encode($trace_info);
		break;
		
	case "get_history_info":
		$inquire = new Inquire();
		$infoes = $inquire->get_history_info($_REQUEST['begin_date'],$_REQUEST['end_date']);
		
		$response->page	= $page;
		
		foreach($infoes as	$key => $val)
		{ 
            
			$response->rows[$key]['id']=$val['id'];
			$response->rows[$key]['cell']=array($val['id'],$val['login_name'],
												$val['type'],$val['issue_time'],$val['begin_time'],$val['end_time'],
												$val['content']);
		}

		//打印json格式的数据
		echo json_encode($response);
		
		break;
		
	break;
}
?>