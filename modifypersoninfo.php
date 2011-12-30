<?php
require_once("conf.php");
require_once("frame.php");
require_once("f.php");
$x=new frame();
if(!$_SESSION['loggedin']){
	echo <<<eot
<div>
	未登录，无法修改信息。
</div>
eot;
exit();
}
//打印一张表格，最上面是用户ID，往下依次是真名，年级，院系，学号，邮箱，手机号码，qq号码等信息
//首先，检验组，lab_vip这个组用来给相关的老师设计，他们不需要填写任何信息,root也属于这个组，但是root可以修改人员相关的信息，而lab_vip则不可以(依情况而定);
//这里采用传参数的形式，如果非root用户，则

$muser='';

$muser=$_SESSION['euser'];
if(isset($_REQUEST['muser'])){
	$muser=$_REQUEST['muser'];
	if(userAingroupB($_SESSION['euser'],'lab_root')){
		#修改人是root，但修改一个不存在的用户
		if(userAingroupB($muser,'lab')==0){
			echo<<<eot
<div>
$muser 非本实验室用户。
</div>
eot;
			exit;
		}
	#修改人不是root,但在尝试修改它人信息
	}else if($muser!=$_SESSION['euser']){
		//非管理用户欲修改它人信息
		echo<<<eot
<div>
你不能修改此用户的信息。
</div>
eot;
		exit;
	}
}else{
	#没有指定任何修改人，默认修改自己
	$muser=$_SESSION['euser'];
}
$link=linkToDBandSelectDB($db_name1);
$sql="select * from personinfo where username='" . $muser . "'";
$result=mysql_query($sql,$link) or die('cannot query in modifypersoninfo');
if(mysql_num_rows($result)==0){
	$q="insert into personinfo(username) values ('$muser')";
	mysql_query($q,$link) or die ('cannot insert in modifypersoninfo');
}
$updateinfo='';
$updateinfoID='';
//以下部分要进行格式的检测
//
if(isset($_REQUEST['realname'])) {
	$realname=$_REQUEST['realname'];
	$q="update personinfo set realname='" . $realname . "' where username='" . $muser . "'";
	mysql_query($q,$link) or die ('cannot update realname in modifypersoninfo');
	$updateinfo="修改成功";
}
if(isset($_REQUEST['grade'])){
	$grade=$_REQUEST['grade'];
	$gradeString=(String)$grade;
	if($gradeString!=''){
		if(!preg_match('/^[0-9]{4}$/i',$gradeString)){
			$y=new formaterror('年级=' . $gradeString);
			exit;
		}else{
			if($grade < 1999 || $grade > 2088){
				$z=new rangeerror('年级=' . $grade);
				exit;
			}
		}
	}
	$q="update personinfo set grade='" . $grade . "' where username='" . $muser . "'";
	mysql_query($q,$link) or die ('cannot update grade in modifypersoninfo');
	$updateinfo="修改成功";
}
if(isset($_REQUEST['academy'])){
	$academy=$_REQUEST['academy'];
	$academyString=(String)$academy;
	if($academyString!=''){
		if(!preg_match('/^[0-9]{1,2}$/i',$academyString)){
			$y=new formaterror('学院=' . $academyString);
			exit;
		}else{
			if($academy < 0 || $academy > $academyTotalNumber){
				$z=new rangeerror('学院=' . $academy);
				exit;
			}
		}
	}
	$q="update personinfo set academy='" . $academy . "' where username='" . $muser . "'";
	mysql_query($q,$link) or die ('cannot update academy in modifypersoninfo');
	$updateinfo="修改成功";
}
if(isset($_REQUEST['StuNum'])){
	$StuNum=$_REQUEST['StuNum'];
	$StuNumString=(String)$StuNum;		
	if($StuNumString!=''){
		if(!preg_match('/^[0-9]{8}$/i',$StuNumString)){
			$y=new formaterror('学号=' . $StuNum);
			exit;
		}
	}
	$q="update personinfo set StuNum='" . $StuNum . "' where username='" . $muser . "'";
	mysql_query($q,$link) or die ('cannot update StuNum in modifypersoninfo');
	$updateinfo="修改成功";

}
if(isset($_REQUEST['mail'])){
	$mail=$_REQUEST['mail'];
	$mailString=(String)$mail;
	if($mailString!=''){
		if(!preg_match('/^[a-zA-Z0-9\-\.]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.]+$/i',$mailString)){
			$y=new formaterror("邮箱=" . $mail);
			exit;
		}
	}
	$q="update personinfo set mail='" . $mail . "' where username='" . $muser . "'";
	mysql_query($q,$link) or die ('cannot update mail in modifypersoninfo');
	$updateinfo="修改成功";

}
if(isset($_REQUEST['mobilephone'])){
	$mobilephone=$_REQUEST['mobilephone'];
	$mobilephoneString=(String)$mobilephone;
	if($mobilephoneString!=''){
		if(!preg_match('/^[0-9]{11}$/i',$mobilephoneString)){
			$y=new formaterror('手机' . $mobilephoneString);
			exit;
		}
	}
	$q="update personinfo set mobilephone='" . $mobilephone . "' where username='" . $muser . "'";
	mysql_query($q,$link) or die ('cannot update mobilephone in modifypersoninfo');
	$updateinfo="修改成功";
}
if(isset($_REQUEST['im'])){
	$im=$_REQUEST['im'];
	$q="update personinfo set im='" . $im . "' where username='" . $muser . "'";
	mysql_query($q,$link) or die ('cannot update im in modifypersoninfo');
	$updateinfo="修改成功";
}

