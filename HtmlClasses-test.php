<?php
require 'Gehwah.php';
$cl=new HtmlClasses(file_get_contents('loudpicks.com.account.html'));
print_r($cl->run());
?>
