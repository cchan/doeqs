<?php
define('ROOT_PATH','');
require_once ROOT_PATH.'functions.php';
restrictAccess('a');//xuca

$f=new fileToStr;
$qp=new qParser;

if(posted("directory")){
	$directory=$_POST["directory"];
	if (is_dir($directory)){
		echo "Processing directory $directory:<br>";
		foreach(glob($directory.'/*.*') as $file)
			{echo " ".$file." parsed:<br><textarea>".$qp->parse($f->convert($file,$file))."</textarea><br><br>";ob_flush();flush();}
	}
	else{
		echo "$directory is not a valid directory.";
	}
}
?>
<form action="bulk_file_process.php" method="POST">
<b>Add All Files From Directory:</b>
<input type="text" name="directory"/>
</form>
