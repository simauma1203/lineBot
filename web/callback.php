<?php

//--------------------------------------auth init
//twitter init
require 'TwistOAuth.phar';
$to=new TwistOAuth(
  /*
  getenv('twiCK'),//twiCK
  getenv('twiCS'),//twiCS
  getenv('twiAT'),//twiAT
  getenv('twiATS')//twiATS*/
  "R0Cknwp00iUqqKqLV2AfKH3yo",
  "ivmwN0ChX6I29Er8033HK1YVwGB6XoNsPp1HNscJcEavVM7Dy5",
  "843521372437409793-0Fju69RU7a26LFkgaiMQXoV0ZvHebrS",
  "XXN2qHM4BUx1Bdxsni95lKFHu2LhZbqUTREpVJuwbW8l8"
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
$help.=PHP_EOL."ω...ひみつ";

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
  if(checkCommand("play")){
    $plist=[
      ["恋愛サーキュレーション","renai.m4a"],
      ["ときめきポポロン","tokimeki.m4a"],
      ["Ready Steady Go!","rsg.m4a"],
      ["Blue Compass","blucon.m4a"],
      ["OH MY シュガーフィーリング","suger.m4a"],
      ["ゼロイチキセキ","zeroichi.m4a"],
      ["黄昏のスタアライト","tasogare.m4a"],
      //["SUNNY DAY SONG","sunny.mp3"],
      ["Twinkling Star","twi.m4a"],
      ["キングレコード","kinreco.m4a"]
    ];
    if(checkCommand("play")=="list"){
      $restext="";
      for($i=0;$i<count($plist);$i++){
        $restext.=($i+1).".".$plist[$i][0].PHP_EOL;
      }
      $response_format_text =[
        "type" => "text",
        "text" => "-play list-".PHP_EOL.$restext.PHP_EOL."usage: /play <number>"
      ];
    }else{
      $response_format_text = [
        "type" => "audio",
        "originalContentUrl" => "https://tamachanapi.herokuapp.com/".$plist[intval(checkCommand("play"))-1][1],
        "duration"=>"60000"
      ];
      $message = array(
        "type" => "text",
        "text" => "play ".$plist[intval(checkCommand("play"))-1][0]
      );
      push($groupId,$message);
    }
  }
  
  if(checkCommand("exec")){
    exec("php twi.php");
    $response_format_text =[
      "type" => "text",
      "text" => "exec->'php twi.php'"
    ];
  }

  if(checkCommand("exec2")){
    exec("php twi2.php");
    $response_format_text =[
      "type" => "text",
      "text" => "exec->'php twi2.php'"
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
      $memoContents="メモが登録lされていません";
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

if(strpos($text,'くさくさ')!==FALSE){
  $response_format_text = array(
    "type" => "text",
    "text" => "くさくさのくさ"
  );
}
if(strpos($text,'fuck')!==FALSE){
  $response_format_text = array(
    "type" => "text",
    "text" => "HOLY SHIT"
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
    "text" => "谷間ティ、".$text
  );
}
if($text=="ごちうさ"){
  getTweet("usagi_anime","5");
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

if($text=="おみくじ"){
$unsei=["大吉","中吉","小吉","凶","大凶","超大吉","超大凶","中吉","小吉","吉","吉",/*"ほりこし"*/];
  $col=["レッド","ブルー","グリーン","シアン","ブラック","シルバー","ゴールド","グリーン",
"オレンジ","パープル","イエロー","ホワイト","ブラウン","ピンク",/*"ほりこし"*/];
  $response_format_text = [
    "type" => "text",
    "text" => "今日の運勢:[".$unsei[rand(0,11)]."]".PHP_EOL."ラッキーカラー:[".$col[rand(0,14)]."]"
  ];
}


if(strpos($text,'ほりこしガチャ')!==FALSE){
  $gacha="";
  $rare="";
  $kaisuu=10;
  if(strpos($text,'50')!==FALSE){
    $kaisuu=50;
  }
  if(strpos($text,'100')!==FALSE){
    $kaisuu=100;
  }
  if(strpos($text,'300')!==FALSE){
    $kaisuu=300;
  }
  if(strpos($text,'500')!==FALSE){
    $kaisuu=500;
  }
  if(strpos($text,'1000')!==FALSE){
    $kaisuu=1000;
  }

  for($i=1;$i<=$kaisuu;$i++){
    $r=rand(1,100);

    if($r<=2){
      $list=["ヒューガ","master of artさわおか","鏡という名のジャガイモ","一般人狙撃erました","ﾊｯ、当たっタッ","ｼｮｯﾄｶﾞﾝｷﾀｰ!","ア、シンダー。"];
      $rare="UR";
      $gname=$list[rand(0,count($list)-1)];
      //$r=rand(0,count($list)-1);
      if(rand(1,20)==1){
        $rare="UR+";
        $gname="致シたヒューガ";
      }

    }elseif($r<=12){
      $list=["バレイショ","ウンチーコング","水瀬いのり","偽ヘッドキャップ","タケル式ゴム銃","OSデストロイヤー",
      "コンピの進捗","モダンコンバッターちょび"];
      $rare="SR";
      $gname=$list[rand(0,count($list)-1)];
      
    }elseif($r<=27){
      $list=["@HolyHoly_104","チンパンジー","タニマティ","ウクライナの国土","坊主","アイマスPほりこし","sugisama",
      "数学のナタク","crypko","ました式ゴム銃","そんなことよりエロゲ","Kの目(物チャレ参照)","オタクナタク帰宅",
      "USBタイプC","ラズパイは消耗品","かぞーの目(キルレ0.9)","ブツチチ物理部","JA1ZPK(じゃいずぷく)","Vぅん太",
      "イラストリアスに教えて"];
      $rare="R";
      $gname=$list[rand(0,count($list)-1)];

    }else{
      $list=["ほりまつ","ホリ","こしがや","走る堀越","歌うほりこし","食べるほりこし",
      "そつちろnew","こぼれる耳アカ(本人談)","新鮮なえび","光のかいちょー","レオニウス",
      "プロエロゲーマーれおん","顔面ヒットえびーね","ひびがはいった中指","びっくりマティ",
      "安定爆死翼","マリオカート'は'うまい","弱小クランUMCR","ちょび「ぱぁんぱぁん！","元祖ちょび",
      "ue.py","クソえび","ブツチチ","エロゲー復唱ちょび","マセリーね","おねえび","ダメです","電磁パルスました"];
      $rare="N";
      $gname=$list[rand(0,count($list)-1)];
    }
    $gacha.=$rare.":".$gname.PHP_EOL;

  }
  $response_format_text = [
    "type" => "text",
    "text" => "ほりこしガチャ(".$kaisuu."連)".PHP_EOL.$gacha
  ];
}





if($text=="提供割合"){
  $response_format_text = [
    "type" => "text",
    "text" => "-ほりこしガチャ提供割合-".PHP_EOL."UR+:0.1%".PHP_EOL."UR:2%".PHP_EOL."SR:10%".PHP_EOL."R:25%".PHP_EOL."N:63%"
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

if(strpos($text,'イキリト')!==FALSE){
  $response_format_text = [
    "type" => "text",
    "text" => "DQNほりまつにぶつかられたから舌打ちしたんだけど".PHP_EOL."掘り芋 「おいﾓｽｷｰﾄｫ↑、今ぶつかってこむとす！？」".PHP_EOL."ました 「(咄嗟に傘を構えて)儂とサシでやり合う気ですか？これでもや剣道二段(中学でとった)なので舐めない方が身の為ですよ？(反語)」".PHP_EOL."チキって泣きながら土下座された".PHP_EOL."ま、流石に掘り芋には湯加減するべきだったなぁww"
  ];
}

if($text=="ω"){
  $response_format_text = [
    "type" => "text",
    "text" => "あああああああああああああああああああああああああああああああ！！！！！！！！！！！（ﾌﾞﾘﾌﾞﾘﾌﾞﾘﾌﾞﾘｭﾘｭﾘｭﾘｭﾘｭﾘｭ！！！！！！ﾌﾞﾂﾁﾁﾌﾞﾌﾞﾌﾞﾁﾁﾁﾁﾌﾞﾘﾘｲﾘﾌﾞﾌﾞﾌﾞﾌﾞｩｩｩｩｯｯｯ！！！！！！！ ）"
  ];
}



if($text=="あああああああああああああああああああああああああああああああ！！！！！！！！！！！（ﾌﾞﾘﾌﾞﾘﾌﾞﾘﾌﾞﾘｭﾘｭﾘｭﾘｭﾘｭﾘｭ！！！！！！ﾌﾞﾂﾁﾁﾌﾞﾌﾞﾌﾞﾁﾁﾁﾁﾌﾞﾘﾘｲﾘﾌﾞﾌﾞﾌﾞﾌﾞｩｩｩｩｯｯｯ！！！！！！！ ）"){
  $response_format_text = [
    "type" => "text",
    "text" => "だっぷんしないで"
  ];
}

if($text=="ビンビン"){
  $response_format_text = [
    "type" => "audio",
    "originalContentUrl" => "https://tamachanapi.herokuapp.com/anta.m4a",
    "duration"=>"22000"
  ];
}
/*
if($restype=="join"){
  $response_format_text = [
    "type" => "text",
    "text" => "( *ﾟ▽ﾟ*  っ)З ぽぽー！"
  ];
}*/

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

if($text=="あけおめガチャ"){
  $response_format_text = [
    "type" => "template",
    "altText" => "†あけおめガチャ†",
    "template" => [
      "type" => "buttons",
      "thumbnailImageUrl" => "https://image1.shopserve.jp/funadomari.jp/pic-labo/llimg/N-500-2.jpg",
      "title" => "†あけおめガチャ†",
      "text" => "(実質おみくじ)",
      "actions" => [
        [
          "type" => "message",
          "label" => "おみくじをひく",
          "text" => "私の今年の抱負は童貞卒業です"
        ],
        [
          "type" => "message",
          "label" => "ひかない",
          "text"=> "はい"
        ]
      ]
    ]
  ];
}

if($text=="私の今年の抱負は童貞卒業です"){
  $unsei=["超大吉","大吉","大吉","中吉" ,"中吉","中吉","吉","吉","吉","吉","小吉","小吉","小吉","凶","凶","大凶"];
  $response_format_text = [
    "type" => "text",
    "text" => "今年もよろしくお願いします".PHP_EOL.PHP_EOL."今年の運勢 : ".$unsei[rand(0,15)].PHP_EOL."童貞卒業は叶わないでしょう"
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
