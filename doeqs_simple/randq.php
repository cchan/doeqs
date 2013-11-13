<?php
require_once "functions.php";

if(posted("markBad")&&$_POST["markBad"]=="1"){
	$q=new qIO();
	$q->addByQID($_POST["qid"]);
	$q->markBad(0);
	$markedBad="<div style='font-weight:bold;font-size:0.9em;color:#FF0000;'>Marked question {$_POST["qid"]} as bad.</div>";
	unset($q);
}

$stuff=array("QParts"=>"tb","Subjects"=>"bcpme","QTypes"=>"sm");
$fullname=array("QParts"=>"Question Part","Subjects"=>"Subject","QTypes"=>"Question Type");
$string=array("QParts"=>"","Subjects"=>"","QTypes"=>"");
$checkboxoptions="";
foreach($stuff as $name=>$all){
	$checkboxoptions.='<div><b>'.$fullname[$name].'</b>';
	foreach(str_split($all) as $ind=>$val)
		$checkboxoptions.='<br>'.$ruleSet[$name][$ind].'<input type="checkbox" name="'.$name.'[]" value="'.$val.'" '.((!(posted($name)&&!in_array($val,$_POST[$name])))?'checked':'').' />';
	$checkboxoptions.='</div>';
	if(posted($name)){
		foreach($_POST[$name] as $val){
			if(strpos($all,$val)===false)continue;//Don't add it if it's invalid
			if(strpos($string[$name],$val)!==false)continue;//Don't add it if it's already been added
			$string[$name].=$val;
		}
	}
	else{$string[$name]=$all;}
}

$qText="";
$Q=new qIO();
try{$Q->addRand($string["QParts"],$string["Subjects"],$string["QTypes"]);}catch(Exception $e){$qText="No such questions exist.";}
if($qText=="")$qText=$Q->allToHTML("<br>ANSWER: <span class='hiddenanswer'><span class='ans'>","</span> <span class='hov'>[hover for answer]</span></span><br>");
?>
<html>
<head>
<link rel="stylesheet" href="style.css"/>
<script type="text/javascript">
function keydown(e){
	if(!e)var e=window.event;
	if(e.keyCode==13)window.nextq.submit();//enter
	if(e.keyCode==32){//space
		var as=document.getElementById("question").getElementsByClassName("ans");
		for(var i=0;i<as.length;i++)as[i].style.display="inline";
		return false;
	}
}
function keyup(e){
	if(!e)var e=window.event;
	if(e.keyCode==32){//space
		var as=document.getElementById("question").getElementsByClassName("ans");
		for(var i=0;i<as.length;i++)as[i].style.display="none";
		return false;
	}
}
</script>
</head>
<body onkeydown="return keydown();" onkeyup="return keyup();">
<div id="main-wrapper">
<h1>Random Question</h1>
<a href="index.php">Home</a><br>
<a href="input.php">Question Entry</a><br>
<div>Hotkeys <span class='hiddenanswer'><span class='ans'>space to reveal answer, enter for next question</span> <span class='hov'>[hover]</span></span></div>
<br>
<?php if(isSet($markedBad))echo $markedBad;else echo "<br>";?>
<form action="randq.php" method="POST" id="nextq">
<div>Mark as Bad: <input type="checkbox" name="markBad" value="1"/></div>
<div id='question'><?php echo $qText;?></div>
<input type="hidden" name="ver" value="<?=genVerCode();?>"/>
<?php echo $checkboxoptions;?>
<input type="hidden" name="qid" value="<?=implode("",$Q->getQIDs());?>"/>
<input type="submit" value="Next"/>
</form>
</div>
</body>
</html>