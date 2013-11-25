<?php
//functions.php
//Any useful functions.

require_once "conf/config.php";
function __autoload($class_name) {//Lovely magic function, autorequires the file when you attempt to construct the class
	//for DB qIO filetoStr qParser
    require "classes/class.".str_replace(array("/","\\"),"",$class_name).".php";
}
$database=new DB;
//$mobileDetect=new Mobile_Detect; //I don't know.


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

function Array2DTranspose($arr){
    $out = array();
    foreach ($arr as $key => $subarr)
		foreach ($subarr as $subkey => $subvalue)
			$out[$subkey][$key] = $subvalue;
    return $out;
}




function session_total_destroy(){//Destroys a session according to the php.net method.
	// Unset all of the session variables.
	$_SESSION = array();

	// If it's desired to kill the session, also delete the session cookie.
	// Note: This will destroy the session, and not just the session data!
	if (ini_get("session.use_cookies")) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
			$params["path"], $params["domain"],
			$params["secure"], $params["httponly"]
		);
	}

	// Finally, destroy the session.
	session_destroy();
}
session_start();
if (!isset($_SESSION['LAST_ACTIVITY']) || (time() - $_SESSION['LAST_ACTIVITY'] > $SESSION_TIMEOUT_MINUTES*60)) {// last request was more than 15 minutes ago
	session_total_destroy();
	session_start();
}
$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp




function csrfVerify(){//Checks CSRF code validity, and returns whether to proceed. The return value is static.
	static $valid=NULL;
	if(is_null($valid)){
		if(posted("ver")&&sessioned("ver")&&$_POST["ver"]===$_SESSION["ver"]){
			unset($_POST["ver"],$_SESSION["ver"]);
			$valid=true;
		}
		else $valid=false;
		unset($_POST["ver"],$_SESSION["ver"]);
	}
	return $valid;
	//--todo-- Exceptions are bad and messy and not being caught. They're not meant to propagate all the way up.
}
function csrfCode(){//Returns randomly generated CSRF code. The return value is static.
	static $code="";
	if(sessioned("ver")&&$code===$_SESSION["ver"])return $code;
	$length=rand(48,64);
    $c = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';$cl = strlen($c);
    $s = '';
    for($i=0;$i<$length;$i++)$s.=$c[rand(0,$cl-1)];
	$_SESSION["verpage"]=parse_url($_SERVER["SCRIPT_FILENAME"],PHP_URL_PATH);
    return ($code=$_SESSION["ver"]=$s);
}


if (version_compare(PHP_VERSION, '5.4.0', '>=')) {//from php.net
  ob_start(null, 0, PHP_OUTPUT_HANDLER_STDFLAGS ^
	PHP_OUTPUT_HANDLER_REMOVABLE);
} else {
  ob_start(null, 0, false);
}
//register_shutdown_function("ob_end_flush");//is it already registered automatically? gets weird error
function templateify(){
	global $pagesTitles;
	
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
	
	//tried OB which died for some reason... ob_start();
	$template=file_get_contents(__DIR__."/html_template.php");
	
	global $VERSION_NUMBER;
	echo str_replace(["%title%","%content%","%nav%","%version%"],[$title,$content,$nav,$VERSION_NUMBER],$template);
	ob_flush();
	flush();
}
register_shutdown_function("templateify");

function error($description){
	ob_clean();
	echo "An error occurred";
	if($DEBUG_MODE)echo ": $description";
}



function database_stats(){
	global $database,$ruleSet;
	$ret="<div>Number of questions in database:";
	$totalN=0;
	$q=$database->query("SELECT Subject, COUNT(*) AS nQs FROM questions WHERE Deleted=0 GROUP BY Subject");
	while($r=$q->fetch_assoc()){
		$totalN+=$r["nQs"];
		$ret.="<br>{$ruleSet["Subjects"][$r["Subject"]]}: <b>".($r["nQs"])."</b>";
	}
	$ret.="<br>Total: <b>$totalN</b>";
	$ret.="</div>";
	return $ret;
}


?>