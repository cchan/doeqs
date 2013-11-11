<?php
//qIO.php
	//class qIO

//DOCUMENTATION OF DATABASE
/*
--todo--
*/


class qIO{//Does all the validation... for you! By not trusting you at all. ;)
	private $QID,$isTU,$Subject,$isMC,$Question,$MCChoices,$Answer,$Rating;
	public function __construct(){
		$this->QID=$this->isTU=$this->Subject=$this->isMC=$this->Question=$this->MCChoices
			=$this->Answer=$this->Rating=array();
		}
	public function add($paramsArray){//Add to the array of questions, each from array or ID.
		global $ruleSet;
		global $database;
		if(is_null($database))$database=new DB();
		
		$RatingThreshold=-3;
		
		if(is_null($paramsArray)){//Huh. No parameters. -_-
			throw new Exception("Q: No parameters");
		}
		elseif($paramsArray==="rand"||$paramsArray==="randtossup"||$paramsArray==="randbonus"||$paramsArray==="randpair"){
			$row=array();
			$query="SELECT QID, isTU, Subject, isMC, Question, MCW, MCX, MCY, MCZ, Answer, Rating FROM questions 
WHERE Rating > %0% AND Deleted=0
AND TimesViewed IN (SELECT MIN(TimesViewed) FROM questions WHERE Deleted=0)
ORDER BY RAND() LIMIT 1";
			if($paramsArray==="randtossup")$row[]=$database->query_assoc(str_replace("WHERE","WHERE isTU=TRUE AND",$query),[$RatingThreshold]);
			else if($paramsArray==="randbonus")$row[]=$database->query_assoc(str_replace("WHERE","WHERE isTU=FALSE AND",$query),[$RatingThreshold]);
			//else if($paramsArray==="randpair"){
			//	$row[]=$database->query_assoc(str_replace("WHERE","WHERE isTU=TRUE AND",$query),[$RatingThreshold]);
			//	$row[]=$database->query_assoc(str_replace("WHERE","WHERE isTU=FALSE AND Subject=%1% AND",$query,$row[count($row)-1]["Subject"]),[$RatingThreshold]);
			//}
			else $row[]=$database->query_assoc($query,[$RatingThreshold]);
			
			if(count($row)==0)throw new Exception("No questions in database.");
			
			
			foreach($row as $r){
				$database->query_assoc("UPDATE questions SET TimesViewed=TimesViewed+1 WHERE QID=%0%",[$r["QID"]]);
				$this->QID[]=$r["QID"];
				$this->isTU[]=$r["isTU"];
				$this->Subject[]=$r["Subject"];
				$this->isMC[]=$r["isMC"];
				$this->Question[]=$r["Question"];
				$this->MCChoices[]=[$r["MCW"],$r["MCX"],$r["MCY"],$r["MCZ"]];
				$this->Answer[]=$r["Answer"];
				$this->Rating[]=$r["Rating"];
			}
			return;
		}
		elseif(!is_array($paramsArray)){
			throw new Exception("Invalid input params");
		}
		
		
		
		$queryadd="INSERT INTO questions (Subject, isMC, Question, MCW, MCX, MCY, MCZ, Answer) VALUES ";
		$queryarr=array();
		foreach($paramsArray as $n=>$params){
			if($params==strval(intval($params))){
				$row=$database->query_assoc("SELECT QID, isTU, Subject, isMC, Question, MCW, MCX, MCY, MCZ, Answer, Rating FROM questions WHERE QID = %0% AND Deleted=FALSE LIMIT 1",[$params]);
				if(count($row)==0)throw new Exception("Invalid QID provided.");
				
				$this->QID[$n]=$row["QID"];
				$this->isTU[$n]=$row["isTU"];
				$this->Subject[$n]=$row["Subject"];
				$this->isMC[$n]=$row["isMC"];
				$this->Question[$n]=$row["Question"];
				$this->MCChoices[$n]=[$row["MCW"],$row["MCX"],$row["MCY"],$row["MCZ"]];
				$this->Answer[$n]=$row["Answer"];
				$this->Rating[$n]=$row["Rating"];
			}
			elseif(is_array($params)){//Then it's (probably) being given all the needed parameters in an array.
				$n+=count($this->QID);//"Temporary" fix. Ugly.
				$this->isTU[$n]=$params["isTU"]==1?1:0;
			
				$this->Subject[$n]=intval($params["Subject"]);
				if($this->Subject[$n]===false||$this->Subject[$n]>4||$this->Subject[$n]<0)throw new Exception("Invalid subject");
				
				$this->isMC[$n]=(bool)$params["isMC"];
				$this->Question[$n]=$params["Question"];
				$this->Answer[$n]=$params["Answer"];
				$this->MCChoices[$n]=$params["MCChoices"];
				
				//Validity checking
				global $DEFAULT_QUESTION_TEXT,$DEFAULT_ANSWER_TEXT;
				if(!($this->isMC[$n]===true||$this->isMC[$n]===false))throw new Exception("Invalid question-type");
				if($this->Question[$n]==""||$this->Question[$n]==$DEFAULT_QUESTION_TEXT
					||$this->Answer[$n]==""||$this->Answer[$n]==$DEFAULT_ANSWER_TEXT)
					throw new Exception("Blank question/answer");//handle js-side too
				
				//Deal with MC vs SA
				if($this->isMC[$n]){
					if(anyIndicesEmpty($this->MCChoices[$n],0,1,2,3))throw new Exception("Some multiple choice blank");
					if(($this->Answer[$n]=strpos('wxyz',strtolower(substr(trim($this->Answer[$n]),0,1))))===false)throw new Exception("Invalid answer");
				}
				
				//Start value for rating.
				$this->Rating=0;
				
				//Hm. Start value for QID.
				$this->QID[$n]=0;
				
				
				$n-=count($this->QID);
			}
			else{
				throw new Exception("Q: Bad parameters");
			}
		}
	}
	
