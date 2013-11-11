<?php
require_once "functions.php";
?>
<link rel="stylesheet" href="style.css"/>
<div id="main-wrapper">
<h1>Random Question</h1>
<a href="index.php">Home</a><br>
<a href="input.php">Question Entry</a><br>
<br>
<?php
if(posted("rate","rateid","ver")&&sessioned("ver")&&$_POST["ver"]===$_SESSION["ver"]){
	unset($_SESSION["ver"]);
	$q=new qIO();
	$q->add([$_POST["rateid"]]);
	$q->rate(0,$rateval=intval($_POST["rate"]));
	echo "<div style='font-weight:bold;font-size:0.9em;'>Voted question {$_POST["rateid"]} as {$rateval}; total rating now {$q->getRating(0)}.</div>";
	unset($q);
}
else echo "<br>";
?>

<br>
<div id='question'>
<?php
$Q=new qIO();
$Q->add("rand");
echo $Q->allToHTML(false);
?>
</div>
<br>
<form action="randq.php" method="POST">
<input type="hidden" name="rateid" value="<?php echo implode(", ",$Q->getQIDs());?>"/>
<input type="hidden" name="ver" value="<?php $_SESSION["ver"]=generateRandomString(20);echo $_SESSION["ver"];?>"/>
Rating:
<button name="rate" value="-1">Dislike</button>
<button name="rate" value="0">No opinion</button>
<button name="rate" value="1">Like</button>
</form>
</div>