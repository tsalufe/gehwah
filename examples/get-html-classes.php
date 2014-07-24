<?php
require_once 'autoloader.php';
$hc=new HTML\Classes(file_get_contents('loudpicks.com.account.html'));
print_r($hc->run());
?>
