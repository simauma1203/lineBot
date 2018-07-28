<?php

//---
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
function pushM($text){
    global $groupId;
    $message = array(
        "type" => "text",
        "text" => $text
    );
    push($groupId,$message);
}
//---



$postText = $_POST['text'];

//db接続
$url=parse_url(getenv('DATABASE_URL'));
$dsn=sprintf('pgsql:host=%s;dbname=%s',$url['host'],substr($url['path'],1));
$pdo=new PDO($dsn,$url['user'],$url['pass']);


if($postText==""){
    print("<br>-------- DATABASE --------");
    print("<br><br>----- USER INFORMATION TABLE -----<br><br>");
    $sql="select * from uinfo;";
    $stmt=$pdo->query($sql);
    $data=[];
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        $data[]=$row;
    }
    echo "<PRE>";
    print_r($data);
    echo "<PRE>";

    print("<br><br><br>----- SHARED MAP TABLE -----<br><br>");

    $sql="select * from map;";
    $stmt=$pdo->query($sql);
    $data=[];
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        $data[]=$row;
    }
    echo "<PRE>";
    print_r($data);
    echo "<PRE>";

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

//れーとらんきんぐ
else if($postText=="/getRateRanking"){
    //subArr,superArr : unity側で配列を仮想配列に指定しないと動かない？
    $cnt=0;
    $limit=20;//取得するレコード数
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


elseif(mb_strpos($postText,"/getUinfo")===0){

    $len=strlen("/getUinfo");
    $uidStr=substr($postText,$len+1,strlen($postText)-$len-1);

    $uid=intval($uidStr);
    //subArr,superArr : unity側で配列を仮想配列に指定しないと動かない？
    $cnt=0;
    $subArr[]=[];
    //score(int) の降順
    $sql="SELECT * FROM uinfo where uid=$uid;";
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

//---マップをアップロード---
//mapDB require :uid,mapcode,rate
elseif(mb_strpos($postText,"/uploadMap")===0){
    $len=strlen("/uploadMap");
    $json=substr($postText,$len+1,strlen($postText)-$len-1);

    $data=json_decode($json,true);

    //unityから送られたデータを抽出
    $uid=$data["uid"];
    $mapcodejson=json_encode($data["mapcode"]);
    
    //uidからrateを取得する
    $rate=getElementFromUinfo($uid,"rate");

    //マップハンドルの空きを取得
    $handle=getHandle();

    $sql="insert into map values($uid,'$mapcodejson',$rate,$handle);";
    //pushM($sql);
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

    $data=json_decode($json,true);

    $uid=$data["uid"];
    $score=$data["score"];
    $uname=getElementFromUinfo($uid,"uname");
    $highscore=getElementFromUinfo($uid,"score");

    //ハイスコア更新時だけupする
    if($highscore<$score){
        $sql="update uinfo set score=$score where uid=$uid;";
        $pdo->query($sql);
    }
    print("successful");
}


elseif(mb_strpos($postText,"/getMap")===0){
    $len=strlen("/getMap");
    $json=substr($postText,$len+1,strlen($postText)-$len-1);
    //print($json);
    $data=json_decode($json,true);
    //print($prof);
    $uid=$data["uid"];//自分の名前
    $played=$prof["playedhandle"];//自分の対戦履歴
    $rate =getElementFromUinfo($uid,"rate");


    $sql="select * from map;";
    $stmt=$pdo->query($sql);

    //stmtを配列にする
    $data=[];
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        $data[]=$row;
    }

    //ソート用配列 rateの差(>0)が小さい順にsortする
    foreach($data as $val){
        $sort[]=abs($rate-$val["rate"]);
    }
    //sort
    array_multisort($sort, SORT_ASC, $data);

    //先頭から探索
    foreach($data as $data_){
        if($data_["uid"]!==$uid){//持ち主が自分ではない
            if(!in_array($data_["handle"],$played,true)){///未プレイかたしかめる
                $ret_=$data_;
                break;
            }
        }
    }

    $ret=[
        "uid" => $ret_["uid"],
        "uname" => getElementFromUinfo($ret_["uid"],"uname"),//ここを追加することでreqを一回にする
        "rate" => $ret_["rate"],
        "mapcodejson" => $ret_["mapcodejson"],
        "handle" => $ret_["handle"]
    ];
    
    header('Content-type: application/json;');
    print(json_encode($ret));
    //print(json_encode(["a","b"]));
    //print(json_encode([1,2]));
}
elseif(mb_strpos($postText,"/userRegister")===0){

    $len=strlen("/userRegister");
    $uname=substr($postText,$len+1,strlen($postText)-$len-1);

    $uid=getId();
    //uid uname score rate highestRate wins matchesPlayed
    $sql="insert into uinfo values($uid,'$uname',0,1000,0,0,0);";
    $pdo->query($sql);

    header('Content-type: application/json;');
    print($uid);
}

elseif(mb_strpos($postText,"/getUname")===0){

    $len=strlen("/getUname");
    $uidStr=substr($postText,$len+1,strlen($postText)-$len-1);

    $uid=intval($uidStr);

    //pushM("ID:$uid 's name is...");
    $uname=getElementFromUinfo($uid,"uname");
    //pushM("$uname desuyo");

    header('Content-type: application/json;');
    print($uname);
}
elseif(mb_strpos($postText,"/updateRate")===0){
    $len=strlen("/updateRate");
    $json=substr($postText,$len+1,strlen($postText)-$len-1);

    $data=json_decode($json,true);

    $uid=$data["uid"];
    $newRate=$data["rate"];
    //pushM("$uid 's rate has risen by $newRate");
    updateUser($uid,"rate",$newRate);


    $oldHighest=getElementFromUinfo("highestrate");
    if($newRate>$oldHighest){
        updateUser($uid,"highestrate",$newRate);
    }



}

//matchedPlayedをインクリメント
elseif(mb_strpos($postText,"/incMatchesPlayed")===0){
    $len=strlen("/incMatchesPlayed");
    $uidStr=substr($postText,$len+1,strlen($postText)-$len-1);

    $uid=intval($uidStr);

    $oldMP=getElementFromUinfo($uid,"matchesplayed");
    updateUser($uid,"matchesplayed",$oldMP+1);
}

//winsをインクリメント
elseif(mb_strpos($postText,"/incWins")===0){
    $len=strlen("/incWins");
    $uidStr=substr($postText,$len+1,strlen($postText)-$len-1);

    $uid=intval($uidStr);

    $uid=$data["uid"];
    
    $oldMP=getElementFromUinfo($uid,"matchesplayed");
    updateUser($uid,"matchesplayed",$oldMP+1);

    $oldWins=getElementFromUinfo($uid,"wins");
    updateUser($uid,"wins",$oldWins+1);
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
    return $ret;
}


//空きユーザーID取得 
function getId(){
    global $pdo;
    $ret=getSysVar("nextid");
    updateSysVar("nextid",$ret+1);
    return $ret;
}

//unameの変更にはつかえない
function updateUser($uid,$name,$value){
    global $pdo;
    $sql="update uinfo set $name=$value where uid=$uid;";
    $pdo->query($sql);

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