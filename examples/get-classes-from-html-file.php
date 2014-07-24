<?php
require_once 'autoloader.php';
$hc=new HTML\Classes(file_get_contents('loudpicks.com.account.html'));
$classes=$hc->run();
foreach(Gehwah\Gehwah::GetElementsInClasses($classes) as $elem) echo $elem;
?>
