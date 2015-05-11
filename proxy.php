<?php
error_reporting(E_ALL & ~E_NOTICE);
header('Access-Control-Allow-Headers: X-Requested-With');
include("Snoopy.class.php");


$proxy = new Snoopy();


$proxy->submit("http://quotes.ameriquote.com/cgi-bin/cqsl.cgi",$_POST);
echo $proxy->results;
