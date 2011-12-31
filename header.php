<?php
require_once("f.php");
/*
$nav0="登录";
$nav1="训练情况";
$nav2="值日签到";
$nav3="历史成绩";
$nav4="管理";
$nav5="主页";
 */
if($_SESSION['loggedin']){
	$nav0='<a href="logout.php">' . "登出(" . $_SESSION['euser'] . ")" . '</a>';
}else{
	$nav0='<a href="login.php">' . "登录" . '</a>';
}
$nav1='<a href="training.php">' . "训练情况" . '</a>';
$nav2='<a href="usaco.php">' . "USACO寒假大比拼！" . '</a>';
$nav3='<a href="usaco.php">' . "USACO寒假大比拼！" . '</a>';
$nav5='<a href="index.php">' . "主页" . '</a>';
$nav6='<a href="modifypersoninfo.php">' . "设置" . '</a>';
$nav7='<a href="labcamera.php">' . '实验室实时' . '</a>';
/*
if(以管理员身份登录了){
	$nav4='| <a href="admin.php">' . $nav4 . '</a>';
}
*/
//<!DOCTYPE html>
echo <<<eot
<!DOCTYPE html>
<html>
	<meta charset="utf-8" />
	<head>
		<title>西电ACM实验室</title>
		<link rel="stylesheet" type="text/css" href="css/style.css"/>
	</head>
	<body>
		<center>
		<div class='header'>
			<h2 class="tt">ACM 成员管理系统</h2>
		</div>
		<div class='nav'>
eot;
if($_SESSION['loggedin']==1){
	if($_SESSION['islab']==1){
echo "$nav5 | $nav0 | $nav1 | $nav6 | $nav3";
	}else if($_SESSION['islab_vip']){
echo "$nav5 | $nav0 | $nav1 | $nav3";
	}else if($_SESSION['islab_root']){
echo "$nav5 | $nav0 | $nav1 | $nav6 | $nav3";
	}
}else{
	echo "$nav5 | $nav0 | $nav1 | $nav3";
}
echo <<<eot
		</div>
eot;

?>
