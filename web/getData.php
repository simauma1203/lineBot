<?php

$postText = $_POST['text'];


//db接続
$url=parse_url(getenv('DATABASE_URL'));
$dsn=sprintf('pgsql:host=%s;dbname=%s',$url['host'],substr($url['path'],1));
$pdo=new PDO($dsn,$url['user'],$url['pass']);


if($postText==""){
    $postText='/getMap {"uname":"watasi","rate":810,"handle":[4,5,6]}';
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
    $json=substr($postText,$len+1,strlen($postText)-$len-1);

    $data=json_decode($json,true);
    $uname=$data["uname"];
    $rate=$data["rate"];
    $nextHdl=$data["nexthdl"];
    $mapcode=$data["mapcode"];//array

    $mapcode=str_replace('"',"E'"+'"'+"'");
    
    $sql="/uploadMap insert into map values('$uname','$mapcode_',$rate,$nextHdl,now());";

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

