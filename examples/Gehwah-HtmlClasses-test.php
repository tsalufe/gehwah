<?php
require 'Gehwah.php';
$cl=new HtmlClasses(file_get_contents('loudpicks.com.account.html'));
file_put_contents('a.css',Gehwah::ProcessClasses($cl->run()));
?>
