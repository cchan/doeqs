<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>%%TITLE%%</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width">

        <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->

        <link rel="stylesheet" href="css/normalize.css">
        <link rel="stylesheet" href="css/main.css">
		
		<link rel="stylesheet" href="css/style.css"/>
		<link rel="stylesheet" href="css/alerts.css"/>
		
        <script src="js/vendor/modernizr-2.6.2.min.js"></script>
    </head>
    <body>
        <!--[if lt IE 7]>
            <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
        <![endif]-->

        <!-- Add your site or application content here -->
		<form id="bugreport">
			<b>Bug Report/Feature Request (all fields optional):</b><br>
			What did you expect to happen; what happened instead?<br><textarea name="bughappened"></textarea><br>
			How would I reproduce it?<br><textarea name="bugreproduce"></textarea><br>
			What page, browser, computer system, etc.<br><textarea name="bugsys"></textarea><br>
			Email, if you want a response: <input type="text" name="bugemail"/><br>
			You can contact me at <a href="mailto:<?php echo WEBMASTER_EMAIL;?>"><?php echo WEBMASTER_EMAIL;?></a>.
			<input type="submit" value="Send Bug Report"/>
		</form>
		<div id="header-wrapper">
			<div style="font-size:1.5em;font-weight:bold;">Lexington Science Bowl Team</div>
			<div id="navbar">%%NAVBAR%%</div>
		</div>
		<div id="content-wrapper">
			<div style="text-align:right;font-size:3em;margin-right:30px;">%%TITLE%%</div>
			<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
			<script>window.jQuery || document.write('<script src="js/vendor/jquery.min.js"><\/script>')</script>
			<script src="js/vendor/jquery.cookie.js"></script>
			
			<script src="js/plugins.js"></script>
			<script src="js/main.js"></script>
			
			<script src="js/alerts.js"></script>
			<script src="js/xhr.js"></script>
			
			<div id="content">%%CONTENT%%</div>
		</div>
		<div id="footer-wrapper">
			Page took %%PAGEMICROTIME%% ms to load. Copyright &copy;2013 Lexington Science Bowl Team.
		</div>


		<?php /*
        <!-- Google Analytics: change UA-XXXXX-X to be your site's ID. -->
        <script>
            var _gaq=[['_setAccount','UA-XXXXX-X'],['_trackPageview']];
            (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
            g.src='//www.google-analytics.com/ga.js';
            s.parentNode.insertBefore(g,s)}(document,'script'));
        </script>
		*/ ?>
    </body>
</html>
