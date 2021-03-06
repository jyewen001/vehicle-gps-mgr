<?php
/** 
* 公司管理
* @copyright		company, 2010
* @author			苏元元
* @create date		2010.07.26
* @modify			修改人
* @modify date		修改日期
* @modify describe	修改内容
*/

require_once ("setting/setting.class.php");
$act = $GLOBALS["all"]["operate"];

$page = $_REQUEST['page']; // get the requested page
$limit = $_REQUEST['rows']; // get how many rows we want to have into the grid
$sidx = $_REQUEST['sidx']; // get index row - i.e. user click to sort
$sord = $_REQUEST['sord']; // get the direction
$par = $_REQUEST["par"];
$child = $_REQUEST["child"];
$searchfil = $_REQUEST['searchField']; // get the direction
$searchstr = $_REQUEST['searchString']; // get the direction

//创建设置对象
$set = new Setting(get_session("company_id"));

if(!$sidx) $sidx =1;

switch($act)
{
	case "list":		//模拟测试
		echo $GLOBALS['db']->display(null,$act);
		break;

	case "list_data":
		$comp	= new Company();	//模拟打印润色后的字符串值
		$explorer_id = get_session('user_id');
		$count = $comp->get_all_count($explorer_id);//获取所有公司总数

		if( $count >0 ) {
			$total_pages = ceil($count/$limit);
		} else {
			$total_pages = 0;
		}
		if ($page > $total_pages) $page=$total_pages;
		$start = $limit*$page - $limit;
		if ($start<0) $start = 0;

		//公司状态
		$xml_state  = new Xml("company","state");
		$state = $xml_state->get_array_xml();

		//得到字段类型
		if(empty($searchfil) or empty($searchstr))
			$wh = 'where 1=1';
		else
		{
//				$type = $driver->get_type($searchfil);
//				$searchstr = $db->prepare_value($searchstr,$type);
			$wh = "where ".$searchfil." LIKE '%".$searchstr."%'";
		}

		//得到所有公司
		$rtn = $comp->get_all_companys($explorer_id,$wh,$sidx,$sord,$start,$limit);

		$responce->page	= $page;
		$responce->total = $total_pages;
		$responce->records = $count;

		foreach($rtn as	$key=>$rtn_company)
		{
			$responce->rows[$key]['id']=$rtn_company['id'];
			$responce->rows[$key]['cell']=array($rtn_company['id'],$rtn_company['login_id'],$rtn_company['name'],$rtn_company['register_num'],$rtn_company['area1'],$rtn_company['area2'],$rtn_company['area3'],$rtn_company['description'],$rtn_company['contact'],$rtn_company['address'],$rtn_company['zipcode'],$rtn_company['tel'],$rtn_company['fax'],$rtn_company['mobile'],$rtn_company['email'],$rtn_company['site_url'],$state[$rtn_company['state']],$rtn_company['service_start_time'],$rtn_company['service_end_time'],$rtn_company['charge_standard'],$rtn_company['create_id'],$rtn_company['create_time'],$rtn_company['update_id'],$rtn_company['update_time']);
		}

		//打印json格式的数据
		echo json_encode($responce);

		break;
		
	case "child":		//模拟测试
		echo $GLOBALS['db']->display(null,$act);
		break;
	
	case "child_data": //获取所有子业务员所管辖公司
		$comp	= new Company();	//模拟打印润色后的字符串值
		$explorer_id = get_session('user_id');
		//$count = $comp->get_all_count($explorer_id);//获取所有公司总数
		
		/*
		 * 获取所有公司总数
		 */
		$child_ids = $comp->get_child_ids($explorer_id);//获取子业务员ID
		$rtn = $comp->get_all_companys($explorer_id);
		while($child_ids!=false && count($child_ids) > 0){
			$rtn = array_merge($rtn,$comp->get_all_companys($child_ids));
			$count = $count + $comp->get_all_count($child_ids);//获取所有公司总数
			$child_ids = $comp->get_child_ids($child_ids);
		}

		if( $count >0 ) {
			$total_pages = ceil($count/$limit);
		} else {
			$total_pages = 0;
		}
		if ($page > $total_pages) $page=$total_pages;
		$start = $limit*$page - $limit;
		if ($start<0) $start = 0;

		//公司状态
		$xml_state  = new Xml("company","state");
		$state = $xml_state->get_array_xml();

		//得到字段类型
		if(empty($searchfil) or empty($searchstr))
			$wh = 'where 1=1';
		else
		{
//				$type = $driver->get_type($searchfil);
//				$searchstr = $db->prepare_value($searchstr,$type);
			$wh = "where ".$searchfil." LIKE '%".$searchstr."%'";
		}

		//得到所有公司
		//$rtn = $comp->get_all_companys($explorer_id,$wh,$sidx,$sord,$start,$limit);

		$responce->page	= $page;
		$responce->total = $total_pages;
		$responce->records = $count;

		foreach($rtn as	$key=>$rtn_company)
		{
			$responce->rows[$key]['id']=$rtn_company['id'];
			$responce->rows[$key]['cell']=array($rtn_company['id'],$rtn_company['login_id'],$rtn_company['name'],$rtn_company['register_num'],$rtn_company['area1'],$rtn_company['area2'],$rtn_company['area3'],$rtn_company['description'],$rtn_company['contact'],$rtn_company['address'],$rtn_company['zipcode'],$rtn_company['tel'],$rtn_company['fax'],$rtn_company['mobile'],$rtn_company['email'],$rtn_company['site_url'],$state[$rtn_company['state']],$rtn_company['service_start_time'],$rtn_company['service_end_time'],$rtn_company['charge_standard'],$rtn_company['create_id'],$rtn_company['create_time'],$rtn_company['update_id'],$rtn_company['update_time']);
		}

		//打印json格式的数据
		echo json_encode($responce);
		break;

	case "edit_data":

		// 取到当前的操作
		$oper = $_REQUEST["oper"];
		
		//验证数据格式是否为数字
		$check_arr['电话'] = $_REQUEST['tel'];
		$check_arr['移动电话'] = $_REQUEST['mobile'];
		$check_arr['邮编'] = $_REQUEST['zipcode'];
		$check_arr['传真'] = $_REQUEST['fax'];
		$check_arr['邮费标准'] = $_REQUEST['charge_standard'];
		
		foreach($check_arr as $key=>$value){
			if(!empty($value) && !is_numeric($value)){
				exit(json_encode(array('success'=>false,'errors'=>''.$key.'必须为数字!')));
				break;
			}
		}

		switch($oper)
		{
			// 添加数据
			case "add":
				{
					
					//获取各种数据
					$parms["id"]				= $GLOBALS['db']->prepare_value($_REQUEST["id"],"INT"); 
					$parms["login_id"]			= $GLOBALS['db']->prepare_value($_REQUEST["login_id"],"VARCHAR"); 
					$parms["name"]				= $GLOBALS['db']->prepare_value($_REQUEST["name"],"VARCHAR");
					$parms["register_num"]		= $GLOBALS['db']->prepare_value($_REQUEST["register_num"],"VARCHAR");
					$parms["explorer_id"]		= $GLOBALS['db']->prepare_value(get_session('user_id'),"INT"); 
					/******************************* 暂时都是 0 */
					$parms["area1"]				= 0;
					$parms["area2"]				= 0;
					$parms["area3"]				= 0;
					/******************************* 暂时都是 0 */
					$parms["description"]		= $GLOBALS['db']->prepare_value($_REQUEST["description"],"VARCHAR");
					$parms["contact"]			= $GLOBALS['db']->prepare_value($_REQUEST["contact"],"VARCHAR");
					$parms["address"]			= $GLOBALS['db']->prepare_value($_REQUEST["address"],"VARCHAR");
					$parms["zipcode"]			= $GLOBALS['db']->prepare_value($_REQUEST["zipcode"],"VARCHAR");
					$parms["tel"]				= $GLOBALS['db']->prepare_value($_REQUEST["tel"],"VARCHAR");
					$parms["fax"]				= $GLOBALS['db']->prepare_value($_REQUEST["fax"],"VARCHAR");
					$parms["mobile"]			= $GLOBALS['db']->prepare_value($_REQUEST["mobile"],"VARCHAR");
					$parms["email"]				= $GLOBALS['db']->prepare_value($_REQUEST["email"],"VARCHAR");
					$parms["site_url"]			= $GLOBALS['db']->prepare_value($_REQUEST["site_url"],"VARCHAR");
					$parms["state"]				= $GLOBALS['db']->prepare_value($_REQUEST["state"],"INT");
					if(!empty($_REQUEST["service_start_time"]) && !empty($_REQUEST["service_end_time"]))
					{	
						$parms["service_start_time"]= $GLOBALS['db']->prepare_value($_REQUEST["service_start_time"],"VARCHAR");
						$parms["service_end_time "]	= $GLOBALS['db']->prepare_value($_REQUEST["service_end_time"],"VARCHAR");
					}
					$parms["charge_standard"]	= $GLOBALS['db']->prepare_value($_REQUEST["charge_standard"],"VARCHAR");
	
					$parms["create_id"]				= $GLOBALS['db']->prepare_value(get_session("user_id"),"INT");
					$parms["create_time"]			= $GLOBALS['db']->prepare_value(get_sysdate(),"VARCHAR");
					$parms["update_id"]				= $GLOBALS['db']->prepare_value(get_session("user_id"),"INT");
					$parms["update_time"]			= $GLOBALS['db']->prepare_value(get_sysdate(),"VARCHAR");
					$comp	= new Company();
	
					//检查 login_id 是否重复
					$login = $comp->checkLoignid($_REQUEST["login_id"]);
					if($login)
					{
						exit(json_encode(array('success'=>false,'errors'=>'重复的登录ID，请重试!')));
					}
					
					//执行更新
					$rtn = $comp->add_data($parms,"id");
					
					//添加默认速度颜色
					foreach($init_speed_color as $key=>$speed){
						
						$com_sparms["company_id"]		= $GLOBALS['db']->prepare_value($rtn,"INT");
						$com_sparms["min"]				= $GLOBALS['db']->prepare_value($speed['min'],"INT");
						$com_sparms["max"]				= $GLOBALS['db']->prepare_value($speed['max'],"INT");
						$com_sparms["color"]			= $GLOBALS['db']->prepare_value($speed['color'],"CHAR");
						$com_sparms["create_id"]		= $GLOBALS['db']->prepare_value(get_session("user_id"),"INT");
						$com_sparms["create_time"]		= $GLOBALS['db']->prepare_value(get_sysdate(),"VARCHAR");
						$com_sparms["update_id"]		= $GLOBALS['db']->prepare_value(get_session("user_id"),"INT");
						$com_sparms["update_time"]		= $GLOBALS['db']->prepare_value(get_sysdate(),"VARCHAR");
						
						$rss = $set->add_speed_color($com_sparms);  
					}
					
					//添加默认设置
					$set_sparm["company_id"]		    = $GLOBALS['db']->prepare_value($rtn,"INT");
					$set_sparm["page_refresh_time"]		= $GLOBALS['db']->prepare_value($default_setting['page_refresh_time'],"INT");
					$set_sparm["default_color"]			= $GLOBALS['db']->prepare_value($default_setting['default_color'],"CHAR");
					$set_sparm["speed_astrict"]			= $GLOBALS['db']->prepare_value($default_setting['speed_astrict'],"FLOAT");
					$set_sparm["fatigue_remind_time"]	= floor($GLOBALS['db']->prepare_value($default_setting['fatigue_remind_time'],"FLOAT")*60);
					
					$set_sparm["create_id"]				= $GLOBALS['db']->prepare_value(get_session("user_id"),"INT");
					$set_sparm["create_time"]			= $GLOBALS['db']->prepare_value(get_sysdate(),"VARCHAR");
					$set_sparm["update_id"]				= $GLOBALS['db']->prepare_value(get_session("user_id"),"INT");
					$set_sparm["update_time"]			= $GLOBALS['db']->prepare_value(get_sysdate(),"VARCHAR");
					
					 
					$set->add_setting($set_sparm);	
					
					
					if($rtn > 1)
					{
						//查一下是否已经车辆组，如果没有，则添加一个默认的
						//添加默认车辆组
						$vehicle = new Vehicle_group();
	
						$result = $vehicle->add_vehicle_group_by_company($rtn,$_REQUEST["name"]);
						$add = $comp->add_admin($rtn);//添加公司内部管理员
	
						if($result == 1 && $add)		//对应的公司已经有车辆组
						{
							//也要显示给用户添加成功。添加的只是公司数据
							echo json_encode(array('success'=>true,'errors'=>'添加成功!'));
						}
						else if($result == 2 && $add)
						{
							//成功，添加的是公司和车辆组
							echo json_encode(array('success'=>true,'errors'=>'添加成功!'));
						}
						else
						{
							//删掉添加成功的公司
							$parms["id"] = $GLOBALS['db']->prepare_value($rtn,"INT"); 
							$comp->delete_data($parms);
							echo json_encode(array('success'=>false,'errors'=>'添加失败，请重试!'));
						}						
					}
					else
					{						
						echo json_encode(array('success'=>false,'errors'=>'添加失败，请重试!'));
					}
				}

				break;

			// 修改数据
			case "edit":

				//获取各种数据
				$parms["id"]				= $GLOBALS['db']->prepare_value($_REQUEST["id"],"INT"); 
//				$parms["login_id"]			= $GLOBALS['db']->prepare_value($_REQUEST["login_id"],"INT"); 
				$parms["name"]				= $GLOBALS['db']->prepare_value($_REQUEST["name"],"VARCHAR");
				$parms["register_num"]		= $GLOBALS['db']->prepare_value($_REQUEST["register_num"],"VARCHAR");
				/******************************* 暂时都是 0 */
				$parms["area1"]				= 0;
				$parms["area2"]				= 0;
				$parms["area3"]				= 0;
				/******************************* 暂时都是 0 */
				$parms["description"]		= $GLOBALS['db']->prepare_value($_REQUEST["description"],"VARCHAR");
				$parms["contact"]			= $GLOBALS['db']->prepare_value($_REQUEST["contact"],"VARCHAR");
				$parms["address"]			= $GLOBALS['db']->prepare_value($_REQUEST["address"],"VARCHAR");
				$parms["zipcode"]			= $GLOBALS['db']->prepare_value($_REQUEST["zipcode"],"VARCHAR");
				$parms["tel"]				= $GLOBALS['db']->prepare_value($_REQUEST["tel"],"VARCHAR");
				$parms["fax"]				= $GLOBALS['db']->prepare_value($_REQUEST["fax"],"VARCHAR");
				$parms["mobile"]			= $GLOBALS['db']->prepare_value($_REQUEST["mobile"],"VARCHAR");
				$parms["email"]				= $GLOBALS['db']->prepare_value($_REQUEST["email"],"VARCHAR");
				$parms["site_url"]			= $GLOBALS['db']->prepare_value($_REQUEST["site_url"],"VARCHAR");
				$parms["state"]				= $GLOBALS['db']->prepare_value($_REQUEST["state"],"INT");

				if(!empty($_REQUEST["service_start_time"]) && !empty($_REQUEST["service_end_time"]))
				{	
					$parms["service_start_time"]= $GLOBALS['db']->prepare_value($_REQUEST["service_start_time"],"VARCHAR");
					$parms["service_end_time "]	= $GLOBALS['db']->prepare_value($_REQUEST["service_end_time"],"VARCHAR");
				}
				$parms["charge_standard"]	= $GLOBALS['db']->prepare_value($_REQUEST["charge_standard"],"VARCHAR");

				// session 值
				$parms["create_id"]				= $GLOBALS['db']->prepare_value(get_session("user_id"),"INT");
				$parms["create_time"]			= $GLOBALS['db']->prepare_value(get_sysdate(),"VARCHAR");
				$parms["update_id"]				= $GLOBALS['db']->prepare_value(get_session("user_id"),"INT");
				$parms["update_time"]			= $GLOBALS['db']->prepare_value(get_sysdate(),"VARCHAR");
				$comp	= new Company();

				//执行更新
				$rtn = $comp->edit_data($parms,"id");

				if(!$rtn)
					echo json_encode(array('success'=>false,'errors'=>'更新失败，请重试!'));
				else
					echo json_encode(array('success'=>true,'errors'=>'更新成功!'));

				break;

			// 删除数据
			case "del":
				$comp	= new Company();
				$parms["id"] = $GLOBALS['db']->prepare_value($_REQUEST["id"],"INT"); 
				$comp->delete_data($parms);
				break;
		}
//		file_put_contents("d:\a.txt",$GLOBALS['db']->sql);
		break;
		
	case "select":		//下拉列表
		$p = $_REQUEST["p"];		//获得需要翻译的字段
		$company = new Company();
		switch($p)
		{
			case "state"://公司状态(激活与未激活)
				if(!$par or !$child)
				{
					$par = "company";
					$child = "state";
				}
				
			//读取xml
			$xml = new Xml($par,$child);
			$html = $xml->get_html_xml();
			break;
		}
		echo $html;
		break;
}
?>