	public function commit(){
		global $database;
		$max_query_length=1000;//Estimated maximum query length; be safe and go well below the actual.
		$i=0;//which iteration we're on.
		$q='INSERT INTO questions (isTU, Subject, isMC, Question, MCW, MCX, MCY, MCZ, Answer) VALUES ';
		$valarr=array();
		$lengthestimate=count($q);
		$QIDs=array();
		foreach($this->QID as $ind=>$val){
			$lengthestimate+=count($this->Question[$ind])+count(implode($this->MCChoices[$ind]))+count($this->Answer[$ind])+20;
			if($lengthestimate-1>$max_query_length){
				$database->query_assoc(substr($q,0,-1),$valarr);
				for($j=0;$j<$i;$j++)$QIDs[]=$database->insert_id+$j;
				$i=0;
				$q='INSERT INTO questions (isTU, Subject, isMC, Question, MCW, MCX, MCY, MCZ, Answer) VALUES ';
				$lengthestimate=count($q);
				$valarr=array();
			}
			
			$how_many_entries=9;
			$textadd="(";for($x=$how_many_entries*$i;$x<$how_many_entries*($i+1);$x++)$textadd.="%$x%,";$textadd=substr($textadd,0,-1).")";
			
			array_push($valarr,$this->isTU[$ind],$this->Subject[$ind],$this->isMC[$ind],$this->Question[$ind],
				$this->MCChoices[$ind][0],$this->MCChoices[$ind][1],$this->MCChoices[$ind][2],$this->MCChoices[$ind][3],
				$this->Answer[$ind]);
			$q.=$textadd.",";
			$i++;
		}
		if(count($valarr)>0)$database->query_assoc(substr($q,0,-1),$valarr);//What QIDs?????????????????????????
		for($j=0;$j<$i;$j++)$QIDs[]=$database->insert_id+$j;
		
		foreach($QIDs as $ind=>$QID)$this->QID[$ind]=$QID;
		
		//:( duplicates
		//http://stackoverflow.com/questions/18932/how-can-i-remove-duplicate-rows no idea what it does
		//$database->query_assoc("UPDATE questions SET Deleted=TRUE WHERE QID NOT IN (SELECT MIN(QID) FROM questions WHERE Deleted=FALSE GROUP BY Question)");
		//--todo--so how to do this for our php copy of the questions?
	}
	
	public function toHTML($i,$plainans=true){//Return nice HTML.
		global $ruleSet;
		//Then just compile together. (--todo--chk xss)
		$return="<div class='question'>";
		$return.="[QID {$this->QID[$i]}, rating {$this->Rating[$i]}]";
		$return.="<div style='font-weight:bold;text-align:center;'>{$ruleSet["QParts"][!$this->isTU[$i]]}</div>{$ruleSet["Subjects"][$this->Subject[$i]]} <i>{$ruleSet["QTypes"][(int)$this->isMC[$i]]}</i> ".nl2br(strip_tags($this->Question[$i]))."<br>";
		if($this->isMC[$i])for($j=0;$j<4;$j++)$return.="<div style='font-size:0.9em;'>{$ruleSet["MCChoices"][$j]}) {$this->MCChoices[$i][$j]}</div>";
		
		$AnswerText=($this->isMC[$i])?$ruleSet["MCChoices"][$this->Answer[$i]].") ".$this->MCChoices[$i][$this->Answer[$i]]//MC
				:strip_tags($this->Answer[$i]);//SA
		if($plainans)$return.="<br>ANSWER: <b>$AnswerText</b><br>";
		else{
			$return.="<br>ANSWER: <span class='hiddenanswer'><span class='ans'>$AnswerText</span> <span class='hov'>[hover for answer]</span></span><br>";
			$return.="<style type='text/css'>.hiddenanswer .hov{font-weight:bold;color:#00f;font-size:0.8em;}.hiddenanswer .ans{display:none;font-weight:bold;}.hiddenanswer:hover .ans{display:inline;}</style>";
		}
		$return.="</div>";
		return $return;
	}
	public function allToHTML($plainans=true){
		$ret="";
		for($i=0;$i<count($this->QID);$i++)$ret.=$this->toHTML($i,$plainans);
		return $ret;
	}
	
	public function getQIDs(){return $this->QID;}
	public function getQID($i){return $this->QID[$i];}
	public function getSubj($i){return $this->Subject[$i];}
	public function getRating($i){return $this->Rating[$i];}
	
	public function rate($i,$x){//Rate question.
		global $database;
		static $rated=array();
		if(@$rated[$i]===true)return;
		if($x!=intval($x))return;
		//Being super-careful.
		
		if(intval($x)>=-1||intval($x)<=1){
			$this->Rating[$i]+=intval($x);
			$database->query_assoc("UPDATE questions SET Rating=Rating+%1% WHERE QID=%0% LIMIT 1",[$this->QID[$i],intval($x)]);
		}
		
		$rated[$i]=true;
	}
};
function getExportSize(){
	$a=$database->query_assoc("SELECT COUNT(*) FROM questions WHERE Deleted=FALSE");
	echo "Estimated size: ".($a[0]/5)."KB";
}
function exportQuestionsCSV(){
	$database->query_assoc("SELECT * INTO OUTFILE 'questionsExport.csv' FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' LINES TERMINATED BY '\r\n' FROM questions WHERE Deleted=FALSE");
}

?>