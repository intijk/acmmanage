#!/usr/bin/php
<?php
#此程序用来进行每晚的例行刷新
require_once("conf.php");
require_once("f.php");
$link=linkToDBandselectDB($db_name1);
$sql="select username from personinfo";
$res=mysql_query($sql,$link)or die('cannot query personinfo');
$sql="select ojName from ojList where needOJID=True";
$ojList=mysql_query($sql,$link) or die('cannot query ojList');
while($r=mysql_fetch_assoc($res)){
	$muser=$r['username'];
	echo $muser;
	mysql_data_seek($ojList,0);
	while($oj=mysql_fetch_assoc($ojList)){
		$sql="select ojID from userIDOnOJ where username='$muser' and ojType='" . $oj['ojName'] . "'";
	#	echo $sql . "\n";
		$ids=mysql_query($sql,$link) or die('cannot query user id on oj');
		if(mysql_num_rows($ids)>=1){
			$id=mysql_fetch_assoc($ids);
			$id=$id['ojID'];
		}
		else continue;
		$sql="insert into updateTaskList(username,ojType,id,queryTime) values('$muser','" . $oj['ojName'] . "','" . $id . "','" . date("Y-m-d H:i:s") . "')";
		mysql_query($sql,$link) or die ('cannot insert update task');
	}

}
echo 'ok';
?>
