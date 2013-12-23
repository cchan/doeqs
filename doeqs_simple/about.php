<?php require_once 'functions.php';?>
<p><a href="http://science.energy.gov/wdts/nsb/">Department of Energy National Science Bowl</a> (referred to as "DOE" or "NSB") is a national competition testing knowledge and speed in all areas of science and math. Teams face off in fast-paced buzzer rounds ranging from local to national competitions.</p>
<p>This website, DOE Question Database (currently in version <?php echo $VERSION_NUMBER;?>), is a database for writing and practicing on DOE questions. Currently, we have <?php $q=$database->query_assoc("SELECT COUNT(*) AS nQs FROM questions WHERE Deleted=0");echo $q["nQs"];?> questions in our database and counting!</p>
<br>
<h3>DOE Question Database Website Development and Administration</h3>
Copyright &copy;2013-present Lexington Science Bowl Team<br>
Written in HTML/CSS/JS/<a href="http://www.php.net/">PHP</a>/<a href="http://www.mysql.com/">MySQL</a> by Clive Chan 2013-2016<br>
Design assistance from [still looking]<br>
<?php /*Webmin'd and developed by
<ul>
	<li>Someone 2016-2018</li>
</ul>
*/ ?>
<br>
<h3>Various pieces of code from...</h3>
<a href="http://jquery.com/">JQuery</a><br>
<a href="http://stackoverflow.com/">StackOverflow</a><br>
<a href="https://code.google.com/p/lucene-silverstripe-plugin/source/browse/trunk/thirdparty/class.pdf2text.php?r=19">PDF2Text</a><br>
<a href="http://php.net/">PHP.net</a><br>
<a href="http://www.w3schools.com/">w3schools</a><br>
And most of all <a href="http://www.google.com/">Google Search</a>.<br>
and many more...<br>
<br>