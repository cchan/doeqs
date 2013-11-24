<?php require_once "functions.php";?>
<p><a href="http://science.energy.gov/wdts/nsb/">Department of Energy National Science Bowl</a> ("DOE" or "NSB") is a national competition testing knowledge and speed in all areas of science and math. Teams face off in fast-paced buzzer rounds ranging from local to national competitions.</p>
<p>This website, DOE Question Database (currently in version <?php echo $VERSION_NUMBER;?>), is a database for writing and practicing on DOE questions. Currently, we have <?php $q=$database->query_assoc("SELECT COUNT(*) AS nQs FROM questions");echo $q["nQs"];?> questions in our database, and counting!</p>
<br>
<p>Written in HTML, CSS, PHP by Clive Chan 2013-2016</p>
<?php //To Webmasters: put your name here! ?>
<p>Various pieces of code from PHP.net, w3schools, JQuery, PDF2Text, StackOverflow.
Thanks to several wonderful browsers like Chrome and Firefox, and most of all Google Search.
And many more.</p>