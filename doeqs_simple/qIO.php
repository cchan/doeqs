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


//do_post_request, does a post request to the given url, attaching the given file, from http://stackoverflow.com/questions/13785433/php-upload-file-to-another-server-without-curl
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


  
  
function odt2text($filename) {
    return readZippedXML($filename, "content.xml");
}

function docx2text($filename) {
    return readZippedXML($filename, "word/document.xml");
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
            return strip_tags($xml->saveXML());
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
		case "html": return strip_tags(file_get_contents($file['tmp_name']));//get rid of all html tags
		case "doc"://Credit: Seriously abusing the demo of http://www.phpwordlib.motion-bg.com/ [--todo--note filesize limit]
			$xmlstr=do_post_request("http://www.phpwordlib.motion-bg.com/phpwordlib.php", $file);
			return substr($xmlstr,strpos($xmlstr,"<pre>")+5,strpos($xmlstr,"</pre>")-strpos($xmlstr,"<pre>")-5);
		case "docx": return docx2text($file['tmp_name']);
		case "odt": return odt2text($file['tmp_name']);
		case "pdf": return implode("",pdf2string($file['tmp_name']));
		
		default: die("Unsupported file extension <i>$ext</i> - we currently support txt, html, doc, docx, odt, pdf.");
	}
}




function qregex($wantBonus){
	$subjChoices='(?<Subject>ENERGY|BIO(?:LOGY)?|CHEM(?:ISTRY)?|PHYS(?:|ICS|ICAL SCIENCE)|MATH(?:EMATICS)?|E(?:SS|ARTHSCI|ARTH SCIENCE|ARTH AND SPACE SCIENCE))';
	$e='[\.\)\- ]';
	
	$choiceArr=["W","X","Y","Z","ANSWER"];
	$mcChoices='';
	for($i=0;$i<4;$i++)$mcChoices.=$choiceArr[$i].$e.'(?<Choices'.$choiceArr[$i].'>(?:(?!'.$choiceArr[$i+1].$e.')[\s\S])*)\s*';
	return '/(?<Part>[TOSS-UP]+|[BONUS]+)\s*(?:(?<Number>[0-9]+)[\.\)\- ])?\s*'.$subjChoices.'\s*(?:Multiple Choice\s*((?:(?!W'.$e.')[\s\S])*)\s*'.$mcChoices.'|Short Answer\s*((?:(?:(?!ANSWER)[\s\S])*)(?:\s*[IVX0-9]+'.$e.'(?:(?:(?!ANSWER)[\s\S])*))*))\s*ANSWER:*\s*([^\r\n]+)';
}
////--todo--////MAJOR REWORKING: Just changed Qs to be only ONE of TU, B.////--todo--////

function qArrCmp($a, $b){
	
}

//strToQs - high-level question-parsing; accepts string of questions to parse, does whatever with them, and returns string of output.
function strToQs($qstr){
	$out="";
	if($qstr!==NULL){
		preg_match_all(qregex("TOSS-UP"), $qstr, $qt1);
		preg_match_all(qregex("BONUS"), $qstr, $qt2);
		$questiontexts=usort(array_merge($qt1,$qt2),"qArrCmp");
		
		$lastOne=0;
		$bad=array();
		$unparseable=array();
		$duplicates=array();
		$QIDArr=array();
		for($i=0;$i<count($questiontexts[1]);$i++){
			$next=(int)$questiontexts[1][$i];
			if($lastOne>=$next){$bad[]=$lastOne;continue;}
			for($j=$lastOne+1;$j<$next;$j++)$unparseable[]=$j;
			$lastOne=$next;
			try{
				//Indices: full match, 1 number, 2 subject, 3 MC qtext, 4 W, 5 X, 6 Y, 7 Z, 8 SA qtext, 9 last I/II/III/IV choice, 10 answer
				$q=new Question(["Subject"=>strpos('bcpme',strtolower(substr($questiontexts[2][$i],0,1))),
					"QisMC"=>$questiontexts[9][$i]!="",
					"Question"=>$questiontexts[9][$i].$questiontexts[14][$i],
					"Answer"=>$questiontexts[16][$i],
					"MCChoices"=>[$questiontexts[10][$i],$questiontexts[11][$i],$questiontexts[12][$i],$questiontexts[13][$i]],
					"MCa"=>strpos('WXYZ',strtoupper(substr(trim($questiontexts[16][$i]),0,1))),
					]);
				$QIDArr[]=$q->getQID();
			}
			catch(Exception $e){
				if($e->getMessage()=="Duplicate")$duplicates[]=$next;
				else $unparseable[]=$next;
			}
		}
		
		$out.= count($questiontexts[1])." questions successfully parsed.<br><br>Question parsing errors (using our rudimentary missing-question detection mechanism): ";
		if(count($bad)>0)$out.= "[Badly numbered near #s ".arrayToRanges($bad)." (".count($bad)." of them)] ";
		if(count($unparseable)>0)$out.= "[Unparseable/missing #s ".arrayToRanges($unparseable)." (".count($unparseable)." of them)] ";
		if(count($bad)==0&&count($unparseable)==0)$out.= "No errors found.";
		$out.= "<br><span style='font-size:0.7em;'>(Common syntax errors include multi-line question statement, improperly labeled (as MC or SA), missing some necessary components (like multiple choices and an answer), really horrible misspellings.)</span>";
		$out.= "<br><br>Duplicate question #s: ".((count($duplicates)==0)?"none":arrayToRanges($duplicates)." (".count($duplicates)." of them)")."";
		$out.= "<br><br><b>Total uploaded Question-IDs: ".((count($QIDArr)==0)?"no questions entered":arrayToRanges($QIDArr)." (".count($QIDArr)." total entered)")."</b>";
	}
	return $out;
}


