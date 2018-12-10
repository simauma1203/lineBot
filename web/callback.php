<?php

//--------------------------------------auth init
//twitter init
require 'TwistOAuth.phar';
$to=new TwistOAuth(
  getenv('twiCK'),//twiCK
  getenv('twiCS'),//twiCS
  getenv('twiAT'),//twiAT
  getenv('twiATS')//twiATS
);

$accessToken= getenv('lineAT');
$channelSecret=getenv('lineCS');

//--------------------------------------function

function getProfile($uId){
  global $groupId,$roomId,$accessToken;
  //$id='U51eca766d3d062b3a121756b96f51bff';
  $url = "https://api.line.me/v2/bot/profile/".$uId;
  
  $context = [
        "http" => [
                "method"  => "GET",
                "header"  => "Authorization: Bearer ". $accessToken
        ]
      ];
  $res = file_get_contents($url, false, stream_context_create($context));
  return json_decode($res, true);
}

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


function checkCommand($cmd){
  global $text;
  $slashPos=strpos($text,'/');
  if($slashPos!==FALSE){
    if(strpos($text,$cmd)==$slashPos+1){
      $ret=substr($text,strpos($text,$cmd)+strlen($cmd)+1);
      if($ret!==FALSE){
        return substr($text,strpos($text,$cmd)+strlen($cmd)+1);
      }else{
        return TRUE;
      }
    }else{
      return FALSE;
    }
  }else{
    return FALSE;
  }
}


function getTweet($id,$count){
  global  $to,$response_format_text;

  $mes="";
  
  $parms=array(
    'screen_name'=>$id,
    'count'=>$count
  );
  try{
    $res=$to->get('statuses/user_timeline',$parms);
  }catch(TwistException $e){
    echo $e->getMessage();
  }
  foreach($res as $tweet){
    //echo $tweet->text.PHP_EOL;
    if($tweet->user->screen_name=='tamaroning'){
      continue;
    }
    $head=$tweet->user->name."(".$tweet->user->screen_name.")".PHP_EOL;
    $mes.=$head.$tweet->text.PHP_EOL.PHP_EOL;
  }
  $response_format_text = array(
    "type" => "text",
    "text" => '@'.$id.PHP_EOL.$mes
  );
}

//--------------------------------------get json

//get message from user
$json_string = file_get_contents('php://input');
$jsonObj = json_decode($json_string);

//--------------------------------------variable init 

$events=$jsonObj->{"events"}[0];
$type = $jsonObj->{"events"}[0]->{"message"}->{"type"};
$restype=$jsonObj->{"events"}[0]->{"type"};
//get message
$text = $jsonObj->{"events"}[0]->{"message"}->{"text"};
//get ReplyToken
$replyToken = $jsonObj->{"events"}[0]->{"replyToken"};

//get usrPrf
$userId=$jsonObj->{"events"}[0]->{"source"}->{"userId"};
$groupId=$jsonObj->{"events"}[0]->{"source"}->{"groupId"};
$roomId=$jsonObj->{"events"}[0]->{"source"}->{"roomId"};


//tamaron ID
$myId="Uab87fd116b54a89476fb6cc7b6e265bc";

//asobi
$testgroup="C64e8aab2913e65aca30232060f786557";

$urako="Ce8a68f1b2bdec36ed219036a38936419";

//line get prof
$profile = getProfile($userId);

$help="";


