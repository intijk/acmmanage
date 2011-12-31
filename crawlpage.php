<?php
require_once("Snoopy.class.php");
$debug=0;
#getURLPattern 给一个正则表达式以及, 一个页面,返回一个指向结果的多维指针
function getURLPattern($Pat,$URL){
    global $debug;
    if($debug>0){
        echo "你请求的页面是" . $URL . "<br/>";
        echo "你给与的正则表达式是" . $Pat . "<br/>";
    }
    $fp=file_get_contents($URL);
    if($debug>1){
	    echo "你请求的页面的返回结果是" . $fp . "<br/>";
    }
    $c=preg_match($Pat,$fp,$out);
    if($c==0){
	    $out[0]="noResFound";
    }
    return $out;
}
function getxdudata($userID){
	global $debug;
	$P="/Solved\:.*?<td>([0-9]+)<\/td>/is";
	if($userID=='anonymous'){
		$link="http://acm.xidian.edu.cn/land/user/detail?user_id=645";
	}else{
		$link="http://acm.xidian.edu.cn/land/user/detail?username=" . $userID;
	}
	$res=getURLPattern($P,$link);
	if($res[0]=='noResFound'){
		return -1;
	}
	return $res[1];
}
function getpkudata($userID){
	global $debug;
	$P="/(result.*user_id=$userID>)(.*)(<\/a>)/i";
	$res=getURLPattern($P,"http://poj.org/userstatus?user_id=$userID");
	if($debug>0){
		print_r($res[0]);
	}
	if($res[0]=='noResFound'){
		return -1;
	}
	return $res[2];
}
function gethdudata($userID){
	global $debug;
	$P="/(Problems Solved<\/td><td.*?>\s*?)([0-9][0-9]*)(\s*?<\/td>)/i";
	$res=getURLPattern($P,"http://acm.hdu.edu.cn/userstatus.php?user=$userID");
	if($debug>0){
		print_r($res[1]);
	}
	if($res[0]=='noResFound'){
		return -1;
	}
	return $res[2];
}
function getzjudata($userID){
	global $debug;
	$P="/(runUserName.*?userId=)([0-9][0-9]*)/is";
	$res=getURLPattern($P,"http://acm.zju.edu.cn/onlinejudge/showRuns.do?contestId=1&search=true&firstId=-1&lastId=-1&problemCode=&handle=$userID&idStart=&idEnd=");
	if($debug>0){
		print_r($res[0]);
	}
;
	if($res[0]=='noResFound'){
		return -1;
	}
	$userID=$res[2];
	$P="/(<font.*?>)(AC Ratio:)(<\/font>.*<font.*?>)([0-9][0-9]*)(\/[0-9][0-9]*)(.*?<\/font>)/is";
	$res=getURLPattern($P,"http://acm.zju.edu.cn/onlinejudge/showUserStatus.do?userId=$userID");
	if($debug>0){
		print_r($res[0]);
	}
	if($res[0]=='noResFound'){
		return -1;
	}
	return $res[4];
}
function getcfdata($userID){
	global $debug;
	$URL="http://codeforces.com/profile/$userID";	
	$fp=file_get_contents($URL);
	$P="/data.push\(\[(.*?)\]\)/is";
	preg_match($P,$fp,$out);


	#P1用贪婪模式匹配到最后一个，取出rating
	$P1="/.*\[.*?,.*?([0-9]*),.*?,.*?,.*?,.*?,.*?,.*?,.*?\]/is";
	preg_match($P1,$out[1],$res);

	#P2用来匹配有多少组竞赛数据，得到参赛场数
	$P2="/\[.*?,.*?([0-9]*),.*?,.*?,.*?,.*?,.*?,.*?,.*?\]/is";

	$n=preg_match_all($P2,$out[1],$out1);
	return  (int)($res[1]) * 10000 + $n;
}
function getusacodata($userID,$userPass){
	global $debug;
	$snoopy=new Snoopy;
	$submit_url="http://ace.delos.com/usacogate";
	$submit_vars["NAME"]=$userID;
	$submit_vars["PASSWORD"]=$userPass;
	$snoopy->submit($submit_url,$submit_vars);
	#echo $snoopy->results;
	#通关特判
	$P='/Congratulations!\s*You\s*have\s*finished\s*all\s*available\s*material/is';
	$c=preg_match($P,$snoopy->results,$out0);
	#echo $out0[0];
	if($c>0){
		return 62;
	}
	#未通关的抓取
	$P='/(SECTION.*?(TODO|VIEWED))/is';
	preg_match($P,$snoopy->results,$out1);
	$P='/.*SECTION ([1-6]\.[1-7]).*?(TODO|VIEWED)/is';
	$c=preg_match($P,$out1[0],$out2);
	$out=$out2[1];
	if($debug>0){
		print_r($out);
	}
	if($c==0){
		return -1;
	}
	return (int)(10*(float)($out));
}
function gettcdata($userID){//返回值是rating*10000+比赛场数.
	global $debug;
	$snoopy=new Snoopy;
	$submit_url="http://www.topcoder.com/tc";
	$submit_vars["ha"]=$userID;
	$submit_vars["module"]="SimpleSearch";
	$snoopy->submit($submit_url,$submit_vars);
	$P="/(Algorithm Rating:.*?)(<span.*?>.*?)([0-9][0-9]*)(<\/span>)/is";
    	$c=preg_match($P,$snoopy->results,$rating);
	if($debug>0){
		print_r($rating);	
	}
	if($c==0){
		return -1;
	}
	$P="/(Competitions:.*?)(<a.*?>)([0-9][0-9]*)(<\/a>)/is";
	$c=preg_match($P,$snoopy->results,$competitions);
	if($debug>0){
		print_r($competitions);	
	}
	if($c==0){
		return -1;
	}
	return (int)$rating[3]*10000+(int)$competitions[3];
}
#=======================================
/*
$user='intijk';
if($user!=''){
    $pkuNum=getPKUSolvedNum($user);
    $pkuNum=(int)$pkuNum;
    echo $user . "在 pku 做了" . $pkuNum .  "道题" . "<br/>";
}
else{
    echo 'id=?';
}
$user=$_GET['id'];
if($user!=''){
	$zjuNum=getZJUSolvedNum($user);
	$zjuNum=(int)$zjuNum;
	echo $user . "在 zju 做了" . $zjuNum . "道题<br/>"; 
}
else{
    echo 'id=?';
}
$user=$_GET['id'];
if($user!=''){
    $hduNum=getHDUSolvedNum($user);
    $hduNum=(int)$hduNum;
    echo $user . "在 hdu 做了" . $hduNum .  "道题" . "<br/>";
}
else{
    echo 'id=?';
}
$user=$_GET['id'];
if($user!=''){
    $cfRating=getCFRating($user);
    $cfRating=(int)$cfRating;
	echo $user . "在 cf 的 Rating 是 " . floor((float)$cfRating/10000) . 
		" 参加的场数是 " . $cfRating%10000 . "<br/>";
}
else{
    echo 'id=?';
}

$user=$_GET['id'];
if($user!=''){
	$tcRating=getTCRating($user);
	$tcRating=(int)$tcRating;
	echo $user . "在 tc 的 rating 是" . floor((float)$tcRating/10000) . 
	"参加tc的场数是" . $tcRating%10000 . "<br/>";
}
else{
    echo 'id=?';
}

echo '使用时在地址栏的 id= 后面输入你想要查询的 id 号' . '<br/>';
echo '如 http://intijk.com/t.php?id=admin';
*/
?>
