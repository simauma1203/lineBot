<?php

require 'TwistOAuth.phar';

$to=new TwistOAuth(
  getenv('twiCK'),//twiCK
  getenv('twiCS'),//twiCS
  getenv('twiAT'),//twiAT
  getenv('twiATS')//twiATS
);

$url=parse_url(getenv('DATABASE_URL'));
$dsn=sprintf('pgsql:host=%s;dbname=%s',$url['host'],substr($url['path'],1));
$pdo=new PDO($dsn,$url['user'],$url['pass']);


$stmt=$pdo->exec($sql);

// foreach文で配列の中身を一行ずつ出力
foreach ($stmt as $row) {
 
    // データベースのフィールド名で出力
    echo $row[''];
}