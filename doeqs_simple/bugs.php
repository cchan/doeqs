<?php
require "functions.php";

if(posted("bug")){
	//no db needed
	$bug="\r\nBug submitted ".date('l jS \of F Y h:i:s A')."\r\n".$_POST["bug"]."\r\n\r\n\r\n\r\n";
	file_put_contents("bugs.log",$bug,FILE_APPEND | LOCK_EX);
	echo "<pre>$bug</pre><br>We got your bug! Thanks!";
}
else{
	echo '<p>Hi, this is the bug-processing page! You can submit a bug or feature request up there in the upper-right corner. Thanks!</p>';
	echo '<p>Or, you can send it to me directly at <a href="mailto:'.$WEBMASTER_EMAIL.'">'.$WEBMASTER_EMAIL.'</a> if even the bug reporting isn\'t working.';
}
?>