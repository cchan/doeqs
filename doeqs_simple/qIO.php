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
define("DB_DB","doeqs_simple");
require_once "common.php";


//http://stackoverflow.com/questions/5540886/extract-text-from-doc-and-docx
function read_doc($filename) {
    $fileHandle = fopen($filename, "r");
    $line = @fread($fileHandle, filesize($filename));   
    $lines = explode(chr(0x0D),$line);
    $outtext = "";
    foreach($lines as $thisline)
      {
        $pos = strpos($thisline, chr(0x00));
        if (($pos !== FALSE)||(strlen($thisline)==0))
          {
          } else {
            $outtext .= $thisline."\n";
          }
      }
     $outtext = preg_replace("/[^a-zA-Z0-9\s\,\.\-\n\r\t@\/\_\(\)]/","",$outtext);
    return $outtext;
}


//pdf2string with helper ExtractText, extracts string from pdf, from http://php.net/manual/en/ref.pdf.php
function pdf2string ($sourceFile)
  {
    $textArray = array ();
    $objStart = 0;
    
    $fp = fopen ($sourceFile, 'rb');
    $content = fread ($fp, filesize ($sourceFile));
    fclose ($fp);
    
    $searchTagStart = chr(13).chr(10).'stream';
    $searchTagStartLenght = strlen ($searchTagStart);
    
    while ((($objStart = strpos ($content, $searchTagStart, $objStart)) && ($objEnd = strpos ($content, 'endstream', $objStart+1))))
    {
      $data = substr ($content, $objStart + $searchTagStartLenght + 2, $objEnd - ($objStart + $searchTagStartLenght) - 2);
      $data = @gzuncompress ($data);
      
      if ($data !== FALSE && strpos ($data, 'BT') !== FALSE && strpos ($data, 'ET') !== FALSE)
      {
        $textArray [] = ExtractText ($data);
      }
      
      $objStart = $objStart < $objEnd ? $objEnd : $objStart + 1;
    }
    
    return $textArray;
  }
  function ExtractText ($postScriptData)
  {
    while ((($textStart = strpos ($postScriptData, '(', $textStart)) && ($textEnd = strpos ($postScriptData, ')', $textStart + 1)) && substr ($postScriptData, $textEnd - 1) != '\\'))
    {
      $plainText .= substr ($postScriptData, $textStart + 1, $textEnd - $textStart - 1);
      if (substr ($postScriptData, $textEnd + 1, 1) == ']') //this adds quite some additional spaces between the words
      {
        $plainText .= ' ';
      }
      
      $textStart = $textStart < $textEnd ? $textEnd : $textStart + 1;
    }
    
    return stripslashes ($plainText);
  }


  
