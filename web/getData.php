<?php

$postText = $_POST['text'];


//db接続
$url=parse_url(getenv('DATABASE_URL'));
$dsn=sprintf('pgsql:host=%s;dbname=%s',$url['host'],substr($url['path'],1));
$pdo=new PDO($dsn,$url['user'],$url['pass']);


if($postText==""){
    $postText='/getMap {"uname":"watasi","rate":810,"handle":[]}';
    //$postText="/uploadScore insert into score(uname,score,instdate) values('player?',15,now())";;
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

}
else if($postText=="/getNextHdl"){

    $nextHdl=getSysVar('nexthandle');

    header('Content-type: application/json;');
    print($nextHdl);

    $nextHdl++;
    updateSysVar("nexthandle",$nextHdl);

}
elseif(mb_strpos($postText,"/uploadMap")===0){
    $len=strlen("/uploadMap");
    $sql=substr($postText,$len+1,strlen($postText)-$len-1);

    //print($sql);
    $pdo->query($sql);
    print("successful");

}
elseif(mb_strpos($postText,"/uploadScore")===0){
    $len=strlen("/uploadScore");
    $sql=substr($postText,$len+1,strlen($postText)-$len-1);
    
    $pdo->query($sql);
    print("successful");
}

elseif(mb_strpos($postText,"/getHdlArr")===0){
    $len=strlen("/getHdlArr");
    $rateStr=substr($postText,$len+1,strlen($postText)-$len-1);
    $rate=intval($rateStr);

    $sql="select * from map;";
    $stmt=$pdo->query($sql);

    //配列にする
    $data = $stmt->fetchAll();
    //print_r($data);

    //ソート用配列
    foreach($data as $val){
        $sort[]=abs($rate-$val["rate"]);
    }
    //print_r($sort);

    //sort
    array_multisort($sort, SORT_ASC, $data);

    //返す配列
    $hdlArr=[];
    foreach($data as $data_){
        $hdlArr[]=$data_["handle"];
    }
    //print_r($hdlArr);
    

    header('Content-type: application/json;');
    print(json_encode(["data"=>$hdlArr]));
}

elseif(mb_strpos($postText,"/getMap")===0){
    $len=strlen("/getMap");
    $json=substr($postText,$len+1,strlen($postText)-$len-1);
   
    $prof=json_decode($json);

    $uname=$prof["uname"];
    $rate=$prof["rate"];
    $played=$prof["handle"];
    print($uname);
    print($rate);
    print($played);


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

