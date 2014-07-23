<?php

namespace HTML;


/**
* object to obtain all tags defined in an html context
*/
class HtmlTags{
	public $html;
	public $tags;
	public function __construct($html){
		$this->html=$html;
	}
	public function run(){
		return $this->RetrieveClasses();
	}
	public function RetrieveClasses(){
		$this->tags=array();
		$regex="/<([a-zA-Z]+)[^>]*>/";
		preg_match_all($regex,$this->html,$matched);
		if(isset($matched[1])){
			foreach($matched[1] as $tag){
				if(!in_array($tag,$this->tags)){
					$this->tags[]=$tag;
				}
			}
		}
		return $this->tags;
	}
}
