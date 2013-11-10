<?php
session_start();
if(isSet($_SESSION["admin"])||isSet($_POST["p"])&&$_POST["p"]==="supersecretstuff"){
$_SESSION["admin"]=true;
echo "<h1>Admin</h1><a href='input.php'>Question Entry</a><br><a href='randq.php'>Get Random Question</a><br>";
define("DB_DB","doeqs_simple");
require "common.php";
if(isSet($_POST["logout"])){unset($_SESSION["admin"]);die("logged out");}
elseif(isSet($_POST["truncQs"])){$database->query_assoc("TRUNCATE TABLE questions");echo "Table <i>questions</i> truncated.<br><br>";}
elseif(isSet($_POST["timesViewed"])){$database->query_assoc("UPDATE questions SET TimesViewed=0");echo "All questions' times-viewed zeroed.<br><br>";}
elseif(isSet($_POST["ratings"])){$database->query_assoc("UPDATE questions SET Rating=0");echo "All questions' ratings zeroed.<br><br>";}
?>
<form method="POST">
<fieldset>
<legend>Database</legend>
<input type="submit" name="truncQs" value="Delete All Questions" onclick="confirm('Are you sure?');"/><br>
<input type="submit" name="timesViewed" value="Reset TimesVieweds" onclick="confirm('Are you sure?');"/><br>
<input type="submit" name="ratings" value="Reset Ratings" onclick="confirm('Are you sure?');"/><br>
<input type="submit" name="dbInt" value="Database Integrity Check" disabled/>
</fieldset>
<fieldset>
<legend>Server Files</legend>
<input type="submit" name="fileInt" value="Files Integrity Check" disabled/><br><?php //checks existence and sizes of all files and nonexistence of others ?>
</fieldset>
<input type="submit" name="logout" value="Logout"/><br>
</form>
<?php
}
else{
?>
<form method="POST"><input type="password" name="p"/></form>
<?php }?>