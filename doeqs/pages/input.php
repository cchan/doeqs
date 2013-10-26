<?php
global $ruleSet;
//Bugs:
//Test, test, test.
//Compatibility... Browsers... OSs...
?>
Pick a question set to enter your question(s) into: 
<div id="qsetselect"></div>
<div id="qsethas">&nbsp;</div>
<div id="question-wrapper">
	<ul id="question-input-menu">
		<li class="currentinput">Direct Entry</li>
		<li>Copy-Paste</li>
		<li>File Upload</li>
	</ul>
	<form id="directentry" autocomplete="off" class="currentinput">
		<?php foreach($ruleSet["QParts"] as $qpartval=>$qpart){?>
			<fieldset>
				<legend><div><?php echo $qpart;?></div></legend>
				<select name="Subject"><?php foreach($ruleSet["Subjects"] as $subjval=>$subj)echo "<option value='$subjval'>$subj</option>";?></select>
				<select name="QType[<?php echo $qpartval;?>]"><?php foreach($ruleSet["QTypes"] as $typeval=>$type)echo "<option value='$typeval'>$type</option>";?></select><br>
				<textarea name="Question[<?php echo $qpartval;?>]" placeholder="<?php echo DEFAULT_QUESTION_TEXT;?>"></textarea><br>
				<div id="MC[<?php echo $qpartval;?>]"><?php foreach($ruleSet["MCChoices"] as $choiceval=>$choice)echo "<label><input type='radio' name='MCa[$qpartval]' value='$choiceval'/>$choice) <input type='text' name='MC[$qpartval][]'/></label><br>";?></div>
				ANSWER: <input type="text" name="Answer[<?php echo $qpartval;?>]" placeholder="<?php echo DEFAULT_ANSWER_TEXT;?>"/><br>
			</fieldset>
		<?php }?><br>
		<input type="submit" name="directentry" value="Submit Question"/>
	</form>
	<form id="copypaste" action="index.php?p=input" method="POST">
		Paste it all here:<br>
		<textarea name="copypaste" style="width:100%;height:10em;"></textarea><br>
		<input type="submit" name="copypaste" value="Submit Question(s)"/>
	</form>
	<form id="fileupload" action="index.php?p=input" method="POST" enctype="multipart/form-data">
		Select file to upload: <input type="file" name="file"><br>
		<input type="submit" value="Upload"><br>
	</form>
</div>
<script type="text/javascript" src="js/qform.js"></script>