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

	public function __construct($options=null){

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
		$this->selector='.';
		if($options==null||!is_array($options)) Requests::InitForGehwah();
		else Requests::Add($options);
		$allR=Requests::All();
		if(!isset($allR['class'])){
			$this->error="\nMissing class name to start.\nUsage:\n".Gehwah::usage();
		}
		$this->selector=$allR['class'];
		$this->regex='/[\\.a-z0-9_ >+\\-]*\\'.$this->selector.'([^a-z0-9_\\-\\{][^{]*{|{)[^}]*}/';
		if(!isset($allR['ext'])||strlen($allR['ext'])==0){
			$allR['ext']='gehwah';
		}
		$this->extension=$allR['ext'];
	}

	public function run(){
		$this->GetCssFromBootstrap();
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
	}
	public function SaveToFile($name){
			file_put_contents($name.'.'.$this->extension.".css",$this->css);
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
