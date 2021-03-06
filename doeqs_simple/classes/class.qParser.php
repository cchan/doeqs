<?php
if(!defined('ROOT_PATH')){header('HTTP/1.0 404 Not Found');die();}

class qParser{
	public function __construct(){}
	
	//strParseQs - high-level question-parsing; accepts string of questions to parse, does whatever with them, and returns string of output.
	public function parse($qstr){
		global $database,$ruleSet;
		if(str_replace([" ","	","\n","\r"],'',$qstr)===''){echo "Error: No text submitted.";return '';}
		
		//$t=microtime();
		$nMatches=preg_match_all($this->qregex(), $qstr, $qtext);
		//echo '(TIME-millis:'.(microtime()-$t).':TIME)';
		
		$qs=new qIO();
		for($i=0;$i<$nMatches;$i++){
			try{
				//Indices: 0 full match, Part, Number, Subject, MCQText, ChoicesW, ChoicesX, ChoicesY, ChoicesZ, SAQText, MCa, AnswerText
				$qs->addByArray(array(array(
					"isB"=>strpos('tb',strtolower(substr($qtext[1][$i],0,1))),//--todo-- THIS IS A BIG PROBLEM.
					"Subject"=>array_search(strtolower(substr($qtext[3][$i],0,1)),$ruleSet["SubjChars"]),//--todo-- THIS IS A PROBLEM.
					"isSA"=>$qtext[4][$i]=='',
					"Question"=>str_replace(["\r","\n"],'',$qtext[4][$i].$qtext[9][$i]),//:O IMPORTANT: single quotes do not escape \n etc!
					"MCW"=>$qtext[5][$i],"MCX"=>$qtext[6][$i],"MCY"=>$qtext[7][$i],"MCZ"=>$qtext[8][$i],
					"MCa"=>(!empty($qtext[10][$i]))?strpos('wxyz',strtolower($qtext[10][$i])):'',//--todo-- what if 1st char ISN'T [WXYZ]!?
					"Answer"=>$qtext[11][$i],
					)));
			}
			catch(Exception $e){
				echo "ERROR: ".$e->getMessage();
				//--todo-- DISPLAY ERRORS? OR JUST IGNORING? OR IDK IT SHOULDN'T EVEN HAPPEN
			}
		}
		$qs->commit();
		$parsedQIDs=$qs->getQIDs();
		
		/*echo "Duplicates: none<br><br>";*/
		echo "<b>Total uploaded Question-IDs: ".((count($parsedQIDs)==0)?"no questions entered":arrayToRanges($parsedQIDs)." (".count($parsedQIDs)." total entered)")."</b>";
		return preg_replace($this->qregex(),'',$qstr);//stuff remaining after questions detected
	}
	private function qregex(){
	//dafuq [in regexpal] it works fine except doesn't match mc questions where there's "how" or "law" in the question, or where there's "only" in X
	//also, mislabeled MC as SA passes in no-linebreaks mode
		global $ruleSet;//including SubjRegex
		
		$e='[\:\.\)]';//Endings: W. or W) or W- or W:. //can't have space because if has "asdfy asdf" as x, will catch "y "
		$mcChoices='';
		$choiceArr=array_merge($ruleSet['MCChoices'],array("ANSWER"));
		for($i=0;$i<4;$i++)$mcChoices.=$choiceArr[$i].'\)((?:(?!'.$choiceArr[$i+1].'\))[^\n\r])*)\s*';
		return '/(TOSS ?\-? ?UP|BONUS)\s*(?:([0-9]+)[\.\)\- ])?\s*'.$ruleSet["SubjRegex"].'\s*(?:Multiple Choice\s*((?:(?!W'.$e.')[^\n\r])*)\s*'.$mcChoices.'|Short Answer\s*((?:(?:(?!ANSWER'.$e.')[\s\S])*)(?:\s*[IVX0-9]+'.$e.'(?:(?!ANSWER'.$e.')(?![IVX0-9]+'.$e.')[^\n\r])*)*))\s*ANSWER'.$e.'*\s*([WXYZ]?)((?:[^\n\r])*)([\n\r]|$)/i';
	}
	//for($i=0;$i<4;$i++)$mcChoices.=$choiceArr[$i].$e.'((?:(?!'.$choiceArr[$i+1].$e.')[\s\S])*)\s*';
	//return '/(TOSS\-?UP|BONUS)\s*(?:([0-9]+)[\.\)\- ])?\s*'.$subjChoices.'\s*(?:Multiple Choice\s*((?:(?!W'.$e.')[\s\S])*)\s*'.$mcChoices.'|Short Answer\s*((?:(?:(?!ANSWER'.$a.')[^\s\S])*)(?:\s*[IVX0-9]+'.$e.'(?:(?!ANSWER'.$a.')(?![IVX0-9]+'.$e.')[\s\S])*)*))\s*ANSWER'.$a.'*\s*((?:(?![\n\r]|$|TOSS\-?UP|BONUS)[\s\S])*)/i';
}

?>