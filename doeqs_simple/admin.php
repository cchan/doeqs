<?php
require_once "functions.php";

//separate face of this page: "Are you sure?"
//echo $_SESSION["admin-ver"]=genVerCode();
//if($_POST["admin-ver"]===$_SESSION["admin-ver"])

//for particularly dangerous ones "Reenter password to do this action"

if(csrfVerify()){
	$_SESSION["admin"]=true;
	if(isSet($_POST["logout"])){
		session_total_destroy();
		die("logged out");
	}
	elseif(isSet($_POST["truncQs"])){
		$database->query_assoc("TRUNCATE TABLE questions");
		echo "All questions erased.<br><br>";
	}
	elseif(isSet($_POST["timesViewed"])){
		$database->query_assoc("UPDATE questions SET TimesViewed=0");
		echo "All questions' times-viewed-s zeroed.<br><br>";
	}
	elseif(isSet($_POST["markBad"])){
		$database->query_assoc("UPDATE questions SET MarkBad=0");
		echo "All questions' marked-as-bad-s zeroed.<br><br>";
	}
	elseif(isSet($_POST["dbInt"])){
	//Subject in {0,1,2,3,4}
	//isB and isSA in {0,1}
	//Question not blank or null
	//MCs exist for all isSA=1
	//Answer in {0,1,2,3} for isSA=1, not blank or null for isSA=0
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
}
	
	
	$filesTotalSize="no idea";
	//calculated thru system commands or something? Since if just tabulates directory doesn't count tmp files and such
?>
<form action="admin.php" method="POST">
	<input type="hidden" name="ver" value="<?=csrfCode();?>"/>
	<fieldset>
		<legend>Database</legend>
		<?=database_stats();?>
		<?php //Do a separate CONFIRM page ?>
		<input type="submit" name="truncQs" value="Delete All Questions" class="confirm"/><br>
		<input type="submit" name="timesViewed" value="Reset TimesVieweds" class="confirm"/><br>
		<input type="submit" name="markBad" value="Reset Marked-As-Bad's" class="confirm"/><br>
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
	<script type="text/javascript">
	var c=document.getElementsByClassName("confirm");
	for(var i=0;i<c.length;i++)c[i].onclick=function(){return confirm('Are you sure you want to "'+this.value+'"?');}
	</script>
</form>