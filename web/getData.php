<?php

$postText = $_POST['text'];

$accessToken = 'v61upqQUN/oE4yiwgij6n9IbIy8PbStfbvan2xrNlgg2OFswMK7XLBLO4rlyjmk30/a3EkNtwVqIcSMOOVgZMQlhlpF6hxuJXG6GugC9s/X008nYQ8s04Z38eb+l3zOaeIaUPWmQCv6ybAjtrIHdVAdB04t89/1O/w1cDnyilFU=';
$channelSecret='dfd80f0736d4a20a2114cc6d4babcd5f';//lineCS
$groupId="C8727e59e0381bc8c6a7fef3f7f8e4cf2";
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

//db接続
$url=parse_url(getenv('DATABASE_URL'));
$dsn=sprintf('pgsql:host=%s;dbname=%s',$url['host'],substr($url['path'],1));
$pdo=new PDO($dsn,$url['user'],$url['pass']);


if($postText==""){
    push($groupId,"pushs");
    //$postText='/uploadScore {"uid":10,"score":114514}';
    //$postText='/uploadMap {"uname":"keidaroo2","mapcode":["114","514"],"rate":810,"nexthdl":66}';
    //$postText="/uploadScore insert into score(uname,score,instdate) values('player?',15,now())";;
}

if($postText=="/getScoreRanking"){
    //subArr,superArr : unity側で配列を仮想配列に指定しないと動かない？
    $cnt=0;
    $limit=20;//取得するカラム数
    $subArr[]=[];
    //score(int) の降順
    $sql="SELECT * FROM uinfo ORDER BY score DESC LIMIT $limit;";
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

}
else if($postText=="/getRateRanking"){
    //subArr,superArr : unity側で配列を仮想配列に指定しないと動かない？
    $cnt=0;
    $limit=20;//取得するカラム数
    $subArr[]=[];
    //score(int) の降順
    $sql="SELECT * FROM uinfo ORDER BY rate DESC LIMIT $limit;";
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
}

//マップをアップロード
//mapDB require :uid,mapcode,rate
elseif(mb_strpos($postText,"/uploadMap")===0){
    $len=strlen("/uploadMap");
    $json=substr($postText,$len+1,strlen($postText)-$len-1);

    $data=json_decode($json,true);

    //unityから送られたデータを抽出
    $uid=$data["uid"];
    $mapcode=json_encode($data["mapcode"]);
    
    //uidからrateを取得する
    $rate=getElementFromUinfo($uid,"rate");

    //マップハンドルの空きを取得
    $handle=getHandle();

    $sql="insert into map values($uid,'$mapcode',$rate,$handle);";
    $pdo->query($sql);

    header('Content-type: application/json;');
    print($data["uname"]);
    print($data["rate"]);
    print($data["nexthdl"]);
    print_r($data["mapcode"]);
    
    print("successful");


}
elseif(mb_strpos($postText,"/uploadScore")===0){
    $len=strlen("/uploadScore");
    $json=substr($postText,$len+1,strlen($postText)-$len-1);
    
    $upmap=json_decode($json,true);

    $uid=$upmap["uid"];
    $score=$upmap["score"];

    push($groupId,"$uid さんが $score とったよ");

    $sql="update set score=$score where uid=$uid;";

    $pdo->query($sql);
    print("successful");
}


elseif(mb_strpos($postText,"/getMap")===0){
    $len=strlen("/getMap");
    $json=substr($postText,$len+1,strlen($postText)-$len-1);
    //print($json);
    $prof=json_decode($json,true);
    //print($prof);
    $uname=$prof["uname"];
    $rate=$prof["rate"];
    $played=$prof["handle"];

    $sql="select * from map;";
    $stmt=$pdo->query($sql);
    //配列にする
    $data=[];
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        $data[]=$row;
    }
    //$data = $stmt->fetchAll();

    //ソート用配列
    foreach($data as $val){
        $sort[]=abs($rate-$val["rate"]);
    }
    //sort
    array_multisort($sort, SORT_ASC, $data);

    
    foreach($data as $data_){
        if($data_["uname"]!=$uname){//持ち主が自分ではない
            if(!in_array($data_["handle"],$played)){//handleが未プレイ
                //数字だけだと文字扱いにされそう
                //$data_["uname"]=(string)$data_["uname"];
                //substr($data_["mapcode"]1,);

                //print(json_decode($data_["mapcode"]));
                //$data_["mapcode"]=json_encode($data_["mapcode"]);
                //print(json_decode($data_["mapcode"]));
                $ret=$data_;
                break;
            }
        }
    }

    header('Content-type: application/json;');
    print(json_encode($ret));
    //print(json_encode(["a","b"]));
    //print(json_encode([1,2]));


}


function getSysVar($name){
    global $pdo;
    $sql="select * from sysvar where name='$name';";
    $stmt=$pdo->query($sql);
    while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        $sysVar=$row['value'];
    }
    return $sysVar;
}

function updateSysVar($name,$value){
    global $pdo;
    $sql="update sysvar set value=$value where name='$name';";
    $pdo->query($sql);
}

function getHandle(){
    global $pdo;
    $ret=getSysVar("nexthandle");
    updateSysVar("nexthandle",$ret+1);
    return ret;
}

function getElementFromUinfo($uid,$elementName){
    global $pdo;
    $sql="select * from uinfo where uid=$uid;";
    $stmt=$pdo->query($sql);
    while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        $ret=$row[$elementName];
    }
    return $ret;
}