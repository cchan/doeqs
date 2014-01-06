<?php
/*
functions.php

Any useful functions, and lots of includes.

Useful Tips List:
'\n' is just slash n; "\n" is newline.

*/

/****************INCLUDES******************/
require_once 'conf/config.php';//Config.
require_once 'classes/class.DB.php';//Safe, consistent (MySQL) databasing.
$database=new DB;//Surprisingly, it's faster if we load it every page.
require_once 'accountManagement.php';//Account and session management.
function __autoload($class_name) {//Lovely magic function, autorequires the file when you attempt to construct the class
	//for DB, qIO, filetoStr, qParser, etc.
    require 'classes/class.'.str_replace(array('/',"\\"),'',$class_name).'.php';
}


/****************LOGGING*******************/
file_put_contents(__DIR__.'/'.$REQUEST_LOG_FILE,$_SERVER['REMOTE_ADDR'].' '.date('l, F j, Y h:i:s A').' '.$_SERVER['REQUEST_URI']."\r\n",FILE_APPEND);

/******************FILES*******************/
/*
 * dirsize($path)
 *
 * Calculate the size of a directory by iterating its contents
 *
 * @author      Aidan Lister <aidan@php.net>
 * @version     1.2.0
 * @link        http://aidanlister.com/2004/04/calculating-a-directories-size-in-php/
 * @param       string   $directory    Path to directory
 */
function dirsize($path)
{
    // Init
    $size = 0;

    // Trailing slash
    if (substr($path, -1, 1) !== DIRECTORY_SEPARATOR) {
        $path .= DIRECTORY_SEPARATOR;
    }

    // Sanity check
    if (is_file($path)) {
        return filesize($path);
    } elseif (!is_dir($path)) {
        return false;
    }

    // Iterate queue
    $queue = array($path);
    for ($i = 0, $j = count($queue); $i < $j; ++$i)
    {
        // Open directory
        $parent = $i;
        if (is_dir($queue[$i]) && $dir = @dir($queue[$i])) {
            $subdirs = array();
            while (false !== ($entry = $dir->read())) {
                // Skip pointers
                if ($entry == '.' || $entry == '..') {
                    continue;
                }

                // Get list of directories or filesizes
                $path = $queue[$i] . $entry;
                if (is_dir($path)) {
                    $path .= DIRECTORY_SEPARATOR;
                    $subdirs[] = $path;
                } elseif (is_file($path)) {
                    $size += filesize($path);
                }
            }

            // Add subdirectories to start of queue
            unset($queue[0]);
            $queue = array_merge($subdirs, $queue);

            // Recalculate stack size
            $i = -1;
            $j = count($queue);

            // Clean up
            $dir->close();
            unset($dir);
        }
    }

    return $size;
}


/****************INTEGERS****************/
function val_int($n){//Validates that it's an integer
	if(!is_numeric($n)||intval($n)!=$n)
		return false;
	return true;
}
function normRange($n,$a,$b){//Normalizes $n to the range [$a,$b] (if it's smaller than $a, $a; if it's larger than $b, $b; otherwise $n same.)
	$n=intval($n);
	if($a>$b)err('normRange: invalid range');
	if($n<$a)return $a;
	if($n>$b)return $b;
	return $n;
}

/******************ARRAYS*****************/
function anyIndicesEmpty($array/*, var1, var2, ...,varN*/){//it's NOT anyIndicesNull. '' is empty.
	$args=func_get_args();
	array_shift($args);//shift off the $array one
	foreach($args as $arg)
		if(!array_key_exists($arg,$array)||empty($array[$arg])/*&&$array[$arg]==='0'*/)return true;
	return false;
}
function randomizeArr($arr){//Randomly permute an array - yes, it works! in what amounts to O(n)!
	for($i=count($arr)-1;$i>0;$i--){
		$ind=mt_rand(0,$i);//Get the index of the one to swap with.
		$tmp=$arr[$ind];$arr[$ind]=$arr[$i];$arr[$i]=$tmp;//Swap with the last one.
	}
	return $arr;
}
function arrayToRanges($arr){//Converts [1,2,3,5,6,8,9,10] to the human-readable "1-3, 5-6, 8-10"
	if(count($arr)==0)return '';
	if(count($arr)==1)return $arr[0];
	sort($arr);
	$string='';
	$string.=$arr[0];
	for($i=1;$i < count($arr);$i++){
		if($arr[$i] > $arr[$i-1]+1){
			if($i>=2&&$arr[$i-1]==$arr[$i-2]+1)$string.=$arr[$i-1];
				$string.=', '.$arr[$i];
		}
		elseif($arr[$i]==$arr[$i-1]+1&&($i<2||$arr[$i]>$arr[$i-2]+2))$string.='-';
	}
	if($arr[count($arr)-1]==$arr[count($arr)-2]+1)$string.=$arr[count($arr)-1];
	return $string;
}
function Array2DTranspose($arr){//Transposes a 2d array (aka flipping x and y; aka flipping around its primary axis)
    $out = array();
    foreach ($arr as $key => $subarr)
		foreach ($subarr as $subkey => $subvalue)
			$out[$subkey][$key] = $subvalue;
    return $out;
}

/***************HTTP Data Existence*******************/
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
function ifpost($n){
	if(posted($n))return htmlentities($_POST[$n]);
	else return '';
}

