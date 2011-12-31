<html>
<?php 
require_once("conf.php");
require_once("f.php");
?>
	<meta charset='utf-8'/>
<link rel="stylesheet" type="text/css" href="u.css"/>
	<title>
		西电ACM 2012寒假USACO刷题大比拼!
	</title>
	<head>
		<h1>2012 寒假 <a href="http://ace.delos.com/usacogate"><img class="ver2" src="./usaco2.png"/></a> 刷题 <img class="ver1" src="./dabipin2.png"/></h1>
	</head>
	<body>
 	<h2>发送 XDU OJ 的 ID 到 acmxidian@gmail.com 开通，<a href="http://acm.xidian.edu.cn/acmmanage">填写 USACO 帐号</a>进入刷题榜！</h2><br/>
<br/>
<table class="nav"><tr>
<td><a href="http://acm.xidian.edu.cn/land">XDOJ</a></td>
<td><a href="http://acm.xidian.edu.cn/acmmanage">训练</a></td>
<td><a href="http://ace.delos.com/usacogate">USACO</td>
</table></tr>
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
	echo '第 ' . $i  . ' 名';
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
		echo "<img src='finish.png' width=120px></img>";
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
