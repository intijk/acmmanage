<?php
require_once("frame.php");
require_once("f.php");
$x=new frame();
$modyinfo='';
$sortStyle='syn';//默认的排序方式
$styleInList=1;
$groupShow='lab';
##检测用户的登录状态
#if(!$_SESSION['loggedin']){
#	echo <<<eot
#<div>
#	你还没有登录，无权查看信息.
#</div>
#eot;
#exit();
#}
$euser=$_SESSION['euser'];
#检测提交的排序方式
if(isset($_REQUEST['sortStyle'])){//判断列表的排序方式是不是规定的 几种方式
	$sortStyle=$_REQUEST['sortStyle'];
	$styleInList=0;
	foreach($sortStyleList as $e){
		if($e==$sortStyle){
			$styleInList=1;
			break;
		}
	}
}
if($styleInlist=0){
	$y=new notfind();
	exit();
}
$order='desc';//默认的排序方式是按syn降序，但是如果用户选择按用户名排序，则选择升序
if($sortStyle=='username') $order='asc';
#检测提交的分组查看
if(isset($_REQUEST['groupShow'])){
	$groupShow=$_REQUEST['groupShow'];
	$groupInlist=0;
	foreach($groupList as $e){
		if($e==$groupShow){
			$groupInlist=1;
			break;
		}
	}
}
if($groupInlist=0){
	$y=new notfine();
	exit();
}
#打开数据库以备查询
$link=linkToDBAndSelectDB($db_name1);
#如果是实验室用户第一次登录,则产生一条空的记录 /*


if($_SESSION['islab']){
	#以下检查training的完整性
	$q="select * from RecentTrainingQuery where username='$euser'";
	$currentUserStatus=mysql_query($q,$link) or die ('Cannot query RecentTrainingQuery');
	if(mysql_num_rows($currentUserStatus)==0){
		foreach($ojList as $ojType){
			$sql="insert into training(username,ojType,queryID,time,value) values ('$euser','$ojType','','" . date(YmdHis) ."',0)" ;
			if($ojType=='syn'){//如果是syn的话，用户名是username
				$sql="insert into training(username,ojType,queryID,time,value) values ('$euser','syn','$euser','" . date(YmdHis) ."',0)" ;
			}
			$result=mysql_query($sql,$link) or die ('Cannot query training when init user trainig');

		}
	}
	mysql_free_result($currentUserStatus);
	#以下检查userIDOnOJ的完整性,主要是加入syn这个字段
	$q="select * from userIDOnOJ where username='$euser' and ojType='syn'";
	$result=mysql_query($q,$link) or die ('Cannot query userIDOnOJ');
	if(mysql_num_rows($result)==0){
		$sql="insert into userIDOnOJ(username,ojType,ojID) values('$euser','syn','$euser')";
		mysql_query($sql,$link) or die('cannot query initialize syn id');
	}
}
#以下根据提供的排序方式和分组方式,从数据库中取出内容并产生一个信息数组
#1.选出所有最近做题信息,将这些信息,按用户名分组,
#2.将同类组的用户名信息导入到一个数组中.
#3.数组按行打印
#syn的更新利用综合刷新时的php脚本来控制
$q="select * from RecentTrainingQuery group by username,ojType";#使用分组可以保证排序，当然，使用orderby 也可以
$result=mysql_query($q,$link) or die('Cannot query RecentTrainingQuery when create table of trainging');

$userNum=-1;
$i=0;
$currentUsername='';
//将查询结果进行变换,保存在trainTable数组中
while($row=mysql_fetch_assoc($result)){
	if($row['username']!=$currentUsername){
		$userNum++;
		$i=0;
		$currentUsername=$row['username'];
		$trainTable[$userNum]['username']['value']=$currentUsername;

		#修改$modify变量,添加刷新和修改按钮
		if($_SESSION['islab_root']){
			$trainTable[$userNum]['modify']="<a href='updatetraining.php?muser=" . $row['username'] ."'>刷新</a>";
		}else if($row['username']==$euser){
			$trainTable[$userNum]['modify']="<a href='updatetraining.php?muser=" . $row['username'] . "'>刷新</a>";
		}else {
			$trainTable[$userNum]['modify']='';
		}
	}
	$ojType=$row['ojType'];

	#echo $use昵称rNum . " " . $ojType . " " ;
	#print_r($row);
	#echo "<br/>";

	$trainTable[$userNum][$ojType]=$row;
	$i++;
}
#这里写排序的代码
#sortTraining
function compare_asc($a,$b){
	global $sortStyle;
	if($a[$sortStyle]['value'] < $b[$sortStyle]['value']){
		return -1;
	}else if($a[$sortStyle]['value'] > $b[$sortStyle]['value']){
		return 1;	
	}else return 0;
}
function compare_desc($a,$b){
	global $sortStyle;
	if($a[$sortStyle]['value'] < $b[$sortStyle]['value']){
		return 1;
	}else if($a[$sortStyle]['value'] > $b[$sortStyle]['value']){
		return -1;	
	}else return 0;
}
#根据$order的不同选择排序的比较函数是升序还是降序,如果为空表则不排序
if($trainTable!=NULL){
	usort($trainTable,$order=='asc'?compare_asc:compare_desc);
}

echo <<<eot
<div>
<table class="trainTable">
<tr>
	<td>Rank</td><td><a href="training.php?sortStyle=username">ID</a></td> 
	<td>昵称</td>
eot;
foreach($ojList as $ojType){
	if($ojType=='username'){
		$ojTab='ID';
	}else if($ojType=='syn'){
		$ojTab='综合';
	}else $ojTab=$ojType;
	echo "<td><a href='training.php?sortStyle=$ojType'>$ojTab</td>";
}
echo "<td>$modyinfo</td></tr>";

#打印表单
$rank=1;
if($trainTable!=NULL){
	foreach($trainTable as $u){
		echo "<tr><td>" . $rank .  "</td>";

		if($_SESSION['islab_root']){	
			echo "<td><a href=modifypersoninfo.php?muser='" . $u['username']['value'] .  "'>" . $u['username']['value'] . "</a></td>";
		}elseif($_SESSION['islab_vip']){
			echo "<td><a href=personinfo.php?user='" . $u['username']['value'] .  "'>" . $u['username']['value'] . "</a></td>";
		}else{
			echo "<td>" . $u['username']['value'] .  "</td>";
		}

		$sql="select nickname from personinfo where username='" . $u['username']['value'] . "'";
		$result=mysql_query($sql,$link) or die ("cannot query nickname in training");
		$row=mysql_fetch_assoc($result);
		echo "<td id='tdnickname'>" . $row['nickname'] . "</td>";

		foreach($ojList as $ojType){
			$score=$u[$ojType]['value'];
			if($ojType=='tc' || $ojType=='cf'){
					$score=(String)( ((int)(($u[$ojType]['value']) /10000)) ) . '/' .  (String)((int)($u[$ojType]['value']))%1000;
			}else if($ojType=='usaco'){
					$score=(String)($u[$ojType]['value']/10);
			}
			echo "<td title='$ojType 帐号: " . $u[$ojType]['queryID'] . " " .
				"最后更新时间:" . $u[$ojType]['updateTime'] .
				"'>" . $score . "</td>";
		}
		echo "<td class='modify'>" . $u['modify'] . "</td></tr>";
		$rank++;
	}
}
echo <<<eot
</table>
</div>
eot;
?>
