<?php
require_once("frame.php");
require_once("f.php");
$x=new frame();
$sortStyle='syn';//默认的排序方式
$styleInList=1;
$sortStyleList=array('rank','username','pku','zju','hdu','usaco','tc','cf','syn');
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

if(!$_SESSION['loggedin']){
	echo <<<eot
<div>
	你还没有登录，无权查看信息.
</div>
eot;
exit();
}
$euser=$_SESSION['euser'];
function cmpsynthesize($a,$b){//根据综合成绩进行比较的函数,大者靠前
	if($a==$b)return 0;
	if($a['syn']<$b['syn']){
		return 1;
	}
	return -1;
}

$link=linkToDBAndSelectDB($db_name1);
$order='desc';//默认的排序方式是按syn降序，但是如果用户选择按用户名排序，则选择升序
if($sortStyle=='username') $order='asc';
$q="select username,pku,zju,hdu,usaco,tc,cf,syn,T.time time,R.uname,R.time from train T,Recent R where username=R.uname and T.time=R.time order by $sortStyle $order";
//这条语句选出每个用户最新的一组值
$result=mysql_query($q,$link) or die('Cannot query in train');
$s="select username,pku,zju,hdu,usaco,tc,cf,syn,T.time time,R.uname,R.time from train T,Recent R where username='$euser' and username=R.uname and T.time=R.time";
//$s="select uname,time from Recent where uname='$euser'";
$currentUserStatus=mysql_query($s,$link) or die ('cannot query in currentUserStatus');
//这里首先检测用户是否在这个表单里，如果不在这个表单里,则检测用户是否属于实验室组，则向数据库插入当月一个全零值,如果用户进行登录之后的月份要大于当前其最新月份，则插入一组与原先相同的新值来代表当月的数据情况
if(mysql_num_rows($currentUserStatus)==0){
	if(userAingroupB($_SESSION['euser'],'lab')){
		$link1=linkToDBAndSelectDB($db_name1);
		//向数据库插入该用户全0
		$sql="insert into train(username,pku,zju,hdu,usaco,tc,cf,syn,time) values('$euser',0,0,0,0,0,0,0," . "'" . date('Y-m') . "-01'" . ")";
		mysql_query($sql,$link1) or die('cannot query train');
		//由于进行了检测性的更新，所以重新查询数据库
		mysql_free_result($currentUserStatus);
		mysql_free_result($result);
		mysql_close($link1);
		$result=mysql_query($q,$link) or die('Cannot query in train');
	}
}else{
	$row=mysql_fetch_assoc($currentUserStatus);
	if(strtotime(date('Y-m') . '-01') > strtotime($row['time'])){
		$q1="insert into train(username,pku,zju,hdu,usaco,tc,cf,syn,time) values('$euser'," . $row['pku'] . "," . $row['zju'] . "," . $row['hdu'] . "," . $row['usaco'] . "," . $row['tc'] . "," . $row['cf'] . "," . $row['syn'] . ",'" . date('Y-m') . '-01' . "')";
		mysql_query($q1) or die('cannot insert in train');
		mysql_free_result($result);
		$result=mysql_query($q,$link) or die('Cannot query in train');
	}
}
$i=0;
while($row=mysql_fetch_assoc($result)){
	//if(userAingroupB($row['username'],'lab'),此处检测用户是否是实验室组的用户
	$trainTable[$i]=$row;
	$i++;
}
mysql_free_result($result);
mysql_close($link);
//usort($trainTable,'cmpsynthesize');
echo <<<eot
<div>
<table class="trainTable">
<tr>
	<td>Rank</td><td><a href="train.php?sortStyle=username">ID</a></td> 
eot;
echo <<<eot
<td><a href='train.php?sortStyle=pku'>pku</a></td> <td><a href="train.php?sortStyle=zju">zju</a></td> <td><a href="train.php?sortStyle=hdu">hdu</a></td> <td><a href="train.php?sortStyle=usaco">usaco</a></td> <td><a href="train.php?sortStyle=tc">tc</a></td> <td><a href="train.php?sortStyle=cf">cf</a></td> <td><a href="train.php?sortStyle=syn">综合</a></td> <td>时间</td> <td></td>
</tr>

eot;
$i=1;
foreach($trainTable as $e){
	
	if(userAingroupB($_SESSION['euser'],'lab_root'))$modify="<a href=modifytrain.php?muser=" . $e['username'] . ">修改</a>";
	else if($_SESSION['euser']==$e['username']) $modify="<a href=modifytrain.php?muser=" . $e['username'] . ">修改</a>";
	else $modify='';
	echo "<tr>\n";
	echo "	<td>$i</td>";
	echo "	<td><a href=personinfo.php?user=" . $e['username'] . ">" . $e['username'] . "</a></td>";
	echo '<td>' . $e['pku'] . '</td>'; 
	echo '<td>' . $e['zju'] . '</td>';
	echo '<td>' . $e['hdu'] . '</td>';
	printf("<td>%.1f</td>",$e['usaco']);
	echo '<td>' . $e['tc'] . '</td>';
	echo '<td>' . $e['cf'] . '</td>';    
	echo '<td>' . $e['syn'] . '</td>';
	echo '<td>' . date("Y-m",strtotime($e['time'])) . '</td>';
	echo '<td>' . $modify . "</td>\n";
	echo "</tr>\n";
	$i++;
}
echo <<<eot
</table>
</div>
eot;
?>
