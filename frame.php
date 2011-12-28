<?php
session_start();
if(!isset($_SESSION['loggedin'])){
	$_SESSION['loggedin']=0;
}
class frame {
	public function __construct(){
		require_once("header.php");
	}
	public function __destruct(){
		require_once("footer.php");
	}
}
class notloggedin{
	public function __construct(){
		echo <<<eot
<div>
	您还没有登录，请登录。
</div>
eot;
	}
}
class notfind{
	public function __construct(){
		echo <<<eot
<div>
	没有找到您所要的页面。
</div>
eot;
	}
}
class formaterror{
	public function __construct($con){
		echo <<<eot
<div>
	$con 格式错.
</div>
eot;
	}
}
class rangeerror{
	public function __construct($con){
		echo <<<eot
<div>
	$con 数据范围错
</div>
eot;
	}
}
?>
