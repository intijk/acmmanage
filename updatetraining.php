<?php
require_once("frame.php");
require_once("conf.php");
require_once("f.php");
$muser='';
$x=new frame();
if(!isset($_SESSION['islab']) and !isset($_SESSION['isroot'])){
	echo <<<eot
<div>
无权进行此操作.
</div>
eot;
	exit();
}
if(!isset($_REQUEST['muser'])){
	echo <<<eot
<div>
没有指定刷新对象
</div>
eot;
	exit();
}else{
	$muser=$_REQUEST['muser'];
}
if(!$_SESSION['islab_root'] and $_SESSION['euser']!=$muser){
	echo <<<eot
<div>
无法为此用户进行刷新
</div>
eot;
	exit;
}
if(!userAinGroupB($muser,'lab')){
	echo<<<eot
<div>
该用户id不在lab组内
</div>
eot;
	exit;
}
$link=linkToDBandSelectDB($db_name1);
$sql="select ojName from ojList where needOJID=True";
$ojList=mysql_query($sql,$link) or die('cannot query ojList');
while($oj=mysql_fetch_assoc($ojList)){
	$sql="select ojID from userIDOnOJ where username='$muser' and ojType='" . $oj['ojName'] . "'";
	$ids=mysql_query($sql,$link) or die('cannot query user id on oj');
	if(mysql_num_rows($ids)>=1){
		$id=mysql_fetch_assoc($ids);
		$id=$id['ojID'];
	}
	else continue;
	$sql="insert into updateTaskList(username,ojType,id,queryTime) values('$muser','" . $oj['ojName'] . "','" . $id . "','" . date("Y-m-d H:i:s") . "')";
	mysql_query($sql,$link) or die ('cannot insert update task');
}
echo <<<eot
已经提交刷新请求, 请稍候查看结果.如果做题数量没有改变,查询结果将不会改变(包括最后查询时间).
eot;
?>
