<?php
if(permissionsLevel()<3){header("HTTP/1.0 404 Not Found");die();}
?>
<h1>Webmaster Dashboard</h1>
<a href="verifyDatabase.php">Verify Database Integrity</a>
<a href="userAdmin.php">View/Add/Change Users</a>
<a href="backupDB.php">Backup Database</a>
<?php //arguments to webmaster.php?>