<?php
//submit


//get
if(is_null($_POST["n"]))die("failure");
$lines=file_get_contents("live.txt").explode("\n\n");
foreach($lines as $line)$line=$line.explode(" ");
$lines["n"]=count($lines);
if($_POST["n"]+30>count($lines))$lines=array_slice($lines,$_POST["n"]);
else $lines=array_slice($lines,-30);
die(getJSON($lines));
?>