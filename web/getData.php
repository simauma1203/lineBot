<?php

$postText = $_POST['text'];


//db接続
$url=parse_url(getenv('DATABASE_URL'));
$dsn=sprintf('pgsql:host=%s;dbname=%s',$url['host'],substr($url['path'],1));
$pdo=new PDO($dsn,$url['user'],$url['pass']);

/*
$accessToken = 'v61upqQUN/oE4yiwgij6n9IbIy8PbStfbvan2xrNlgg2OFswMK7XLBLO4rlyjmk30/a3EkNtwVqIcSMOOVgZMQlhlpF6hxuJXG6GugC9s/X008nYQ8s04Z38eb+l3zOaeIaUPWmQCv6ybAjtrIHdVAdB04t89/1O/w1cDnyilFU=';
$channelSecret='dfd80f0736d4a20a2114cc6d4babcd5f';
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
*/
if($postText==""){
    $postText="/rankingUpload 12345";
}

if($postText=="/getRanking"){
    //subArr,superArr : unity側で配列を仮想配列に指定しないと動かない？
    $cnt=0;
    $limit=10;//取得するカラム数
    $subArr[]=[];
    //score(int) の降順
    $sql="SELECT * FROM score ORDER BY score DESC LIMIT $limit;";
    $stmt=$pdo->query($sql);//実行
    while($row = $stmt -> fetch(PDO::FETCH_ASSOC)) {
        $subArr[$cnt]=$row;
        $cnt++;
        if($row==""){
            break;
        }
    }
    $superArr=["data"=>$subArr,"count"=>$cnt];
    //printな文字列をjsonで送信
    header('Content-type: application/json;');
    print(json_encode($superArr));

}else if($postText=="/getMap"){
    //subArr,superArr : unity側で配列を仮想配列に指定しないと動かない？
    $cnt=0;
    $limit=1;
    $subArr[]=[];
    //score(int) の降順
    $sql="SELECT * FROM map ORDER BY liked DESC LIMIT $limit;";
    $stmt=$pdo->query($sql);//実行
    while($row = $stmt -> fetch(PDO::FETCH_ASSOC)) {
        $subArr[$cnt]=$row;
        $cnt++;
        if($row==""){
            break;
        }
    }
    $superArr=["data"=>$subArr,"count"=>$cnt];
    //printな文字列をjsonで送信
    header('Content-type: application/json;');
    print(json_encode($superArr));
}elseif(mb_strpos($postText,"/rankingUpload")===0){
    $len=strlen("/rankingUpload");
    $sql=substr($postText,$len+1,strlen($postText)-$len-1);
    print($sql);



}elseif(mb_strpos($postText,"/uploadMap")===0){
    $len=strlen("/uploadMap");
    $sql=substr($postText,$len+1,strlen($postText)-$len-1);

    //print($sql);
    $pdo->query($sql);
    print("successful");

}elseif(mb_strpos($postText,"/uploadScore")===0){
    $len=strlen("/uploadScore");
    $sql=substr($postText,$len+1,strlen($postText)-$len-1);
    
    $pdo->query($sql);
    print("successful");
}


