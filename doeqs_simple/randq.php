<h1>Random Question</h1>
<a href="input.php">Question Entry</a>
<br>
<?php
require_once "qIO.php";
require_once "common.php";

session_start();
if(posted("rate","rateid","ver")&&isset($_SESSION["ver"])&&$_POST["ver"]===$_SESSION["ver"]){
	unset($_SESSION["ver"]);
	$q=new Questions();
	$q->add([$_POST["rateid"]]);
	$q->rate(0,$rateval=intval($_POST["rate"]));
	echo "Voted question {$_POST["rateid"]} as {$rateval}; now rated {$q->getRating(0)}.";
}
?>

<br>
<div id='question'>
<?php
$Q=new Questions();
$Q->add("randtossup");
echo $Q->allToHTML();
?>
</div>
<form action="randq.php" method="POST">
<input type="hidden" name="rateid" value="<?php echo implode(", ",$Q->getQIDs());?>"/>
<input type="hidden" name="ver" value="<?php $_SESSION["ver"]=generateRandomString(20);echo $_SESSION["ver"];?>"/>
Rating:
<input type="submit" name="rate" value="-2"/>
<input type="submit" name="rate" value="-1"/>
<input type="submit" name="rate" value="&nbsp;&nbsp;0&nbsp;&nbsp;"/>
<input type="submit" name="rate" value="1"/>
<input type="submit" name="rate" value="2"/>
</form>
<br><br>