/**********************PAGE GENERATION*************************/
//Upon shutdown, templateify() will run, emptying the output buffer into a page template and then sending *that* instead.
ob_start();
$TIME_START=microtime(true);
function templateify(){
	global $CANCEL_TEMPLATEIFY;//In case, for example, you want to send an attachment through this page.
	if(@$CANCEL_TEMPLATEIFY)return;
	
	global $pagesTitles,$hiddenPagesTitles,$adminPagesTitles;
	
	$pagename=basename($_SERVER['SCRIPT_FILENAME'],'.php');
	if(array_key_exists($pagename,$pagesTitles)){
		$title=$pagesTitles[$pagename];
		$content=ob_get_clean();
	}
	elseif(array_key_exists($pagename,$hiddenPagesTitles)){
		$title=$hiddenPagesTitles[$pagename];
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
	$content=fetch_alerts_html().$content;
	
	$nav='[';
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
	$template=file_get_contents(__DIR__.'/html_template.php');
	
	global $VERSION_NUMBER,$TIME_START;
	echo str_replace(['%title%','%content%','%nav%','%version%','%loadtime%'],[$title,$content,$nav,$VERSION_NUMBER,substr(1000*(microtime(true)-$TIME_START),0,6)],$template);
	ob_flush();
	flush();
}
register_shutdown_function('templateify');


/**********************ERROR HANDLING**********************/
//Shorthand function for trigger_error.
function err($description){
	trigger_error($description,E_USER_ERROR);
}
//Error-handler function.
function error_catcher($errno,$errstr,$errfile,$errline){
	global $DEBUG_MODE, $ERROR_LOG_FILE;
	ob_start();
	debug_print_backtrace();
	$backtrace=str_replace('\n','\r\n',ob_get_clean());
	$date=date('l, F j, Y h:i:s A');
	$err="Error at $date. Error #$errno: '$errstr' at line $errline of file $errfile.\r\nDebug Backtrace:\r\n$backtrace\r\n\r\n";
	
	//File log, in the functions.php directory
	file_put_contents(__DIR__.'/'.$ERROR_LOG_FILE,$err,FILE_APPEND);
	
	//Printing out
	if($DEBUG_MODE){
		echo "An error occurred:<br><pre>$err</pre><br>(logged as above)";
	}
	else{
		ob_clean();//Shh, nothing happened!
		echo 'An error occurred!';
	}
	
	die();//Either way, an error should not let it go on executing.
	//If you want to have errors within classes, implement a class error-catching system yourself and output it to the user that way. Preferably through alerts.
}
set_error_handler('error_catcher', E_ALL);
ini_set('display_errors',1);
ini_set('error_reporting',E_ALL);
error_reporting(E_ALL);
//Strict: No notices allowed, either.


/*******************ALERTS*********************/
//Also assumes that templateify() will add it in via fetch_alerts_html()
//Call this to add an alert to be displayed at the top.
//Text: the alert text
//Disposition: negative means bad (red), positive means good (green), zero means neutral (black)
function alert($text,$disposition=0){
	$page_name=$_SERVER['REQUEST_URI'];//as long as it's unique to the page doesn't matter
	$sp='alerts_'.$page_name;
	
	if(!sessioned($sp))$_SESSION[$sp]=array();
	$_SESSION[$sp][]=[$text,$disposition];
}
function fetch_alerts_html(){
	$page_name=$_SERVER['REQUEST_URI'];//as long as it's unique to the page doesn't matter
	$sp='alerts_'.$page_name;
	
	$html='';
	
	if(sessioned($sp)){
		foreach($_SESSION[$sp] as $alert){
			if($alert[1]>0)$disposition='pos';
			else if($alert[1]<0)$disposition='neg';
			else $disposition='neut';
			$html.="<div class='alert_{$disposition}'>".htmlentities($alert[0]).'</div>';
		}
		unset($_SESSION[$sp]);
	}
	
	return $html;
}


/***********************MISC************************/
function database_stats(){//Returns the database statistics as an HTML string.
	//Note: huh hm try caching? Time the slowest parts of the code.
	
	global $database,$ruleSet;
	$ret='<div>Question Database Stats:';
	$totalN=0;
	$q=$database->query('SELECT Subject, COUNT(*) AS nQs FROM questions WHERE Deleted=0 GROUP BY Subject');
	
	while($r=$q->fetch_assoc()){
		$totalN+=$r['nQs'];
		$ret.="<br>{$ruleSet['Subjects'][$r['Subject']]}: <b>".($r['nQs']).'</b>';
	}
	$ret.="<br>Total: <b>$totalN</b>";
	$ret.='</div>';
	return $ret;
}


function generateForm($form,$inputs){
	//prompt type name value autofocus
	$csrf=csrfCode();
	$a='';
	foreach($form as $name=>$value)
		$a.=' '.$name.'="'.$value.'" ';
	
	$form=<<<HEREDOC
<form $a>
<input type="hidden" name='ver' value="$csrf"/>
<table>
HEREDOC;
	
	foreach($inputs as $input){
		if($input=='')
			$form.='<tr><td colspan="2">&nbsp;</td></tr>';
		elseif(is_string($input))
			$form.='<tr><td colspan="2">'.$input.'</td></tr>';
		else{
			$elem='<input ';
			foreach($input as $name=>$value)
				if($name!='prompt')
					$elem.=" {$name}=\"{$value}\" ";
			$elem.=' />';
			if(array_key_exists('prompt',$input))$form.="<tr><td>{$input['prompt']}<td>$elem</td></tr>";
			else $form.="<tr><td colspan='2'>$elem</td></tr>";
		}
	}
	$form.='</table></form>';
	return $form;
}


?>