//randomizeArr - Randomly permute an array - yes, it works! in O(n)!
function randomizeArr($arr){
	for($i=count($arr)-1;$i>0;$i--){
		$ind=mt_rand(0,$i);//Get the index of the one to swap with.
		$tmp=$arr[$ind];$arr[$ind]=$arr[$i];$arr[$i]=$tmp;//Swap with the last one.
	}
	return $arr;
}




define("DEFAULT_QUESTION_TEXT","Your question here...");
define("DEFAULT_ANSWER_TEXT","Your answer here...");

$ruleSet=array(//--todo--store mc as 0,1,2,3 & other DB updates above [ruleSet obj?]
	"Subjects"=>array("BIOLOGY","CHEMISTRY","PHYSICS","MATHEMATICS","EARTH AND SPACE SCIENCE"),//'bcpme'
	"QTypes"=>array("Short Answer","Multiple Choice"),
	"QParts"=>array("TOSS-UP","BONUS"),
	"MCChoices"=>array("W","X","Y","Z"),
);

class Question{//Does all the validation... for you! By not trusting you at all. ;)
	private $QID;
	private $Subject;
	private $QisMC;
	private $Question;
	private $MCChoices;
	private $MCa;
	private $Answer;
	private $Rating;
	public function __construct($ArrayOrID){//Construct, from array or ID.--todo--Remember always to check whether constructed success!
		//--todo--if just return or die or whatever, timing attacks!
		//--todo--is $ArrayOrID valid?
		global $ruleSet;
		global $database;
		
		if(is_null($ArrayOrID)){//Huh. No parameters. -_-
			throw new Exception("Q: No parameters");
		}
		elseif(is_numeric($ArrayOrID)&&ctype_digit(strval($ArrayOrID))){//Then it's only being provided an id.
			$RatingThreshold=-3;
			if($ArrayOrID===0)
				$row=$database->query_assoc("SELECT QID, Subject, 0isMC, 0Question, 0Answer, 1isMC, 1Question, 1Answer, Rating FROM questions WHERE Rating > %1% ORDER BY RAND() LIMIT 1",$RatingThreshold);
			elseif(elemInSQLReq($ArrayOrID,"QID","questions"))
				$row=$database->query_assoc("SELECT QID, Subject, 0isMC, 0Question, 0Answer, 1isMC, 1Question, 1Answer, Rating FROM questions WHERE QID = \"%1%\" LIMIT 1",$ArrayOrID);
			else
				throw new Exception("question_construct: Invalid QID $ArrayOrID.");
			if(count($row)==0)throw new Exception("Error: no questions in database");
			
			$this->QID=$row["QID"];
			$this->Subject=$row["Subject"];
			foreach($ruleSet["QParts"] as $ind=>$part){
				$this->QisMC[$ind]=$row[$ind."isMC"];
				$this->Question[$ind]=$row[$ind."Question"];
				$this->Answer[$ind]=$row[$ind."Answer"];
				$this->Rating=$row["Rating"];
			}
		}
		elseif(is_array($ArrayOrID)){//Then it's (probably) being given all the needed parameters in an array.
			$this->Subject=$ArrayOrID["Subject"];
			if($this->Subject===false||intval($this->Subject)>4||intval($this->Subject)<0)throw new Exception("Invalid subject");
			
			$queryadd="INSERT INTO questions (Subject";
			$choicearr=array("W","X","Y","Z");
			foreach($ruleSet["QParts"] as $ind=>$part){
				$this->QisMC[$ind]=(bool)$ArrayOrID["QisMC"][$ind];
				$this->Question[$ind]=$ArrayOrID["Question"][$ind];
				$this->Answer[$ind]=$ArrayOrID["Answer"][$ind];
				
				//Validity checking
				if(!($this->QisMC[$ind]===true||$this->QisMC[$ind]===false))throw new Exception("Invalid question-type");
				if($this->Question[$ind]==""||$this->Question[$ind]==DEFAULT_QUESTION_TEXT
					||$this->Answer[$ind]==""||$this->Answer[$ind]==DEFAULT_ANSWER_TEXT)
					throw new Exception("Blank question/answer");//handle js-side too
				//Deal with MC vs SA
				if($this->QisMC[$ind]){//--todo--display questions in a <pre></pre>? b/c \n, since <br> isn't allowed. Frown.
					if(anyIndicesEmpty($ArrayOrID["MCChoices"][$ind],0,1,2,3))throw new Exception("Some multiple choice blank");
					if(array_search($ArrayOrID["MCa"][$ind],[0,1,2,3])===false)throw new Exception("Invalid multiple choice answer");
					
					$this->MCChoices[$ind]=$ArrayOrID["MCChoices"][$ind];
					$this->MCa[$ind]=$ArrayOrID["MCa"][$ind];
				}
				else $this->Answer[$ind]=$ArrayOrID["Answer"][$ind];
				
				//Duplicate checking //--todo-- make more thorough - answers can be same, but question text can't. Can always check for misspell too :P
				//echo "SELECT QID FROM questions WHERE {$ind}Question = \"%1%\"",$this->Question[$ind];
				$result=$database->query_assoc("SELECT CASE WHEN EXISTS(SELECT 1 FROM questions WHERE %1% = \"%2%\") THEN 1 ELSE 0 END AS duplicate",$ind."Question",$this->Question[$ind]);
				if($result["duplicate"])throw new Exception("Duplicate");//telling them...
				
				//--todo-- wait it's not adding MCs and MCas.
				
				//ADD the question to the database.
				$queryadd.=", {$ind}isMC, {$ind}Question, {$ind}Answer";
			}
			$queryadd.=") VALUES (\"%1%\",\"%2%\",\"%3%\",\"%4%\",\"%5%\",\"%6%\",\"%7%\")";
			
			//--todo--really should store mc separate cols
			//--todo--disallow identical choices for mc
			//--todo--js, on switch between mc and sa, it should keep W = SAAnswer
			
			$database->query_assoc($queryadd,$this->Subject,
				$this->QisMC[0],$this->Question[0],$this->Answer[0],//--todo--whoops, manual indexing. There goes that.
				$this->QisMC[1],$this->Question[1],$this->Answer[1]);
			
			//Set new ID.
			$this->QID=$database->insert_id;
		}
		else{
			throw new Exception("Q: Bad parameters");
		}
	}
	public function toHTML($plain=true){//Return nice HTML.
		global $ruleSet;
		//Then just compile together. (--todo--chk xss)
		$return="[QID {$this->QID}, rating {$this->Rating}]";
		foreach($ruleSet["QParts"] as $ind=>$part){
			$return.="<div style='font-weight:bold;text-align:center;'>{$part}</div>{$ruleSet["Subjects"][$this->Subject]} <i>{$ruleSet["QTypes"][(int)$this->QisMC[$ind]]}</i> ".nl2br(strip_tags($this->Question[$ind]))."<br>";
			if(isSet($this->MCChoices[$ind]))for($i=0;$i<4;$i++)$return.="{$ruleSet["MCChoices"][$i]}) {$this->MCChoices[$ind][$i]}<br>";
			if($plain)$return.="<br>ANSWER: <b>".strip_tags($this->Answer[$ind])."</b><br>";
			else{
				$return.="<br>ANSWER: <span class='hiddenanswer'><span class='ans'>".strip_tags($this->Answer[$ind])."</span> <span class='hov'>[hover for answer]</span></span><br>";
				$return.="<style type='text/css'>.hiddenanswer .hov{font-weight:bold;color:#00f;font-size:0.8em;}.hiddenanswer .ans{display:none;font-weight:bold;}.hiddenanswer:hover .ans{display:inline;}</style>";
			}
		}
		return $return;
	}
	public function getQID(){//Get QID.
		return $this->QID;
	}
	public function getSubj(){//Get subject.
		return $this->Subject;
	}
	public function getRating(){//Get rating.
		return $this->Rating;
	}
	public function rate($x){//Rate question.
		global $database;
		static $rated=false;
		if($rated)return;
		if($x!=intval($x))return;
		//Being super-careful.
		
		if(intval($x)>=-2||intval($x)<=2){
			$database->query_assoc("UPDATE questions SET Rating=Rating+%2% WHERE QID=%1% LIMIT 1",$this->QID,intval($x));
			$this->Rating+=intval($x);
		}
		
		$rated=true;
	}
};

?>