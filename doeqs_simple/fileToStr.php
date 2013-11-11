<?php


//fileToStr()
//redirects file conversions-to-plaintext to other functions that do the work.
function fileToStr($file){
	$ext=substr($file['name'],strrpos($file['name'],'.')+1);
	switch($ext){
		case "txt": return file_get_contents($file['tmp_name']);
		case "html": case "htm": return strip_tags(str_replace(["<br>","<div>"],"\n",file_get_contents($file['tmp_name'])));//get rid of all html tags, but keep some linebreaks there.
		case "doc":	return docToText($file['tmp_name']);
		case "docx": return docxToText($file['tmp_name']);
		case "odt": return odtToText($file['tmp_name']);
		//case "pdf": return pdfToText($file['tmp_name']);
		//case "csv"://really awk case. Plus not sanitized.
		//$database->query_assoc("LOAD DATA INFILE '%0%' INTO TABLE questions FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\r\n' IGNORE 1 LINES",[$_FILE["file"]["tmp_name"]]);
		//return "";
		
		default:
			echo("Unsupported file extension <i>$ext</i> - we currently support txt, html, doc, docx, odt.");
			return "";
	}
}


//http://stackoverflow.com/questions/5540886/extract-text-from-doc-and-docx
//docToText()
//Turns a .doc Word file into text. Magically.
function docToText($filename) {
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
//Guess what? It converts PDFs to plain text. Duh. Naturally, this one doesn't work either.
function pdfToText($filename){
	require "class.pdf2text.php";//it's HUGE. Magical black box. See file for citations.
	$a = new PDF2Text();
	$a->setFilename($filename);
	$a->decodePDF();
	return $a->output();
}


//DOCX and ODT are variations on zipped XML, but need different methods of adding the linebreaks necessary for the regex to properly read things.
function odtToText($filename) {
    return strip_tags(str_replace("</text:p>","</text:p>\n",readZippedXML($filename, "content.xml")));
}
function docxToText($filename) {
//ALSOOOOO make the regex even better, to detect it _without_ needing linebreaks!
    return strip_tags(str_replace("</w:p>","</w:p>\n",readZippedXML($filename, "word/document.xml")));
}

//The actual zipped-XML function, which works for a number of document formats.
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
           @ $xml = DOMDocument::loadXML($data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
			
            // Return data without XML formatting tags
            return $xml->saveXML();
        }
        $zip->close();
    }

    // In case of failure return empty string
    return "";
}

?>