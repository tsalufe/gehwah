<?php

function run(){
	Requests::InitForGehwah();
	$requests=Requests::All();
	if(isset($requests['url'])){
		if(preg_match('/^http[s]{0,1}:\/\//',$requests['url'])){
			$html=file_get_contents($requests['url']);
			$hc=new HtmlClasses($html);
			$gw=new Gehwah();
			echo $gw->rmUnusedClasses($hc->run());
		}
	}
}

run();

class Gehwah
{
	public $bspaths;
	public $nomedia_regex='';
	public $media_regex='';
	public $selector;
	public $extension;

	public $bstags;
	public $bsmedias;
	public $bsclasses;

	public $css='';

	public $logs='';
	public $error='';

	public function __construct(){

		$this->bspaths=Bootstrap::GetFiles();
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

	public function CommaBeforeRegex($selector){
		return '/,[^,{}]*\\'.$selector.'(?![a-zA-Z0-9_-])[^,{}]*/';
	}

	public function CommaAfterRegex($selector){
		return '/[^,{}]*\\'.$selector.'(?![a-zA-Z0-9_-])[^,{}]*,/';
	}

	public function CssRegex($selector){
		return '/[\\.a-z0-9_ >+\\-]*\\'.$selector.'(?![a-zA-Z0-9_-])[^{]*{[^}]*}/';
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

	public function rmUnusedClasses($classes){
		if(is_string($classes)){
			$classes=preg_split('/[,; ]+/',$classes);
		}
		if(is_array($classes)&&count($classes)>0){
			foreach(Bootstrap::GetClasses() as $i=>$bsclasses){
				$bsfile=CssClasses::rmComments(file_get_contents($this->bspaths[$i]));
				foreach($bsclasses as $bsclass){
					if(!in_array($bsclass,$classes)){
						//echo "removing $bsclass\n";
						$bsfile=preg_replace($this->CommaBeforeRegex($bsclass),'',$bsfile);
						$bsfile=preg_replace($this->CommaAfterRegex($bsclass),'',$bsfile);
						$bsfile=preg_replace($this->CssRegex($bsclass),'',$bsfile);
					}
				}
				$this->css.=$bsfile;
			}
			$this->css=preg_replace("/@media[^{]*{}/",'',$this->css);
		}
		return $this->css;
	}

	public static function ProcessClasses($classes){
		if(is_string($classes)){
			$classes=preg_split('/[,; ]+/',$classes);
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
	public static function usage(){
		$usage_str="	* gehwah -c .[class name] [-e [extension name]]\n	* gehwah -class .[class name] [-ext [extension name]]\n";
		$usage_str.="\n";
		return $usage_str;
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

class CssClasses{
	public $css;
	public $classes;
	public function __construct($css){
		$this->css=self::rmComments($css);
	}
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

class CssTags{
	public $css;
	public $tags;
	public function __construct($css){
		$this->css=self::rmComments($css);
	}
	public static function rmComments($css){
		$cmt_regex="/\\/\\*((?!\\*\\/).)*\\*\\//s";
		$css=preg_replace($cmt_regex,'',$css);
		return $css;
	}
	public function run(){
		return $this->RetrieveClasses();
	}
	public function RetrieveClasses(){
		$this->tags=array();
		$regex="/\\.[a-zA-Z][a-zA-Z0-9_-]*/";
		preg_match_all($regex,$this->css,$matched);
		if(isset($matched[0])){
			foreach($matched[0] as $class){
				if(!in_array($class,$this->tags)){
					$this->tags[]=$class;
				}
			}
		}
		return $this->tags;
	}
}
class Bootstrap{
	private static $_files;
	private static $_classes;
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
				$cc=new CssClasses(file_get_contents($file));
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
}

class Requests{
	private static $_requests;

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
				case '-url': if(++$i<$argc){
							$allR['url']=$argv[$i];
						} else{
							$allR['url']='';
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
