<?php

namespace Requests;


/**
* object to merge requests made by $_GET,$_POST,$_argv or input array
*/
class Requests{
	private static $_requests;

	private function __construct(){}
	private function __clone(){}

	public static function All(){
		if(self::$_requests!==null) return self::$_requests;
		else {
			self::$_requests=array();
			self::Init();
			return self::$_requests;
		}
	}

	public static function Add($array){
		if(self::$_requests==null) self::$_requests=array();
		if(count($array)>0){
			self::$_requests=array_merge(self::$_requests,$array);
		}
	}


	public static function Init(){
		global $argc,$argv;
		$allR=$_REQUEST;
		$i=0;
		unset($allR['ext']);
		$argc=count($argv);
		while($i<$argc){
			if(substr($argv[$i],0,1)==='-'){
				if($i+1<$argc&&substr($argv[$i+1],0,1)!=='-'){
					$allR[$argv[$i]]=$argv[$i+1];
					$i++;
				} else {
					$allR[$argv[$i]]='';
				}
			}
			$i++;
		}
		self::$_requests=$allR;
	}

	public static function Reset(){
		self::Destroy();
	}

	public static function Destroy(){
		self::$_requests=null;
	}

	public static function InitForGehwah(){
		if(self::$_requests==null) self::Init();
		$allR=& self::$_requests;
		if(isset($allR['-c'])) $allR['class']=$allR['-c'];
		if(isset($allR['-e'])) $allR['ext']=$allR['-e'];
		if(isset($allR['-extension'])) $allR['ext']=$allR['-extension'];
	}
}
