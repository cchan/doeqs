<?php
require_once "functions.php";

if(csrfVerify()&&posted("markBad")&&$_POST["markBad"]=="1"){
	$q=new qIO();
	$q->addByQID($_POST["qid"]);
	$q->markBad(0);
	$markedBad="<div style='font-weight:bold;font-size:0.9em;color:#FF0000;'>Marked question {$_POST["qid"]} as bad.</div>";
	unset($q);
}

$stuff=array("QParts"=>"tb","Subjects"=>"bcpme","QTypes"=>"sm");
$fullname=array("QParts"=>"Question Part","Subjects"=>"Subject","QTypes"=>"Question Type");
$string=array("QParts"=>"","Subjects"=>"","QTypes"=>"");
$checkboxoptions="";
foreach($stuff as $name=>$all){
	$checkboxoptions.='<div><b>'.$fullname[$name].'</b>';
	foreach(str_split($all) as $ind=>$val)
		$checkboxoptions.='<br>'.$ruleSet[$name][$ind].'<input type="checkbox" name="'.$name.'[]" value="'.$val.'" '.((!(posted($name)&&!in_array($val,$_POST[$name])))?'checked':'').' />';
	$checkboxoptions.='</div>';
	if(posted($name)){
		foreach($_POST[$name] as $val){
			if(strpos($all,$val)===false)continue;//Don't add it if it's invalid
			if(strpos($string[$name],$val)!==false)continue;//Don't add it if it's already been added
			$string[$name].=$val;
		}
	}
	else{$string[$name]=$all;}
}

$qText="";
$Q=new qIO();
try{$Q->addRand($string["QParts"],$string["Subjects"],$string["QTypes"]);}catch(Exception $e){$qText="No such questions exist.";}

if($qText=="")$qText=$Q->allToHTML("<br>ANSWER: <span class='hiddenanswer'><span class='ans'>","</span> <span class='hov'>[hover to show]</span></span><br>");
?>
<div><b>Hotkeys</b> space to reveal answer, enter for next question</div>
<br>
<?=(isSet($markedBad))?$markedBad:"<br>";?>
<form action="randq.php" method="POST" id="nextq">
<div>Mark as Bad: <input type="checkbox" name="markBad" value="1"/></div>
<div id='question'><?=$qText;?></div>
<input type="hidden" name="ver" value="<?=csrfCode();?>"/>
<?php echo $checkboxoptions;?>
<input type="hidden" name="qid" value="<?=implode("",$Q->getQIDs());//multi question not supported here, since it's not fair to each individual question. ?>"/>
<input type="submit" value="Next"/>
</form>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script type="text/javascript">
$(function(){$('body').removeClass('noJQuery');
	$(document).keydown(function(e){
		if(!e)var e=window.event;
		if(e.keyCode==13)window.nextq.submit();//enter
		if(e.keyCode==32){//space
			$("#question .ans").css("display","inline");
			return false;
		}
	}).keyup(function(e){
		if(!e)var e=window.event;
		if(e.keyCode==32){//space
			$("#question .ans").css("display","none");
			return false;
		}
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