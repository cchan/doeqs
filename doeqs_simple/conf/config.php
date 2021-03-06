<?php
if(!defined('ROOT_PATH')){header('HTTP/1.0 404 Not Found');die();}
/*
config.php

Any configuration stuff.
*/


$DEBUG_MODE=false;//True if want lots of debug output. False on real production to hide everything.

$SERVER_DOWN=false;//Teapot on every page if it's true. See top of functions.php. :)

/**********************METADATA*********************/
//$DOEQS_URL;
$VERSION_NUMBER='0.2.2';
//$WEBMASTER_EMAIL;
//date_default_timezone_set()

/************************SESSION*********************/
$SESSION_TIMEOUT_MINUTES=10;
ini_set('session.gc_maxlifetime',600);
//$MAX_REQUESTS_PER_MINUTE=30;//Still to be implemented. What's a good number, and what's a good response?
ini_set('display_errors',false);
ini_set('log_errors',true);
ini_set('safe_mode',true);
ini_set('safe_mode_gid',true);
//register_globals 0
//disable_functions extract mysql_connect
//disable_classes mysql

//$DB_SERVER
//$DB_USERNAME
//$DB_PASSWORD
//$DB_DATABASE

/***********************DOEQS************************/
$ruleSet=array(//...to be honest, this is annoying.
	"Subjects"=>array("BIOLOGY","CHEMISTRY","PHYSICS","MATHEMATICS","EARTH AND SPACE SCIENCE"),
	"SubjRegex"=>'(BIO(?:LOGY)?|CHEM(?:ISTRY)?|PHYS(?:|ICS|ICAL SCIENCE)|MATH(?:EMATICS)?|E(?:SS|ARTHSCI|ARTH SCIENCE|ARTH (?:AND|&) SPACE(?: SCIENCE)?))',
	"QTypes"=>array("Multiple Choice","Short Answer"),
	"QParts"=>array("TOSS-UP","BONUS"),
	"MCChoices"=>array("W","X","Y","Z"),
	"SubjChars"=>str_split('bcpme'),
	"TypeChars"=>str_split('ms'),
	"PartChars"=>str_split('tb'),
);
$MARK_AS_BAD_THRESHOLD=2;//RANDQ: How many times can a question can be marked bad until being ignored?
$MAX_NUMQS=25;//RANDQ: How many questions can you fetch per pageload?
$DEFAULT_NUMQS=5;//RANDQ: Default number of questions to fetch

/****************FILE TRANSFER LIMITS****************/
$UPLOAD_MAX_FILESIZE = 2;ini_set('upload_max_filesize',$UPLOAD_MAX_FILESIZE);//MB
$POST_MAX_SIZE = 2;ini_set('post_max_size',$POST_MAX_SIZE);//MB
$MAX_FILE_UPLOADS=5;ini_set('max_file_uploads',$MAX_FILE_UPLOADS);//in multi-upload or just multiple file form elements

/********************LOGGING**********************/
$REQUEST_LOG_FILE='request_log.log';
$ERROR_LOG_FILE='error_log.log';
$BUG_REPORT_FILE='bug_log.log';

/******************PAGES AND NAV*****************/
//This specifies not only the navbar, but also the allowed pages accessible. 404 if not in below, even if real file.
//db entry for each page? [file, title, nav, permission, visibility] db is comp intensive but nicer and live-editable
$pagesTitles=array(
	"index"=>"Home",
	"input"=>"Question Entry",
	"randq"=>"Random Question",
	"about"=>"About",
	"login"=>"Login",
);
$hiddenPagesTitles=array(
	"bugs"=>"Bug Report/Feature Request",
);
$adminPagesTitles=array(
	"admin"=>"Admin",
);

/******************CUSTOM LOCAL*******************/
require 'config.server.php';//Server-specific private - stuff like DB, etc.
@include 'config.local.php';//local dev settings private, which may override preset server stuff

?>