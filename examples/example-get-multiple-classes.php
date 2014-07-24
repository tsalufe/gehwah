<?php
require_once 'autoloader.php';
foreach(Gehwah\Gehwah::GetElementsInClasses(array('.navbar','.navbar-inverse')) as $gwe) echo $gwe;

