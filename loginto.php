<?php
require_once("conf.php");
require_once("frame.php");
require_once("f.php");
if($_SESSION['loggedin']==1){
	$x=new frame();
	echo <<<eot
<div>
你已经登录了，请先退出再登录别的用户.
</div>
eot;
	exit();
}
$user=$_REQUEST['user'];
$password=$_REQUEST['password'];
$link=mysql_connect($db_server,$db_user,$db_password);

if(!$link){
	die('Could not connet:' . mysql_error());
}
if(!mysql_select_db($db_name,$link)){
	die('Could not find database');
}
$sql="SELECT * FROM users WHERE username='$user'";
$result=mysql_query($sql,$link) or die('cannot query when logging');
if(!$result){
	die('Cound not query the database');
}
$row=mysql_fetch_assoc($result);
$euser=$row['username'];
$epassword=$row['password'];
mysql_free_result($result);
if($epassword==md5($password)){
	$_SESSION['islab']=0;
	$_SESSION['islab_vip']=0;
	$_SESSION['islab_root']=0;
	if(userAingroupB($euser,'lab_root')){
		$_SESSION['islab_root']=1;
	}else if(userAingroupB($euser,'lab_vip')){
		$_SESSION['islab_vip']=1;
	}else if(userAingroupB($euser,'lab')){
		$_SESSION['islab']=1;
	}else{
		$x=new frame();
		echo <<<eot
<div>
你不是实验室用户，无法登录。
</div>
eot;
		exit();
	}
	$_SESSION['loggedin']=1;
	$_SESSION['euser']=$euser;
	$_SESSION['iscamera']=0;
	if(userAingroupB($euser,'lab_camera')){
		$_SESSION['iscamera']=1;
	}
	$x=new frame();
	echo <<<eot
<div>
登录成功
</div>
eot;
}else{
	$x=new frame();
	echo <<<eot
<div>
用户名或密码错误
</div>
eot;
}
?>
