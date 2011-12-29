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
	#lab_root 和 lab可共存， lab_vip和lab 不可共存,lab_vip和lab_root可共存	
	if(userAingroupB($euser,'lab_root')){
		$_SESSION['loggedin']=1;
		$_SESSION['islab_root']=1;
	}
	if(userAingroupB($euser,'lab_vip')){
		$_SESSION['loggedin']=1;
		$_SESSION['islab_vip']=1;
	}
	if(userAingroupB($euser,'lab')){
		$_SESSION['loggedin']=1;
		$_SESSION['islab']=1;
	}
	if($_SESSION['islab_vip']==1 && $_SESSION['islab']==1){
		#出现这种情况肯定是oj里的组管理没设好，所以解开冲突
		$_SESSION['islab']=0;
	}
	if($_SESSION['loggedin']!=1){
		$x=new frame();
		echo <<<eot
<div>
你不是实验室用户，无法登录。
</div>
eot;
		exit();
	}
	$_SESSION['euser']=$euser;
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
