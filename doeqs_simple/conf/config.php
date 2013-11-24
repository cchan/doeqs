<?php
//config.php - Any configuration stuff.
//Various settings - most importantly, DEBUG_MODE. Change to false for production version.
//function posted($postvarname1,$postvarname2,...) //Returns whether all of those are existent $_POST variables. (ie $_POST[$postvarname1],...)
//function getted($getvarname1,$getvarname2,...) //Returns whether all of those are existent $_GET variables. (ie $_GET[$getvarname1],...)

$DEBUG_MODE=false;//True if want lots of output. False on real production.
date_default_timezone_set("America/Toronto");//(No Boston)

//pointlessbutton.xp3.biz settings are the default.
$DB_DOMAIN = "localhost";
$DB_UNAME = "621516";
$DB_PASSW = "dooba"."head".(5*(128*5+7)%1000+1000);
$DB_DB = "621516";

$ruleSet=array(
	"Subjects"=>array("BIOLOGY","CHEMISTRY","PHYSICS","MATHEMATICS","EARTH AND SPACE SCIENCE"),
	"QTypes"=>array("Multiple Choice","Short Answer"),
	"QParts"=>array("TOSS-UP","BONUS"),
	"MCChoices"=>array("W","X","Y","Z"),
	"SubjChars"=>str_split('bcpme'),
	"TypeChars"=>str_split('sm'),
	"PartChars"=>str_split('tb'),
);
$DEFAULT_QUESTION_TEXT="Your question here...";
$DEFAULT_ANSWER_TEXT="Your answer here...";
$RANDQ_MAX_QUESTIONS_AT_ONCE=10;//How many questions can you fetch per pageload?
$MARK_AS_BAD_THRESHOLD=2;//How many times can a question can be marked bad until being ignored?
$SESSION_TIMEOUT_MINUTES=15;
$DEFAULT_NUMQS=5;

//--todo--max requests per minute


if(file_exists("conf/local_config.php"))include "local_config.php";//If necessary, stuff will be overridden here.


if(!$DEBUG_MODE){//If it's actually the production version, don't say anything about what happened.
	ini_set('display_errors',0);
	ini_set('error_reporting',0);
	error_reporting(0);
	function silent($errno, $errstr, $errfile, $errline) {
		die("An error occurred.");
	}
	set_error_handler('silent',E_ALL);
}
else{
	ini_set('display_errors',1);
	ini_set('error_reporting',E_ALL);
	error_reporting(E_ALL);
	function tell($errno, $errstr, $errfile, $errline) {
		die("An error occurred: Error #(".$errno."): '".$errstr."' in file ".$errfile." on line ".$errline);
	}
	set_error_handler('tell',E_ALL);
}
?>