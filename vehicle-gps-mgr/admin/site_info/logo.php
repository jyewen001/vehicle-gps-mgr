<?php
/** 
* 登录界面管理
* @copyright		company, 2010
* @author			史恩秀
* @create date		2010.11.24
*/
$act = $GLOBALS["all"]["operate"];
switch($act)
{
	case "upload":	
		echo $GLOBALS['db']->display(null,$act);
		break;

	case "save":
		//如果收到表单传来的参数，则进行上传处理，否则返回表单
		if(isset($_FILES['uploadinput'])){
			//建目录函数，其中参数$directoryName最后没有"/"，
			//要是有的话，以'/'打散为数组的时候，最后将会出现一个空值
			function makeDirectory($directoryName) {
				$directoryName = str_replace("\\","/",$directoryName);
				$dirNames = explode('/', $directoryName);
				$total = count($dirNames) ;
				$temp = '';
				for($i=0; $i<$total; $i++) {
					$temp .= $dirNames[$i].'/';
					if (!is_dir($temp)) {
						$oldmask = umask(0);
						if (!mkdir($temp, 0777)) exit("不能建立目录 $temp"); 
						umask($oldmask);
					}
				}
				return true;
			}
		
			if($_FILES['uploadinput']['name'] <> ""){
				//包含上传文件类
				require_once ('logo.class.php');
				//设置文件上传目录
				$savePath = "images/upload";
				//创建目录
				makeDirectory($savePath);
				//允许的文件类型
				$fileFormat = array('gif','jpg','jpge','png');
				//文件大小限制，单位: Byte，1KB = 1000 Byte
				//0 表示无限制，但受php.ini中upload_max_filesize设置影响
				$maxSize = 0;
				//覆盖原有文件吗？ 0 不允许  1 允许 
				$overwrite = 0;
				//初始化上传类
				$logo = new Logo( $savePath, $fileFormat, $maxSize, $overwrite);
				//如果想生成缩略图，则调用成员函数 $f->setThumb();
				//参数列表: setThumb($thumb, $thumbWidth = 0,$thumbHeight = 0)
				//$thumb=1 表示要生成缩略图，不调用时，其值为 0
				//$thumbWidth  缩略图宽，单位是像素(px)，留空则使用默认值 130
				//$thumbHeight 缩略图高，单位是像素(px)，留空则使用默认值 130
				$logo->setThumb(1);
				
				//参数中的uploadinput是表单中上传文件输入框input的名字
				//后面的0表示不更改文件名，若为1，则由系统生成随机文件名
				if (!$logo->run('uploadinput',1)){
					//通过$logo->errmsg()只能得到最后一个出错的信息，
					//详细的信息在$logo->getInfo()中可以得到。
					echo $logo->errmsg()."<br>\n";
				}
				//上传结果保存在数组returnArray中。
				$imgName=$logo->saveName;
				$url=$savePath."/".$imgName;
				$name=$_POST['company_name'];
				
				if($url!="" && $url!=false && $name!="" && $name!=false){
					$file_name="include/interface_manage.php";
					file_put_contents($file_name, "<?php \$image=array('image_url'=>'".$url."','name'=>'".$name."'); ?>");
					goto_url("/login.php");
				}else{
					echo "<script type='text/javacript'> alert('图片路径与公司名字不能为空');</script>";
					exit;
				}	
			}
		}
		break;

}

?>