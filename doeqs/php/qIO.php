<?php
//qIO.php
	//class Question
	//class QuestionSet
	//function getQuestionSetNames()
//DOCUMENTATION OF DATABASE
/*
	TABLE assignments (possibly deprecated?)
		
	TABLE doeqs --todo--implement the below table
		QID INT NOT NULL AUTO_INCREMENT UNIQUE PRIMARY KEY
		Subject TINYINT(1)//number from 1 to 5 => Bio, Chem, Phys, ESS, Math //make it extensible
		TUisMC TINYINT(1)//boolean
		TUQuestion TEXT
		TUAnswer TEXT
		BisMC TINYINT(1)//boolean
		BQuestion TEXT
		BAnswer TEXT
		TimestampEntered DEFAULT CURRENT_TIMESTAMP
	TABLE bugreports
		Bug TEXT
		System TEXT
		HowReproduce TEXT
		Email TEXT
		Timestamp DEFAULT CURRENT_TIMESTAMP
		[make sure not necessarily "NOT NULL"? Or not needed?]
	TABLE reqlog
		
	TABLE users
		Username
		Password
		PermissionsLevel
*/
//Credit for function: http://stackoverflow.com/questions/13785433/php-upload-file-to-another-server-without-curl
function do_post_request($url, $file){ 
	$data = ""; 
	$boundary = "---------------------".substr(md5(rand(0,32000)), 0, 10); 

	$data .= "--$boundary\n"; 

	//Collect Filedata 
	$data .= "Content-Disposition: form-data; name=\"filename\"; filename=\"{$file['name']}\"\n"; 
	$data .= "Content-Type: image/jpeg\n"; 
	$data .= "Content-Transfer-Encoding: binary\n\n"; 
	$data .= file_get_contents($file['tmp_name'])."\n"; 
	$data .= "--$boundary--\n"; 

	$params = array('http' => array( 
		   'method' => 'POST', 
		   'header' => 'Content-Type: multipart/form-data; boundary='.$boundary, 
		   'content' => $data 
		)); 

   $ctx = stream_context_create($params); 
   $fp = fopen($url, 'rb', false, $ctx); 

   if (!$fp) { 
	  throw new Exception("Problem with $url, $php_errormsg"); 
   } 

   $response = @stream_get_contents($fp); 
   if ($response === false) { 
	  throw new Exception("Problem reading data from $url, $php_errormsg"); 
   } 
   return $response; 
} 

function fileToStr($file){
	$ext=pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
	switch($ext){
		case "doc"://Credit: Seriously abusing the demo of http://www.phpwordlib.motion-bg.com/ [--todo--note filesize limit]
			$xmlstr=do_post_request("http://www.phpwordlib.motion-bg.com/phpwordlib.php", $_FILES['file']);
			return substr($xmlstr,strpos($xmlstr,"<pre>")+5,strpos($xmlstr,"</pre>")-strpos($xmlstr,"<pre>")-5);
			
		case "docx":
		
		case "pdf":
		
		case "html":
		
		default: return false;
	}
}


function strToQArr($qstr){//Accepts string and returns array of Question objects
	if($rawquestionstring!==NULL){
		preg_match_all('/(TOSS-UP|BONUS)\n+([^\n]+)\n+ANSWER:([^\n]+)/', file_get_contents("sampleQText.txt"),$questiontexts);
		var_dump($questiontexts);
		//whatever-just-get-it-done-already regex: /(TOSS-UP|BONUS)\n+[^\n]+\n+ANSWER[^\n]+/i
		//super-generous, so far: /((\A|\n)[a-z\- ]+\n((?!(\s[a-z]+\s?[:=\)]|[ANSWER]+[^\n]{0,4}))[\s\S])+(\s[a-z]+\s?[:=\)]|[ANSWER]+[^\n]{0,4})((?!(\n[a-z\- ]+\n|\n{4,}|\Z))[\s\S])+)/i
		//super-particular, so far: (TOSS-UP|BONUS)\s+([0-9]{1,3}[\)\.\:]\s*)?\s*(BIO(LOGY)?|CHEM(ISTRY)?|PHYSIC(S|AL SCIENCE)|MATH(EMATICS)?|EARTH(SCI| SCIENCE| AND SPACE SCIENCE)) +(Multiple Choice((?!(\s*W[\)\.\:]))[\s\S])+\s*W[\)\.\:][^\n]+\s*X[\)\.\:][^\n]+\s*Y[\)\.\:][^\n]+\s*Z[\)\.\:]((?!(ANSWER:))[\s\S])+|Short Answer((?!(ANSWER:))[\s\S])+)ANSWER:((?!(TOSS(-UP|UP| UP)|BONUS))[\s\S])+\s*(?=(TOSS(-UP|UP| UP)|BONUS|\n{3}))
		//Probs: SA 1-2-3-4/I-II-III-IV, multi-line questions
		//$Questions=QStrParse(/*stuff*/);//adding to set?
		//MUST be a function that takes the text of a SINGLE question (either TU or B) and parses it. Then apply.
		//foreach($Questions as $Question)echo $Question->toHTML();
	}
}


