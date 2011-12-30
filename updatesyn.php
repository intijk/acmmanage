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
		$value=(float)($row['value']);
		$ojName=$row['ojType'];
		$ojWeight=$row1['ojWeight'];

		$thisValue=(float)(0);	
		if($ojName=='cf'){
			$thisValue=pow(($value/10000)/1500,6.3)*($value%10000)*2;
		
		}elseif($ojName=='tc'){
			$thisValue=pow(($value/10000)/1100,5)*($value%10000)*3;
		}elseif($ojName=='usaco'){
			$C=(int)($value/10);
			$S=(int)($value%10);
			$thisValue=($C-1)*4*4+($S-1)*4;
		}elseif($ojName=='syn'){
			$thisValue=0;
		}else{
			$thisValue=(float)($ojWeight*(float)($value));
		}
		echo $ojName . " " . $thisValue . "\n";
		$synValue+=$thisValue;
	}
	$synValue=((int)($synValue));
	$timeString=date("Y-m-d H:i:s");
	$sql="insert into training(username,ojType,time,queryID,value) values('$username','syn','$timeString','$username',$synValue)";
	$result=mysql_query($sql,$link) or die("cannot insert syn update");;
	echo "$username synValue=$synValue $timeString 已经更新\n";
}
?>
