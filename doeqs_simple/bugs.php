<?php
require "functions.php";

if(posted("bug")){
//database stuff? retr at admin
echo $_POST["bug"];
echo "We got your bug!";
}
?>