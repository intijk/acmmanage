<?php
require_once("frame.php");
$x=new frame();
//下方加入页面所调用的功能

echo <<<eot
		<div align="left" style="margin-left:20%;margin-right:20%">
		<ul>

			<li>注册方法：将你在<a href="http://acm.xidian.edu.cn/land"/>西电Online Judge</a>的账号和姓名，学院，年级发给acmxidian@gmail.com. </li>
			<br/>
			<li>当你的账号审核通过以后，就可以用西电Online Judge的账号登录此系统.</li>
			<br/>
<li>进入之后先点”设置“，填写你在各个OJ的账号，即可进行自动抓取，如果长时间（5分钟）没有得到最新数据，你可以尝试点击右侧的刷新，但请不要刷新过多。</li>
			<br/>
<br/>
		</ul>
		</div>

eot;

?>
