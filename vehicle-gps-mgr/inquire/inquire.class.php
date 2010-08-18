<?php
class Inquire extends BASE
{
	
	
	/**
	*		查询所有车辆
	*		@param $wh 条件 $sidx 字段 $sord 排序 $start&$limit 取值区间
	*		@return no
	*/
	function get_all_vehicles()	{
		$this->sql = "select id, number_plate from vehicle_manage where company_id = ".get_session("company_id");
		return $this->data_list = $GLOBALS["db"]->query($this->sql);
	}
	
	/**
	 *  查询历史发布消息
	 *  @param $begin_date开始日期，$end_date结束日期
	 */
	function get_history_info($wh="",$sidx="",$sord="",$start="",$limit="",$begin_date,$end_date){
		$user_id = get_session("user_id");
		if($wh!=""){
			$this->sql = "select info.*,u.login_name from info_issue info left join user u on info.issuer_id=u.id ".$wh." and (info.issue_time >='".$begin_date."' and info.issue_time<='"
		             .$end_date."') and info.issuer_id=".$user_id." order by ".$sidx." ". $sord." LIMIT ".$start." , ".$limit;
		}else{
			$this->sql = "select info.*,u.login_name from info_issue info left join user u on info.issuer_id=u.id where (info.issue_time >='".$begin_date."' and info.issue_time<='"
		             .$end_date."') and info.issuer_id=".$user_id." order by ".$sidx." ". $sord." LIMIT ".$start." , ".$limit;
		}
		return $this->data_list = $GLOBALS['db']->query($this->sql);
		}
		
	/**
	 *  查询历史发布消息总数
	 *  @param $begin_date开始日期，$end_date结束日期
	 */
	function get_history_info_count($begin_date,$end_date){
		$user_id = get_session("user_id");
		$this->sql = "select count(*) from info_issue  where (issue_time >='".$begin_date."' and issue_time<='"
		             .$end_date."') and issuer_id=".$user_id;
		 $count = $GLOBALS['db']->query_once($this->sql);
		 return $count[0];
		}
		
	/**
	 *  信息类型解析
	 *  $type 信息类型
	 */
	function change_type($type){
		$comm_setting_path = $all ["BASE"] . "xml/comm_setting.xml";
		$dataMapping = new Data_mapping_handler ( $comm_setting_path );//从xml文件中映射相应的数据库字段值
		$test = $dataMapping->getMappingText("info_issue","type",$type);
		return $test;
	}
	
	/**
	 * 检查车辆列表是否位于指定的区域内
	 * @param unknown_type $vehicle_list
	 * @param unknown_type $areaInfo
	 */
	function check_in_area(&$vehicle_list, $areaInfo, $hour_list){
		$vehicle_in_area = array();
		
		foreach($hour_list as $value_h){
			$gps_info_path = $GLOBALS["all"]["BASE"]."/log/".$value_h.".log";
			if(!file_exists($gps_info_path)){
				continue;
			}
			
			for($i=0;$i<count($vehicle_list);$i++){
				if($this->is_vehicle_in_area($vehicle_list[$i], $areaInfo, $gps_info_path, $value_h)){
					array_push($vehicle_in_area, $vehicle_list[$i]);
					array_splice($vehicle_list,$i,1); 
				    $i--; 
				}
			}
			
		}
		
		return $vehicle_in_area;
	}
	
	/**
	 * 检查某辆车是否位于指定的区域内
	 * @param unknown_type $vehicle_id
	 * @param unknown_type $areaInfo
	 */
	function is_vehicle_in_area($vehicle_id, $areaInfo, $gps_info_path, $time){
		require_once 'traceInfo.php';
		$company_id = get_session("company_id"); //获取当前公司ID
		$parser = new Position_parser($company_id,$gps_info_path,$vehicle_id, $time);
		return $parser->is_in_area($areaInfo);
	}
	
}
?>
