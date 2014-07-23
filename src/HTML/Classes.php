<?php
namespace HTML;


/**
* process html string to get classes in the string
*/
class Classes{
	/**
	* html string
	*/
	public $html;
	/**
	* classes to be obtained from $this->html
	*/
	public $classes;
	/**
	* construct the class from html string
	*/
	public function __construct($html){
		$this->html=$html;
	}
	/**
	* run $this->RetrieveClasses
	*/
	public function run(){
		return $this->RetrieveClasses();
	}
	/**
	* retrieve classes defined in the html context
	*/
	public function RetrieveClasses(){
		$this->classes=array();
		$regex="/<[^>]*class=['\"]([^'\"]+)['\"][^>]*>/";
		preg_match_all($regex,$this->html,$matched);
		if(isset($matched[1])){
			foreach($matched[1] as $class_str){
				$classes=preg_split('/[ ]+/',$class_str);
				foreach($classes as $class){
					if(!in_array('.'.$class,$this->classes)){
						$this->classes[]='.'.$class;
					}
				}
			}
		}
		return $this->classes;
	}
}
