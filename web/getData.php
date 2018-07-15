<?php

$postText = $_POST['text'];

//不正な入力はスルー
if($postText==null){
    exit;
}

$accessToken="v61upqQUN/oE4yiwgij6n9IbIy8PbStfbvan2xrNlgg2OFswMK7XLBLO4rlyjmk30/a3EkNtwVqIcSMOOVgZMQlhlpF6hxuJXG6GugC9s/X008nYQ8s04Z38eb+l3zOaeIaUPWmQCv6ybAjtrIHdVAdB04t89/1O/w1cDnyilFU=";
$channelSecret="dfd80f0736d4a20a2114cc6d4babcd5f";

function push($gId,$message){
    global $accessToken,$channelSecret;  
    $url = 'https://api.line.me/v2/bot/message/push';
    // データの受信(するものないので不要?)
    //$raw = file_get_contents('php://input');
    //$receive = json_decode($raw, true);
    // イベントデータのパース(不要？)
    //$event = $receive['events'][0];
    // ヘッダーの作成
    $headers = ['Content-Type: application/json','Authorization: Bearer '.$accessToken];
    // 送信するメッセージ作成
    $body = json_encode(array('to' => $gId,'messages'=> array($message)));  // 複数送る場合は、array($mesg1,$mesg2) とする。
    // 送り出し用
    $options = [
      CURLOPT_URL => $url,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTPHEADER => $headers,
      CURLOPT_POSTFIELDS => $body
    ];
  $curl = curl_init();
  curl_setopt_array($curl, $options);
  curl_exec($curl);
  curl_close($curl);
  
  }



    $message = array(
      "type" => "text",
      "text" => $postText
    );
    push("C8727e59e0381bc8c6a7fef3f7f8e4cf2",$message);
  

/*
try{
    $pdo=new PDO('mysql:host=us-cdbr-iron-east-04.cleardb.net;dbname=heroku_7f49637117262f3;charset=utf8',
    'b22d7c8e9a3b75', '6a6806d7');
    print("success");
}catch(PDOException $e){
    print("err");
}*/

$url=parse_url(getenv('DATABASE_URL'));
$dsn=sprintf('pgsql:host=%s;dbname=%s',$url['host'],substr($url['path'],1));
$pdo=new PDO($dsn,$url['user'],$url['pass']);






$resultData="contentFromPHP";

//読み込んだデータをjson形式で端末に送信する
header('Content-type: application/json');
print json_encode($resultData);

