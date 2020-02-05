<?php

$today=getdate();
$h=$today[hours];
$h=($h+9)%23;

//if($h!=5 && $h!=11 && $h!=17 && $h!=23)exit();

//init
require_once("phpQuery-onefile.php");
$my_screen_name="tamaroning";


//--------------------------------------auth init
//twitter init
require 'TwistOAuth.phar';

$to=new TwistOAuth(
  //getenv('twiCK'),//twiCK
  "vyruFEtyPHuGTJqcf9cOd8fzm",
  //getenv('twiCS'),//twiCS
  "PnsDxvEsKeOlC6HLNVckvf0w39sEwTp95kVfJdHdiGrq6xFxBV",
  //getenv('twiAT'),//twiAT
  "843521372437409793-nKWdt11J1RThTouhkGmwGnrPYozQWsm",
  //getenv('twiATS')//twiATS
  "nogg2UYDMnCwJP7lLbaVPxN4swiDVqkuJ2WGHlSTvSqe2"
);
$time_array=["🕛","🕐","🕑","🕒","🕓","🕔","🕕","🕖","🕗","🕘","🕙","🕚"];
$time_char=$time_array[$h%12];

$html = file_get_contents("https://www.jma.go.jp/jp/yoho/319.html");
$weather_stat=phpQuery::newDocument($html)->find(".weather:eq(0)")->find("img")->attr("alt");

$weather_char="";
if(strpos($weather_stat,'曇り') !== false){
  $weather_char="☁️";
}
if(strpos($weather_stat,'晴れ') !== false){
  $weather_char="☀️";
}
if(strpos($weather_stat,'雨') !== false){
  $weather_char="☔️";
}
if(22<=$h || $h<=3){
  $weather_char="🌟";
}

$name=$time_char."まろん".$weather_char."(".$weather_stat.")";

$to->post('account/update_profile', array('name' => $name));

