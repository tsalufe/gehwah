<?php
require_once 'Gehwah.php';
$argv=array('class'=>'.navbar-inverse');
$gw=new Gehwah();$gw->InitFromRequests($argv);
$gw->run();
$gw->SaveToFile('inverse');
?>
