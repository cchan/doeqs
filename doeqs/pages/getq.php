<?php
$con=new DB();
$qresult=$con->query("SELECT QID FROM questions ORDER BY RAND() LIMIT 1");//--todo--How good is SQL RAND()?
//--todo--get how many questions?
$QID=$qresult->fetch_array();
if(is_null($QID)){
	echo("No questions in database. :'(");
}
else{
	$Question=new Question($QID[0]);
	echo "<div id='question'>{$Question->toHTML(false)}</div>";
	echo '<button onclick="window.location.reload(true);">Another!</button>';
}
?>