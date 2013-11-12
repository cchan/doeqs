<?php require_once "functions.php";?>
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
<div>Hotkeys <span class='hiddenanswer'><span class='ans'>space to reveal answer, enter for next question</span> <span class='hov'>[hover]</span></span></div>
<a href="index.php">Home</a><br>
<a href="input.php">Question Entry</a><br>
<br>
<?php
if(posted("rate","rateid","ver")&&sessioned("ver")&&$_POST["ver"]===$_SESSION["ver"]){
	unset($_SESSION["ver"]);
	$q=new qIO();
	$q->add(array($_POST["rateid"]));
	$q->rate(0,$rateval=intval($_POST["rate"]));
	echo "<div style='font-weight:bold;font-size:0.9em;'>Voted question {$_POST["rateid"]} as {$rateval}; total rating now {$q->getRating(0)}.</div>";
	unset($q);
}
else echo "<br>";
?>

<br>
<div id='question'>
<?php
$allparts="tb";$allsubjs="bcpme";
$partstring="";$subjstring="";

if(posted("parts")){
	foreach($_POST["parts"] as $val){
		if(strpos($allparts,$val)===false)continue;//Don't add it if it's invalid
		if(strpos($partstring,$val)!==false)continue;//Don't add it if it's already been added
		$partstring.=$val;
	}
}
else{$partstring=$allparts;}
if(posted("subjects")){
	foreach($_POST["subjects"] as $val){
		if(strpos($allsubjs,$val)===false)continue;//Don't add it if it's invalid
		if(strpos($subjstring,$val)!==false)continue;//Don't add it if it's already been added
		$subjstring.=$val;
	}
}
else{$subjstring=$allsubjs;}

$Q=new qIO();
$Q->addRand($partstring,$subjstring);
echo $Q->allToHTML(false);
?>
</div>
<br>
<form action="randq.php" method="POST" id="nextq">
<input type="hidden" name="rateid" value="<?php echo implode(", ",$Q->getQIDs());?>"/>
<input type="hidden" name="ver" value="<?php $_SESSION["ver"]=generateRandomString(20);echo $_SESSION["ver"];?>"/>
<div><b>Question Part:</b> <?php foreach(str_split($allparts) as $ind=>$val)echo '<br>'.$ruleSet["QParts"][$ind].'<input type="checkbox" name="parts[]" value="'.$val.'" '.((!(posted("parts")&&!in_array($val,$_POST["parts"])))?'checked':'').' />';?></div>
<div><b>Subject:</b> <?php foreach(str_split($allsubjs) as $ind=>$val)echo '<br>'.$ruleSet["Subjects"][$ind].'<input type="checkbox" name="subjects[]" value="'.$val.'" '.((!(posted("subjects")&&!in_array($val,$_POST["subjects"])))?'checked':'').' />';?></div>
<input type="submit" value="Next"/>
</form>
</div>
</body>
</html>