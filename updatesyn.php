#!/usr/bin/php
<?php
#updatesyn username
#1. 取出数据库中该用户的行
#2. 检测该用户是否存在，不存在则退出
#3. 取得该用户在所有OJ上的id，积分和权值，进行累加后求得syn值
#4. 把syn值插入到用户在oj上的题量中，要插入时间。

require_once("conf.php");
require_once("f.php");
$username=$argv[1];
$link=linkToDBandSelectDB($db_name1);
$sql="select * from personinfo where username='$username'";
$result=mysql_query($sql,$link) or die("cannot query personinfo in updatesyn");
$nr=mysql_num_rows($result);
if($nr<=0){
	echo "用户 $username 不存在";
	return -1;
}
else{
	$sql="select * from RecentTrainingQuery where username='$username'";
	$result=mysql_query($sql,$link) or die("cannot query RecentTrainingQuery in updatesyn");
	$synValue=0;
	while($row=mysql_fetch_assoc($result)){
		$sql="select ojWeight from ojList where ojName='" . $row['ojType']  . "'";
		$result1=mysql_query($sql,$link) or die("cannot query ojWeight in updatesyn");
		$row1=mysql_fetch_assoc($result1);
		$synValue+=((float)($row1['ojWeight'])*(float)($row['value']));
	}
	$synValue=((int)($synValue));
	$timeString=date("Y-m-d H:i:s");
	$sql="insert into training(username,ojType,time,queryID,value) values('$username','syn','$timeString','$username',$synValue)";
	$result=mysql_query($sql,$link) or die("cannot insert syn update");;
	echo "$username synValue=$synValue $timeString 已经更新\n";
}
?>
