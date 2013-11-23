<?php
//qIO.php
	//class qIO

//DOCUMENTATION OF DATABASE
/*
--todo--
*/


class qIO{//Does all the validation... for you! By not trusting you at all. ;)
	private $QID,$isB,$Subject,$isSA,$Question,$MCChoices,$Answer;
	public function __construct(){
		$this->QID=$this->isB=$this->Subject=$this->isSA=$this->Question=$this->MCChoices
			=$this->Answer=array();
	}
	public function __destruct(){
		foreach($this->QID as $id)if($id==0)throw new Exception("Uncommitted added questions.");
	}
	public function addRand($parts,$subjects,$types){//arrays of the numbers to include eg subj [0,1,4] for b,c,e
		global $database;
		global $markBadThreshold;
		global $ruleSet;
	
		$row=array();
		$query="SELECT QID, isB, Subject, isSA, Question, MCW, MCX, MCY, MCZ, Answer FROM questions WHERE MarkBad < %0% AND Deleted=0";
		
		$stuff=array("QParts"=>$parts,"Subjects"=>$subjects,"QTypes"=>$types);
		$counts=array("QParts"=>count($ruleSet["QParts"]),"Subjects"=>count($ruleSet["Subjects"]),"QTypes"=>count($ruleSet["QTypes"]));
		$dbname=array("QParts"=>"isB","Subjects"=>"Subject","QTypes"=>"isSA");
		$checkboxoptions="";
		foreach($counts as $name=>$howmany){
			$stuff[$name]=array_values(array_unique($stuff[$name]));
			if(count($stuff[$name])<$howmany-1 && count($stuff[$name])>0){
				$query.=" AND (";
				for($i=0;$i<count($stuff[$name]);$i++)
					if(array_search($stuff[$name][$i],range(0,$howmany-1))!==false)//if it's not absolutely valid go away
						$query.=$dbname[$name]."=".$stuff[$name][$i]." OR ";
				$query.="0)";//'or zero' => won't affect rest of condition, and it's a neater way to end.
			}
		}
		
		//NOTE that TimesViewed is across everyone, and if you have something like 2 10 10 10, you'll get the 2 at least 8 times in a row.
			//The assumption that there is a large pool for _each_ possible classification (2*5*2=20 of them) eliminates this problem.
		$query.=" ORDER BY TimesViewed ASC, RAND() LIMIT 1";//Order by TimesViewed, and then randomize within each TimesViewed value.
		$r=$database->query_assoc($query,array($markBadThreshold));
		
		if(count($r)==0)throw new Exception("No questions.");
		
		$database->query_assoc("UPDATE questions SET TimesViewed=TimesViewed+1 WHERE QID=%0%",array($r["QID"]));
		$this->QID[]=$r["QID"];
		$this->isB[]=$r["isB"];
		$this->Subject[]=$r["Subject"];
		$this->isSA[]=$r["isSA"];
		$this->Question[]=$r["Question"];
		$this->MCChoices[]=array($r["MCW"],$r["MCX"],$r["MCY"],$r["MCZ"]);
		$this->Answer[]=$r["Answer"];
	}
	public function addByQID($qid){
		global $database;
		if($qid!=strval(intval($qid)))throw new Exception("Invalid QID $qid.");
		$row=$database->query_assoc("SELECT QID, isB, Subject, isSA, Question, MCW, MCX, MCY, MCZ, Answer FROM questions WHERE QID = %0% AND Deleted=FALSE LIMIT 1",array(intval($qid)));
		if(count($row)==0)throw new Exception("Invalid QID $qid.");
		
		$this->QID[]=$row["QID"];
		$this->isB[]=$row["isB"];
		$this->Subject[]=$row["Subject"];
		$this->isSA[]=$row["isSA"];
		$this->Question[]=$row["Question"];
		$this->MCChoices[]=array($row["MCW"],$row["MCX"],$row["MCY"],$row["MCZ"]);
		$this->Answer[]=$row["Answer"];
	}
	public function addByArray($paramsArray){//Add to the array of questions, each from array or ID.
		global $ruleSet;
		global $database;
		
		if(is_null($paramsArray))throw new Exception("No parameters");
		elseif(!is_array($paramsArray))throw new Exception("Invalid input params");
		
		foreach($paramsArray as $n=>$params){
			if(!is_array($params))throw new Exception("Bad parameters");//Given all the needed parameters in an array.
			$n+=count($this->QID);//"Temporary" fix. Ugly.
			$this->isB[$n]=$params["isB"]==1?1:0;
		
			$this->Subject[$n]=intval($params["Subject"]);
			if($this->Subject[$n]===false||$this->Subject[$n]>4||$this->Subject[$n]<0)throw new Exception("Invalid subject");
			
			$this->isSA[$n]=(bool)$params["isSA"];
			$this->Question[$n]=$params["Question"];
			$this->Answer[$n]=$params["Answer"];
			$this->MCChoices[$n]=$params["MCChoices"];
			
			//Validity checking
			global $DEFAULT_QUESTION_TEXT,$DEFAULT_ANSWER_TEXT;
			if(!($this->isSA[$n]===true||$this->isSA[$n]===false))throw new Exception("Invalid question-type");
			if($this->Question[$n]==""||$this->Question[$n]==$DEFAULT_QUESTION_TEXT
				||$this->Answer[$n]==""||$this->Answer[$n]==$DEFAULT_ANSWER_TEXT)
				throw new Exception("Blank question/answer");//handle js-side too
			
			//Deal with MC
			if(!$this->isSA[$n]){
				if(anyIndicesEmpty($this->MCChoices[$n],0,1,2,3))throw new Exception("Some multiple choice blank");
				if(($this->Answer[$n]=array_search(substr(trim($this->Answer[$n]),0,1),$ruleSet["MCChoices"]))===false)throw new Exception("Invalid answer");
			}
			
			//Hm. Start value for QID.
			$this->QID[$n]=0;
			
			$n-=count($this->QID);
		}
	}
	
