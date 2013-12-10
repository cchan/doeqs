<?php
//config.php - Any configuration stuff.
//Various settings - most importantly, DEBUG_MODE. Change to false for production version.
//function posted($postvarname1,$postvarname2,...) //Returns whether all of those are existent $_POST variables. (ie $_POST[$postvarname1],...)
//function getted($getvarname1,$getvarname2,...) //Returns whether all of those are existent $_GET variables. (ie $_GET[$getvarname1],...)

$VERSION_NUMBER="0.2.1";

$DEBUG_MODE=false;//True if want lots of output. False on real production.
date_default_timezone_set("America/Toronto");//(No Boston)

//pointlessbutton.xp3.biz settings are the default.
$DB_DOMAIN = "localhost";
$DB_UNAME = "621516";
$DB_PASSW = "dooba"."head".(5*(128*5+7)%1000+1000);
$DB_DB = "621516";

$ruleSet=array(
	"Subjects"=>array("BIOLOGY","CHEMISTRY","PHYSICS","MATHEMATICS","EARTH AND SPACE SCIENCE"),
	"SubjRegex"=>'(BIO(?:LOGY)?|CHEM(?:ISTRY)?|PHYS(?:|ICS|ICAL SCIENCE)|MATH(?:EMATICS)?|E(?:SS|ARTHSCI|ARTH SCIENCE|ARTH (?:AND|&) SPACE(?: SCIENCE)?))',
	"QTypes"=>array("Multiple Choice","Short Answer"),
	"QParts"=>array("TOSS-UP","BONUS"),
	"MCChoices"=>array("W","X","Y","Z"),
	"SubjChars"=>str_split('bcpme'),
	"TypeChars"=>str_split('sm'),
	"PartChars"=>str_split('tb'),
);
$RANDQ_MAX_QUESTIONS_AT_ONCE=25;//How many questions can you fetch per pageload?
$MARK_AS_BAD_THRESHOLD=2;//How many times can a question can be marked bad until being ignored?
$SESSION_TIMEOUT_MINUTES=15;
$DEFAULT_NUMQS=5;

$UPLOAD_MAX_FILESIZE = 2;ini_set('upload_max_filesize',$UPLOAD_MAX_FILESIZE);//MB
$POST_MAX_SIZE = 2;ini_set('post_max_size',$POST_MAX_SIZE);//MB
$MAX_FILE_UPLOADS=5;ini_set('max_file_uploads',$MAX_FILE_UPLOADS);//in multi-upload or just multiple file form elements

//db entry for each page? file, title, nav, permission
//no db is expensive
$pagesTitles=array(
	"index"=>"Home",
	"input"=>"Question Entry",
	"randq"=>"Random Question",
	"about"=>"About",
	"login"=>"Login",
);
$adminPagesTitles=array(
	"admin"=>"Admin",
);

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