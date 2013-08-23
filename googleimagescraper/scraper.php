<?php
class NetworkingException extends Exception {}
class Transmitter{

	public function FetchUrl($url){
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$ret['RETURN'] = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);
		$ret['HTTP_CODE'] = $info['http_code'];
		if(!$info['http_code']){
			throw new NetworkingException('Resource ('.$url.') could not be reached.');
		}
		return $ret;
	}

	public function PostXML($url,$Data=array()){
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, true );
		curl_setopt ($ch, CURLOPT_POSTFIELDS, $Data);
		$ret['RETURN'] = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);
		$ret['HTTP_CODE'] = $info['http_code'];
		if(!$info['http_code']){
			throw new NetworkingException('Resource ('.$url.') could not be reached.');
		}
		return $ret;
	}
}


class Scraper{

	public $Transmitter;

	function makeSearchURL($term){
		$baseURL = 'http://www.google.com.hk/search?q=';
		$endURL = '&ie=UTF-8&hl=en&tbm=isch&source=og&sa=N';
		return $baseURL.$term.$endURL;
	}

	function getHTMLForTerm($term){
		$results = $this->Transmitter->FetchURL($this->makeSearchURL($term));
		return $results['RETURN'];
	}

	function parseHTMLForImageURLS($html){
		$regex = '/<img\s+.*\s+src="(http:\/\/.+\.gstatic\.com\/images\?q=tbn:[^"]+)">/U';
		$matches = array();
		preg_match_all($regex, $html,$matches);
		return $matches;
	}

	function pullFirstImage($matchArray){
		$url = $matchArray[1][0];
		$results = $this->Transmitter->FetchURL($url);

		$fh = fopen('../data/images/'.$this->Term.'.jpg','w+');
		fwrite($fh,$results['RETURN']);
		fclose($fh);
	}

	function getImage($term){
		$this->Term = $term;
		$html = $this->getHTMLForTerm($term);
		$matches = $this->parseHtmlForImageURLS($html);
		$this->pullFirstImage($matches);
	}

	public function __construct(){
		$this->Transmitter = new Transmitter();
	}

	public function execute($source){
		$fh = fopen($source,'r');
		while(!feof($fh)){
			$line = fgets($fh);
			$line = trim($line);
			echo $line;

			$this->getImage($line);
			echo "succes!\n";
			sleep(1);
		}
	}
}

$scraper = new Scraper();
$scraper->execute('../artifacts/nouns.txt');
//$scraper->getImage($term);
/*
$fh = fopen($term.'.html','w+');
fwrite($fh,$results['RETURN']);

//$xml = simplexml_load_string($results['RETURN']);
//print_r($xml);
//
//<img height="101" width="135" src="http://t3.gstatic.com/images?q=tbn:ANd9GcRT-VkRYy0wwM_rW0ciifcabl0aXnlsIZj6QVDPjn89Ji_din8-dutb6g">
$regex = '/<img\s+.*\s+src="(http:\/\/(.+)\.gstatic.com\/images\?q=tbn:[^"])">/';
$matchText = print_r($matches,true);
$fh2 = fopen($term.'matches.txt','w+');
fwrite($fh2,$matchText);

 */
