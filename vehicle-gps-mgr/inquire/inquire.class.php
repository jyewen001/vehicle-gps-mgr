<?php
class Inquire extends BASE
{
	/**
	*		查询所有车辆
	*		@param $wh 条件 $sidx 字段 $sord 排序 $start&$limit 取值区间
	*		@return no
	*/
	function get_all_vehicles()
	{
		$this->sql = "select id, number_plate from vehicle_manage where company_id = ".get_session("company_id");
		return $this->data_list = $GLOBALS["db"]->query($this->sql);
	}
	
	/**
	 *  查询历史发布消息
	 *  @param $begin_date开始日期，$end_date结束日期
	 */
	function get_history_info($begin_date,$end_date){
		$user_id = get_session("user_id");
		$this->sql = "select info.*,u.login_name from info_issue info left join user u on info.issuer_id=u.id where (info.issue_time >='".$begin_date."' and info.issue_time<='"
		             .$end_date."') and info.issuer_id=".$user_id;
		return $this->data_list = $GLOBALS['db']->query($this->sql);
		}
}
?>
