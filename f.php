<?php
require_once("conf.php");
function linkToDBandSelectDB($dbname){
	if(!$link=mysql_connect($GLOBALS['db_server'],$GLOBALS['db_user'],$GLOBALS['db_password'])){
		die('Cannot connect to mysql in linkToDBandSelectDB');
	}
	if(!mysql_select_db($dbname,$link)){
		die('Cannot select database in linkToDBandSelectDB');
	}
	if(!mysql_query('set names utf8')){
		die('Cannot set encoding');
	}
	return $link;
}
function userAingroupB($A,$B){
	if(!$link=mysql_connect($GLOBALS['db_server'],$GLOBALS['db_user'],$GLOBALS['db_password'])){
		die('Cannot connect to mysql in userAingroupB');
	}
	if(!mysql_select_db($GLOBALS['db_name'],$link)){
		die('Cannot select database in userAingroupB');
	}
	$sql="select group_ids from users where username='$A'";
	$result=mysql_query($sql,$link);
	if(!$result){
		die('Cannot query database in userAingroupB1');
	}
	$row=mysql_fetch_assoc($result);
	//对row进行分割,然后查询groups表看有没有$B这个组
	$groups=explode(';',$row['group_ids']);		
	foreach($groups as $group){
		$sql1="select group_name from groups where group_id='$group'"; 	
		$result1=mysql_query($sql1,$link) or die('Cannot query database in userAingroupB');
		$row1=mysql_fetch_assoc($result1);	
		if($row1['group_name']==$B)return 1;		
	}
	mysql_free_result($result);
	return 0;
}
?>
