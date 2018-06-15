<?php

$today=getdate();
$h=$today[hours];
sif($h!=20 && $h!=4 && $h!=12)exit();


$twitext="";
//--------------------------------------auth init
//twitter init
require 'TwistOAuth.phar';
$to=new TwistOAuth(
  '5zHyrNlr2TZ81h8yzJlGrWOPl',//
  'I7dB3LupCTrq7FSVjrvmyGIiJ2muWc1mDP7HQqXu2menI3Xsdm',//
  '919202972927586304-TsOE7kvkltMV0GsUTs2NsCytga0uZFf',//
  'levwq2Zam3N6yEe45GntRvUuqjvJjxwx0BDpduqOCWK5P'//
);
//--------------------------------------function

function retweet(){
  global $to; 
  $rt_count=0;
  $rt_max=100;

    $parms=[
      'q'=>'サイン+OR+ギフト+OR+プレゼント+OR+クオカード+OR+商品券 フォロー RT+OR+リツイート min_retweets:400',
      'count'=>'100'
    ];
    try{
      $res=$to->get('search/tweets',$parms)->statuses;
    }catch(TwistException $e){
      echo $e->getMessage().PHP_EOL;
    }
    foreach($res as $tweet){ 
      try{
        $retweeted_status=$to->post("statuses/retweet/{$tweet->id_str}");
        $rt_count++;
        $follow_status=$to->post("friendships/create",['screen_name'=>$tweet->user->screen_name]);            
      }
      catch(TwistException $e){
        echo $e->getMessage().PHP_EOL;
      }
      if($rt_count==$rt_max){
        break;
      } 
    }
    $parms=[
      'q'=>'サイン+OR+台本 声優+OR+アニメ フォロー RT+OR+リツイート min_retweets:400',
      'count'=>'100'
    ];
    try{
      $res=$to->get('https://api.twitter.com/1.1/users/show.json',$parms)->statuses;
    }catch(TwistException $e){
      echo $e->getMessage().PHP_EOL;
    }
    foreach($res as $tweet){ 
      try{
        $retweeted_status=$to->post("statuses/retweet/{$tweet->id_str}");
        $rt_count++;
        $follow_status=$to->post("friendships/create",['screen_name'=>$tweet->user->screen_name]);            
      }
      catch(TwistException $e){
        echo $e->getMessage().PHP_EOL;
      }
      if($rt_count==$rt_max){
        break;
      } 
    }


    $twitext="twi.php has run".PHP_EOL."--result--".PHP_EOL."RT:".$rt_count.PHP_EOL."MaxRT:".$rt_max.PHP_EOL."#tamaronbot_log";
    
    $status = $to->post('statuses/update', ['status' => $twitext]);

  //mada syori<-なにこのしょり？
  $target=[
    'Present_RT_FR'
  ];
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

$res = $to->get('https://api.twitter.com/1.1/users/show.json',['screen_name'=>"tamaromaron"]);
$follow=$res->friends_count;
echo $follow.PHP_EOL;
$amari=0;
$amari=$follow-1300;//maxかいてね
if($amari<0)$amari=0;

$url=parse_url(getenv('DATABASE_URL'));
$dsn=sprintf('pgsql:host=%s;dbname=%s',$url['host'],substr($url['path'],1));
$pdo=new PDO($dsn,$url['user'],$url['pass']);

$sql="DELETE FROM follow ORDER BY add_time LIMIT ".$amari.";";
$count=$pdo->exec($sql);
$close_flag = pg_close($link);

retweet();