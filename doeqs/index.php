<?php
try{
	if(@is_null($_SERVER["REQUEST_TIME_FLOAT"]))define(START_LOAD,microtime(true));
	
	require_once "common/common.php";
	define("DB_DB","doeqs");
	require_once "php/user.php";

	$con=new DB();//New DB
	/*record ip, page, browser, referer*/$con->query("INSERT INTO reqlog (REMOTE_ADDR,REQUEST_URI,HTTP_USER_AGENT,HTTP_REFERER) VALUES (\"%1%\",\"%2%\",\"%3%\",\"%4%\")",$_SERVER['REMOTE_ADDR'],$_SERVER['REQUEST_URI'],$_SERVER['HTTP_USER_AGENT'],@$_SERVER['HTTP_REFERER']);
	/*if more than 10 requests in 1 min, die, but um still huge overhead*/$qresult=$con->query("SELECT REMOTE_ADDR FROM reqlog WHERE REMOTE_ADDR=\"%1%\" AND UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(DATE)<60",$_SERVER['REMOTE_ADDR']);if($qresult->num_rows>10)die();
	unset($con);//Cleanup.
	
	function htmlify($title,$content){
		//Give it a title and html content and it will echo the page,
		//or give it the title and the php file to require, and it'll detect that and act accordingly.
		//(File name must be alphanumeric. i.e. no dashes or underscores.)
		
		header('Content-Type: text/html; charset=utf-8');
		ob_start();
		require "php/html_template.php";
		$template=ob_get_contents();
		ob_end_clean();
		$template=str_replace("%%TITLE%%",$title,$template);
		if(strlen($content)<32&&substr($content,-4)===".php"&&ctype_alnum(substr($content,0,-4))){
			ob_start();
			if(!file_exists("pages/$content"))throw new Exception("htmlify: page does not exist");
			require "pages/$content";
			$content=ob_get_contents();
			ob_end_clean();
		}
		$template=str_replace("%%CONTENT%%",$content,$template);
		$navbar="";
		if(permissionsLevel()>=0){
			$navbar='<table style="width:100%;"><tr><td><ul id="navbar">
			<li><a href="index.php">Home</a></li>
			<li><a href="index.php?p=input">Input Questions</a></li>
			<li><a href="index.php?p=getq">Get Random Question</a></li>
			<li><a href="index.php?p=round">Get Question Round</a></li>
			<li><a href="#" id="downloadlink">Download Questions</a></li>
			</ul><td style="text-align:right;"><span id="logged-in-as">Logged in as <i>'.getSessionName().'</i> <a href="#" id="logoutbutton">[Logout]</a></span>
			</table>';
		}
		if(permissionsLevel()&&PERMISSIONS_TEAMLEADER){
			
		}
		if(permissionsLevel()&&PERMISSIONS_WEBMASTER){
			
		}
		if(permissionsLevel()&&PERMISSIONS_CAPTAIN){
			
		}
		$template=str_replace("%%NAVBAR%%",$navbar,$template);
		
		if(defined("START_LOAD"))$startloadtime=START_LOAD;else $startloadtime=$_SERVER["REQUEST_TIME_FLOAT"];
		$template=str_replace("%%PAGEMICROTIME%%",round(1000*(microtime(true)-$startloadtime),3),$template);
		
		echo $template;
	}
	
	if(posted("bughappened")||posted("bugsys")||posted("bugreproduce")||posted("bugemail")){//SUBMIT BUGREPT
		$con=new DB();
		$con->query("INSERT INTO bugreports (Bug,System,HowReproduce,Email) VALUES (\"%1%\",\"%2%\",\"%3%\",\"%4%\")",
			$_POST["bughappened"],$_POST["bugsys"],$_POST["bugreproduce"],$_POST["bugemail"]);
		die("success");
	}
	if(permissionsLevel()<0){//NOT LOGGED IN
		if(posted("uname","passw")){//LOG IN --todo--# of attempts?
			if(!setSession($_POST["uname"],$_POST["passw"]))die("Login failure.");//ten-min cookie
			//--todo-- $_SESSION? how to auth (hash("sha1",$user.$pass.time())?)?
			die("success");
		}
		else{//LOGIN PAGE --todo--if any fields on login page blank, will topalert this!! :(
			htmlify("Login","login.php");
		}
	}
	else{//IT'S LOGGED IN
		renewSession();//Renewal of login cookie
		require_once "php/qIO.php";
		$downloadsupported=array(
			"doc"=>"Microsoft Word 1997-2003 *.doc",
			"html"=>" HTML *.html",
			//"docx"=>"Microsoft Word *.docx",
			//"pdf"=>"Adobe PDF Portable Document Format *.pdf",
		);
		
		if(posted("uname","passw")){
			die("You're already logged in!");
		}
		elseif(posted("logout")){//LOGOUT
			if(endSession())die("success");else die("Error.");
		}
		elseif(posted("downloadoptions")){//GET OPTIONS FOR DOWNLOAD
			echo "<form id='downloadoptions'>";
			echo "Question Set: <select name='downloadset'>";
			foreach(getQuestionSetNames() as $SID=>$name)echo "<option value=\"$SID\">$name</option>";//--todo--option for randomly generated
			echo "</select><br>Format: <select name='downloadformat'>";
			foreach($downloadsupported as $ext=>$descr)echo "<option value=\"$ext\">$descr</option>";
			echo "</select><input type='submit' value='Download'/>";
			echo "</form>";
		}
		elseif(posted("directentry")){//SUBMIT QUESTION
			try{$q=new Question($_POST);}catch(Exception $e){die("ERROR: {$e->getMessage()}.");}
			if($_POST["qset"]!="0"){
				$s=new QuestionSet($_POST["qset"]);
				if(!$s->addByQID($q->getQID()))die("ERROR: adding question to qset failed.");
			}
			die("Successfully submitted!");
		}
		elseif(posted("copypaste")){}//--todo--this
		elseif(posted("fileupload")){}//--todo--this
		elseif(posted("qsetselect")){
			if(!getQuestionSetNames()||count(getQuestionSetNames())==0){
				echo '<b>No question sets available!?</b>';
			}
			else{
				echo '<select id="qset" name="qset" form="directentry copypaste fileupload"><option value="0">No set</option>';
				//--todo--text-field, and sequentially add to sets - text-suggest
				foreach(getQuestionSetNames() as $SID=>$setname)echo "<option value='$SID'>$setname</option>";
				echo '<option value="new"><b>Create New Set</b></option>';
				echo '<select>';
			}//--todo--output the selects on the client side, only send JSON. [actually, output a autofill-thing on client side, with plain-browse option]
		}
		elseif(posted("qset")){//GETS Qs IN SET
			$s=new QuestionSet($_POST["qset"]);
			echo "This set has ";
			foreach($s->getSubjCounts() as $subj=>$howmany)
				echo "<b>$howmany</b> $subj ";
			echo "</div>";
		}
		elseif(getted("downloadset","downloadformat")){//DOWNLOAD FILE
			$ext2mime=array(//Translates extensions to mimes.
				"doc"=>"application/msword",
				"html"=>"text/html",
				"docx"=>"application/vnd.openxmlformats-officedocument.wordprocessingml.document",
				"pdf"=>"application/pdf",
			);
			if(is_null(@$ext2mime[$_GET["downloadformat"]]))die("Download: Invalid format.");
			if(!ctype_digit($_GET["downloadset"]))die("Download: Invalid question-set.");
			$s=new QuestionSet($_GET["downloadset"]);
			header("Content-type: ".$ext2mime[$_GET["downloadformat"]]);
			header("Content-Disposition: attachment;filename=DOEQuestions_{$s->getName()}.{$_GET["downloadformat"]}");//I want a file download.
			echo $s->toHTMLDoc();//and then echo the questions doc as html.
		}
		else{
			switch(@$_GET["p"]){//PAGES
				case "home":case NULL:case "":htmlify("Home","home.php");break;//Home page.
				case "input":htmlify("Input","input.php");break;//Question input page.
				case "getq":htmlify("Random Question","getq.php");break;//Get a random question.
				case "round":htmlify("Round","round.php");break;//Get an interactive round of questions.
				case "login":htmlify("Login","login.php");break;//Login page.
				case "browse":htmlify("Browse","browse.php");break;//Browse questions.
				default:htmlify("404 Not Found","<center><div>Oops, the page you requested, <b><i>".htmlentities($_GET["p"])."</i></b>, wasn't found.</div><br><img src=\"img/grumpycat.jpg\" alt=\"grumpycat no\" height=\"200px\"/></center>");
			}
		}
	}
}catch(Exception $e){
	if(DEBUG_MODE)die("EXCEPTION: {$e->getMessage()}!");
	else die("An error occurred.");//...--todo--is there a way to clear the screen? ob?
}
//Consistency of error messages? err() func?
//Here come the actual html pages. -->how to prevent from access other than through this? DB content?
//random idea: outreach to other teams & hold tournament. Or something like LMT for middle school.
//--todo--output buffering? So then on exit can specify whether was a good exit or a getmeoutofhere exit so can decide whether to send data
?>