<h1>Random Question</h1>
<a href="input.php">Enter Questions</a>
<br>
<?php
require_once "qIO.php";
require_once "common.php";

session_start();
if(posted("rate","rateid","ver")&&isset($_SESSION["ver"])&&$_POST["ver"]===$_SESSION["ver"]){
	unset($_SESSION["ver"]);
	$q=new Question($_POST["rateid"]);
	$rateval=intval($_POST["rate"]);
	if($rateval<=2&&$rateval>=-2){$q->rate($rateval);echo "Voted question {$_POST["rateid"]} as {$rateval}; now rated {$q->getRating()}.";}
}
?>

<br>
<div id='question'>
<?php
$Question=new Question(0);
echo $Question->toHTML(false);
?>
</div>
<form action="randq.php" method="POST">
<input type="hidden" name="rateid" value="<?php echo $Question->getQID();?>"/>
<input type="hidden" name="ver" value="<?php $_SESSION["ver"]=generateRandomString(20);echo $_SESSION["ver"];?>"/>
Rating:
<input type="submit" name="rate" value="-2"/>
<input type="submit" name="rate" value="-1"/>
<input type="submit" name="rate" value="&nbsp;&nbsp;0&nbsp;&nbsp;"/>
<input type="submit" name="rate" value="1"/>
<input type="submit" name="rate" value="2"/>
</form>
<br><br>