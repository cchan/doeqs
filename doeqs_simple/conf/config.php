<?php
//config.php - Any configuration stuff.
//Various settings - most importantly, DEBUG_MODE. Change to false for production version.
//function posted($postvarname1,$postvarname2,...) //Returns whether all of those are existent $_POST variables. (ie $_POST[$postvarname1],...)
//function getted($getvarname1,$getvarname2,...) //Returns whether all of those are existent $_GET variables. (ie $_GET[$getvarname1],...)

$DEBUG_MODE=false;//True if want lots of output. False on real production.
date_default_timezone_set("America/Toronto");//(No Boston)

//pointlessbutton.net46.net settings are the default.
$DB_DOMAIN = "mysql2.000webhost.com";
$DB_UNAME = "a1409277_doeqs";
$DB_PASSWORD = "moo123";
$DB_DB = "a1409277_doeqs";

$ruleSet=array(
	"Subjects"=>array("BIOLOGY","CHEMISTRY","PHYSICS","MATHEMATICS","EARTH AND SPACE SCIENCE"),//'bcpme'
	"QTypes"=>array("Short Answer","Multiple Choice"),
	"QParts"=>array("TOSS-UP","BONUS"),
	"MCChoices"=>array("W","X","Y","Z"),
);
$DEFAULT_QUESTION_TEXT="Your question here...";
$DEFAULT_ANSWER_TEXT="Your answer here...";

include "local_config.php";//If necessary, stuff will be overridden here.

//All errors reported
ini_set('display_errors',1);
ini_set('error_reporting',E_ALL);
error_reporting(E_ALL);
if(!$DEBUG_MODE){//If it's actually the production version, don't say anything about what happened.
	function silent($errno, $errstr, $errfile, $errline) {
		die("An error occurred.");
	}
	set_error_handler('silent',E_ALL);
}
else{
	function tell($errno, $errstr, $errfile, $errline) {
		die("An error occurred: Error #(".$errno."): '".$errstr."' in file ".$errfile." on line ".$errline);
	}
	set_error_handler('tell',E_ALL);

}
?>