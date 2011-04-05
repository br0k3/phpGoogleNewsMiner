<?php

class CurlHelper {

	private $ch;
	private $response;
	private $url;
	private $options;
	
	public function __construct($url = '', $options = array()){
		$this->url = $url;
		$this->options = $options;
		$this->response = '';
		$this->ch = null;
	}
	
	public function run($url = ''){
		$this->init();
		$this->setUrl($url);
		$this->setOptions();
		$this->execute();
		$this->close();
	}
	
	public function init(){
		$this->ch = curl_init();
	}
	
	public function setUrl($url = ''){
		$this->url = $url;
		curl_setopt($this->ch, CURLOPT_URL, $this->url);
	}
	
	public function setOptions($options = array(CURLOPT_USERAGENT => 'Firefox (WindowsXP) - Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6', CURLOPT_FAILONERROR => true, CURLOPT_AUTOREFERER => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30)){
		$this->options = $options;
		foreach($this->options as $k => $v){
			curl_setopt($this->ch, $k, $v);
		}
	}
	
	public function execute(){
		if($this->options[CURLOPT_RETURNTRANSFER]){
			$this->response = curl_exec($this->ch);
		} else {
			curl_exec($this->ch);
		}
	}
	
	public function getResponse(){
		return $this->response;
	}
	
	public function close(){
		curl_close($this->ch);
	}
	
}

class FeedItem {

	private $title;
	private $description;
	private $link;
	
	public function __construct($title = '', $description = '', $link = ''){
		$this->description = $description;
		$this->link = $link;
		$this->title = $title;
	}
	
	public function getTitle(){
		return $this->title;
	}
	public function getDescription(){
		return $this->description;
	}
	public function getLink(){
		return $this->link;
	}
	
	public function setTitle($title = ''){
		$this->title = $title;
	}
	
	public function setLink($link = ''){
		$this->link = $link;	
	}
	
	public function setDescription($description = ''){
		$this->description = $description;	
	}
	
	public function readTags(Array $names = array(), $item = ''){
		foreach($names as $name){
			preg_match('/<'.$name.'>(.*)<\/'.$name.'>/Us',$item,$tags);
			$method = 'set'.ucfirst($name);
			$this->$method($tags[1]);
		}
	}

}

class GoogleNewsFeedReader {
	
	private $items;
	
	public function __construct(){
		$this->items = array();
	}
	
	public function getTopics($feed = ''){
		preg_match_all('/<item>(.*)<\/item>/Us',$feed,$matches);
		foreach($matches[1] as $k => $item){
			$this->items[$k] = new FeedItem();
			$this->items[$k]->readTags(array('title','link','description'),$item);
		}
	}
	
	public function getItems(){
		return $this->items;
	}
	
}

// implementation
try{
	$curl = new CurlHelper();
	$curl->run('http://news.google.com/news/section?pz=1&cf=all&ned=us&topic=t&output=rss');
	$response = $curl->getResponse();
	$reader = new GoogleNewsFeedReader();
	$reader->getTopics($response);
	$items = serialize($reader->getItems());
	$fh = fopen('newsData-'.time().'.txt','w');
	fwrite($fh,$items,strlen($items));
	fclose($fh);
	echo('done mining...');
	/*foreach($reader->getItems() as $item){
		preg_match('/url=.+/',$item->getLink(),$matches);
		$url = str_replace('url=','',$matches[0]);
		var_dump($url);
	}*/
} catch(Exception $e) {
	die('Exception caught : ' . $e->getMessage());
}
