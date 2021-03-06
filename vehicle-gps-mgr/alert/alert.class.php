<?php

/**
 * 告警信息类
@copyright  秦运恒, 2010
 * @author 　　段贵山
 * @create date 　 2010.07.30
 */

class Alert extends BASE {
	
	public $mysel_table_name = "alert_info"; //	告警记录表
	public $user_table_name = "user"; //FK 用户表名称
	public $vehicle_manage_table_name = "vehicle_manage"; // FK 车辆管理表名称

	public $data = false; //数据
	public $data_list = false; //数据集合
	public $sql; //SQL语句
	public $message; //消息
	

	private $alert_id = false; //人员ID
	

	/**
	 * 构造函数
	 * @param $alert_id 
	 * @return no
	 */
	function Alert($alert_id = false) {
		if ($alert_id && ! empty ( $alert_id )) {
			$this->alert_id = $alert_id;
		}
	}
	
	/**
	 * 查询所有记录
	 * @param $wh
	 * @param $sidx
	 * @param $sord
	 * @param $start
	 * @param $limit
	 * @return 查询记录
	 */
	function get_all_alerts($group_id="",$vehicle_id="",$wh = "", $sidx = "", $sord = "", $start = "", $limit = "",$company_id="") {
		 if($group_id==-1){
		 	$this->sql = "select * from " . $this->mysel_table_name .$wh." and vehicle_id in 
                         (select id from vehicle_manage where company_id=".$company_id.")  order by  alert_time desc " . " LIMIT " . $start . " , " . $limit;
		 }else if($vehicle_id==-1){
		 	$this->sql ="select a_i.id,a_i.alert_time,a_i.alert_type,a_i.vehicle_id,a_i.dispose_id,a_i.dispose_opinion,a_i.description from ".
                    "((select * from alert_info) as a_i ".
                    "inner join ".
                    "(select id from   vehicle_manage where vehicle_group_id=".$group_id.") as v_m ".
                    "on v_m.id=a_i.vehicle_id)  " . $wh . " order by " . " a_i.alert_time desc " . " LIMIT " . $start . " , " . $limit; 	
		 }else{
		 	$this->sql ="select a_i.id,a_i.alert_time,a_i.alert_type,a_i.vehicle_id,a_i.dispose_id,a_i.dispose_opinion,a_i.description from ".
                    "((select * from alert_info) as a_i ".
                    "inner join ".
                    "(select id from   vehicle_manage where vehicle_group_id=".$group_id.") as v_m ".
                    "on v_m.id=a_i.vehicle_id) ". $wh ." and a_i.vehicle_id=".$vehicle_id ." order by " . " a_i.alert_time desc ". " LIMIT " . $start . " , " . $limit; 

		 }
		return $this->data = $GLOBALS ["db"]->query ( $this->sql );
	}
	
