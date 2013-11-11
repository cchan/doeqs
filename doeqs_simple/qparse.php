<?php
require_once "common.php";
require_once "qIO.php";

function qregex(){
//dafuq [in regexpal] it works fine except doesn't match mc questions where there's "how" or "law" in the question, or where there's "only" in X
//also, mislabeled MC as SA passes in no-linebreaks mode
	$subjChoices='(ENERGY|BIO(?:LOGY)?|CHEM(?:ISTRY)?|PHYS(?:|ICS|ICAL SCIENCE)|MATH(?:EMATICS)?|E(?:SS|ARTHSCI|ARTH SCIENCE|ARTH (?:AND|&) SPACE(?: SCIENCE)?))';
	$e='[\:\.\)\- ]';//W. or W) or W- or W: or W .
	$a='[\:\.\)\-]';//W. or W) or W- or W:.
	
	$mcChoices='';
	$choiceArr=["W","X","Y","Z","ANSWER"];
	for($i=0;$i<4;$i++)$mcChoices.=$choiceArr[$i].$e.'((?:(?!'.$choiceArr[$i+1].$e.')[^\n\r])*)\s*';
	return '/(TOSS\-?UP|BONUS)\s*(?:([0-9]+)[\.\)\- ])?\s*'.$subjChoices.'\s*(?:Multiple Choice\s*((?:(?!W'.$e.')[^\n\r])*)\s*'.$mcChoices.'|Short Answer\s*((?:(?:(?!ANSWER'.$a.')[^\n\r])*)(?:\s*[IVX0-9]+'.$e.'(?:(?!ANSWER'.$a.')(?![IVX0-9]+'.$e.')[^\n\r])*)*))\s*ANSWER'.$a.'*\s*((?:[^\n\r])*)([\n\r]|$)/i';
}

	//for($i=0;$i<4;$i++)$mcChoices.=$choiceArr[$i].$e.'((?:(?!'.$choiceArr[$i+1].$e.')[\s\S])*)\s*';
	//return '/(TOSS\-?UP|BONUS)\s*(?:([0-9]+)[\.\)\- ])?\s*'.$subjChoices.'\s*(?:Multiple Choice\s*((?:(?!W'.$e.')[\s\S])*)\s*'.$mcChoices.'|Short Answer\s*((?:(?:(?!ANSWER'.$a.')[^\s\S])*)(?:\s*[IVX0-9]+'.$e.'(?:(?!ANSWER'.$a.')(?![IVX0-9]+'.$e.')[\s\S])*)*))\s*ANSWER'.$a.'*\s*((?:(?![\n\r]|$|TOSS\-?UP|BONUS)[\s\S])*)/i';

//strParseQs - high-level question-parsing; accepts string of questions to parse, does whatever with them, and returns string of output.
function strParseQs($qstr){
	if($qstr===""){echo "Error: No text submitted.";return "";}
	if(strpos($qstr,"\n")===false){echo "Needs line breaks for delineation; no questions uploaded.";return $qstr;}
	
	$nMatches=preg_match_all(qregex(), $qstr, $qtext);
	
	$qs=new Questions();
	for($i=0;$i<$nMatches;$i++){
		try{
			//Indices: 0 full match, Part, Number, Subject, MCQText, ChoicesW, ChoicesX, ChoicesY, ChoicesZ, SAQText, Answer
			$qs->add([[
				"isTU"=>strpos('bt',strtolower(substr($qtext[1][$i],0,1))),
				"Subject"=>strpos('bcpme',strtolower(substr($qtext[3][$i],0,1))),
				"isMC"=>$qtext[4][$i]!="",
				"Question"=>$qtext[4][$i].$qtext[9][$i],
				"MCChoices"=>[$qtext[5][$i],$qtext[6][$i],$qtext[7][$i],$qtext[8][$i]],
				"Answer"=>$qtext[10][$i],
				"MCa"=>strpos('wxyz',strtolower(substr(trim($qtext[10][$i]),0,1))),
				]]);
		}
		catch(Exception $e){}
	}
	$qs->commit();
	$parsedQIDs=$qs->getQIDs();
	
	/*echo "Duplicates: none<br><br>";*/
	echo "<b>Total uploaded Question-IDs: ".((count($parsedQIDs)==0)?"no questions entered":arrayToRanges($parsedQIDs)." (".count($parsedQIDs)." total entered)")."</b>";
	return preg_replace(qregex(),"",$qstr);//stuff remaining after questions detected
}


?>