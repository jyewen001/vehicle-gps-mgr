<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>GPS智能车辆监控调度系统-登录</title>
	
	<link href="css/login.css" rel="stylesheet" />
	<link type="text/css" href="css/alert.css" rel="stylesheet" />
	<link type="text/css" href="css/jquery.loadmask.small.css"  media="screen" rel="stylesheet" />
	
	<style type="text/css">
	img, div, a, input { behavior: url(css/resources/iepngfix.htc) }
}
	</style> 
	
	<script language="javascript" src="js/login/cookie.js"></script>
	<script language="javascript" src="js/login/login.js"></script>
	<script language="javascript" src="js/jquery-1.4.2.js" ></script>
	<script language="javascript" src="js/jquery.loadmask.min.js" ></script>
	<script language="javascript" src="js/jquery.ui.core.js" ></script>
	<script language="javascript" defer="defer" src="js/jquery-ui-1.8.1.custom.min.js" ></script>
	<script type="text/javascript">
		$(document).ready(function(){
			$("#loginCar").click(function(){
				$("#body").mask("验证中，请稍候...");
				$("#clue").html("");
				var companyId=document.getElementById("companyId").value;
				var userName=document.getElementById("userName").value;
				var password=document.getElementById("password").value;
				
				var pat=new RegExp("[^a-zA-Z0-9\_\u4e00-\u9fa5]","i"); 
				if(pat.test(companyId)==true|| pat.test(userName)==true||pat.test(password)==true) 
				{ 
					$("#body").unmask();
					$("#clue").html("<img src='images/sad.png' alt='禁止通行'/>公司ID或用户名或密码含有非法字符!"); 
				}else {
					 $.post("login_check.php?companyloginid="+companyId+"&username="+userName+"&password="+password,function(data){
							$("#body").unmask();
							if(data==1){
								document.location= "index.php?a=1003";
								$("#body").mask("页面跳转中，请稍候...");
							}else if(2==data){
								document.location= "index.php?a=1004";
								$("#body").mask("页面跳转中，请稍候...");
							}else{
								$("#clue").html("<img src='images/sad.png' alt='禁止通行'/>公司ID或用户名或密码错误！");
							}
						});
				 }
				
			});
			
		});
		function more_message(str){
			$("#show_div").show();
			$("#show_message").html(str);
		}

		function close_message(){
			$("#show_div").hide();
		}
	</script>
</head>

<body>
	<div class="body_div">
		<input type="hidden" name="act" value="signin" />
		<div class="top">		
			<?php 
				require_once 'include/interface_manage.php';
				echo "<div class='logo'><img src='".$image['image_url']."'/></div>";
				echo "<div class='title'>".$image['name']."</div>";
			?>
		</div>
		<div class="content mt5">
			<div class="login_img"></div>
			<div class="content_right">
				<div class="new_info">
					<ul>
						<li>★最新消息</li>
						<li class="mt5">
							<?php 
								require_once 'templates/new_message.php';
								$messages = $new_message['messages'];
								$arr = json_encode($messages);
								if(strlen($messages)>324){
									$sub_messages = substr($messages,0,320);
									echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"
										.$sub_messages
										."......<a href=javascript:more_message(".$arr.")>查看更多</a>";
								}else{
									echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$messages;
								}
							?>
						</li>
					</ul>
				</div><!--最新消息-->
				<div class="form_info mt65">
					<ul>
						<li>
							公司ID：
							<span>
								<input type="text" name="companyloginid" id="companyId" class="input"/>
							</span>
						</li>
						<li class="mt10">
							用户ID：
							<span>
								<input type="text" name="username" id="userName"  class="input"/>
							</span>
						</li>
						<li class="mt10">
							密&nbsp;&nbsp;&nbsp;&nbsp;码：
							<span>
								<input type="password" name="password" id="password" class="input" />
							</span>
						</li>
						<li class="mt15 tc">
							 <input id="saveall" type="checkbox" value="" /> 
							<span class="remenber">记住</span>&nbsp;&nbsp;
							<span>
								<input type="submit" class="submit" id="loginCar" name="login" value="登录" />
								<div id="clue" style="font-size:10px;color:red;"></div>
							</span>
						</li>
					</ul>
				</div><!--表单-->
				<div id="show_div" class="display_none" style="display:none;">
					<div style="height:25px; background-color:gray;">
					<ul>
						<li style="display:inline;float:left;font-weight:700;padding:5px;">详细信息</li>
						<li style="display:inline;float:right;padding:5px;" onclick="close_message()">关闭</li>
					</ul>
					</div>
					<div id="show_message"></div>
				</div>
			</div>
		</div><!--内容-->
		<div class="help_info mt5">
			<ul>
				<li>推荐使用ie浏览器。（使用ie8浏览器可以获得最佳的使用体验）</li>
				<li class="mt5">
					初次使用，请对ie浏览器进行设置，设置方式请点击<a href="help.php" target="view_window">这里。</a>
				</li>
			</ul>
		</div>
	</div>
</body>
</html>