//--------------------------------------command

  if(checkCommand("help")){
    $response_format_text = [
      "type" => "text",
      "text" => $help
    ];
  }
  if(checkCommand("info")){
    $response_format_text = [
      "type" => "text",
      "text" => $info
    ];
  }
  if(checkCommand("time")){
    $response_format_text = [
      "type" => "text",
      "text" => date('m月d日 H:i:s')
    ];
  }
  if(checkCommand("echo")){
    $response_format_text = array(
      "type" => "text",
      "text" => checkCommand("echo")
    );
  }
  if(checkCommand("myid")){
    $response_format_text = [
      "type" => "text",
      "text" => $userId
    ];
  }
  if(checkCommand("groupid")){
    $response_format_text = [
      "type" => "text",
      "text" => $groupId
    ];
  }
  if(checkCommand("roomid")){
    $response_format_text = [
      "type" => "text",
      "text" => $roomId
    ];
  }
  if(checkCommand("myname")){
    $response_format_text = [
      "type" => "text",
      "text" => $profile['displayName']
    ];
  }
  if(checkCommand("mystat")){
    $response_format_text = [
      "type" => "text",
      "text" => $profile['statusMessage']
    ];
  }
  if(checkCommand("mypic")){
    $response_format_text = [
      "type" => "image",
      "originalContentUrl" => $profile['pictureUrl'],
      "previewImageUrl" => $profile['pictureUrl']
    ];
  }
  if(checkCommand("gettweet")){
    getTweet(substr($text,strpos($text,'@')+1),'5');
  }
  if(checkCommand("uname")){
    $uprof=getProfile(checkCommand("uname"));
    $response_format_text = [
      "type" => "text",
      "text" => $uprof['displayName']
    ];
  }
  if(checkCommand("ustat")){
    $uprof=getProfile(checkCommand("ustat"));
    $response_format_text = [
      "type" => "text",
      "text" => $uprof['statusMessage']
    ];
  }
  if(checkCommand("upic")){
    $uprof=getProfile(checkCommand("upic"));
    $response_format_text = [
      "type" => "image",
      "originalContentUrl" => $uprof['pictureUrl'],
      "previewImageUrl" => $uprof['pictureUrl']
    ];
  }
  if(checkCommand("phpstat")){
    $response_format_text =[
      "type" => "text",
      "text" => "DIR:".__DIR__
    ];
  }

  if(checkCommand("tweet")){
    $twitext=checkCommand("tweet");
    $status = $to->post('statuses/update', ['status' => $twitext]);

    $response_format_text =[
      "type" => "text",
      "text" => "tweeted:'".checkCommand("tweet")."'"
    ];
  
  }
  

  if(checkCommand("push")){
    $message = [
      "type" => "text",
      "text" => checkCommand("push")
    ];
    push($urako,$message);
    $message = [
      "type" => "text",
      "text" => $profile['displayName']." pushed ".checkCommand("push")
    ];
    push($testgroup,$message);
  }

  if(checkCommand("test")){
    $message = array(
      "type" => "text",
      "text" => "hi"
    );
    push($groupId,$message);
  }
  


  $url=parse_url(getenv('DATABASE_URL'));
  $dsn=sprintf('pgsql:host=%s;dbname=%s',$url['host'],substr($url['path'],1));
  $pdo=new PDO($dsn,$url['user'],$url['pass']);



  if(checkCommand("sql")){
    $sql=substr($text,strpos($text,'sql')+4);
    $count=$pdo->exec($sql);
    $response_format_text = array(
      "type" => "text",
      "text" => "Command is running".PHP_EOL.'"'.$sql.'"'.PHP_EOL.$count." command have run"
    );
  }

  if(checkCommand("memowrite")){
    $memoText=substr($text,strpos("memowrite",$slashPos)+10);
    $sql="INSERT INTO memo VALUES('".$memoText."');";
    $count=$pdo->exec($sql);
    $response_format_text = array(
      "type" => "text",
      "text" => "メモを追加:".'"'.$memoText.'"'
    );
  }


  if(checkCommand("memoshow")){
    $memoContents="";
    $memoCount=0;
    $sql="SELECT * FROM memo";
    foreach($pdo->query($sql) as $row){
      $memoCount+=1;
      $memoContents=$memoContents.$row['memo'].PHP_EOL;
    }
    if($memoCount==0){
      $memoContents="メモが登録されていません";
    }
    $response_format_text = array(
      "type" => "text",
      "text" => "メモ内容(全".$memoCount."件):".PHP_EOL.$memoContents
    );
  }

  if(checkCommand("memodel")){
    $sql="DELETE FROM memo";
    $count=$pdo->exec($sql);
    $response_format_text = array(
      "type" => "text",
      "text" => "メモ内容を初期化しました"
    );
  }
  if(checkCommand("insert")){
    $arr=[];
    $json=json_encode($arr);
    for($i=0+49;$i<22+49;$i++){
      $sql="insert into history values($i,'$json');";
      $count=$pdo->exec($sql);
    }
    
    $response_format_text = array(
      "type" => "text",
      "text" => "(tabun)success"
    );
  }

  if(checkCommand("sqlstat")){
    $response_format_text = array(
      "type" => "text",
      "text" =>  "version:".$pdo->getAttribute(PDO::ATTR_SERVER_VERSION)
    );
  }

  if(checkCommand("server")){
    $response_format_text = array(
      "type" => "text",
      "text" => "https://".$_SERVER['SERVER_NAME']
    );
  }

  if(checkCommand("dbstat")){
    $response_format_text = array(
      "type" => "text",
      "text" => "DB_URL:".getenv('DATABASE_URL').PHP_EOL.sprintf("pgsql:host=%s".PHP_EOL."dbname=%s",$url['host'],substr($url['path'],1)).PHP_EOL."user:".$url['user'].PHP_EOL."pass:".$url['pass']
    );
  }


//--------------------------------------reply

if(strpos($text,'こんにち')!==FALSE){
  $response_format_text = array(
    "type" => "text",
    "text" => "こんにちわ、".$profile['displayName']."さん"
  );
}


if($text=="ゆゆうた"){
  getTweet("kinkyunoyuyuta","7");
}


if($text=="やっちゃえ"){
  $response_format_text = [
    "type" => "text",
    "text" => "NISSAN"
  ];
}

if($restype=="join"){
  $response_format_text = [
    "type" => "text",
    "text" => "( *ﾟ▽ﾟ*  っ)З ぽぽー！"
  ];
}


if($groupId==$urako){
  $uprof=getProfile($userId);
  if($type=="text"){
    $message = [
      'type' => 'text',
      'text' => "浦高新入生グル".PHP_EOL.$uprof['displayName']."'s chat".PHP_EOL.$text
    ];
    push($testgroup,$message);
  }
  if($type=="image"){
    $message = [
      'type' => 'text',
      'text' => $profile['displayName']."'s pic"
    ];
    push($testgroup,$message);
    $message = [
      "type" => "image",
      "originalContentUrl" => $profile['pictureUrl'],
      "previewImageUrl" => $profile['pictureUrl']
    ];
    push($testgroup,$message);
    
  }
  if($type=="sticker"){
    $message = [
      'type' => 'text',
      'text' => $profile['displayName']."'s stamp"
    ];
    push($testgroup,$message);
  }
}


//--------------------------------------post
/*
$response_format_text = [
  "type" => "text",
  "text" => "check"
];*/

$post_data = [
	"replyToken" => $replyToken,
	"messages" => [$response_format_text]
];

$ch = curl_init("https://api.line.me/v2/bot/message/reply");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json; charser=UTF-8',
    'Authorization: Bearer ' . $accessToken
    ));
$result = curl_exec($ch);
curl_close($ch);
