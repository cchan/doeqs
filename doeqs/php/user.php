<?php
//user.php (formerly misc.php until htmlify and ext2mime removed, other.php, and before that data.php)
	//$ext2mime, conversion array, file extension => mime type string
	//function htmlify($title,$content) //Echoes html documents.
		//If $content is html string, will echo html page with %%xxx%% filled into template
		//If $content is *.php filename, will echo results of that page with %%xxx%% filled in.
		//--todo--restrict access to certain files within func itself?
		//--todo--different permissions levels get different-looking pages
	//function permissionsLevel() //Gets the permissions level of the current user.
		//If not logged in, returns -1
		//Else, AND with PERMISSIONS_CAPTAIN, PERMISSIONS_WEBMASTER, and PERMISSIONS_TEAMLEADER
			//to see whether they are each of those.

//Place this at the very beginning.
//if(@is_null($_SERVER["REQUEST_TIME_FLOAT"]))define(START_LOAD,microtime(true));

define("PERMISSIONS_CAPTAIN",4);
define("PERMISSIONS_WEBMASTER",2);
define("PERMISSIONS_TEAMLEADER",1);
function permissionsLevel(/*could have optional uid param here for any user...*/){
	static $permissionsLevel;
	if(!is_null($permissionsLevel))return $permissionsLevel;
//Do multi-verification: cookie, session (post or get?), diy session (the other one of post or get), db

//--todo--bitwise.
//Permissions:
//bit 2: captain or not
//bit 1: webmaster or not
//bit 0: teamleader or not
//and -1 is nobody (make sure all uses are correct!)
//Naming consistency: teamleader
//when structuring ifstatements, always be strict and ungenerous.
	//verify that it's logged in
	if(is_null(@$_COOKIE["login"]))return ($permissionsLevel=-1);
	
	//verify that connection works and the cookie is valid and in the users db
	if(!ctype_alnum($_COOKIE["login"])||!elemInSQLReq($_COOKIE["login"],"Name","users")){
		setcookie("login","",0);
		throw new Exception("PLevel: uname error");
	}
	
	$con=new DB();
	$result=$con->query("SELECT PermissionsLevel FROM users WHERE Name = \"%1%\"",$_COOKIE["login"]);
	if($result===false)throw new Exception("PLevel: db error");
	$row=$result->fetch_array();
	return ($permissionsLevel=$row[0]);
}
function setSession($username,$password){
	$con=new DB();
	$passArr=$con->query("SELECT Password FROM users WHERE Name = \"%1%\"",$username)->fetch_row();
	if(/*!$qresult||*/!$passArr||hash("whirlpool",$password)!=$passArr[0])return false;
	return setcookie("login",$username,time()+LOGIN_TIMEOUT_SEC);
	//setcookie gives back a bool
}
function renewSession(){
	if(permissionsLevel()>-1)return setcookie("login",$_COOKIE["login"],time()+LOGIN_TIMEOUT_SEC);
	else return false;
}
function endSession(){
	return setcookie("login","",0);
}
function getSessionName(){
	return $_COOKIE["login"];
}
?>