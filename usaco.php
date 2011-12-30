<html>
<?php 
require_once("conf.php");
require_once("f.php");
?>
	<meta charset='utf8'/>
	<title>
		西电ACM 寒假usaco刷题大比拼
	</title>
	<head>
	</head>
	<body>
寒假放假期间，有没有想要提高自己的算法功底，训练自己的代码能力呢？ 参与到西电ACM
寒假USACO刷题大比拼中来吧！这里有高手指导，菜鸟陪练，保证你寒假独自刷题不再寂寞，还在等什么呢？
发送xdu oj的id到acmxidian@gmail.com开通，填写usaco帐号进入刷题榜哦！
<?php
	$link=linkToDBandSelectDB($db_name1);
	$sql="select username,nickname,value from personinfo,RecenetTrainingQuery order by value desc";
	$result=mysql_query($sql,$link) or die ("cannot query db in usaco.php");
	while($r=mysql_fetch_assoc($result)){
		echo "$r['username']";
	}
	

?>
	</body>
</html>
