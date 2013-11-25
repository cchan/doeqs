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
	public function addRand($parts,$subjects,$types,$num){//arrays of the numbers to include eg subj [0,1,4] for b,c,e
		global $database, $MARK_AS_BAD_THRESHOLD, $ruleSet, $RANDQ_MAX_QUESTIONS_AT_ONCE;
		
		if(!is_numeric($num)||!($num=intval($num)))$num=$DEFAULT_NUMQS;
		if($num<1)$num=1;
		if($num>$RANDQ_MAX_QUESTIONS_AT_ONCE)$num=$RANDQ_MAX_QUESTIONS_AT_ONCE;
		
		$query="SELECT QID FROM questions WHERE MarkBad < $MARK_AS_BAD_THRESHOLD AND Deleted=0";
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
		
		//NOTE that TimesViewed is despite categories, and if you have something like 2 10 10 10, you'll get the 2 at least 8 times in a row.
			//The assumption that there is a large pool for _each_ possible classification (2*5*2=20 of them) eliminates this problem.
		$query.=" ORDER BY TimesViewed ASC, RAND() LIMIT $num";//Order by TimesViewed, and then randomize within each TimesViewed value.
		$result=$database->query($query);
		$QIDs=array();
		while($r=$result->fetch_assoc())$QIDs[]=$r["QID"];
		
		if($result->num_rows==0)return "No such questions exist.";
		$this->addByQID($QIDs);
		if($result->num_rows!=$num)return "More questions requested than such questions exist.";
	}
	public function addByQID($qids){
		global $database;
		
		$query="SELECT QID, isB, Subject, isSA, Question, MCW, MCX, MCY, MCZ, Answer FROM questions WHERE (";
		foreach($qids as $i=>$qid){
			if(!(is_numeric($qid)&&$qid==intval($qid)&&intval($qid)>0))throw new Exception("Invalid QID $qid.");
			$qid=intval($qid);
			$query.=" QID=".intval($qid)." OR ";
		}
		$query.=" 0) AND Deleted=0 LIMIT ".count($qids);
		
		$this->updateQIDs($qids,"TimesViewed=TimesViewed+1");
		
		$result=$database->query($query);
		if($result->num_rows<count($qids))throw new Exception("QIDs do not exist.");
		
		while($row=$result->fetch_assoc()){
			$this->QID[]=$row["QID"];
			$this->isB[]=$row["isB"];
			$this->Subject[]=$row["Subject"];
			$this->isSA[]=$row["isSA"];
			$this->Question[]=$row["Question"];
			$this->MCChoices[]=array($row["MCW"],$row["MCX"],$row["MCY"],$row["MCZ"]);
			$this->Answer[]=$row["Answer"];
		}
	}
	public function addByArray($paramsArray){//Add to the array of questions, each from array or ID.
		global $ruleSet;
		global $database;
		global $DEFAULT_QUESTION_TEXT,$DEFAULT_ANSWER_TEXT;
		
		if(is_null($paramsArray))throw new Exception("No parameters");
		elseif(!is_array($paramsArray))throw new Exception("Invalid input params");
		
		$offsetN=count($this->QID);//"Temporary" fix. Ugly. Offsets the $n=0...howevermany-1 to account for $n stuff. This is so in case there's problems with entering Q, won't mess up alignment of arrays.
		foreach($paramsArray as $n=>$params){
			if(!is_array($params))continue;//Given all the needed parameters in an array.
			$n+=$offsetN;
			$this->isB[$n]=$params["isB"]==1?1:0;
		
			$this->Subject[$n]=intval($params["Subject"]);
			if($this->Subject[$n]===false||!array_key_exists($this->Subject[$n],$ruleSet["Subjects"]))throw new Exception("Invalid subject");
			
			$this->isSA[$n]=(bool)intval($params["isSA"]);
			if(!($this->isSA[$n]===true||$this->isSA[$n]===false))throw new Exception("Invalid question-type");
			
			
			//Deal with MC vs SA answers
			$this->Answer[$n]="";
			$this->MCChoices[$n]=array();
			if(!$this->isSA[$n]){
				$this->MCChoices[$n]=$params["MCChoices"];
				for($i=0;$i<count($ruleSet["MCChoices"]);$i++)
					if(!array_key_exists($i,$this->MCChoices[$n])||empty($this->MCChoices[$n][$i]))
						throw new Exception("Some multiple choice blank");
				if(!array_key_exists("MCa",$params))throw new Exception("No MC answer chosen");
				$this->Answer[$n]=intval($params["MCa"]);
				if(!(is_int($this->Answer[$n])&&array_key_exists($this->Answer[$n],$ruleSet["MCChoices"])))
					throw new Exception("Invalid MC answer chosen");
			}
			else {
				$this->Answer[$n]=$params["Answer"];
				if($this->Answer[$n]==""||$this->Answer[$n]==$DEFAULT_ANSWER_TEXT)throw new Exception("Blank answer");
			}
			
			//Validity checking
			$this->Question[$n]=$params["Question"];
			if($this->Question[$n]==""||$this->Question[$n]==$DEFAULT_QUESTION_TEXT)throw new Exception("Blank question");//handle js-side too
			
			//Hm. Start value for QID.
			$this->QID[$n]=0;
		}
	}
	
	public function commit(){
	//foreach($this as $ind=>$var)echo $ind." ".count($var)."<br>";die();
		global $database,$ruleSet;
		$max_query_length=10000;//Estimated maximum query length; the actual is something like 16MB but whatever
		$i=0;//which iteration we're on.
		$q='INSERT INTO questions (isB, Subject, isSA, Question, MCW, MCX, MCY, MCZ, Answer) VALUES ';
		$valarr=array();
		$lengthestimate=count($q);
		$QIDs=array();
		foreach($this->QID as $ind=>$val){
			if($val!=0)continue;
			$lengthestimate+=count($this->Question[$ind])+count(implode($this->MCChoices[$ind]))+count($this->Answer[$ind])+10;
			if($lengthestimate>$max_query_length){
				$database->query_assoc(substr($q,0,-1),$valarr);
				for($j=0;$j<$i;$j++)$QIDs[]=$database->insert_id+$j;
				$i=0;
				$q='INSERT INTO questions (isB, Subject, isSA, Question, MCW, MCX, MCY, MCZ, Answer) VALUES ';
				$lengthestimate=count($q);
				$valarr=array();
			}
			
			$how_many_entries=9;
			$textadd="(";for($x=$how_many_entries*$i;$x<$how_many_entries*($i+1);$x++)$textadd.="%$x%,";$textadd=substr($textadd,0,-1).")";
			
			foreach($ruleSet["MCChoices"] as $x=>$v)
				if(!array_key_exists($x,$this->MCChoices[$ind]))
					$this->MCChoices[$ind][$x]=0;
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
		//$database->query_assoc("UPDATE questions SET Deleted=1 WHERE QID NOT IN (SELECT MIN(QID) FROM questions WHERE Deleted=0 GROUP BY Question)");
		//--todo--so how to do this for our php copy of the questions?
	}
	
	public function toHTML($i,$formatstr){//Return nice HTML for question $i, based on $formatstr replacements.
		global $ruleSet;
		$MCOptions='';
		if(!$this->isSA[$i])
			foreach($ruleSet["MCChoices"] as $n=>$letter)
			$MCOptions.='<div>'.$letter.") ".$this->MCChoices[$i][$n].'</div>';
		return str_replace(
			array(
				"%N%",
				"%QID%",
				"%PART%",
				"%SUBJECT%",
				"%TYPE%",
				"%QUESTION%",
				"%MCOPTIONS%",
				"%ANSWER%"
			),
			array(
				$i,
				$this->QID[$i],
				$ruleSet["QParts"][1-intval($this->isB[$i])],
				$ruleSet["Subjects"][intval($this->Subject[$i])],
				$ruleSet["QTypes"][intval($this->isSA[$i])],
				nl2br(strip_tags($this->Question[$i])),
				$MCOptions,
				($this->isSA[$i])?strip_tags($this->Answer[$i]):$ruleSet["MCChoices"][$this->Answer[$i]].") ".$this->MCChoices[$i][$this->Answer[$i]]
			),
			$formatstr);
		
		//--todo--test xss
	}
	public function allToHTML($formatstr){//Return nice HTML
		$ret="";
		for($i=0;$i<count($this->QID);$i++)$ret.=$this->toHTML($i,$formatstr);
		return $ret;
	}
	
	public function getQIDs(){return $this->QID;}
	public function getQID($i){return $this->QID[$i];}
	
	public function error(){}//returns error state
	public function count(){return count($this->QID);}
	
	public function markBad($i=-1){//Rate question $i. Default rate all.
		global $database;
		static $rated=array();
		
		if($i===-1){//Default action: update ALL.
			$range=range(0,count($this->QID)-1);
			$this->updateIs(array_diff($range,$rated),"MarkBad=MarkBad+1");
			$rated=$range;
			return;
		}
		
		if(array_key_exists($i,$rated))return;//Being super-careful.
			$database->query_assoc("UPDATE questions SET markBad=MarkBad+1 WHERE QID=%0% LIMIT 1",array($this->QID[$i]));
		$rated[$i]=true;
	}
	private function updateQIDs($qids,$setstr){
		//$setstr is risky.
		global $database;
		$wherestr="";
		foreach($qids as $qid)
			$wherestr.=" QID=".$qid." OR ";
		$query="UPDATE questions SET ".$setstr." WHERE (".substr($wherestr,0,-3).") LIMIT ".count($this->QID);
		$database->query_assoc($query);
	}
	private function updateIs($is,$setstr){
		//$setstr is risky.
		global $database;
		$wherestr="";
		foreach($is as $i)
			$wherestr.=" QID=".$this->QID[$i]." OR ";
		$query="UPDATE questions SET ".$setstr." WHERE (".substr($wherestr,0,-3).") LIMIT ".count($this->QID);
		$database->query_assoc($query);
	}
};
function getExportSize(){
	$a=$database->query_assoc("SELECT COUNT(*) FROM questions WHERE Deleted=0");
	echo "Estimated size: ".($a[0]/5)."KB";
}
function exportQuestionsCSV(){
	$database->query_assoc("SELECT * INTO OUTFILE 'questionsExport.csv' FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' LINES TERMINATED BY '\r\n' FROM questions WHERE Deleted=0");
}

?>