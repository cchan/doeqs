<?php require_once "functions.php";?>
<p><a href="http://science.energy.gov/wdts/nsb/">Department of Energy National Science Bowl</a> (referred to as "DOE" or "NSB") is a national competition testing knowledge and speed in all areas of science and math. Teams face off in fast-paced buzzer rounds ranging from local to national competitions.</p>
<p>This website, DOE Question Database (currently in version <?php echo $VERSION_NUMBER;?>), is a database for writing and practicing on DOE questions. Currently, we have <?php $q=$database->query_assoc("SELECT COUNT(*) AS nQs FROM questions WHERE Deleted=0");echo $q["nQs"];?> questions in our database and counting!</p>
<br>
<h3>DOE Question Database Website</h3>
Written in HTML/CSS/JS/PHP/MySQL by Clive Chan 2013-2016<br>
<?php //To Webmasters: put your name here! "Webmin'd and improved by [you] [years]" ?>
<br>
<h3>Various pieces of code from...</h3>
<a href="http://jquery.com/">JQuery</a><br>
<a href="http://stackoverflow.com/">StackOverflow</a><br>
PDF2Text<br>
<a href="http://php.net/">PHP.net</a><br>
<a href="http://www.w3schools.com/">w3schools</a><br>
And most of all Google Search.<br>
and many more...<br>