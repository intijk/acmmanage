<?php
require_once("frame.php");
$x=new frame();
echo <<<eot
<div>
<form action="loginto.php" method="post">
<table>
<tr>  <td>User:</td>
	  <td><input type="text" name="user"></td>
</tr>
<tr>
<tr>
	<td>Password:</td>
	<td><input type="password" name="password"</td>
</tr>
<tr>
	<td></td>
	<td><input type="submit" value="submit"/></td>
</tr>
</table>
</form>
</div>
eot;
?>
