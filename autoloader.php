<?php

define('BASE_DIR',__DIR__);

function autoloader($class){
	$path='src/'.preg_replace('/\\\\/','/',$class).'.php';
	require_once BASE_DIR.'/'.$path;
}

spl_autoload_register('autoloader');
