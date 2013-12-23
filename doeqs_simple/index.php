<?php
require_once 'functions.php';
?>
<br>
This is <b>DOE Question Database version <?php echo $VERSION_NUMBER;?></b>!
<br>
<i>Note that you should not actually put any questions into the database, because they will be erased regularly until version 1.0 or so.</i>
<br><br>
<?=database_stats();?>