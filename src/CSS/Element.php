<?php

namespace CSS;

class Element{
	public $media;
	public $scope;
	public $style;
	public function __construct($css_str=null){
		if($css_str&&strlen($css_str)>0){
			$parts=self::splitCss($css_str);
			$this->scope='';
			$this->style='';
			$this->media='';
			if(count($parts[0])>0){
				$this->setScope($parts[1][0]);
				$this->setStyle($parts[2][0]);
			}
		}
	}
	public function setScope($scope){
		$this->scope=$scope;
	}
	public function setStyle($style){
		$this->style=$style;
	}
	public function setMedia($media){
		$this->media=$media;
	}
	public function __toString(){
		return $this->scope.'{'.$this->style.'}';
	}
	public static function splitCss($css_str){
		preg_match_all('/([^{]+){([^}]*)}/',$css_str,$css_parts);
		return $css_parts;
	}
	public function reduceTo($class){
		$scopes=explode(',',$this->scope);
		foreach($scopes as $i=>$scope){
			if(!preg_match('/\\'.$class.'(?![a-zA-Z0-9_-])/',$scope)){
				unset($scopes[$i]);
			}
		}
		if(count($scopes)>0){
			$ele=new Element();
			$ele->setScope(implode(',',$scopes));
			$ele->setStyle($this->style);
			return $ele;
		} else return null;
	}
	public function reduceIn($classes){
		$scopes=explode(',',$this->scope);
		foreach($scopes as $i=>$scope){
			$match_one=false;
			foreach($classes as $class){
				if(preg_match('/\\'.$class.'(?![a-zA-Z0-9_-])/',$scope)){
					$match_one=true;
					break;
				}
			}
			if(!$match_one) {
				unset($scopes[$i]);
			}
		}
		if(count($scopes)>0){
			$ele=new Element();
			$ele->setScope(implode(',',$scopes));
			$ele->setStyle($this->style);
			return $ele;
		} else return null;
	}

	public static function rmElement($css_str){
		$ele_rm=preg_replace('/([^{]+){([^}]*)}/','',$css_str);
		return $ele_rm;
	}

	public static function parseAll($css_str){
		$parts=self::splitCss($css_str);
		$css_eles=array();
		if(count($parts[0])>0){
			foreach($parts[1] as $i=>$scope){
				$css_ele=new Element();
				$css_ele->setScope($scope);
				$css_ele->setStyle($parts[2][$i]);
				$css_eles[]=$css_ele;
			}
		}
		return $css_eles;
	}
}
