<h1>Question Entry</h1>
<a href="randq.php">Get Random Question</a><br>
<br><br>

<?php
require_once "qIO.php";
require_once "common.php";
global $ruleSet;

session_start();
if(isSet($_POST["ver"])&&isSet($_SESSION["ver"])&&$_POST["ver"]==$_SESSION["ver"]){
	unset($_SESSION["ver"]);
	if(isSet($_POST["copypaste"])||isSet($_FILES["fileupload"])||isSet($_POST["directentry"])){
		echo '<div style="font-size:0.8em;border:solid 1px #000000;display:inline-block;padding:5px;">
			<i>We are processing your questions right now...</i><br><br>';
		if(isSet($_POST["copypaste"]))$unparsed=strParseQs($_POST["copypaste"]);
		elseif(isSet($_FILES["fileupload"]))$unparsed=strParseQs(fileToStr($_FILES["fileupload"]));
		elseif(isSet($_POST["directentry"])){
			$err="";
			try{$q=new Questions([$_POST]);}
			catch(Exception $e){$err="Error: ".$e->getMessage();}
			
			if($err=="")echo "Question entered successfully, with Question-ID <b>".(new Question($_POST))->getQID()."</b><br><br><br>";
			else echo $err;
		}
		echo "<br><br>";
/*trim??*/if(str_replace(array("\n","\r"," ","	"),"",$unparsed)!="")
			echo "Below, in the copy-paste section, are what remains in the document after detecting all the questions we could find.<br>"
				."<span style='font-size:0.8em;'>(Common syntax errors include multi-line question statement, improperly labeled (as MC or SA), "
				."missing some necessary components (like multiple choices and an answer), mislabeled multiple choice, really horrible misspellings.)</span>";
		else echo "No unparsed question text found. Yay!";
		
		echo '</div><br><br>';
	}
}

?>

Enter some questions:
<div id="question-wrapper">
	<h2>Direct Entry</h2>
	<form id="directentry" action="input.php" method="POST" autocomplete="off">
		<?php foreach($ruleSet["QParts"] as $qpartval=>$qpart){?>
			<fieldset>
				<legend><div><?php echo $qpart;?></div></legend>
				<select name="Subject"><?php foreach($ruleSet["Subjects"] as $subjval=>$subj)echo "<option value='$subjval'>$subj</option>";?></select>
				<select name="QisMC[<?php echo $qpartval;?>]"><?php foreach($ruleSet["QTypes"] as $typeval=>$type)echo "<option value='$typeval'>$type</option>";?></select><br>
				<textarea name="Question[<?php echo $qpartval;?>]" placeholder="<?php echo DEFAULT_QUESTION_TEXT;?>"></textarea><br>
				<div><?php foreach($ruleSet["MCChoices"] as $choiceval=>$choice)echo "<input type='radio' name='MCa[$qpartval]' value='$choiceval'/>$choice) <input type='text' name='MCChoices[$qpartval][]'/><br>";?></div>
				ANSWER: <input type="text" name="Answer[<?php echo $qpartval;?>]" placeholder="<?php echo DEFAULT_ANSWER_TEXT;?>"/><br>
			</fieldset>
		<?php }?><br>
		<input type="hidden" name="ver" value="<?php $_SESSION["ver"]=generateRandomString(20);echo $_SESSION["ver"];?>"/>
		<input type="submit" name="directentry" value="Submit Question"/>
	</form>
	
	<h2>Copy-Paste</h2>
	<form id="copypaste" action="input.php" method="POST" autocomplete="off">
		Paste it all here:<br>
		<textarea name="copypaste" style="width:100%;height:10em;"><?php echo @preg_replace('/[\r\n]+/', "\n",$unparsed);?></textarea><br>
		<input type="hidden" name="ver" value="<?php echo $_SESSION["ver"];?>"/>
		<input type="submit" value="Submit Question(s)"/>
	</form>
	
	<h2>File Upload</h2>
	<form id="fileupload" action="input.php" method="POST" enctype="multipart/form-data">
		Select file to upload: <input type="file" name="fileupload"><br>
		<input type="hidden" name="ver" value="<?php echo $_SESSION["ver"];?>"/>
		<input type="submit" value="Upload"><br>
	</form>
</div>