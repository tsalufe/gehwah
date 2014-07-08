#! /usr/bin/php
<?php

echo "\nGehwah is a bootstrap helper.\n Your bootstrap.min.css should be located in one of the following directories and scanned in the following order:\n	* ./\n	* ./css/\n	* ./public/css/\n	* http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/\n\n";
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
		echo "$bsfile cannot be found in ./, ./css/, ./public/css. \nUse http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/ instead.\n\n";
		$bspaths[]='http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/'.$bsfile;
	}
}

$class='.';
$allR=all_request();
if(!isset($allR['class'])){
	die("\nMissing class name to start.\nUsage:\n".usage());
}
$class=$allR['class'];
$regex='/[\\.a-z0-9_ >+\\-]*\\'.$class.'([^a-z0-9_\\-\\{][^{]*{|{)[^}]*}/';
if(!isset($allR['ext'])||strlen($allR['ext'])==0){
	$allR['ext']='.gehwah.';
}
if($allR['ext'][0]!=='.') $allR['ext']='.'.$allR['ext'];
if($allR['ext'][strlen($allR['ext'])-1]!=='.') $allR['ext'].='.';
$extension=$allR['ext'];

foreach($bspaths as $bspath){
	$bootstrap=file_get_contents($bspath);
	preg_match_all($regex,$bootstrap,$matched_csses);
	if(count($matched_csses[0])>0){
		echo "\n\nFound $class in $bspath\n\n";
		$css='';
		foreach($matched_csses[0] as $matched_css){
			$css.=$matched_css."\n";
		}
		echo $css;
		file_put_contents(preg_replace('/\.min\./',$extension,$bspath),$css);
		echo "\n";
	}
}

function all_request(){
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
						echo "\nMissing class name after -c or -class.\n";
					}
					break;
			case '-e':
			case '-extension':
			case '-ext': if(++$i<$argc){
						$allR['ext']=$argv[$i];
					} else {
						echo "\nMissing extension after -e or -ext or -extension.\n";
					}
					break;
			default:break;
		}
		++$i;
	}
	return $allR;
}

function usage(){
	$usage_str="	* gehwah -c .[class name] [-e [extension name]]\n	* gehwah -class .[class name] [-ext [extension name]]\n";
	$usage_str.="\n";
	return $usage_str;
}
