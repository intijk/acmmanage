<?php
require_once("f.php");
require_once("frame.php");
//这里打印某学生的综合信息，包括训练信息，竞赛信息，以及私人联系信息（只有lab_vip和lab_root才可以访问);
$x=new frame();
if(isset($_REQUEST['user'])){
	$user=$_REQUEST['user'];
}
else $user=$_SESSION['euser'];
if(userAingroupB($user,'lab')==0){
	echo <<<eot
<div>
	错误的用户名。
</div>
eot;
	exit;
}
$link=linkToDBandSelectDB($db_name1);
if($_SESSION['islab_vip']==1 or $_SESSION['islab_root']==1 or $_SESSION['islab']==1){
	echo "<div>\n";
	echo "<table>\n";
	$sql="select * from personinfo where username='$user'";
	$result=mysql_query($sql,$link) or die('cannot query in personinfo');
	$row=mysql_fetch_array($result);
	echo "<div>\n";
	echo "<table>\n";
	echo "<tr><td>ID</td><td>" . $user . "</td></tr>";
	echo "<tr><td>真实姓名</td><td>" . $row['realname'] . "</td></tr>";
	echo "<tr><td>年级</td><td>" . $row['grade'] . "</td></tr>";

	$sql="select value from academynum where num='" . $row['academy'] . "'";
	$result1=mysql_query($sql,$link) or die('cannot query academy');
	$row1=mysql_fetch_assoc($result1);
	echo "<tr><td>院系</td><td>" . $row1['value'] . "</td></tr>";
	echo "<tr><td>学号</td><td>" . $row['StuNum'] . "</td></tr>";
	echo "<tr><td>邮箱</td><td>" . $row['mail'] . "</td></tr>";
	echo "<tr><td>手机</td><td>" . $row['mobilephone'] . "</td></tr>";
	echo "<tr><td>QQ</td><td>" . $row['im'] . "</td></tr>";
	echo "<tr><td>昵称</td><td>" . $row['nickname'] . "</td></tr>";
	echo "<tr><td></td><td></td></tr>";

	$sql="select ojType,ojID from userIDOnOJ where username='$user' and needOJID=true order by ojType asc;
	$resQueryOJID=mysql_query($sql,$link) or die('cannot query user ID on OJ');
	
	while($ojID=mysql_fetch_assoc($resQueryOJID)){
		echo "<tr><td>" . $ojID['ojType'] . " ID</td><td>" . $ojID['ojID'] . "</td></tr>";
	}

	echo "</table>\n";
	echo "</div>\n";
	mysql_free_result($result);
	mysql_free_result($result1);
}
mysql_close($link);
?>
