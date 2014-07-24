<?php

namespace CSS;

use CSS\Element;

class MediaElement{
	public $scope;
	public $elements;
	public function __construct($css_str=null){
		if($css_str&&strlen($css_str)>0){
			$parts=self::splitMedia($css_str);
			$this->scope='';
			$this->elements=array();
			if(count($parts[0])>0){
				$this->setScope($parts[1][0]);
				$this->setElements($parts[2][0]);
			}
		}
	}
	public function setScope($scope){
		$this->scope=$scope;
	}
	public function setElements($css_eles){
		$this->elements=Element::parseAll($css_eles);
		foreach($this->elements as $elem){
			$elem->setMedia($this->scope);
		}
	}
	public function __toString(){
		$style='';
		foreach($this->elements as $elem){
			$style.=$elem;
		}
		return $this->scope.'{'.$style.'}';
	}
	public static function splitMedia($css_str){
		preg_match_all('/(@[^{}]+){(([^{}]+{[^{}]*})*)}/',$css_str,$css_parts);
		return $css_parts;
	}
	public function reduceTo($class){
		$media=new MediaElement();
		$media->setScope($this->scope);
		$media->elements=array();
		foreach($this->elements as $elem){
			$el=$elem->reduceTo($class);
			if($el){
				$el->setMedia($this->scope);
				$media->elements[]=$el;
			}
		}
		if(count($media->elements)>0) return $media;
		else return null;
	}
	public function reduceIn($classes){
		if(is_array($classes)){
			$media=new MediaElement();
			$media->setScope($this->scope);
			$media->elements=array();
			foreach($this->elements as $elem){
				$el=$elem->reduceIn($classes);
				if($el){
					$el->setMedia($this->scope);
					$media->elements[]=$el;
				}
			}
			if(count($media->elements)>0) return $media;
			else return null;
		}
		else return null;
	}
	public static function rmMedia($css_str){
		$media_rm=preg_replace('/(@[^{}]+){(([^{}]+{[^{}]*})*)}/','',$css_str);
		return $media_rm;
	}

	public static function parseAll($css_str){
		$parts=self::splitMedia($css_str);
		$css_eles=array();
		if(count($parts[0])>0){
			foreach($parts[1] as $i=>$scope){
				$css_ele=new MediaElement();
				$css_ele->setScope($scope);
				$css_ele->setElements($parts[2][$i]);
				$css_eles[]=$css_ele;
			}
		}
		return $css_eles;
	}
}
