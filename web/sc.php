<?php

require_once("phpQuery-onefile.php");

$html = file_get_contents("https://www.jma.go.jp/jp/yoho/319.html");

$weather_stat=phpQuery::newDocument($html)->find(".weather:eq(0)")->find("img")->attr("alt");

if(strpos($weather_stat,'☁️') !== false){
  $weather_char="☁️";
}
if(strpos($weather_stat,'晴れ') !== false){
  $weather_char="☀️";
}
if(strpos($weather_stat,'雨') !== false){
  $weather_char="☔️";
}

echo $weather_char.$h=($h+9)%23;
var_dump(getdate());