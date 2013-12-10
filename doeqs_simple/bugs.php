<?php
require "functions.php";

if(posted("bug")){
	//no db needed
	$bug="\r\nBug submitted ".date('l jS \of F Y h:i:s A')."\r\n".$_POST["bug"]."\r\n\r\n\r\n\r\n";
	file_put_contents("bugs.log",$bug,FILE_APPEND | LOCK_EX);
	echo "<pre>$bug</pre><br>We got your bug! Thanks!";
}
?>