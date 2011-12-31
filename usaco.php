<html>
<?php 
require_once("conf.php");
require_once("f.php");
?>
	<meta charset='utf-8'/>
<link rel="stylesheet" type="text/css" href="ustyle.css"/>
	<title>
		西电ACM 寒假USACO刷题大比拼!
	</title>
	<head>
		<h1>西电ACM 寒假USACO刷题大比拼!</h1>
	</head>
	<body>
	&nbsp; &nbsp; &nbsp; 寒假放假期间，有没有想要提高自己的算法功底，训练自己的代码能力呢？ 参与到西电ACM寒假USACO刷题大比拼中来吧！这里有高手指导，菜鸟陪练，保证你寒假独自刷题不再寂寞，还在等什么呢？</br></br>
 	&nbsp; &nbsp; &nbsp; 发送 XDU OJ 的 ID 到 acmxidian@gmail.com 开通，<a href="http://acm.xidian.edu.cn/acmmanage">填写 USACO 帐号</a>进入刷题榜哦！<br/>
<br/>
<?php
$link=linkToDBandSelectDB($db_name1);
$sql="select t.username as username ,p.nickname as nickname ,t.updateTime as updateTime,t.value as value from RecentTrainingQuery as t,personinfo as p where ojType='usaco' and t.username=p.username order by t.value desc,t.updateTime asc";

echo <<<eot
<table class='utable'>
<tr><th>排名</th><th>ID</th><th>昵称</th><th>USACO章节</th><th>更新时间</th></tr>
eot;
$result=mysql_query($sql,$link) or die ("cannot query db in usaco.php");
$i=0;	
while($r=mysql_fetch_assoc($result)){
	$i++;
	if($i%2==0){
		echo '<tr>';
	}else{
		echo "<tr class='alt'>";
	}
	echo '<td>';
	echo '第' . $i  . '名';
	echo '</td>';
	echo '<td>';	
	echo $r['username'];
	echo '</td>';
	echo '<td>';
	if($r['nickname']==''){
		echo '&nbsp';
	}else{
		echo $r['nickname'];
	}
	echo '</td>';
	echo '<td>';
	$score=(int)($r['value']/10) . "." . (int)($r['value']%10);
	if($score=='6.2'){
		echo "<img src='finish.png' width='140em'></img>";
	}else{
		echo $score;
	}
	echo '</td>';
	echo '<td>';
	echo $r['updateTime'];
	echo '</td>';
	echo '</tr>';
}

echo <<<eot
</table>
eot;
?>
	</body>
</html>
