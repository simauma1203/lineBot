<?php

require_once("phpQuery-onefile.php");

$html = file_get_contents("https://www.jma.go.jp/jp/yoho/319.html");

$res=phpQuery::newDocument($html)->find(".weather");

var_dump($res);