function randomizeArr($arr){//Randomly permute - yes, it works! in O(n)!
	for($i=count($arr)-1;$i>0;$i--){
		$ind=mt_rand(0,$i);//Get the index of the one to swap with.
		$tmp=$arr[$ind];$arr[$ind]=$arr[$i];$arr[$i]=$tmp;//Swap with the last one.
	}
	return $arr;
}


define("DEFAULT_QUESTION_TEXT","Your question here...");
define("DEFAULT_ANSWER_TEXT","Your answer here...");

$ruleSet=array(//--todo--store mc as 0,1,2,3 & other DB updates above [ruleSet obj?]
	"Subjects"=>array("BIOLOGY","CHEMISTRY","PHYSICS","EARTH SCIENCE","MATHEMATICS"),
	"QTypes"=>array("Short Answer","Multiple Choice"),
	"QParts"=>array("TOSS-UP","BONUS"),
	"MCChoices"=>array("W","X","Y","Z"),
);//oooh ughhhh what if transforming from one ruleSet to another? transformByRuleSet($q,$rs1,$rs2)

//--todo--Implement way to assign team captains, and have them submit a SetID for the question set. ["submitted" database with team-date-qset containing all submitted in past times to prevent doubles]
class Question{//Does all the validation... for you! By not trusting you at all. ;)
	private $Subject;
	private $QType;private $Question;private $Answer;
	private $QID;
	public function __construct($ArrayOrID){//Construct, from array or ID.--todo--Remember always to check whether constructed success!
		//--todo--if just return or die or whatever, timing attacks!
		//--todo--is $ArrayOrID valid?
		global $ruleSet;
		if(is_null($ArrayOrID)){//huh?? you just constructed it with no params??
			throw new Exception("Q: No parameters");
		}
		elseif(ctype_digit($ArrayOrID)){//Then it's only being provided an id.
			if(elemInSQLReq($ArrayOrID,"QID","questions")){
				$con=new DB();
				$qresult=$con->query("SELECT Subject, TUQType, TUQuestion, TUAnswer, BQType, BQuestion, BAnswer FROM questions WHERE QID = %1%",$ArrayOrID);
				$row=$qresult->fetch_assoc();
				
				$this->QID=$ArrayOrID;
				$this->Subject=$row["Subject"];
				foreach(array("TU","B") as $abbr){
					$this->QType[$abbr]=$row[$abbr."QType"];
					$this->Question[$abbr]=$row[$abbr."Question"];
					$this->Answer[$abbr]=$row[$abbr."Answer"];
				}
			}
			else{
				throw new Exception("question_construct: Invalid QID $ArrayOrID.");
			}
		}
		else{//Then it's being given all the needed parameters in an array.
			$this->Subject=$ArrayOrID["Subject"];
			if(intval($this->Subject)>4||intval($this->Subject)<0)throw new Exception("Invalid subject");
			
			$con=new DB();
			$queryadd="INSERT INTO questions (Name, Subject";
			$choicearr=array("W","X","Y","Z");
			foreach(array("TU","B") as $abbr){
				$this->QType[$abbr]=$ArrayOrID[$abbr."QType"];
				echo $this->QType[$abbr];
				$this->Question[$abbr]=$ArrayOrID[$abbr."Question"];
				
				if(!$this->QType[$abbr]=="mc"||$this->QType[$abbr]=="sa")throw new Exception("Invalid question-type");
				
				if($this->Question[$abbr]==""||$this->Question[$abbr]==DEFAULT_QUESTION_TEXT
					||(($this->Answer[$abbr]==""||$this->Answer[$abbr]==DEFAULT_ANSWER_TEXT)&&$ArrayOrID[$abbr."QType"]=="sa"))
					throw new Exception("Blank question/answer");//handle js-side too
					//--todo--maybe send back error *codes* for JS to process, instead of just doing it here
				
				//Deal with MC vs SA
				if($this->QType[$abbr]=="mc"){//--todo--display questions in a <pre></pre>? b/c \n, since <br> isn't allowed. Frown.
					if(anyIndicesEmpty($ArrayOrID["TUMC"],0,1,2,3))throw new Exception("Some multiple choice blank");
					for($i=0;$i<4;$i++)$this->Question[$abbr].="\n{$choicearr[$i]}) ".$ArrayOrID[$abbr."MC"][$i];
					$this->Answer[$abbr]=$choicearr[$ArrayOrID[$abbr."MCa"]].") ".$ArrayOrID["TUMC"][$ArrayOrID[$abbr."MCa"]];
				}
				else $this->Answer[$abbr]=$ArrayOrID[$abbr."Answer"];
				
				//ADD the question to the database.
				$queryadd.=", {$abbr}QType, {$abbr}Question, {$abbr}Answer";
				
				//CHECK NO DUPLICATES //--todo-- make more thorough - answers can be same, but question text can't. Can always check for misspell too :P
				//$result=$con->query("SELECT QID FROM questions WHERE {$abbr}Question = \"%1%\"",$this->Question[$abbr]);
				//if($result->num_rows>0)throw new Exception("Duplicate");//telling them...
				//$result->free();
			}
			$queryadd.=") VALUES (\"%1%\",\"%2%\",\"%3%\",\"%4%\",\"%5%\",\"%6%\",\"%7%\",\"%8%\")";
			
			//--todo--really should store mc separate cols
			//--todo--disallow identical choices for mc
			//--todo--js, on switch between mc and sa, it should keep W = SAAnswer
			
			
			if(!$con->query($queryadd,getSessionName(),$this->Subject,
				$this->QType["TU"],$this->Question["TU"],$this->Answer["TU"],//--todo--whoops, manual indexing.
				$this->QType["B"],$this->Question["B"],$this->Answer["B"]))throw new Exception("Query failed, unknown why");
			
			//Set new ID.
			$this->QID=$con->insert_id;
			unset($con);
		}
	}
	public function toHTML($plain=true){//Return nice HTML.
		//Then just compile together. (--todo--chk xss)
		$return="[qid: {$this->QID}]";
		$qpartarr=array("TU"=>"TOSS-UP","B"=>"BONUS");//do away with this entirely
		$qtypearr=array("mc"=>"Multiple Choice","sa"=>"Short Answer");
		foreach($qpartarr as $abbr=>$full){
			$return.="<div style='font-weight:bold;text-align:center;'>{$full}</div>"
				.strtoupper($this->Subject)." <i>{$qtypearr[$this->QType[$abbr]]}</i> {$this->Question[$abbr]}<br>";
			if($plain)$return.="ANSWER: <b>".$this->Answer[$abbr]."</b><br>";
			else $return.="ANSWER: <span class='hiddenanswer'><span>".$this->Answer[$abbr]."</span> <a href='#'></a></span><br>";
		}
		return $return;
	}
	public function getSubj(){//Get subject.
		return $this->Subject;
	}
	public function getQID(){//Get QID.
		return $this->QID;
	}
};
class QuestionSet{//Question sets can only be named with [a-zA-Z0-9_\-] //escape _?
	//who-has-access entry in table
	//--todo--set WhatHas (how many of each subj)
	private $SID;
	private $Name;
	private $QIDArr;
	public function __construct($id=NULL){
		if(is_null($id)){//Not given an id, then it's a new set.
			$this->QIDArr=array();
			return;
		}
		elseif(elemInSQLReq($id,"SID","questionsets")){//Given an id, then a particular set is being requested.
			$con=new DB();
			$QIDs=$con->query("
			SELECT QID
			FROM questionsetdata
			WHERE SID = \"%1%\"
			",$id);
			$name=$con->query("
			SELECT Name
			FROM questionsets
			WHERE SID=\"%1%\"
			",$id);
			unset($con);
			
			$this->SID=$id;
			
			$tmp=$name->fetch_row();//should be able to do it directly in php >=5.4
			$this->Name=$tmp[0];
			
			//hmm something sql-side? pivot?
			$this->QIDArr=array();
			while($row=$QIDs->fetch_row())$this->QIDArr[]=$row[0];
		}
		else{//Whoops. Someone did something stupid. Like invalid id or something.
			throw new Exception("QSET: invalid sid");
		}
	}
	//public function changeName($newName){}
	public function addByQID($QID){//Adds QID to qset. Returns true on success, false on failure.
		$this->QIDArr[]=$QID;
		$this->sanityCheck();
		$this->QIDArr=array_values(array_unique($this->QIDArr));
		
		$con=new DB();
		if(!$con->query("
		INSERT INTO questionsetdata
		(SID, QID)
		VALUES
		(\"%1%\",\"%2%\")
		",$this->SID,$QID))return false;
		return true;
	}
	public function delByQID($QID){//Deletes QID from qset. Returns true on success, false on failure, NULL when QID isn't even in the set in the first place
		$this->QIDArr[]=$QID;
		$this->sanityCheck();
		
		if(($key=array_search($QID,$this->QIDArr))!==false)unset($this->QIDArr[$key]);
		else return NULL;
		$this->QIDArr=array_values(array_unique($this->QIDArr));
		
		$con=new DB();
		if(!$con->query("
		DELETE FROM questionsetdata
		WHERE SID=\"%1%\"
		AND QID=\"%2%\"
		LIMIT 1
		",$this->SID,$QID))return false;
		return true;
	}
	public function toHTMLDoc(){//Parse a set into a full html doc
		//validation of team?
		$permuted=randomizeArr($this->QIDArr);
		$return="<html><head></head><body>";
		$return.="<center><h2>".($this->Name)."</h2></center>";
		foreach($permuted as $QID){
			$Question=new Question($QID);
			$return.="<div>".$Question->toHTML()."</div>";
		}
		$return.="</body></html>";
		return $return;
	}
	public function getName(){
		return $this->Name;
	}
	public function getSubjCounts(){
		$Bio=0;$Chem=0;$Phys=0;$ESS=0;$Math=0;
		foreach($this->QIDArr as $QID){
			$Question=new Question($QID);
			switch($Question->getSubj()){
				case "Biology":$Bio++;break;
				case "Chemistry":$Chem++;break;
				case "Physics":$Phys++;break;
				case "EarthSci":$ESS++;break;
				case "Math":$Math++;break;
				default:throw new Exception("QSET: getSubjCounts invalid subj");
			}
		}
		return array("Biology"=>$Bio,"Chemistry"=>$Chem,"Physics"=>$Phys,"EarthSci"=>$ESS,"Math"=>$Math);
	}
	public function sanityCheck(){
		foreach($this->QIDArr as $QID)
			if(!ctype_digit(strval($QID))||!ctype_digit(strval($this->SID))||!ctype_alnum(strval($this->Name)))throw new Exception("QSET: insanity");
	}
};
function getQuestionSetNames(){//Get the names of all question sets. [--todo--param limit, to return only the limit most recently updated sets]
	$con=new DB();
	$qresult=$con->query("SELECT SID, Name FROM questionsets");
	$arr=array();
	while($row=$qresult->fetch_assoc())
		$arr[$row["SID"]]=$row["Name"];
	return $arr;
}
?>