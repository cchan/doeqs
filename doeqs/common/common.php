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
else{die("I'm not supposed to be here. ?_?");}

if(DEBUG_MODE){
	error_reporting(E_ALL);//All errors reported
	ini_set('display_errors','1');
}
else{
	error_reporting(0);//No errors reported
	ini_set('display_errors','0');
}

require_once dirname(__FILE__)."/sql.php";//SQL stuff, with access class and convenience functions

function anyIndicesEmpty($array/*var1, var2, ...,varN*/){//it's NOT anyIndicesNULL
	$args=func_get_args();
	array_shift($args);//shift off the $array one
	foreach($args as $arg)
		if(empty($array[$arg]))return false;
	return true;
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
?>