if(isset($_REQUEST['nickname'])){
	$nickname=$_REQUEST['nickname'];
	$q="update personinfo set nickname='" . $nickname . "' where username='" . $muser . "'";
	mysql_query($q,$link) or die ('cannot update nickname in modifypersoninfo');
	$updateinfo="修改成功";
}



#开始检测和更新oj的账户和密码
$sql="select * from ojList where needOJID=True";
$ojList=mysql_query($sql,$link) or die('cannot query ojList');
#遍历每个OJ，检索是否有修改ID的请求
while($oj=mysql_fetch_assoc($ojList)){
	#0. 是否有此oj的信息
	$idOnOJExists=False;
	#1. 取出历史信息
	#2. 检测账户对应的oj对应的id项是否存在。 $idOnOJExists
	#3. 根据是否存在决定如何插值，即使没有相关项，如密码，也要进行插值（空串）。
	$needUpdateID=False;
	$needUpdatePass=False;
	$needUpdateTask=False;

	#如果不存在或账户改变或密码改变，都进行相应的更新
	
	#0. 是否有此oj的更新信息
	if(isset($_POST[$oj['ojName'] . 'ID'])){
		#1. 取出历史信息
		$id=$_POST[$oj['ojName'] . 'ID'];#取出$_POST['pkuID']这样的数据
		$pass=$_POST[$oj['ojName'] . 'Pass'];#同上，取出密码
		$sql="select ojID from userIDOnOJ where ojType='" . $oj['ojName'] . "' and username='$muser'";
		$idCountArray=mysql_query($sql,$link) or die('cannot query user id on oj');
	
		$idCount=mysql_num_rows($idCountArray);
		#2. 检测是否已经存在id项
		if($idCount>0){
			$idOnOJExists=True;
			$idCountRow=mysql_fetch_assoc($idCountArray);
			if($idCountRow['ojID']!=$id){
				#如果存在该ID，并且帐号或密码不同则需要更新
				$needUpdateID=True;
			}
			if($pass!='' and $idCountRow['ojPass']!=$pass){
				$needUpdatePass=True;
			}
		else{
			#如果不存在该ID，则标记不存在
			$idOnOJExists=false;
		}
		#如果发生了一个插入id或者id更新事件 ,则需要自动抓取一次数据
		if($idOnOJExists==false or $needUpdateID==True or $needUpdatePass==True){
			$needUpdateTask=True;
		}
		#如果该用户在该oj上的账户不存在,则插入一该id值
		if(!$idOnOJExists){
			$sql="insert into userIDOnOJ(username,ojType,ojID,ojPass) values('$muser','" . $oj['ojName'] . "','$id','$pass')";
			mysql_query($sql,$link) or die('cannot insert new user id on oj') ;
		}
		#如果账户存在但是账户或密码修改了，需要更新
		if($needUpdateID){
			$ojType=$oj['ojName'];
			$sql="update userIDOnOJ set ojID='$id' where username='$muser' and ojType='$ojType'";
			mysql_query($sql,$link) or die('cannot update user id and password');
		}
		if($needUpdatePass){
			$ojType=$oj['ojName'];
			$sql="update userIDOnOJ set ojPass='$pass' where username='$muser' and ojType='$ojType'";
			mysql_query($sql,$link) or die('cannot update user id and password');
		}
		#不论插入还是修改引起的，都抓取一次数据

		if($needUpdateTask){
			$sql="insert into updateTaskList(username,ojType,id,queryTime,status,failTimes) values('$muser','" . $oj['ojName'] . "','$id','" . date("Y-m-d H:i:s") ."',0,0)";
			mysql_query($sql,$link) or die('cannot add query task');

		}
		$updateinfoID='修改成功';
	}
}



#开始进行页面的显示
$sql="select * from personinfo where username='" . $muser . "'";
mysql_free_result($result);
$result=mysql_query($sql,$link) or die('cannot query in modifypersoninfo');
$row=mysql_fetch_assoc($result);
echo "<div>";
echo "<table>\n";
echo "<form action='modifypersoninfo.php' method='get'>\n";
echo "<tr><td>ID</td><td>" . $muser . "</td></tr>";
echo "<tr><td>真实姓名</td><td><input type='text' name='realname' value='" . $row['realname'] . "'/></td></tr>";
echo "<tr><td>年级</td><td><input type='text' name='grade' value='" . $row['grade'] . "'/></td></tr>\n";

$selectStatus[$row['academy']]="selected='selected'";
echo "<tr><td>院系</td><td>\n";
echo "<select name='academy'>\n";
echo "<option value='0' $selectStatus[0]>-----------</option>\n";
echo "<option value='1' $selectStatus[1]>通信工程学院</option>\n";
echo "<option value='2' $selectStatus[2]>电子工程学院</option>\n";
echo "<option value='3' $selectStatus[3]>计算机学院</option>\n";
echo "<option value='4' $selectStatus[4]>机电工程学院</option>\n";
echo "<option value='5' $selectStatus[5]>技术物理学院</option>\n";
echo "<option value='6' $selectStatus[6]>经济管理学院</option>\n";
echo "<option value='7' $selectStatus[7]>人文学院</option>\n";
echo "<option value='8' $selectStatus[8]>理学院</option>\n";
echo "<option value='9' $selectStatus[9]>微电子学院</option>\n";
echo "<option value='10' $selectStatus[10]>软件学院</option>\n";
echo "<option value='11' $selectStatus[11]>长安学院</option>\n";
echo "<option value='12' $selectStatus[12]>网络与继续教育学院</option>\n";
echo "<option value='13' $selectStatus[13]>生命科学技术学院</option>\n";
echo "<option value='14' $selectStatus[14]>国际教育学院</option>\n";
echo "</select>\n";
echo "</tr>\n";
echo "<input type='hidden' name='muser' value='$muser'>";
echo "<tr><td>学号</td><td><input type='text' name='StuNum' value='" . $row['StuNum'] . "'/></td></tr>";
echo "<tr><td>邮箱</td><td><input type='text' name='mail' value='" . $row['mail'] . "'/></td></tr>";
echo "<tr><td>手机</td><td><input type='text' name='mobilephone' value='" . $row['mobilephone'] . "'/></td></tr>";
echo "<tr><td>QQ</td><td><input type='text' name='im' value='" . $row['im'] . "'/></td></tr>";
echo "<tr><td>昵称</td><td><input type='text' name='nickname' value='" . $row['nickname'] . "'/></td></tr>";
echo "<tr><td><font color='red'>$updateinfo</font></td><td><input type='submit' value='修改'/></td>";
echo "</form>";
echo "</table>";
echo "</div>";
#以下打印ojID的修改列表
echo "<table>";
echo "<form action='modifypersoninfo.php' method='post'>\n";
echo "<input type='hidden' name='muser' value='$muser'>";
echo "<tr><td>OJ名称</td><td>OJ 帐号</td><td>OJ密码</td></tr>";
mysql_data_seek($ojList, 0);
#数据可能发生不一一致,导致一个用户多个ID的情况，这里首先取出用户的所有ID，然后只取第一个使用
while($r=mysql_fetch_assoc($ojList)){
	$sql="select ojID,ojPass from userIDOnOJ where username='$muser' and ojType='" . $r['ojName'] . "'";
	$idList=mysql_query($sql,$link) or die('cannot query user ID');
	$id=mysql_fetch_assoc($idList);  

	echo "<tr>";
	echo "<td>" . $r['ojName'] . "</td>";
	echo "<td>";
	if( $r['needOJID']){
		echo "<input type='text' name='" . $r['ojName'] . "ID' value='" . $id['ojID'] . "'/>";
	}
	echo "</td>";
	echo "<td>";
	if( $r['needOJPass'] ){
		echo "<input type='password' name='" . $r['ojName'] . "Pass' value=''/>";
	}

	echo "</td>";
	echo "</tr>";
}
echo "<tr><td><font color='red'>$updateinfoID</font></td><td><input type='submit' value='修改'/></td><td></td>";
echo "</form>";
echo "</table>";
mysql_free_result($result);
mysql_free_result($ojList);
mysql_close($link);
?>
