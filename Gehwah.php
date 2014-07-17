<?php

class Gehwah
{
	public $bsfiles;
	public $bspaths;
	public $regex='';
	public $selector;
	public $extension;

	public $css='';

	public $logs='';
	public $error='';

	public static function usage(){
		$usage_str="	* gehwah -c .[class name] [-e [extension name]]\n	* gehwah -class .[class name] [-ext [extension name]]\n";
		$usage_str.="\n";
		return $usage_str;
	}

	public function __construct(){

		$this->bsfiles=array('bootstrap-theme.min.css','bootstrap.min.css');// bootstrap files, bootstrap.min.css
		$this->bspaths=array();
		
		foreach($this->bsfiles as $bsfile){
			if(file_exists('./'.$bsfile)){
				$this->bspaths[]='./'.$bsfile;
			}elseif(file_exists('./css/'.$bsfile)){
				$this->bspaths[]='./css/'.$bsfile;
			}elseif(file_exists('./public/css'.$bsfile)){
				$this->bspaths[]='./public/css/'.$bsfile;
			}else{
				$this->error="$bsfile cannot be found in ./, ./css/, ./public/css. \nUse http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/ instead.\n\n";
				$this->bspaths[]='http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/'.$bsfile;
			}
		}
		$this->extension='gehwah';
	}

	public function InitFromRequests($options=null){
		if($options==null||!is_array($options)) Requests::InitForGehwah();
		else Requests::Add($options);
		$allR=Requests::All();
		if(!isset($allR['class'])){
			$this->error="\nMissing class name to start.\nUsage:\n".Gehwah::usage();
		}
		$this->SetSelector($allR['class']);
		if(isset($allR['ext'])) $this->extension=$allR['ext'];
	}

	public function SetSelector($selector){
		$this->selector=$selector;
		$this->regex='/[\\.a-z0-9_ >+\\-]*\\'.$this->selector.'([^a-z0-9_\\-\\{][^{]*{|{)[^}]*}/';
	}

	public function run(){
		return $this->GetCssFromBootstrap();
	}
	
	public function GetCssFromBootstrap(){
		foreach($this->bspaths as $i=>$bspath){
			$bootstrap=file_get_contents($bspath);
			preg_match_all($this->regex,$bootstrap,$matched_csses);
			if(count($matched_csses[0])>0){
				$this->logs.="\n\nFound $this->selector in $bspath\n\n";
				$css='';
				foreach($matched_csses[0] as $matched_css){
					$css.=$matched_css."\n";
				}
				$this->css.=$css."\n";
			}
		}
		return $this->css;
	}
	public function SaveToFile($name){
			file_put_contents($name.'.'.$this->extension.".css",$this->css);
	}

	public static function ProcessClasses($classes){
		print_r($classes);
		if(is_string($classes)){
			$classes=preg_split('/[,;]/',$classes);
		}
		if(is_array($classes)){
			$gw=new Gehwah();
			$css='';
			foreach($classes as $class){
				$gw->SetSelector($class);
				$css.=$gw->GetCssFromBootstrap();
			}
			return $css;
		}
		else return '';
	}
}

class HtmlClasses{
	public $html;
	public $classes;
	public function __construct($html){
		$this->html=$html;
	}
	public function run(){
		return $this->RetrieveClasses();
	}
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

class Requests{
	public static $_requests;

	private function __construct(){}
	private function __clone(){}

	public static function All(){
		if(self::$_requests!==null) return Requests::$_requests;
		else {
			self::$_requests=array();
			return self::$_requests;
		}
	}

	public static function Add($array){
		if(self::$_requests==null) self::$_requests=array();
		if(count($array)>0){
			self::$_requests=array_merge(self::$_requests,$array);
		}
	}

	public static function InitForGehwah(){
		global $argc,$argv;
		$allR=$_REQUEST;
		$i=0;
		unset($allR['ext']);
		$argc=count($argv);
		while($i<$argc){
			switch($argv[$i]){
				case '-c':
				case '-class': if(++$i<$argc){
							$allR['class']=$argv[$i];
						} else{
							$allR['class']='';
						}
						break;
				case '-e':
				case '-extension':
				case '-ext': if(++$i<$argc){
							$allR['ext']=$argv[$i];
						} else {
							$allR['ext']='';
						}
						break;
				default:break;
			}
			++$i;
		}
		self::$_requests=$allR;
	}
}