//DOCX and ODT are variations on zipped XML
function odt2text($filename) {
    return strip_tags(str_replace("<text","\n<text",readZippedXML($filename, "content.xml")));
}
function docx2text($filename) {
//NOOOOOOO this isn't working... need to remove <w:prooferr and other tags (& children) somehow...
//ALSOOOOO make the regex even better, to detect it _without_ needing linebreaks!
    return strip_tags(preg_replace("<w:prooferr","\n",readZippedXML($filename, "word/document.xml")));
}
function readZippedXML($archiveFile, $dataFile) {
    // Create new ZIP archive
    $zip = new ZipArchive;

    // Open received archive file
    if (true === $zip->open($archiveFile)) {
        // If done, search for the data file in the archive
        if (($index = $zip->locateName($dataFile)) !== false) {
            // If found, read it to the string
            $data = $zip->getFromIndex($index);
            // Close archive file
            $zip->close();
            // Load XML from a string
            // Skip errors and warnings
            $xml = DOMDocument::loadXML($data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
			
            // Return data without XML formatting tags
			echo $xml->saveXML();
            return $xml->saveXML();
        }
        $zip->close();
    }

    // In case of failure return empty string
    return "";
}


//fileToStr - redirects file conversions
function fileToStr($file){
	$ext=substr($file['name'],strrpos($file['name'],'.')+1);
	switch($ext){
		case "txt": return file_get_contents($file['tmp_name']);
		case "html": case "htm": return strip_tags(file_get_contents($file['tmp_name']));//get rid of all html tags
		case "doc":	return read_doc($file['tmp_name']);
		//case "docx": return docx2text($file['tmp_name']);
		case "odt": return odt2text($file['tmp_name']);
		//case "pdf": return implode("",pdf2string($file['tmp_name']));
		
		//case "csv"://really awk case. Plus not sanitized.
		//$database->query_assoc("LOAD DATA INFILE '%0%' INTO TABLE questions FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\r\n' IGNORE 1 LINES",[$_FILE["file"]["tmp_name"]]);
		//return "";
		
		default: die("Unsupported file extension <i>$ext</i> - we currently support txt, html, doc, odt.");
	}
}




function qregex(){
//also, mislabeled MC as SA passes in no-linebreaks mode
	$subjChoices='(ENERGY|BIO(?:LOGY)?|CHEM(?:ISTRY)?|PHYS(?:|ICS|ICAL SCIENCE)|MATH(?:EMATICS)?|E(?:SS|ARTHSCI|ARTH SCIENCE|ARTH AND SPACE SCIENCE))';
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


//randomizeArr - Randomly permute an array - yes, it works! in what amounts to O(n)!
function randomizeArr($arr){
	for($i=count($arr)-1;$i>0;$i--){
		$ind=mt_rand(0,$i);//Get the index of the one to swap with.
		$tmp=$arr[$ind];$arr[$ind]=$arr[$i];$arr[$i]=$tmp;//Swap with the last one.
	}
	return $arr;
}




define("DEFAULT_QUESTION_TEXT","Your question here...");
define("DEFAULT_ANSWER_TEXT","Your answer here...");

$ruleSet=array(
	"Subjects"=>array("BIOLOGY","CHEMISTRY","PHYSICS","MATHEMATICS","EARTH AND SPACE SCIENCE"),//'bcpme'
	"QTypes"=>array("Short Answer","Multiple Choice"),
	"QParts"=>array("TOSS-UP","BONUS"),
	"MCChoices"=>array("W","X","Y","Z"),
);

class Questions{//Does all the validation... for you! By not trusting you at all. ;)
	private $QID,$isTU,$Subject,$isMC,$Question,$MCChoices,$Answer,$Rating;
	public function __construct(){
		$this->QID=$this->isTU=$this->Subject=$this->isMC=$this->Question=$this->MCChoices
			=$this->Answer=$this->Rating=array();
		}
	public function add($paramsArray){//Add to the array of questions, each from array or ID.
		global $ruleSet;
		global $database;
		
		$RatingThreshold=-3;
		
		if(is_null($paramsArray)){//Huh. No parameters. -_-
			throw new Exception("Q: No parameters");
		}
		elseif($paramsArray==="rand"||$paramsArray==="randtossup"||$paramsArray==="randbonus"){
			if($paramsArray==="randtossup")$row=$database->query_assoc("SELECT QID, isTU, Subject, isMC, Question, MCW, MCX, MCY, MCZ, Answer, Rating FROM questions WHERE isMC=TRUE AND Rating > %0% AND Deleted=0 ORDER BY RAND() LIMIT 1",[$RatingThreshold]);
			else if($paramsArray==="randbonus")$row=$database->query_assoc("SELECT QID, isTU, Subject, isMC, Question, MCW, MCX, MCY, MCZ, Answer, Rating FROM questions WHERE isMC=FALSE AND Rating > %0% AND Deleted=0 ORDER BY RAND() LIMIT 1",[$RatingThreshold]);
			else $row=$database->query_assoc("SELECT QID, isTU, Subject, isMC, Question, MCW, MCX, MCY, MCZ, Answer, Rating FROM questions WHERE Rating > %0% AND Deleted=0 ORDER BY RAND() LIMIT 1",[$RatingThreshold]);
			
			$this->QID[]=$row["QID"];
			$this->isTU[]=$row["isTU"];
			$this->Subject[]=$row["Subject"];
			$this->isMC[]=$row["isMC"];
			$this->Question[]=$row["Question"];
			$this->MCChoices[]=[$row["MCW"],$row["MCX"],$row["MCY"],$row["MCZ"]];
			$this->Answer[]=$row["Answer"];
			$this->Rating[]=$row["Rating"];
			return;
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
				if(!($this->isMC[$n]===true||$this->isMC[$n]===false))throw new Exception("Invalid question-type");
				if($this->Question[$n]==""||$this->Question[$n]==DEFAULT_QUESTION_TEXT
					||$this->Answer[$n]==""||$this->Answer[$n]==DEFAULT_ANSWER_TEXT)
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
		if(isSet($this->MCChoices[$i]))for($j=0;$j<4;$j++)$return.="{$ruleSet["MCChoices"][$j]}) {$this->MCChoices[$i][$j]}<br>";
		if($plainans)$return.="<br>ANSWER: <b>".strip_tags($this->Answer[$i])."</b><br>";
		else{
			$return.="<br>ANSWER: <span class='hiddenanswer'><span class='ans'>".strip_tags($this->Answer[$i])."</span> <span class='hov'>[hover for answer]</span></span><br>";
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
		
		if(intval($x)>=-2||intval($x)<=2){
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