<?php

function autoloader($class){
	$path='src/'.preg_replace('/\\\\/','/',$class).'.php';
	require_once __DIR__.'/'.$path;
}

spl_autoload_register('autoloader');
