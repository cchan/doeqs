<?php
require_once "functions.php";

if(csrfVerify()&&posted("markBad","qids")){//--todo-- should be able to EDIT instead of just marking wrong. Also store history of questions viewed - "Views" table (hugeness) so can look back, mark for look back, etc.
	$q=new qIO();
	$q->addByQID(array_intersect_key($_POST["qids"],array_flip($_POST["markBad"])));//Only do the QIDs that are in markBad.
	$q->markBad();
	$markedBad="<div style='font-weight:bold;font-size:0.9em;color:#FF0000;'>Marked question(s) ".arrayToRanges($q->getQIDs())." as bad.</div>";
	unset($q);
}

$counts=array("QParts"=>count($ruleSet["QParts"]),"Subjects"=>count($ruleSet["Subjects"]),"QTypes"=>count($ruleSet["QTypes"]));
$fullname=array("QParts"=>"Question Part","Subjects"=>"Subject","QTypes"=>"Question Type");

if(csrfVerify()&&posted("numqs"))$numqs=$_POST["numqs"];else $numqs=$DEFAULT_NUMQS;

//--todo--should remember settings in SESSION so if reload, doesn't erase
$Q=new qIO();
if(posted("QParts","Subjects","QTypes"))$addRandStatus=$Q->addRand($_POST["QParts"],$_POST["Subjects"],$_POST["QTypes"],$numqs);
else $addRandStatus=$Q->addRand(array(),array(),array(),$numqs);

//Config options
$checkboxoptions="<div style='font-size:1.5em;font-weight:bold;'>Options</div>";
foreach($counts as $name=>$count){
	$checkboxoptions.='<div><b>'.$fullname[$name].'</b> ';
	for($i=0;$i<$count;$i++)
		$checkboxoptions.='<label>'.$ruleSet[$name][$i].'<input type="checkbox" name="'.$name.'[]" value="'.$i.'" '.((!posted($name)||in_array($i,$_POST[$name]))?'checked':'').' /></label> ';
	$checkboxoptions.='</div>';
}
$checkboxoptions.='<b>Number of Questions</b> (max '.$RANDQ_MAX_QUESTIONS_AT_ONCE.') <input type="number" name="numqs" value="'.$numqs.'" min="1" max="'.$RANDQ_MAX_QUESTIONS_AT_ONCE.'"/>';
?>
<span style="color:#FFFFFF;background-color:#000000;border-radius:5px;padding:0px 5px;"><b>Hotkeys</b> space to display next hidden answer, backspace to hide last revealed answer, enter for fetching more questions</span>
<br>
<?=(isSet($markedBad))?$markedBad:"<br>";?>
<form action="randq.php" method="POST" id="nextq">
<input type="hidden" name="ver" value="<?=csrfCode();?>"/><?php //can just copy code to submit any invalid request ?>
<div id='options'>
<?php echo $checkboxoptions;?>
<br><input type="submit" value="Next"/>
</div>
<div id='questions'>
<?php
if($addRandStatus!="")echo $addRandStatus;
else echo $Q->allToHTML(<<<HEREDOC
<div class='question'>
[QID %QID%]
<div>Mark as Bad: <input type="checkbox" name="markBad[]" value="%N%"/></div>
<input type="hidden" name="qids[]" value="%QID%"/>
<div style='font-weight:bold;text-align:center;'>%PART%</div>
<div>%SUBJECT% <i>%TYPE%</i> %QUESTION%</div>
<div style="font-size:0.9em;">%MCOPTIONS%</div>
<br>ANSWER: <span class='hiddenanswer'><span class='ans'>%ANSWER%</span> <span class='hov'>[hover to show]</span></span>
<br>
<a href="#top">Back to Top</a>
</div>
HEREDOC
);
?>
</div>
<br><input type="submit" value="Next"/>
</form>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script type="text/javascript">
$(function(){$('body').removeClass('noJQuery');
	$(document).keydown(function(e){
		if(!e)var e=window.event;
		if(e.keyCode==13)window.nextq.submit();//enter
		if(e.keyCode==32){//space
			window.elem=$(".question .ans").filter(function(){return $(this).css("display")=="none";}).first().css("display","inline")
				.children(".hov").text("[click to hide]").parent(".question");
			//$('body').animate({scrollTop:window.elem.offset().top},500)
			e.preventDefault();
			return false;
		}
		if(e.keyCode==8){//backspace
			window.elem=$(".question .ans").filter(function(){return $(this).css("display")=="inline";}).last().css("display","none")
				.children(".hov").text("[click to show]").parent(".question");
			//$('body').animate({scrollTop:window.elem.offset().top},500)
			e.preventDefault();
			return false;
		}
	}).keyup(function(e){
		if(!e)var e=window.event;
	});

	$(".hiddenanswer").click(
		function(){
			if($(this).children(".ans").is(':visible'))$(this).children(".hov").text("[click to show]");
			else $(this).children(".hov").text("[click to hide]");
			$(this).children(".ans").toggle();
		});
	$(".hiddenanswer").children(".ans").hide();
	$(".hiddenanswer").children(".hov").text("[click to show]");
});
</script>