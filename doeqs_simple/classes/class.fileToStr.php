<?php
//fileToStr()
//redirects file conversions-to-plaintext to other functions that do the work.
class fileToStr{
	public function __construct(){}
	public function convert($file){//The actual $_FILE array, not just the file path or anything.
		$ext=substr($file['name'],strrpos($file['name'],'.')+1);
		switch($ext){
			case "txt": return file_get_contents($file['tmp_name']);
			case "html": case "htm": return strip_tags(str_replace(array("<br>","<div>"),"\n",file_get_contents($file['tmp_name'])));//get rid of all html tags, but keep some linebreaks there.
			case "doc":	return $this->docToText($file['tmp_name']);
			case "docx": return $this->docxToText($file['tmp_name']);
			case "odt": return $this->odtToText($file['tmp_name']);
			//case "pdf": return $this->pdfToText($file['tmp_name']);
			//case "csv"://really awk case. Plus not sanitized. D:
			//$database->query_assoc("LOAD DATA INFILE '%0%' INTO TABLE questions FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\r\n' IGNORE 1 LINES",($_FILE["file"]["tmp_name"]));
			
			default:
				return "Unsupported file extension <i>$ext</i> - we currently support txt, html, doc, docx, odt.";
		}
	}
	
	//http://stackoverflow.com/questions/5540886/extract-text-from-doc-and-docx
	//docToText()
	//Turns a .doc Word file into text. Magically.
	private function docToText($filename) {
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
	//It just (doesn't) work! Magically, it turns pdf into string.
	//Redacted, since it didn't work.
	//pdfToText()
	//Naturally, this one doesn't work either.
	private function pdfToText($filename){
		require "class.pdf2text.php";//it's HUGE. Magical black box. See file for citations.
		$a = new PDF2Text();
		$a->setFilename($filename);
		$a->decodePDF();
		return $a->output();
	}


	//DOCX and ODT are variations on zipped XML, but need different methods of adding the linebreaks necessary for the regex to properly read things.
	private function odtToText($filename) {
		return strip_tags(str_replace("</text:p>","</text:p>\n",$this->readZippedXML($filename, "content.xml")));
	}
	private function docxToText($filename) {
	//ALSOOOOO make the regex even better, to detect it _without_ needing linebreaks!
		return strip_tags(str_replace("</w:p>","</w:p>\n",$this->readZippedXML($filename, "word/document.xml")));
	}

	//The actual zipped-XML function, which works for a number of document formats.
	private function readZippedXML($archiveFile, $dataFile) {
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
				$doc = new DOMDocument();
				$doc->loadXML($data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
				
				// Return data without XML formatting tags
				return $doc->saveXML();
			}
			$zip->close();
		}

		// In case of failure return empty string
		return "";
	}

}

?>