<?php

$postText = $_POST['text'];

//不正な入力はスルー
if($postText==null){
    //exit;
}

$accessToken = 'v61upqQUN/oE4yiwgij6n9IbIy8PbStfbvan2xrNlgg2OFswMK7XLBLO4rlyjmk30/a3EkNtwVqIcSMOOVgZMQlhlpF6hxuJXG6GugC9s/X008nYQ8s04Z38eb+l3zOaeIaUPWmQCv6ybAjtrIHdVAdB04t89/1O/w1cDnyilFU=';
$channelSecret='dfd80f0736d4a20a2114cc6d4babcd5f';//lineCS

$url=parse_url(getenv('DATABASE_URL'));
$dsn=sprintf('pgsql:host=%s;dbname=%s',$url['host'],substr($url['path'],1));
$pdo=new PDO($dsn,$url['user'],$url['pass']);


$groupid="C8727e59e0381bc8c6a7fef3f7f8e4cf2";
function push($gId,$message){
    global $accessToken,$channelSecret;  
    $url = 'https://api.line.me/v2/bot/message/push';
    // データの受信(するものないので不要?)
    $raw = file_get_contents('php://input');
    $receive = json_decode($raw, true);
    // イベントデータのパース(不要？)
    $event = $receive['events'][0];
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

$maxCnt=5;
$cnt=0;

$sql="SELECT * FROM score ORDER BY score DESC;";
$stmt=$pdo->query($sql);//実行


//読み込んだデータをjson形式で端末に送信する
header('Content-type: application/json;');
//print("{");
$arrArr[]=[];
while($row = $stmt -> fetch(PDO::FETCH_ASSOC)) {
    //$tr = $row["uname"];
    //print($row);
    //print($tr.PHP_EOL);
    //print(json_encode($row));
    $arrArr[$cnt]=$row;
    $cnt++;
    if($cnt==$maxCnt){
        break;
    }else{
        //print(",");
    }
    
}
//print("}");
print(json_encode($arrArr));
//print("e");
