<?php
require_once 'Gehwah.php';
$argv=array('-c','.navbar-inverse');
$gw=new Gehwah();
$gw->InitFromRequests();
print_r(Requests::All());
print_r($gw);
$gw->run();
$gw->SaveToFile('inverse');
?>
