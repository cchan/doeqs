<?php
require_once 'functions.php';

if(csrfVerify()&&posted("markBad","qids")){//--todo-- should be able to EDIT instead of just marking wrong. Also store history of questions viewed - "Views" table (hugeness) so can look back, mark for look back, etc.
	$q=new qIO();
	$q->addByQID(array_intersect_key($_POST["qids"],array_flip($_POST["markBad"])));//Only do the QIDs that are in markBad.
	$q->markBad();
	$markedBad="<div style='font-weight:bold;font-size:0.9em;color:#FF0000;'>Marked question(s) ".arrayToRanges($q->getQIDs())." as bad.</div>";
	unset($q);
}

$counts=array("QParts"=>count($ruleSet["QParts"]),"Subjects"=>count($ruleSet["Subjects"]),"QTypes"=>count($ruleSet["QTypes"]));
$fullname=array("QParts"=>"Question Part","Subjects"=>"Subject","QTypes"=>"Question Type");





//Config options
$checkboxoptions="<div style='font-size:1.5em;font-weight:bold;'>Options</div>";
foreach($counts as $name=>$count){
	$checkboxoptions.='<div><b>'.$fullname[$name].'</b> ';
	if(csrfVerify()&&posted($name)&&array_key_exists($name,$_SESSION["randq"]))$_SESSION["randq"][$name]=$_POST[$name];
	elseif(!array_key_exists($name,$_SESSION["randq"]))$_SESSION["randq"][$name]=NULL;//Remembering in $_SESSION
	for($i=0;$i<$count;$i++)
		$checkboxoptions.='<label>'.$ruleSet[$name][$i].'<input type="checkbox" name="'.$name.'[]" value="'.$i.'" '.((sessioned("randq")&&is_array($_SESSION["randq"][$name])&&in_array($i,$_SESSION["randq"][$name]))?'checked':'').' /></label> ';
	$checkboxoptions.='</div>';
}
if(csrfVerify()&&posted("numqs")&&val_int($_POST["numqs"]))$_SESSION["randq"]["numqs"]=normRange($_POST["numqs"],1,$MAX_NUMQS);elseif(!sessioned($name))$_SESSION["randq"]["numqs"]=$DEFAULT_NUMQS;
$checkboxoptions.='<b>Number of Questions</b> (max '.$MAX_NUMQS.') <input type="number" name="numqs" value="'.$_SESSION["randq"]["numqs"].'" min="1" max="'.$MAX_NUMQS.'"/>';

$Q=new qIO();
if(posted("QParts","Subjects","QTypes"))$addRandStatus=$Q->addRand($_POST["QParts"],$_POST["Subjects"],$_POST["QTypes"],$_SESSION["randq"]["numqs"]);
else $addRandStatus=$Q->addRand(array(),array(),array(),$_SESSION["randq"]["numqs"]);//--todo-- what's the point of "add" if you're only doing it this once? Overhead w/ $Q?

$checkboxoptions.='<div>Export as QID CSV: <textarea>'.implode(', ',$Q->getQIDs()).'</textarea></div>';
$checkboxoptions.='<div>Export as Document: <select><option>Nope this doesn\'t work yet</option></select><input type="submit" name="getDoc" value="Go" onclick="return false;"/></div>';

?>
<span style="color:#FFFFFF;background-color:#000000;border-radius:5px;padding:5px;display:block;width:100%;text-align:center;"><b>Hotkeys:</b> space to display next hidden answer, backspace to hide last revealed answer, enter for fetching more questions</span>
<br>
<?=(isSet($markedBad))?$markedBad:"<br>";?>
<form action="randq.php" method="POST" id="nextq">
<input type="hidden" name='ver' value="<?=csrfCode();?>"/><?php //can just copy code to submit any invalid request ?>
<div id='options'>
<?php echo $checkboxoptions;?>
<div style="font-weight:bold;color:red;">RAGEQUIT THIS DOESN'T WORK EITHER</div>
<br><input type="submit" value="Next"/>
</div>
<div id='questions'>
<?php
if($addRandStatus!='')echo $addRandStatus;
//QID,isB,Subject,isSA,Question,MCW,MCX,MCY,MCZ,Answer
else echo $Q->allToHTML(<<<HEREDOC
<div class='question'>
<span style='display:inline-block;width:40%;'>[QID %QID%]</span><span style='display:inline-block;width:59%;text-align:right;font-size:0.8em;'><a href="#" class="editbtn">[Edit]</a></span>
<div>Mark as Bad: <input type="checkbox" name="markBad[]" value="%N%"/></div>
<input type="hidden" name="qids[]" value="%QID%"/>
<div style='font-weight:bold;text-align:center;' class="part">%PART%</div>
<div><span class="subject">%SUBJECT%</span> <i><span class="type">%TYPE%</span></i> <span class="qtext">%QUESTION%</span></div>
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
<script type="text/javascript">
$(function(){$('body').removeClass('noJQuery');
	$(document).keydown(function(e){
		if(!e)var e=window.event;
		if(e.keyCode==13)//enter
			if($(".question .ans").filter(function(){return $(this).css("display")=="none";}).length==0//Either there's none left
				||confirm("Not all questions are revealed. Are you sure?"))
				window.nextq.submit();//or confirm
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
	})/*.keyup(function(e){
		if(!e)var e=window.event;
	})*/;

	$(".hiddenanswer").click(
		function(){
			if($(this).children(".ans").is(':visible'))$(this).children(".hov").text("[click to show]");
			else $(this).children(".hov").text("[click to hide]");
			$(this).children(".ans").toggle();
		});
	$(".hiddenanswer").children(".ans").hide();
	$(".hiddenanswer").children(".hov").text("[click to show]");
	
	$(".editbtn").click(function(){alert("Nope, this doesn't work yet.");});
});
</script>