<?php
function genVerCode(){
	$length=rand(48,64);//varying length ^^
    $c = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';$cl = strlen($c);
    $s = '';
    for($i=0;$i<$length;$i++)$s.=$c[rand(0,$cl-1)];
	return $s;
}
function csrfVerify(){//Checks CSRF code validity, and returns whether to proceed. The return value is static. Erases 'ver'.
	static $valid=NULL;
	if(is_null($valid)){
		if(posted('ver')&&sessioned('ver')&&hashEquals($_POST['ver'],$_SESSION['ver'])){
			unset($_POST['ver'],$_SESSION['ver']);
			$valid=true;
		}
		else $valid=false;
		unset($_POST['ver'],$_SESSION['ver']);
	}
	return $valid;
	//--todo-- Exceptions are bad and messy and not being caught. They're not meant to propagate all the way up.
}
function csrfCode(/*$forceNew*/ /*$ver_name*/){//Returns randomly generated CSRF code. The return value is static.
	static $code='';
	if(sessioned('ver')&&$code===$_SESSION['ver'])return $code;
	
    return ($code=$_SESSION['ver']=genVerCode());
}



//objectify?

function authError(){
	logout();
	die('Authentication error.');
}

function hashEquals($a,$b){//Compares the *hashes* of two variables (really salty ones) to mess with timing attacks.
	$m=md5(microtime());
	$n=sha1(uniqid(mt_rand(),true));
	usleep(mt_rand(0,100000));//Even this. Microseconds.
	return saltyStretchyHash($a.$m.$b,$n)==saltyStretchyHash($b.$m.$a,$n);
}

//In order of precedence:
//a=admin
//c=captain
//u=regular user
//x=not logged in

//$_SESSION["user_verification_code"]='';
function userAccess($minPrivilegeLevel){
	$minPrivilegeLevel=strtolower($minPrivilegeLevel);
	if(sessioned('permissions'))$_SESSION['permissions']=strtolower($_SESSION['permissions']);
	else $_SESSION['permissions']='x';
	$hierarchy='xuca';//hierarchy, from lowest to highest
	
	if(count($minPrivilegeLevel)!==1)error("Invalid permission level '$minPrivilegeLevel'");
	if(!sessioned('email'))$nUser=0;
	else $nUser=strpos($hierarchy,$_SESSION['permissions']);
	$nAllowed=strpos($hierarchy,$minPrivilegeLevel);
	
	if($nUser===false)error("Invalid session permission level '{$_SESSION["permissions"]}'");
	if($nAllowed===false)error("Invalid input permission level '$minPrivilegeLevel'");
	
	else return $nUser>=$nAllowed;
}

if(sessioned('user_v')&&(!array_key_exists('v',$_COOKIE)||$_COOKIE['v']!=$_SESSION['user_v']))authError();
function loginEmailPass($email,$pass){
	if(!filter_var($email, FILTER_VALIDATE_EMAIL))return false;

	global $database;
	if(!isSet($database))$database=new DB;
	
	$q=$database->query_assoc('SELECT email, passhash, permissions, salt FROM users WHERE email=%0%',[$email]);
	$passhash=saltyStretchyHash($pass,$q['salt']);
	if(!hashEquals($q['passhash'],$passhash))return false;
	
	$_SESSION['email']=$q['email'];
	$_SESSION['permissions']=$q['permissions'];
	$_SESSION['user_v']=uniqid(mt_rand(),true);
	setcookie('v',$_SESSION['user_v']);//passed back and forth and verified above.
	return true;
}
function saltyStretchyHash($pass,$salt){//WAAAY overdoing it. Messing with any sort of brute force attack.
	$hash=$pass.$salt;
    for($i=0;$i<2741;$i++)$hash=hash('whirlpool',hash('sha512',$pass.$hash.$salt));
    return $hash;
}
function logout(){
	unset($_SESSION['email'],$_SESSION['permissions'],$_SESSION['user_v']);
}
//function newProfile(){
//	$salt=hash('whirlpool',md5(uniqid(mt_rand(),true)));//Yep. Overdoing it. 512 bits.
//}
?>