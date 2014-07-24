<?php
require_once 'autoloader.php';
$gw=new Gehwah\Gehwah();
$gw->class='.navbar-inverse';
foreach($gw->GetElementsFromBootstrap() as $css_elem) echo $css_elem;
