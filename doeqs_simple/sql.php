<?php
//sql.php - database access stuff, with the general class as well as the convenient functions.
//Dependencies: DB_DOMAIN, DB_UNAME, DB_PASSW all defined

//const DB_TABLE_REGEX //regex for valid table name
//const DB_COL_REGEX //regex for valid col name
//function setDB //sets the database to get for DB class. Can be redefined.
//class DB //Database access class, which both makes it securer (replacement + checking) and don't have to specify everything each time opening connection.
	//Construct it to open connection to $db passed in, or defaults to setDB database set.
	//function query_assoc($template,$replace1,$replace2,...) //Submits a query, checking all replaceN for SQL validity and then replacing all occurrences of %N% in $templaet with replaceN. If any of replaceN have strings like %4% in them... just go away. Returns fetch_assoc().
		// ***NOTE you must quotate the template around the %N%'s yourself. Meaning, `` for weird db/table/col names, "" for any string values, no quotes for numbers
		// ***NOTE please no destructive queries :'(
		// ***NOTE all $replaceN are htmlentities()'d then mysqli_real_escape_string()'d
	//Unset it to close connection
//function elemInSQLReq($elem, $col, $table) //Shortcut SQL to return bool on whether, in $table, there's a row with $elem in column $col
//function getSQLRowByID($idval, $col, $table) //Shortcut SQL that returns enumerated and associative array of the values in the row of $table with column $col equal to $idval
	//***NOTE assumes that ID actually is an ID, so if it's more than one row... only the first is returned.

define("DB_TABLE_REGEX","/^[A-Z\_]+$/i");
define("DB_COL_REGEX","/^[A-Z\_]+$/i");

//MUST DEFINE CONSTANT DB_DB. --todo-- what is the function checking whether defined? see below, using is_null which is wrong

function isDestructiveQuery($q){//No DROPs and TRUNCATEs, no DELETEs - just use Deleted flag!
	return false;/////////////////......................
	return(stripos($q,"DROP")!==false
		||stripos($q,"TRUNCATE")!==false
		||stripos($q,"DELETE ")!==false);
}

class DB{
	private $con;
	public $insert_id;
	public function __construct($db=NULL){//All this does is to try to connect to the db, and store that con in $this->con
		if(is_null($db))if(!defined("DB_DB"))throw new Exception("DB: no db specified");else $db=DB_DB;
		$this->con=new MySQLi(DB_DOMAIN,DB_UNAME,DB_PASSW,DB_DB_PREFIX.$db);
		if(!$this->con||$this->con->connect_error)throw new Exception("DB: con to DB ".DB_DB_PREFIX.$db." failed");
	}
	public function __destruct(){
		$this->con->kill($this->con->thread_id);
		$this->con->close();
		unset($this->con);
	}
	public function sanitize($in){
		if($in===""||$in===NULL)return '""';
		//HTMLENTITIES TROUBLESHOOTING
		if($in===true)$in="1";elseif($in===false)$in="0";//Explicit typecasting.
		//Dealing with weird characters
		$search = array(chr(145), //dumb single quotes
							chr(146), //dumb single quotes
							chr(147), //dumb double quotes
							chr(148), //dumb double quotes
							chr(151)); //em dash
		$replace = array("'", 
							 "'", 
							 '"', 
							 '"', 
							 '-');
		$processed=str_replace($search, $replace, $in);
		
		$escaped=$this->con->real_escape_string(htmlentities($processed,ENT_QUOTES|ENT_HTML401,'ISO-8859-1'));
		if($escaped==""){throw new Exception(var_dump($in)."HTMLENTITIES empty for string: ");};
		return '"'.$escaped.'"';
	}
	public function query($template,$replaceArr=array()){//Is this safe??...
		foreach($replaceArr as $ind=>$replace)//Replace all the %% var things
			$template=str_replace("%$ind%",$this->sanitize($replace),$template);//ZERO-INDEXED
		if(isDestructiveQuery($template))throw new Exception("DB: GRUMPYCAT NO - destructive query");
		
		if(($qresult=$this->con->query($template))===false)throw new Exception("DB: query failed: $template");
		$this->insert_id=$this->con->insert_id;
		
		return $qresult;
	}
	public function query_assoc($template,$replaceArr=array()){
		$qresult=$this->query($template,$replaceArr);
		if($qresult===true)return true;//not a data-gathering query
		return $qresult->fetch_assoc();
	}
};
$database=new DB();

/*function elemInSQLReq($elem,$col,$table){//Checks whether a specified element is in the specified column in the specified database.
	global $database;
	
	if(preg_match(DB_COL_REGEX,$col)===0)throw new Exception("eleminsqlreq: invalid col");
	if(preg_match(DB_TABLE_REGEX,$table)===0)throw new Exception("eleminsqlreq: invalid table");
	
	return count($database->query_assoc("SELECT sum(case when %1%=%2% then 1 else 0 end) AS count FROM %0%",[$table,$col,$elem]))>0;
	//Select all from $table where value of $col is $elem. //If there's one or more rows, it is in the column.
}*/
/*Permanently deprecated. Vestigial code for reference. Do NOT restore.
function getSQLRowByID($idval,$col,$table){//Get row by *numeric* ID
	if(!ctype_digit(strval($idval)))throw new Exception("getsqlrowbyid: non-integer id");//--todo-- all $_GET/$_POST variables are STRINGS. Make sure typetesting with ctype_digit.
	if(preg_match(DB_COL_REGEX,$col)===0)throw new Exception("getsqlrowbyid: invalid col");
	if(preg_match(DB_TABLE_REGEX,$table)===0)throw new Exception("getsqlrowbyid: invalid table");
	$con=new DB();
	$qresult=$con->query("SELECT * FROM %1% WHERE %2%=%3%",$table,$col,$idval);
	unset($con);
	if($qresult->num_rows<1)throw new Exception("getsqlrowbyid: no row with that id");
	if($qresult->num_rows>1)throw new Exception("getsqlrowbyid: more than one row with that id");
	if($arr=$qresult->fetch_array())return $arr;//Returns the first (and presumably only) item as enumerated AND associative array. (meaning, size is doubled)
	throw new Exception("getsqlrowbyid: failed fetch array");//(Only if there actually _is_ a row with that id!)
}*///--todo--sorta evil, uses SELECT *
//--todo--isn't this very intensive and slow? Called so many times? (put a dump in the query function)
?>