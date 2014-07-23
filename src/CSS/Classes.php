<?php

namespace CSS;


/**
* object to retrieve classes defined in css styling string
*/
class Classes{
	public $css;
	public $classes;
	public function __construct($css){
		$this->css=self::rmComments($css);
	}
	/**
	* static function: remove comments from a css string
	*/
	public static function rmComments($css){
		$cmt_regex="/\\/\\*((?!\\*\\/).)*\\*\\//s";
		$css=preg_replace($cmt_regex,'',$css);
		return $css;
	}
	public function run(){
		return $this->RetrieveClasses();
	}
	public function RetrieveClasses(){
		$this->classes=array();
		$regex="/\\.[a-zA-Z][a-zA-Z0-9_-]*/";
		preg_match_all($regex,$this->css,$matched);
		if(isset($matched[0])){
			foreach($matched[0] as $class){
				if(!in_array($class,$this->classes)){
					$this->classes[]=$class;
				}
			}
		}
		return $this->classes;
	}
}
