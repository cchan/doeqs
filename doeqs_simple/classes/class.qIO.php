<?php
//qIO.php
	//class qIO

//DOCUMENTATION OF DATABASE
/*
--todo--
*/


class qIO{//Does all the validation... for you! By not trusting you at all. ;)
	//private $QID,$isB,$Subject,$isSA,$Question,$MCChoices,$Answer;
	private $Questions;
	public function __construct(){
		//$this->QID=$this->isB=$this->Subject=$this->isSA=$this->Question=$this->MCChoices=$this->Answer=array();
		global $database;
		if(!isSet($database))$database=new DB;
	}
	public function __destruct(){
		if(!is_null($this->Questions))foreach($this->Questions as $q)if($q[0]==0)throw new Exception("Uncommitted added questions.");
	}
	public function addRand($parts,$subjects,$types,$num){//arrays of the numbers to include eg subj [0,1,4] for b,c,e
		global $database, $MARK_AS_BAD_THRESHOLD, $ruleSet, $RANDQ_MAX_QUESTIONS_AT_ONCE;
		
		if(!is_numeric($num)||!($num=intval($num)))$num=$DEFAULT_NUMQS;
		if($num<1)$num=1;if($num>$RANDQ_MAX_QUESTIONS_AT_ONCE)$num=$RANDQ_MAX_QUESTIONS_AT_ONCE;
		
		$query="SELECT QID, isB, Subject, isSA, Question, MCW, MCX, MCY, MCZ, Answer FROM questions WHERE MarkBad < $MARK_AS_BAD_THRESHOLD AND Deleted=0";
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
		
		if($result->num_rows==0)return "No such questions exist.";
		
		$i=count($this->Questions);
		while($row=$result->fetch_assoc()){
			$this->Questions[]=array($row["QID"],$row["isB"],$row["Subject"],$row["isSA"],
				$row["Question"],$row["MCW"],$row["MCX"],$row["MCY"],$row["MCZ"],$row["Answer"]);
		}
		$this->updateIs(range($i,count($this->Questions)-1),"TimesViewed=TimesViewed+1");
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
			$this->Questions[]=array($row["QID"],$row["isB"],$row["Subject"],$row["isSA"],
				$row["Question"],$row["MCW"],$row["MCX"],$row["MCY"],$row["MCZ"],$row["Answer"]);
		}
	}
	public function addByArray($paramsArray){//Add to the array of questions, each from array or ID.
		global $ruleSet;
		global $database;
		global $DEFAULT_QUESTION_TEXT,$DEFAULT_ANSWER_TEXT;
		
		if(is_null($paramsArray))throw new Exception("No parameters");
		elseif(!is_array($paramsArray))throw new Exception("Invalid input params");
		
		foreach($paramsArray as $n=>$params){
			if(!is_array($params))error("Wrong parameter type.");//Given all the needed parameters in an array.
			
			$required=["isB","Subject","isSA","Question"];
			//$types=[[0,1],range(0,count($ruleSet["Subjects"])),[0,1],"","","","","","",""];
			if(count(array_intersect_key(array_flip($required),$params))!=count($required))error("Missing parameters.");
			
			//Check the validity of these.
			//Handle JS-side too...
			$params["isB"]=($params["isB"]==1)?1:0;
			if(($params["Subject"]=intval($params["Subject"]))===false||!array_key_exists($params["Subject"],$ruleSet["Subjects"]))error("Invalid subject");
			$params["isSA"]=($params["isSA"]==1)?1:0;
			if($params["Question"]=="")error("Blank question");
			
			//Deal with MC vs SA answers
			if(!$params["isSA"]){
				$required=["MCW","MCX","MCY","MCZ","MCa"];
				if(count(array_intersect_key(array_flip($required),$params))!=count($required))error("Missing parameters.");
				for($i=0;$i<count($ruleSet["MCChoices"]);$i++)
					if(empty($params["MC".$ruleSet["MCChoices"][$i]]))
						error("Some multiple choice blank");
				if(!(is_int($params["MCa"])&&array_key_exists($params["MCa"],$ruleSet["MCChoices"])))
					error("Invalid MC answer chosen");
				$params["Answer"]=$params["MCa"];
			}else{
				if(!array_key_exists("Answer",$params))error("Missing parameters.");
				if($params["Answer"]=="")error("Blank answer");
				$params["MCW"]=$params["MCX"]=$params["MCY"]=$params["MCZ"]=NULL;
			}
			
			var_dump($params);
			//Hm. Start value for QID = 0.
			$this->Questions[]=array(0,$params["isB"],$params["Subject"],$params["isSA"],
				$params["Question"],$params["MCW"],$params["MCX"],$params["MCY"],$params["MCZ"],
				$params["Answer"]);
		}
	}
	
	public function commit(){
		if(empty($this->Questions)||count($this->Questions)==0)return;
		global $database,$ruleSet;
		$q='INSERT INTO questions (isB, Subject, isSA, Question, MCW, MCX, MCY, MCZ, Answer) VALUES ';//Set up query string. The max query length is something like 16MB so no probs there
		$valarr=array();//array of values to be submitted to and sanitized by $database->query
		foreach($this->Questions as $qarr){
			//isB,Subject,isSA,Question,MCW,MCX,MCY,MCZ,Answer
			if($qarr[0]!=0)continue;//only commit non-committed new ones.
			array_shift($qarr);//get rid of QID
			$q.='(%'.implode('%,%',range(count($valarr),count($valarr)+count($qarr)-1)).'%),';//add the (%1%,%2%,%3%,...),(...),...
			$valarr=array_merge($valarr,$qarr);//put it into the values array
		}
		if(count($valarr)==0)error('No questions.');//if there weren't any questions to commit
		
		$database->query_assoc(substr($q,0,-1),$valarr);//Query the db with all necessary stuff, stripping off the last comma
		for($i=0;$i<count($this->Questions);$i++)//For every question, get its inserted id
			$this->Questions[$i][0]=$database->insert_id+$i;//Set QIDs; adding $i because it only returns the first insert_id, and it's almost certainly consecutive
		
		//:( duplicates
		//http://stackoverflow.com/questions/18932/how-can-i-remove-duplicate-rows no idea what it does
		//$database->query_assoc("UPDATE questions SET Deleted=1 WHERE QID NOT IN (SELECT MIN(QID) FROM questions WHERE Deleted=0 GROUP BY Question)");
		//--todo--so how to do this for our php copy of the questions?
	}
	
	public function toHTML($i,$formatstr){//Return nice HTML for question $i, based on $formatstr replacements.
		if(empty($this->Questions)||count($this->Questions)==0)return "";
		global $ruleSet;
		$MCOptions='';
		//QID,isB,Subject,isSA,Question,MCW,MCX,MCY,MCZ,Answer
		if(!$this->Questions[$i][3])
			foreach($ruleSet["MCChoices"] as $ind=>$letter)
				$MCOptions.='<div>'.$letter.") ".$this->Questions[$i][5+$ind].'</div>';
		return str_replace(
			array(
				'%N%',
				'%QID%',
				'%PART%',
				'%SUBJECT%',
				'%TYPE%',
				'%QUESTION%',
				'%MCOPTIONS%',
				'%ANSWER%'
			),
			array(
				$i,
				$this->Questions[$i][0],
				$ruleSet['QParts'][intval($this->Questions[$i][1])],
				$ruleSet['Subjects'][intval($this->Questions[$i][2])],
				$ruleSet['QTypes'][intval($this->Questions[$i][3])],
				nl2br(strip_tags($this->Questions[$i][4])),
				$MCOptions,
				($this->Questions[$i][3])?
					strip_tags($this->Questions[$i][9])//short answer, just there
					:$ruleSet['MCChoices'][$this->Questions[$i][9]].') '.$this->Questions[$i][5+$this->Questions[$i][9]]//mc, it's 0-3 of WXYZ
			),
			$formatstr);
		
		//--todo--test xss
	}
	public function allToHTML($formatstr){//Return nice HTML
		if(empty($this->Questions)||count($this->Questions)==0)return "";
		$ret='';
		for($i=0;$i<count($this->Questions);$i++)$ret.=$this->toHTML($i,$formatstr);
		return $ret;
	}
	
	//returns QIDs of the questions
	public function getQIDs(){if(empty($this->Questions)||count($this->Questions)==0)return array();return array_map(array($this,"getQID"),range(0,count($this->Questions)-1));}
	public function getQID($i){if(empty($this->Questions)||count($this->Questions)==0)return 0;return $this->Questions[$i][0];}
	
	//public function error(){}//returns error state
	public function count(){if(empty($this->Questions))return 0;return count($this->Questions);}
	
	public function markBad($i=-1){//Rate question $i. Default rate all.
		global $database;
		static $rated=array();
		
		if($i===-1){//Default action: update ALL.
			$range=range(0,count($this->Questions)-1);
			$this->updateIs(array_diff($range,$rated),'MarkBad=MarkBad+1');
			$rated=$range;
			return;
		}
		
		if(array_key_exists($i,$rated))return;//Don't re-rate it.
		$database->query_assoc('UPDATE questions SET markBad=MarkBad+1 WHERE QID=%0% LIMIT 1',array($this->Questions[$i][0]));
		$rated[$i]=true;
	}
	private function updateQIDs($qids,$setstr){
		if(empty($this->Questions)||count($this->Questions)==0)return;
		//$setstr is risky.
		global $database;
		$wherestr='';
		foreach($qids as $qid)
			$wherestr.=" QID=$qid OR ";
		$wherestr=substr($wherestr,0,-3);
		$query="UPDATE questions SET $setstr WHERE ($wherestr) LIMIT ".count($this->Questions);
		$database->query_assoc($query);
	}
	private function updateIs($is,$setstr){
		if(empty($this->Questions)||count($this->Questions)==0)return;
		//$setstr is risky.
		global $database;
		$wherestr="";
		foreach($is as $i)
			$wherestr.=" QID=".$this->Questions[$i][0]." OR ";
		$wherestr=substr($wherestr,0,-3);
		$query="UPDATE questions SET $setstr WHERE ($wherestr) LIMIT ".count($this->Questions);
		$database->query_assoc($query);
	}
};
function getExportSize(){
	$a=$database->query_assoc("SELECT COUNT(*) FROM questions WHERE Deleted=0");
	echo "Estimated size: ".($a[0]/5)."KB";//about 5000 chars estimated per question. Eh.
}
function exportQuestionsCSV(){
	$database->query_assoc("SELECT * INTO OUTFILE 'questionsExport.csv' FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' LINES TERMINATED BY '\r\n' FROM questions WHERE Deleted=0");
}

?>