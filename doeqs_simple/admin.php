<?php
require "common.php";
session_start();

if(isSet($_SESSION["admin"])||isSet($_POST["p"])&&$_POST["p"]==="supersecretstuff"){
$_SESSION["admin"]=true;
if(isSet($_POST["logout"])){
	unset($_SESSION["admin"]);
	die("logged out");
}
elseif(isSet($_POST["truncQs"])){
	$database->query_assoc("TRUNCATE TABLE questions");
	echo "Table <i>questions</i> truncated.<br><br>";
}
elseif(isSet($_POST["timesViewed"])){
	$database->query_assoc("UPDATE questions SET TimesViewed=0");
	echo "All questions' times-viewed zeroed.<br><br>";
}
elseif(isSet($_POST["ratings"])){
	$database->query_assoc("UPDATE questions SET Rating=0");
	echo "All questions' ratings zeroed.<br><br>";
}
elseif(isSet($_POST["dbInt"])){
//Subject in {0,1,2,3,4}
//isTU and isMC in {0,1}
//Question not blank or null
//MCs exist for all isMC=1
//Answer in {0,1,2,3} for isMC=1, not blank or null for isMC=0
//Rating within reason (not below -3, since it won't even appear then)
//TimesViewed positive, within reason
//TimestampEntered within reason
}
elseif(isSet($_POST["setStandard"])){
//Modifies the database entry checked against for file integrity test.
}
elseif(isSet($_POST["fileInt"])){
//checks existence and sizes of all files
//verifies nonexistence of any other files
}

$numberQs=$database->query_assoc("SELECT COUNT(*) AS nQs FROM questions");$numberQs=$numberQs["nQs"];

$filesTotalSize="no idea";
//calculated thru system commands or something? Since if just tabulates directory doesn't count tmp files and such
?>

<h1>Admin</h1><a href="input.php">Question Entry</a><br>
<a href="randq.php">Get Random Question</a><br>
<form method="POST">
<fieldset <?php if($numberQs==0)echo "disabled";?>>
<legend>Database</legend>
<div>Total number of questions in database: <b><?php echo $numberQs;?></b></div>
<input type="submit" name="truncQs" value="Delete All Questions" onclick="confirm('Are you sure?');"/><br>
<input type="submit" name="timesViewed" value="Reset TimesVieweds" onclick="confirm('Are you sure?');"/><br>
<input type="submit" name="ratings" value="Reset Ratings" onclick="confirm('Are you sure?');"/><br>
<input type="submit" name="dbInt" value="Database Integrity Check" disabled/>
</fieldset>
<fieldset>
<legend>Server Files</legend>
<div>Total size: <?php echo $filesTotalSize;?>MB</div>
<input type="submit" name="setStandard" value="Set current state as integrity check standard" disabled/>
<input type="submit" name="fileInt" value="Files Integrity Check" disabled/><br>
</fieldset>
<input type="submit" name="logout" value="Logout"/><br>
</form>
<?php }else{?>
<form method="POST"><input type="password" name="p"/></form>
<?php }?>