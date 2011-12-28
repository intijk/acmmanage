<?php
require_once("conf.php");
require_once("frame.php");
require_once("f.php");
if($_SESSION['loggedin']==0){
	$x=new frame();
	$x=new notloggedin();
	exit;
}
$userToModify=$_SESSION['euser'];
if(isset($_REQUEST['muser'])){//检测修改用户的权限，并且产生一个合法的有效用户名$userToModify
	if(userAingroupB($_SESSION['euser'],'lab_root')){
		$userToModify=$_REQUEST['muser'];
	}else if($_SESSION['euser']==$_REQUEST['muser']){
		$userToModify=$_REQUEST['muser'];
	}else{
		$x=new frame();
		echo <<<eot
<div>
	你无权修改此用户的信息。
</div>
eot;
		exit;
	}
}
$x=new frame();
$modiInfo='';
if(isset($_REQUEST['mtime']) && isset($_REQUEST['muser'])){//如果同时提交了muser 和 time 说明要修改相应的数据段了，这时要获取所有字段的信息
	$mtime=$_REQUEST['mtime'];
	//这里连接数据库，检查时间修改的安全性	
	$link=linkToDBandSelectDB($db_name1);
	$sql="select time from train where username='$userToModify' order by time desc";
	$result=mysql_query($sql,$link) or die('cannot query');
	$i=0;
	$mtimeLegal=0;
	if($_SESSION['islab_root']){
		$monthsToModify=mysql_num_rows($result);
	}
	while($i<$monthsToModify && $row=mysql_fetch_assoc($result)){
		if($row['time']==$mtime){
			$mtimeLegal=1;
			break;
		}
		$i++;
	}
	if($mtimeLegal==0){
		die('<div>您欲修改的字段不合法</div>');
	}//时间安全性检查完毕


	//下面部分要进行数据检查，所有的数据要满足相应格式,如果有一项数字不合格，则退出要求对方重新更正
	if(isset($_REQUEST['usaco'])){
		$usaco=$_REQUEST['usaco'];
		$usaco1=(String)$usaco;
		if($usaco1=='0.0' || preg_match('/^[1-9].[1-9]$/i',$usaco1)){//这里的数据范围允许0.0作为未做题的标志
			if($usaco<0 || $usaco > $usacoUpperSectionNumber){
				$y=new rangeerror("usaco=$usaco");
				exit;
			}
		}else{
			$y=new formaterror('usaco=' . $usaco1);
			exit;
		}
	}
	
	//这里产生数据库的更新操作
	$link=linkToDBandSelectDB($db_name1);
	if(isset($usaco)){
		$col='usaco';
		$val=$usaco;
		$sql="update train set $col=$val where username='$userToModify' and time='$mtime'";
		mysql_query($sql,$link) or die('cannot chang \'usaco\' in mysql');
	}
	mysql_close($link);
	$modiInfo="修改成功";
}
$link=linkToDBandSelectDB($db_name1);
$sql="select username,pku,zju,hdu,usaco,tc,cf,syn,time from train time where username='$userToModify' order by time desc";
$result=mysql_query($sql,$link) or die("connot query in modifytrain");
$i=0;
while($row=mysql_fetch_assoc($result)){
	$userTrainTable[$i]=$row;
	$i++;
}
if($i==0){
	echo <<<eot
<div>
所要修改的用户不存在
</div>
eot;
	exit;
}
mysql_free_result($result);
mysql_close($link);
echo "<div>\n";
echo "<table class=\"trainTable\">\n";
if(userAingroupB($_SESSION['euser'],'lab_root')){//root可以修改的时间权限是所有
	$monthsToModify=count($userTrainTable);
}
echo "<tr>\n";
echo "<td>ID</td><td>pku</td><td>zju</td><td>hdu</td><td>usaco</td><td>tc</td><td>cf</td><td>综合</td><td><font color='red'>$modiInfo</font></td>\n";
echo "<tr>\n";

for($i=0;$i<$monthsToModify && $i<count($userTrainTable) ;$i++){
	echo "<tr>\n";

	echo "<form action='modifytrain.php' method='get'>\n";
	echo "<td>" . $userTrainTable[$i]['username'] . "</td>";
    echo "<td>" . $userTrainTable[$i]['pku'] . "</td>";
	echo "<td>" . $userTrainTable[$i]['zju'] . "</td>";
	echo "<td>" . $userTrainTable[$i]['hdu'] . "</td>";

	$userTrainTable[$i]['usaco']=(float)($userTrainTable[$i]['usaco']);
	printf("<td><input type='text' name='usaco' value='%.1f' /></td>",$userTrainTable[$i]['usaco']);
	//echo "<td><input type='text' name='usaco' value='" . $userTrainTable[$i]['usaco'] . "'/></td>";
	echo "<td>" . $userTrainTable[$i]['tc'] . "</td>";
	echo "<td>" . $userTrainTable[$i]['cf'] . "</td>";
	echo "<td>" . $userTrainTable[$i]['syn'] . "</td>";
	echo "<td><input type='hidden' name='mtime' value='" . $userTrainTable[$i]['time'] . "'/>". "<input type='hidden' name='muser' value='" . $userToModify ."'/>" ."<input type='submit' value='提交修改'/>" . "</td>";
	echo "</form>\n";

	echo "</tr>\n";
}
for(;$i<count($userTrainTable);$i++){
	echo "<tr>\n";
	echo "<td>" . $userTrainTable[$i]['username']  . "</td>";
	echo "<td>" . $userTrainTable[$i]['pku']  . "</td>";
	echo "<td>" . $userTrainTable[$i]['zju']  . "</td>";
	echo "<td>" . $userTrainTable[$i]['hdu']  . "</td>";
	$userTrainTable[$i]['usaco']=(float)($userTrainTable[$i]['usaco']);
	printf("<td> %.1f </td>",$userTrainTable[$i]['usaco']);
//	echo "<td>" . $userTrainTable[$i]['usaco']  . "</td>";
	echo "<td>" . $userTrainTable[$i]['tc']  . "</td>";
	echo "<td>" . $userTrainTable[$i]['cf']  . "</td>";
	echo "<td>" . $userTrainTable[$i]['syn']  . "</td>";
	echo "<td> </td>";
	echo "<tr>\n";

}
echo "</table>\n";
echo "</div>\n";
?>
