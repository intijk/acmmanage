#!/usr/bin/php
<?php
#参数列表:
#programName username ojType id [pass]
#用户username查询ojType 上的信息，用id帐号，用pass密码
require_once("crawlpage.php");
require_once("conf.php");
require_once("f.php");
if($argc!=4 && $argc!=5){
	echo "Usage: $argv[0] username ojName ojID [ojPass]\n";
	return -1;
}

$username=$argv[1];
$ojType=$argv[2];
$ojID=$argv[3];
$ojPass='';
$argc==5 and $ojPass=$argv[4];
#函数名称为  get + $ojType + data ,比如pku为 getpkudata
$fName="get" . $ojType . "data";

$link=linkToDBandSelectDB($db_name1);
$sql="select * from ojList where ojName='$ojType'";
$result=mysql_query($sql,$link) or die('Cannot query db in grabOJData.php\n');
$row=mysql_fetch_assoc($result);
if($row['needOJPass']){
	//没有在命令行上给出密码
	if($argc!=5){	
		$sql="select ojPass from userIDOnOJ where username='$username' and ojID='$ojID' and ojType='$ojType'";
		$result=mysql_query($sql,$link) or die('Cannot query userIDOnOJ in grabOJData.php\n');
		$row=mysql_fetch_assoc($result);
		$ojPass=$row['ojPass'];
	}
	echo $fName($ojID,$ojPass);
}else{
	echo $fName($ojID);
}
?>
