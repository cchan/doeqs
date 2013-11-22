<?php
//functions.php
//Any useful functions.

require_once "conf/config.php";
function __autoload($class_name) {//Lovely magic function, autorequires the file when you attempt to construct the class
	//for DB qIO filetoStr qParser
    require "classes/class.".str_replace(array("/","\\"),"",$class_name).".php";
}
$database=new DB;
$mobileDetect=new Mobile_Detect;


function anyIndicesEmpty($array/*, var1, var2, ...,varN*/){//it's NOT anyIndicesNull. "" is empty.
	$args=func_get_args();
	array_shift($args);//shift off the $array one
	foreach($args as $arg)
		if(!array_key_exists($arg,$array)||empty($array[$arg])/*&&$array[$arg]==='0'*/)return true;
	return false;
}

//randomizeArr - Randomly permute an array - yes, it works! in what amounts to O(n)!
function randomizeArr($arr){
	for($i=count($arr)-1;$i>0;$i--){
		$ind=mt_rand(0,$i);//Get the index of the one to swap with.
		$tmp=$arr[$ind];$arr[$ind]=$arr[$i];$arr[$i]=$tmp;//Swap with the last one.
	}
	return $arr;
}

function posted(){
	$args=func_get_args();
	foreach($args as $arg)
		if(!array_key_exists($arg,$_POST))return false;
	return true;
}
function getted(){
	$args=func_get_args();
	foreach($args as $arg)
		if(!array_key_exists($arg,$_GET))return false;
	return true;
}
function sessioned(){
	$args=func_get_args();
	foreach($args as $arg)
		if(!array_key_exists($arg,$_SESSION))return false;
	return true;
}


function arrayToRanges($arr){//Converts [1,2,3,5,6,8,9,10] to "1-3, 5-6, 8-10"
	if(count($arr)==0)return "";
	if(count($arr)==1)return $arr[0];
	sort($arr);
	$string="";
	$string.=$arr[0];
	for($i=1;$i < count($arr);$i++){
		if($arr[$i] > $arr[$i-1]+1){
			if($i>=2&&$arr[$i-1]==$arr[$i-2]+1)$string.=$arr[$i-1];
				$string.=", ".$arr[$i];
		}
		elseif($arr[$i]==$arr[$i-1]+1&&($i<2||$arr[$i]>$arr[$i-2]+2))$string.="-";
	}
	if($arr[count($arr)-1]==$arr[count($arr)-2]+1)$string.=$arr[count($arr)-1];
	return $string;
}


session_start();
function csrfVerify(){//Just returns whether to proceed.
	if(posted("ver")&&sessioned("ver")&&$_POST["ver"]!==$_SESSION["ver"]){
		unset($_POST["ver"],$_SESSION["ver"]);
		return true;
	}
	else {
		unset($_POST["ver"],$_SESSION["ver"]);
		return false;
	}
	//--todo-- Exceptions are bad and messy and not being caught. They're not meant to propagate all the way up.
}
function csrfCode(){
	static $code="";
	if($code!==""&&$code===$_SESSION["ver"])return $code;
	$length=rand(25,35);
    $c = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';$cl = strlen($c);
    $s = '';
    for($i=0;$i<$length;$i++)$s.=$c[rand(0,$cl-1)];
	$_SESSION["verpage"]=parse_url($_SERVER["SCRIPT_FILENAME"],PHP_URL_PATH);
    return ($_SESSION["ver"]=$s);
}


if (version_compare(PHP_VERSION, '5.4.0', '>=')) {//from php.net
  ob_start(null, 0, PHP_OUTPUT_HANDLER_STDFLAGS ^
	PHP_OUTPUT_HANDLER_REMOVABLE);
} else {
  ob_start(null, 0, false);
}
//register_shutdown_function("ob_end_flush");//is it already registered automatically? gets weird error
function templateify(){
	$pagesTitles=array(
		"index"=>"Home",
		"input"=>"Question Entry",
		"randq"=>"Random Question"
	);
	
	if(array_key_exists(basename($_SERVER["SCRIPT_FILENAME"],".php"),$pagesTitles))
		$title=$pagesTitles[basename($_SERVER["SCRIPT_FILENAME"],".php")];
	else
		$title="Not Found";
	
	$content=ob_get_contents();
	ob_clean();
	
	$nav="[&nbsp;&middot;&nbsp;";
	foreach($pagesTitles as $p=>$t)
		$nav.="<a href='$p.php'>$t</a>&nbsp;&middot;&nbsp;";
	$nav.="]";
	
	echo str_replace(["%title%","%content%","%nav%"],[$title,$content,$nav],file_get_contents(__DIR__."\html_template.html"));
	ob_flush();
	flush();
}
register_shutdown_function("templateify");

function error($description){
	ob_clean();
	echo "An error occurred";
	if($DEBUG_MODE)echo ": $description";
	die();
}


?>