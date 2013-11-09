<?php
//common.php - generally useful php stuff, plus requires all other commons.
//Various settings - most importantly, DEBUG_MODE and WEBMASTER_EMAIL. Change DEBUG_MODE to false for production version.
//function posted($postvarname1,$postvarname2,...) //Returns whether all of those are existent $_POST variables. (ie $_POST[$postvarname1],...)
//function getted($getvarname1,$getvarname2,...) //Returns whether all of those are existent $_GET variables. (ie $_GET[$getvarname1],...)

define("DEBUG_MODE",true);//True if want lots of output. False on real production.

date_default_timezone_set("America/Toronto");//No Boston :( I'd rather use Toronto than NY though :)

define("LOGIN_TIMEOUT_SEC",1800);//Half an hour session time

if($_SERVER["SERVER_NAME"]=="localhost"){
	define("WEBMASTER_EMAIL","moose54321@gmail.com");
	define("DB_DOMAIN","localhost");
	define("DB_UNAME","root");
	define("DB_PASSW","");
	define("DB_DB_PREFIX","");
}
elseif($_SERVER["SERVER_NAME"]=="lexnsb.comyr.com"){
	define("WEBMASTER_EMAIL","webmaster@lexnsb.comyr.com");
	define("DB_DOMAIN","mysql6.000webhost.com");
	define("DB_UNAME","a6194030_doeqs");//--todo--change password
	define("DB_PASSW","moose54321)");
	define("DB_DB_PREFIX","a6194030_");
}
elseif($_SERVER["SERVER_NAME"]=="pointlessbutton.net46.net"){
	define("WEBMASTER_EMAIL","webmaster@pointlessbutton.net46.net");
	define("DB_DOMAIN","mysql2.000webhost.com");
	define("DB_UNAME","a1409277_doeqs");//--todo--change password
	define("DB_PASSW","moose54321)");
	define("DB_DB_PREFIX","a6194030_");
}
else{die("I'm not supposed to be here. ?_?");}

if(DEBUG_MODE){
	error_reporting(E_ALL);//All errors reported
	ini_set('display_errors','1');
}
else{
	error_reporting(0);//No errors reported
	ini_set('display_errors','0');
}



require_once "sql.php";//SQL stuff, with access class and convenience functions



function anyIndicesEmpty($array/*, var1, var2, ...,varN*/){//it's NOT anyIndicesNull. "" is empty.
	$args=func_get_args();
	array_shift($args);//shift off the $array one
	foreach($args as $arg)
		if(empty($array[$arg])/*&&$array[$arg]==='0'*/)return true;
	return false;
}
function posted(){
	$args=func_get_args();
	foreach($args as $arg)
		if(is_null(@$_POST[$arg]))return false;
	return true;
}
function getted(){
	$args=func_get_args();
	foreach($args as $arg)
		if(is_null(@$_GET[$arg]))return false;
	return true;
}
function getSessionName(){
	return "IP".$_SERVER["REMOTE_ADDR"];
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

//http://stackoverflow.com/questions/4356289/php-random-string-generator/15914231#15914231
function generateRandomString($length) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}
?>