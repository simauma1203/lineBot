<?php

//--------------------------------------auth init
//twitter init
require 'TwistOAuth.phar';
$to=new TwistOAuth(
  '5zHyrNlr2TZ81h8yzJlGrWOPl',//twiCK
  'I7dB3LupCTrq7FSVjrvmyGIiJ2muWc1mDP7HQqXu2menI3Xsdm',//twiCS
  '919202972927586304-TsOE7kvkltMV0GsUTs2NsCytga0uZFf',//twiAT
  'levwq2Zam3N6yEe45GntRvUuqjvJjxwx0BDpduqOCWK5P'//twiATS
);

$accessToken = 'v61upqQUN/oE4yiwgij6n9IbIy8PbStfbvan2xrNlgg2OFswMK7XLBLO4rlyjmk30/a3EkNtwVqIcSMOOVgZMQlhlpF6hxuJXG6GugC9s/X008nYQ8s04Z38eb+l3zOaeIaUPWmQCv6ybAjtrIHdVAdB04t89/1O/w1cDnyilFU=';
$channelSecret='dfd80f0736d4a20a2114cc6d4babcd5f';

//--------------------------------------function

function getProfile($uId){
  global $groupId,$roomId,$accessToken;
  //$id='U51eca766d3d062b3a121756b96f51bff';
  $url = "https://api.line.me/v2/bot/profile/".$uId;
  
  //$url = "https://api.line.me/v2/bot/group/".$gId."/member/".$uId;
  /*if($gId!=""){
    $url="https://api.line.me/v2/bot/room/".$rId."/member/".$uId;
  }*/
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

$testgroup="C64e8aab2913e65aca30232060f786557";

$urako="Ce8a68f1b2bdec36ed219036a38936419";

//line get prof
$profile = getProfile($userId);

$help="--Command List--";
$help.=PHP_EOL."/help ...ヘルプを表示";
$help.=PHP_EOL."/info ...更新情報を表示";
$help.=PHP_EOL."/time ...時刻を表示";
$help.=PHP_EOL."/echo <text> ...文字列を表示";
$help.=PHP_EOL."/myid ...自分のIDを取得";
$help.=PHP_EOL."/groupid ...グループのIDを取得";
$help.=PHP_EOL."/roomid ...ルームのIDを取得";
$help.=PHP_EOL."/myname ...自分の名前を取得";
$help.=PHP_EOL."/mystat ...自分のステータスを表示";
$help.=PHP_EOL."/mypic ...自分のプロフィール画像を表示";
$help.=PHP_EOL."/gettweet @<TwitterID> ...ツイートを取得";
$help.=PHP_EOL."/server ...アプリケーションサーバーのURLを表示";
$help.=PHP_EOL."/sql <cmd> ...SQLコマンドを発行";
$help.=PHP_EOL."/memowrite <text> ...メモを追加";
$help.=PHP_EOL."/memoshow ...メモを表示";
$help.=PHP_EOL."/memodel ...メモを全削除";
$help.=PHP_EOL."/sqlstat ...SQLサーバーの情報を表示";
//$help.=PHP_EOL."/rt ...RT&フォローを実行(権限あり)";
$help.=PHP_EOL."おみくじ ...運勢を占います";
$help.=PHP_EOL."π...ひみつ❤️";
$help.=PHP_EOL."";

$info="--更新情報--";
$info.=PHP_EOL."17/08/28 当botが誕生";
$info.=PHP_EOL."17/10/03 MessagingAPIを実装";
$info.=PHP_EOL."17/10/06 time等の主要コマンドの追加";
$info.=PHP_EOL."17/10/08 TwitterAPIとの連携を実装";
$info.=PHP_EOL."17/11/01 おみくじ機能を追加";
$info.=PHP_EOL."17/11/05 SQLサーバーとmemo機能を実装";
$info.=PHP_EOL."18/02/03 プログラムを最適化、他ユーザーの取得を実装";
//$info.=PHP_EOL."18/03/03 ほりこしガチャを追加";
$info.=PHP_EOL."";
//$info.=PHP_EOL."";
//$info.=PHP_EOL."";



//--------------------------------------command

//horikoshi ver
/*
if($userId=='U51eca766d3d062b3a121756b96f51bff'){
  $response_format_text = [
    "type" => "text",
    "text" => "ほりだまれや"
  ];
}*/
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


  //table:follow
  $close_flag = pg_close($link);

  if(checkCommand("rt")){
    retweet();
  }



//--------------------------------------reply

if(strpos($text,'こんにち')!==FALSE){
  $response_format_text = array(
    "type" => "text",
    "text" => "こんにちわ、".$profile['displayName']."さん"
  );
}
/*
if(strpos($text,'ました')!==FALSE){
  $mpos=strpos($text,'ました');
  $response_format_text = array(
    "type" => "text",
    "text" => "あやせました".$text
  );
}*/
if(strpos($text,'マティ')!==FALSE){
  $mpos=strpos($text,'マティ');
  $response_format_text = array(
    "type" => "text",
    "text" => "谷間ティ".$text
  );
}
if($text=="ごちうさ"){
  getTweet("usagi_anime","5");
}

if($text=="やっちゃえ"){
  $response_format_text = [
    "type" => "text",
    "text" => "NISSAN"
  ];
}

if($text=="おみくじ"){
$unsei=["大吉","中吉","小吉","凶","大凶","超大吉","超大凶","中吉","小吉","吉","吉",/*"ほりこし"*/];
  $col=["レッド","ブルー","グリーン","シアン","ブラック","シルバー","ゴールド","グリーン",
"オレンジ","パープル","イエロー","ホワイト","ブラウン","ピンク",/*"ほりこし"*/];
  $response_format_text = [
    "type" => "text",
    "text" => "今日の運勢:[".$unsei[rand(0,11)]."]".PHP_EOL."ラッキーカラー:[".$col[rand(0,14)]."]"
  ];
}


if($text=="ほりこしガチャ"){
  $gacha="";
  $rare="";
  for($i=1;$i<=10;$i++){
    $r=rand(1,100);

    if($r<=2){
      $list=["ヒューガ","master of artさわおか","鏡という名のジャガイモ"];
      $rare="UR";
      $gname=$list[rand(0,count($list)-1)];
      //$r=rand(0,count($list)-1);
      if(rand(1,100)==1){
        $rare="UR+";
        $gname="致シたヒューガ";
      }

    }elseif($r<=12){
      $list=["バレイショ","ウンチーコング","水瀬いのり","偽ヘッドキャップ","じゃがいも"];
      $rare="SR";
      $gname=$list[rand(0,count($list)-1)];
      
    }elseif($r<=27){
      $list=["@HolyHoly_104","チンパンジー","タニマティ","ウクライナの国土","坊主","アイマスPほりこし","sugisama"];
      $rare="R";
      $gname=$list[rand(0,count($list)-1)];

    }else{
      $list=["ほりまつ","ホリ","こしがや","走る堀越","歌うほりこし","食べるほりこし","そつちろnew","こぼれる耳アカ(本人談)","新鮮なえび"];
      $rare="N";
      $gname=$list[rand(0,count($list)-1)];
    }
    $gacha.=$rare.":".$gname.PHP_EOL;

  }
  $response_format_text = [
    "type" => "text",
    "text" => "ほりこしガチャ(10連)".PHP_EOL.$gacha
  ];
}

if($text=="提供割合"){
  $response_format_text = [
    "type" => "text",
    "text" => "-ほりこしガチャ提供割合-".PHP_EOL."UR+:0.01".PHP_EOL."UR:2%".PHP_EOL."SR:10%".PHP_EOL."R:25%".PHP_EOL."N:63%"
  ];
}

if($text=="いのりん"){
  $response_format_text = [
    "type" => "image",
    "originalContentUrl" => "https://nizista.com/images/items/70c49a50b20811e681c75b4da4ea3608.jpg",
    "previewImageUrl" => "https://nizista.com/images/items/70c49a50b20811e681c75b4da4ea3608.jpg"
  ];
}

if($text=="ヴァネロピ"){
  $response_format_text = [
    "type" => "text",
    "text" => "ばねとビンビン！！！"
  ];
}
if($text=="ビンビン"){
  $response_format_text = [
    "type" => "audio",
    "originalContentUrl" => $_SERVER['SERVER_NAME']."/web/anta.mp3",
    "duration"=>"22000"
  ];
}

if($type=="join"){
  $response_format_text = [
    "type" => "text",
    "text" => "( *ﾟ▽ﾟ*  っ)З ぽぽー！"
  ];
}

if($text=="π"){
  $response_format_text = [
    "type" => "template",
    "altText" => "極秘メニュー",
    "template" => [
      "type" => "buttons",
      //"thumbnailImageUrl" => "https://image1.shopserve.jp/funadomari.jp/pic-labo/llimg/N-500-2.jpg",
      "title" => "極秘メニュー",
      "text" => "管理者以外アクセス禁止",
      "actions" => [
        [
          "type" => "message",
          "label" => "push me!",
          "text" => "あああああああああああああああああああああああああああああああ！！！！！！！！！！！（ﾌﾞﾘﾌﾞﾘﾌﾞﾘﾌﾞﾘｭﾘｭﾘｭﾘｭﾘｭﾘｭ！！！！！！ﾌﾞﾂﾁﾁﾌﾞﾌﾞﾌﾞﾁﾁﾁﾁﾌﾞﾘﾘｲﾘﾌﾞﾌﾞﾌﾞﾌﾞｩｩｩｩｯｯｯ！！！！！！！ ）"
        ],
        [
          "type" => "message",
          "label" => "切り干し大根界隈",
          "text"=>"ヴァネロピ"
        ]
      ]
    ]
  ];
}

if($text=="えびーね"){
  $response_format_text = [
    "type" => "template",
    "altText" => "こちらのエビはいかがですか？",
    "template" => [
      "type" => "buttons",
      "thumbnailImageUrl" => "https://image1.shopserve.jp/funadomari.jp/pic-labo/llimg/N-500-2.jpg",
      "title" => "エビレストラン",
      "text" => "お探しのエビはこれですね",
      "actions" => [
        [
          "type" => "postback",
          "label" => "予約する",
          "data" => "action=buy&itemid=123"
        ],
        [
          "type" => "postback",
          "label" => "電話する",
          "data" => "action=pcall&itemid=123"
        ],
        [
          "type" => "uri",
          "label" => "詳しく見る",
          "uri" => "https://" . $_SERVER['SERVER_NAME'] . "/"
        ],
        [
          "type" => "message",
          "label" => "殺す",
          "text" => "やっちゃえ"
        ]
      ]
    ]
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
/*
if($groupId==$testgroup){
  push($testgroup,$profile['displayName']."「".$text);
}*/


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
