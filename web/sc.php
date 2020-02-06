<?php

require_once("phpQuery-onefile.php");

$html = file_get_contents("https://www.jma.go.jp/jp/yoho/319.html");

$weather_stat=phpQuery::newDocument($html)->find(".weather:eq(0)")->find("img")->attr("alt");
$weather_stat=="ああ";
$weather_char="";

if(strpos($weather_stat,'曇り') === 0){
  $weather_char="☁️";
}
if(strpos($weather_stat,'晴れ') ===0){
  $weather_char="☀️";
}
if(strpos($weather_stat,'雨') ===0){
  $weather_char="☔️";
}
echo "aaaaaaaaaaa2";
echo $weather_stat.$weather_char.$h=($h+9)%23;
echo "aa";