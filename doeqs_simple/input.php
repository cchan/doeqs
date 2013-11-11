<?php
require_once "functions.php";
?>
<link rel="stylesheet" href="style.css"/>
<div id="main-wrapper">
<h1>Question Entry</h1>
<a href="index.php">Home</a><br>
<a href="randq.php">Get Random Question</a><br>
<br><br>
<?php
$unparsed="";
if(isSet($_POST["ver"])&&isSet($_SESSION["ver"])&&$_POST["ver"]==$_SESSION["ver"]){
	unset($_SESSION["ver"]);
	if(isSet($_POST["copypaste"])||isSet($_FILES["fileupload"])||isSet($_POST["directentry"])){
		echo '<div style="font-size:0.8em;border:solid 1px #000000;display:inline-block;padding:5px;">
			<i>We are processing your questions right now...</i><br><br>';
		if(isSet($_POST["directentry"])){
			$err="";
			try{$q=new qIO([$_POST]);}
			catch(Exception $e){$err="Error: ".$e->getMessage();}
			
			if($err=="")echo "Question entered successfully, with Question-ID <b>".(new Question($_POST))->getQID()."</b><br><br><br>";
			else echo $err;
		}
		else{
			$qp=new qParser();
			if(isSet($_POST["copypaste"]))$unparsed=$qp->parse($_POST["copypaste"]);
			else{
				$fs=new fileToStr();
				if(is_array($_FILES["fileupload"]["tmp_name"])){
					foreach($_FILES["fileupload"]["tmp_name"] as $ind=>$tmp_name){
						$name=$_FILES["fileupload"]["name"][$ind];
						$unparsed.=$qp->parse($fs->convert(["tmp_name"=>$tmp_name,"name"=>$name]));
					}
				}
				else $unparsed=$qp->parse($fs->convert($_FILES["fileupload"]));
			}
			if(str_replace(array("\n","\r"," ","	","_"),"",$unparsed)!="")
				echo "<br><br>Below, in the copy-paste section, are what remains in the document after detecting all the questions we could find.<br>";
			else echo "<br><br>No unparsed question text found (that means we got every question). Yay!";
		}
		
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
				<legend style="text-align:center;"><b><?php echo $qpart;?></b></legend>
				<select name="Subject"><?php foreach($ruleSet["Subjects"] as $subjval=>$subj)echo "<option value='$subjval'>$subj</option>";?></select>
				<select name="QisMC[<?php echo $qpartval;?>]"><?php foreach($ruleSet["QTypes"] as $typeval=>$type)echo "<option value='$typeval'>$type</option>";?></select><br>
				<textarea name="Question[<?php echo $qpartval;?>]" placeholder="<?php echo $DEFAULT_QUESTION_TEXT;?>"></textarea><br>
				<div><?php foreach($ruleSet["MCChoices"] as $choiceval=>$choice)echo "<input type='radio' name='MCa[$qpartval]' value='$choiceval'/>$choice) <input type='text' name='MCChoices[$qpartval][]'/><br>";?></div>
				ANSWER: <input type="text" name="Answer[<?php echo $qpartval;?>]" placeholder="<?php echo $DEFAULT_ANSWER_TEXT;?>"/><br>
			</fieldset>
		<?php }?><br>
		<input type="hidden" name="ver" value="<?php $_SESSION["ver"]=generateRandomString(20);echo $_SESSION["ver"];?>"/>
		<input type="submit" name="directentry" value="Submit Question"/>
	</form>
	
	<h2>Copy-Paste</h2>
	
	<form id="copypaste" action="input.php" method="POST" autocomplete="off">
		<input type="hidden" name="ver" value="<?php echo $_SESSION["ver"];?>"/>
		<?php if(str_replace(array("\n","\r"," ","	"),"",$unparsed)!=""){?>
			<div style='font-size:0.8em;'>
			Common syntax errors include:
			<ul>
			<li>a multi-line question statement,</li>
			<li>improperly labeled type (as "Multiple Choice" or "Short Answer" or even "Question Type"),
			<li>missing some necessary components (like multiple choices and an answer)</li>
			<li>mislabeled multiple choice choices</li>
			<li>really horrible misspellings of keywords</li>
			</ul>
			<i>Also note that sometimes the detector will reject perfectly valid questions; try just resubmitting or moving on.</i>
			</div>
			<br>
		<?php }else{?>
			Paste it all here:<br>
		<?php }?>
		<textarea name="copypaste" style="width:100%;height:10em;"><?php echo preg_replace('/[\r\n]+/', "\n",$unparsed);?></textarea><br>
		<input type="submit" value="Submit Question(s)"/>
	</form>
	
	<h2>File Upload</h2>
	<form id="fileupload" action="input.php" method="POST" enctype="multipart/form-data">
		<input type="hidden" name="ver" value="<?php echo $_SESSION["ver"];?>"/>
		Select file to upload:
		<input type="file" name="fileupload"><br>
		<input type="submit" value="Upload"><br>
	</form>
</div>
</div>