	public function commit(){
		global $database;
		$max_query_length=10000;//Estimated maximum query length; the actual is something like 16MB but whatever
		$i=0;//which iteration we're on.
		$q='INSERT INTO questions (isB, Subject, isSA, Question, MCW, MCX, MCY, MCZ, Answer) VALUES ';
		$valarr=array();
		$lengthestimate=count($q);
		$QIDs=array();
		foreach($this->QID as $ind=>$val){
			$lengthestimate+=count($this->Question[$ind])+count(implode($this->MCChoices[$ind]))+count($this->Answer[$ind])+20;
			if($lengthestimate-1>$max_query_length){
				$database->query_assoc(substr($q,0,-1),$valarr);
				for($j=0;$j<$i;$j++)$QIDs[]=$database->insert_id+$j;
				$i=0;
				$q='INSERT INTO questions (isB, Subject, isSA, Question, MCW, MCX, MCY, MCZ, Answer) VALUES ';
				$lengthestimate=count($q);
				$valarr=array();
			}
			
			$how_many_entries=9;
			$textadd="(";for($x=$how_many_entries*$i;$x<$how_many_entries*($i+1);$x++)$textadd.="%$x%,";$textadd=substr($textadd,0,-1).")";
			
			array_push($valarr,$this->isB[$ind],$this->Subject[$ind],$this->isSA[$ind],$this->Question[$ind],
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
	
	public function toHTML($i,$ansWrap1="",$ansWrap2=""){//Return nice HTML.
		global $ruleSet;
		
		//Then just compile together. (--todo--chk xss)
		$return="<div class='question'>";
		$return.="[QID {$this->QID[$i]}]";//Talk about timestampEntered, author, etc?
		$return.="<div style='font-weight:bold;text-align:center;'>{$ruleSet["QParts"][1-(int)$this->isB[$i]]}</div>{$ruleSet["Subjects"][$this->Subject[$i]]} <i>{$ruleSet["QTypes"][(int)$this->isSA[$i]]}</i> ".nl2br(strip_tags($this->Question[$i]))."<br>";
		if(!$this->isSA[$i])for($j=0;$j<4;$j++)$return.="<div style='font-size:0.9em;'>{$ruleSet["MCChoices"][$j]}) {$this->MCChoices[$i][$j]}</div>";
		
		$AnswerText=(!$this->isSA[$i])?$ruleSet["MCChoices"][$this->Answer[$i]].") ".$this->MCChoices[$i][$this->Answer[$i]]//MC
				:strip_tags($this->Answer[$i]);//SA
		$return.=$ansWrap1.$AnswerText.$ansWrap2;
		$return.="</div>";
		return $return;
	}
	public function allToHTML($ansWrap1="",$ansWrap2=""){
		$ret="";
		for($i=0;$i<count($this->QID);$i++)$ret.=$this->toHTML($i,$ansWrap1,$ansWrap2);
		return $ret;
	}
	
	public function getQIDs(){return $this->QID;}
	public function getQID($i){return $this->QID[$i];}
	public function getSubj($i){return $this->Subject[$i];}
	
	public function error(){}//returns error state
	public function count(){return count($this->QID);}
	
	public function markBad($i){//Rate question.
		global $database;
		static $rated=array();
		if(array_key_exists($i,$rated))return;//Being super-careful.
		$database->query_assoc("UPDATE questions SET markBad=MarkBad+1 WHERE QID=%0% LIMIT 1",array($this->QID[$i]));
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