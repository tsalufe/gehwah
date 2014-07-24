#! /usr/bin/php
<?php

require_once 'autoloader.php';

use Gehwah\Gehwah;
use Requests\Requests;

/**
* run Gehwah to trim bootstrap files to minimal size for classes used in online html file, if a valid url is given
**/
function run(){
	Requests::InitForGehwah();
	$requests=Requests::All();
	if(isset($requests['url'])){
		if(preg_match('/^http[s]{0,1}:\/\//',$requests['url'])){
			$html=file_get_contents($requests['url']);
			$hc=new HTML\Classes($html);
			$classes=$hc->run();
			$with=array();
			if(isset($requests['with'])){
				$with=preg_split('/[,;]+/',$requests['with']);
			}
			$classes=array_merge($classes,$with);
			foreach(Gehwah::GetElementsInClasses($classes) as $elem) echo $elem;
		}
	} elseif(isset($requests['class'])){
		$gw=new Gehwah();
		$gw->setSelector($requests['class']);
		foreach($gw->GetElementsFromBootstrap() as $elem) echo $elem;
	}
}

run();
