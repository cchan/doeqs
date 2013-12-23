<?php
//functions.php
//Any useful functions.

require_once 'conf/config.php';
function __autoload($class_name) {//Lovely magic function, autorequires the file when you attempt to construct the class
	//for DB qIO filetoStr qParser
    require 'classes/class.'.str_replace(array('/',"\\"),'',$class_name).'.php';
}


function anyIndicesEmpty($array/*, var1, var2, ...,varN*/){//it's NOT anyIndicesNull. '' is empty.
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

//Integers
function val_int($n){
	if(!is_numeric($n)||intval($n)!=$n)
		return false;
	return true;
}
function normRange($n,$a,$b){
	$n=intval($n);
	if($a>$b)error("invalid range");
	if($n<$a)return $a;
	if($n>$b)return $b;
	return $n;
}

//Data
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
	if(count($arr)==0)return '';
	if(count($arr)==1)return $arr[0];
	sort($arr);
	$string='';
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




session_start();
function session_total_reset(){//Destroys a session according to the php.net method.
	// Unset all of the session variables.
	$_SESSION = array();

	// If it's desired to kill the session, also delete the session cookie.
	// Note: This will destroy the session, and not just the session data!
	if (ini_get('session.use_cookies')) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
			$params['path'], $params['domain'],
			$params['secure'], $params['httponly']
		);
	}

	// Finally, destroy the session.
	session_destroy();
	session_start();
}
if (!isset($_SESSION['LAST_ACTIVITY']) || (time() - $_SESSION['LAST_ACTIVITY'] > $SESSION_TIMEOUT_MINUTES*60)) {// last request was more than 15 minutes ago
	session_total_reset();
}
$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp


require_once 'accountManagement.php';


/*if (version_compare(PHP_VERSION, '5.4.0', '>=')) {//from php.net
  ob_start(null, 0, PHP_OUTPUT_HANDLER_STDFLAGS ^
	PHP_OUTPUT_HANDLER_REMOVABLE);
} else {
  ob_start(null, 0, false);
}*/
ob_start();
function templateify(){//Runs at end, to put the page contents into a page template.
	global $pagesTitles,$adminPagesTitles;
	
	$pagename=basename($_SERVER['SCRIPT_FILENAME'],'.php');
	if(array_key_exists($pagename,$pagesTitles)){
		$title=$pagesTitles[$pagename];
		$content=ob_get_clean();
	}
	elseif(array_key_exists($pagename,$adminPagesTitles)&&userAccess('a')){
		$title=$adminPagesTitles[$pagename].' [Admin-Only Page]';
		$content=ob_get_clean();
	}
	else{
		$title='404 Not Found';
		$content="Oops, your page <i><a href='{$_SERVER['REQUEST_URI']}'>{$_SERVER['REQUEST_URI']}</a></i> wasn't found! D:<br>Try again?";
		ob_clean();
	}
	
	$nav="[";
	foreach($pagesTitles as $p=>$t)
		$nav.="&nbsp;&middot;&nbsp;<a href='$p.php'>$t</a>";
	if(userAccess('a')){
		$nav.='&nbsp;&mdash;&nbsp;';
		foreach($adminPagesTitles as $p=>$t)
			$nav.="<a href='$p.php'>$t</a>";
	}
	$nav.='&nbsp;&middot;&nbsp;]';
	if(userAccess('u'))$nav.='&nbsp;&nbsp;&nbsp;<form action="login.php" method="POST" style="display:inline-block;"><input type="hidden" name="ver" value="<?=csrfCode();?>"/><input type="submit" name="logout" value="Log Out" /></form>';

	
	//tried OB to get file contents which died for some reason...
	$template=file_get_contents(__DIR__."/html_template.php");
	
	global $VERSION_NUMBER;
	echo str_replace(["%title%","%content%","%nav%","%version%"],[$title,$content,$nav,$VERSION_NUMBER],$template);
	ob_flush();
	flush();
}
register_shutdown_function("templateify");

function error($description){
	global $DEBUG_MODE;
	ob_clean();
	echo "An error occurred";
	if($DEBUG_MODE)echo ": $description";
}



function database_stats(){//Note: huh hm try caching? eh, time the slowest parts of the code.
	global $database,$ruleSet;
	if(!isSet($database))$database=new DB;
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