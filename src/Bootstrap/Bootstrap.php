<?php

namespace Bootstrap;

use CSS\Classes;
use CSS\Element;
use CSS\MediaElement;

/**
B
* object to locate bootstrap files and retrieve the classes from the files
*/
class Bootstrap{
	private static $_files;
	private static $_classes;
	private static $_elements;
	private function __construct(){}
	private function __clone(){}

	private static function Init(){
		if(self::$_files==null){
	                $bsfiles=array('bootstrap-theme.min.css','bootstrap.min.css');// bootstrap files, bootstrap.min.css
	                $bspaths=array();
	
	                foreach($bsfiles as $bsfile){
	                        if(file_exists('./'.$bsfile)){
	                                $bspaths[]='./'.$bsfile;
	                        }elseif(file_exists('./css/'.$bsfile)){
	                                $bspaths[]='./css/'.$bsfile;
	                        }elseif(file_exists('./public/css'.$bsfile)){
	                                $bspaths[]='./public/css/'.$bsfile;
	                        }else{
	                                $error="$bsfile cannot be found in ./, ./css/, ./public/css. \nUse http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/ instead.\n\n";
	                                $bspaths[]='http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/'.$bsfile;
	                        }
	                }
			self::$_files=$bspaths;
		}
	}
	public static function GetFiles(){
		self::Init();
		return self::$_files;
	}
	public static function GetClasses(){
		if(self::$_classes!==null) return self::$_classes;
		else {
			self::Init();
			foreach(self::$_files as $file){
				$cc=new Classes(file_get_contents($file));
				self::$_classes[]=$cc->run();
			}
			self::rmNonclasses();
			return self::$_classes;
		}
	}
	public static function rmNonclasses($exts=null){
		$extensions=array('.eot','.woff','.ttf','.svg','.Microsoft','.gradient');
		if(is_array($exts)){
			$extensions=array_merge($extensions,$exts);
		}
		foreach($extensions as $ext){
			for($i=0;$i<count(self::$_classes);$i++){
				if(false!==($key=array_search($ext,self::$_classes[$i]))){
					unset(self::$_classes[$i][$key]);
				}
			}
		}
		return self::$_classes;
	}

	public static function GetElements(){
		if(self::$_elements!==null) return self::$_elements;
		else{
			self::Init();
			self::$_elements=array();
			foreach(self::$_files as $file){
				$css=file_get_contents($file);
				$css=\CSS\Classes::rmComments($css);
				$medias=MediaElement::parseAll($css);
				$css=MediaElement::rmMedia($css);
				$eles=Element::parseAll($css);
				self::$_elements=array_merge(self::$_elements,$eles,$medias);
			}
			return self::$_elements;
		}
	}
}