	/***
	 * 
	 * 得到总记录数
	 * @return 总记录数
	 * 
	 */
	function get_all_count($group_id,$vehicle_id,$condition,$company_id) {
	 if($group_id==-1){
		 	$this->sql = "select count(*) from " . $this->mysel_table_name. $condition ." and vehicle_id in 
                         (select id from vehicle_manage where company_id=".$company_id.")";
		 }else if($vehicle_id==-1){
		 	$this->sql ="select count(*) from ".
                    "((select * from alert_info) as a_i ".
                    "inner join ".
                    "(select id from   vehicle_manage where vehicle_group_id=".$group_id.") as v_m ".
                    "on v_m.id=a_i.vehicle_id) ".$condition; 	
		 }else{
		 	$this->sql ="select count(*) from ".
                    "((select * from alert_info) as a_i ".
                    "inner join ".
                    "(select id from   vehicle_manage where vehicle_group_id=".$group_id.") as v_m ".
                    "on v_m.id=a_i.vehicle_id) ". $condition . " and  a_i.vehicle_id=".$vehicle_id; 

		 }
		 $count = $GLOBALS ["db"]->query_once ( $this->sql );
		 return $count [0];
	}
	/**
	 * @param $id
	 * 根据vehicle_id（车辆id）
	 * 得到vehicle_manage(车辆管理表)中的车牌号
	 */
	function get_vehicle_manage_number($id = "") {
		$this->sql = "select number_plate from " . $this->vehicle_manage_table_name . " where id=" . $id;
		$count = $GLOBALS ["db"]->query_once ( $this->sql );
		return $count [0];
	}
	/**
	 * @param $id
	 * 根据dispose_id（处理人id）
	 * 得到user(用户表)的用户姓名
	 * user
	 */
	function get_user_name($id = "") {
		$this->sql = "select name from " . $this->user_table_name . " where id=" . $id;
		$count = $GLOBALS ["db"]->query_once ( $this->sql );
		return $count [0];
	}
	/**
	 * 修改告警处理意见
	 * @param $id
	 * @param $advice
	 */
	function edit_alert_advice($alert) {
		if (! $alert) {
			$this->message = "error,object must be not empty!";
			return false;
		}
		//添加主键ID
		$alert ['id'] = $this->alert_id;
		if (! $GLOBALS ['db']->update_row ( $this->mysel_table_name, $alert, "id" )) {
			$this->message = "error,edit data failed!";
			return false;
		}
		return true;
	}

	/**
	 * 查询所有车辆组数据
	 * @return 查询记录
	 */
	function get_vehicle_group($company_id) {
		$this->sql = "select id,name from vehicle_group where company_id=".$company_id;
		return $this->data = $GLOBALS ["db"]->query ( $this->sql );
	}
	
	/**
	 * 查询所有车辆数据(和车辆组联动)
	 * @param unknown_type $vehicle_group_id
	 */
	function get_linkage_vehicle($vehicle_group_id=""){
		$this->sql ="select vm.id,vm.number_plate from vehicle_manage as vm inner join  vehicle_group as vg".
                    " on vg.id=vm.vehicle_group_id and vm.vehicle_group_id=".$vehicle_group_id;
		return $this->data = $GLOBALS ["db"]->query ( $this->sql );
	}
	
	/**
	 * 算出车辆组下对应的车辆数
	 */
	function get_count_vehicle($vehicle_group_id=""){
		$this->sql ="select count(number_plate) from vehicle_manage as vm inner join  vehicle_group as vg
         on vg.id=vm.vehicle_group_id and vm.vehicle_group_id=".$vehicle_group_id;
		return $this->data = $GLOBALS ["db"]->query ( $this->sql );
	}
	
	/**
	 * 获得最新未处理告警记录
	 */
  function get_newest_alert(){
    	$company_id = get_session("company_id");
    	//格式化sql语句
    	$sql ="select a.id,a.alert_time, v.number_plate, a.alert_type ,a.vehicle_id from  %s a inner join %s v where v.id=a.vehicle_id  and v.company_id=%d  and (a.dispose_opinion is null or a.dispose_opinion='') order by a.alert_time desc limit 0,1" ;
    	
    	$this->sql = sprintf($sql,$this->mysel_table_name,$this->vehicle_manage_table_name,$company_id);
    	
    	$record = $GLOBALS ["db"]->query_once ( $this->sql );
    	return $record;
    }
    
    /**
     * 批量修改告警
     */
    function alert_total_update($vehicle_id,$alert_type,$advice,$userID){
    	$sql="update alert_info set dispose_opinion='".$advice."',dispose_id='".$userID."' where vehicle_id='".$vehicle_id."'
    	      and alert_type='".$alert_type."'";	
    	$record = $GLOBALS ["db"]->totleUpdate($sql);
    	return $record;
    }
    
    /*
     * 根据用户 id得到用户姓名
     */
   //function alert_user_name($user_id){
   //  $sql="select name from user where id=".$user_id;
   //  $count = $GLOBALS ["db"]->query_once ( $this->sql );
   //	 return $count [0];
   //}

}
?>