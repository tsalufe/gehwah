<?php

/**
* run Gehwah to trim bootstrap files to minimal size for classes used in online html file, if a valid url is given
**/
function run(){
	Requests::InitForGehwah();
	$requests=Requests::All();
	if(isset($requests['url'])){
		if(preg_match('/^http[s]{0,1}:\/\//',$requests['url'])){
			$html=file_get_contents($requests['url']);
			$hc=new HtmlClasses($html);
			$classes=$hc->run();
			$with=array();
			if(isset($requests['with'])){
				$with=preg_split('/[,;]+/',$requests['with']);
			}
			$classes=array_merge($classes,$with);
			$gw=new Gehwah();
			echo $gw->rmUnusedClasses($classes);
		}
	}
}

run();

/**
* Gehwah is the class to get css styles of a class from bootstrap files
* or
* to trim bootstrap files to minimal size for classes passed to it.
**/
class Gehwah
{
	/**
	* bootstrap file paths
	*/
	public $bspaths;
	public $nomedia_regex='';
	public $media_regex='';
	/**
	* a class starting with '.'
	*/
	public $selector;
	/**
	* file extension for saving results
	*/
	public $extension;

	public $bstags;
	public $bsmedias;
	public $bsclasses;

	/**
	* css results found in bootstrap files
	*/
	public $css='';

	public $logs='';
	public $error='';

	/**
	* construct Gehwah by init paths to bootstrap files
	*/
	public function __construct(){

		$this->bspaths=Bootstrap::GetFiles();
		$this->extension='gehwah';
	}

	/**
	* Init Gehwah parameters from Requests::All() or array
	*/

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

	/**
	* set class selector and init the regular expression of its css styles
	*/

	public function SetSelector($selector){
		$this->selector=$selector;
		$this->regex='/[\\.a-z0-9_ >+\\-]*\\'.$this->selector.'([^a-z0-9_\\-\\{][^{]*{|{)[^}]*}/';
	}

	/**
	* return the regular expression for full class definition starting with ','
	* e.g. ,.row .col-md-12 for .col-md-12
	*/

	public function CommaBeforeRegex($selector){
		return '/,[^,{}]*\\'.$selector.'(?![a-zA-Z0-9_-])[^,{}]*/';
	}

	/**
	* return the regular expression for full class definition ending with ','
	* e.g. .col-md-12 .abc, for .col-md-12
	*/

	public function CommaAfterRegex($selector){
		return '/[^,{}]*\\'.$selector.'(?![a-zA-Z0-9_-])[^,{}]*,/';
	}

	/**
	* return the regular expression for the full css styles of a class
	* e.g. .row .col-md-12{ color:red;}
	*/
	public function CssRegex($selector){
		return '/[\\.a-z0-9_ >+\\-]*\\'.$selector.'(?![a-zA-Z0-9_-])[^{]*{[^}]*}/';
	}

	/**
	* run GetCssFromBootstrap
	*/

	public function run(){
		return $this->GetCssFromBootstrap();
	}

	/**
	* get css styles from bootstrap files for the class $this->selector
	*/
	
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

	/**
	* save $this->css to file with added extension
	*/
	public function SaveToFile($name){
			file_put_contents($name.'.'.$this->extension.".css",$this->css);
	}

	/**
	* remove unused classes in bootstrap files but not in $classes
	*/
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
}

