<?php

$today=getdate();
$h=$today[hours];

//if($h!=5 && $h!=11 && $h!=17 && $h!=23)exit();

$my_screen_name="tamaron_bot";
$follow_limit=1370;
$twitext="";
//--------------------------------------auth init
//twitter init
require 'TwistOAuth.phar';

$to=new TwistOAuth(
  //getenv('twiCK'),//twiCK
  "5ProI0Gqar4WCc4gL6KSROxiyM",
  //getenv('twiCS'),//twiCS
  "5Die1lvgPH08i4lyIynwxUgnVm514aHrBJBbVHUHLt9n2J5iAz",
  //getenv('twiAT'),//twiAT
  "947516229731868672-nRK1xsvozjg1r9qvWQr3kZF286D3DNa",
  //getenv('twiATS')//twiATS
  "ODcIGr71wisFZDrOjuSIl1KBCrRBqmF0Hn35hES7GO7pz"
);

//--------------------------------------function

function retweet(){
  global $to,$rt_count,$rt_max,$follow; 
  //global $pdo;
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
        //$sql="INSERT INTO follow(id) VALUES (' ".$tweet->user->screen_name."');";
        //$count=$pdo->exec($sql);
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
        //$sql="INSERT INTO follow (id) VALUES (' ".$tweet->user->screen_name."');";
        //$count=$pdo->exec($sql);
      }

      catch(TwistException $e){
        echo $e->getMessage().PHP_EOL;
      }
      if($rt_count==$rt_max){
        //break;
      } 
    }
}

$res = $to->get('https://api.twitter.com/1.1/users/show.json',['screen_name'=>"tamaromaron"]);
$follow=0;
$follow=$res->friends_count;
$amari=0;
$amari=$follow-$follow_limit;//maxかいてね
if($amari<0)$amari=0;

echo $follow.PHP_EOL;
echo "amari=$amari";

/*
$url=parse_url(getenv('DATABASE_URL'));
$dsn=sprintf('pgsql:host=%s;dbname=%s',$url['host'],substr($url['path'],1));
$pdo=new PDO($dsn,$url['user'],$url['pass']);
*/
//$sql="DELETE FROM follow ORDER BY add_time LIMIT ".$amari.";";
//$count=$pdo->exec($sql);
try{
  retweet();
}catch(Exception $e){
  //nop
}

$twitext="exec->php twi.php".PHP_EOL."--result--".PHP_EOL."RT:$rt_count".PHP_EOL."MaxRT:$rt_max".PHP_EOL."following:$follow".PHP_EOL."#tamaronbot2_log";
$status = $to->post('statuses/update', ['status' => $twitext]);

//-----test---start

$res = $to->get('https://api.twitter.com/1.1/users/show.json',['screen_name'=>$my_screen_name]);
$follow=0;
$follow=$res->friends_count;
$amari=0;
$amari=$follow-1330;//maxかいてね
if($amari<0)$amari=0;

$following = $to->get('friends/ids', array('screen_name' => $my_screen_name,'count'=>'2000'));
$followers = $to->get('followers/ids', array('screen_name' => $my_screen_name,'count'=>'2000'));

//var_dump($following);
$following_=array_reverse($following->ids);//配列を逆順にする

$rmcnt=0;

foreach($following_ as $id){
  if($amari==0)break;
  if (!in_array($id, $followers->ids)) {
    $to->post('friendships/destroy', array('user_id' => $id));
    $rmcnt=$rmcnt+1;
    echo "Removed : ".$id.PHP_EOL;
    if($rmcnt>=$amari)break;
  }
}

$twitext="remove:$rmcnt".PHP_EOL."#tamaronbot2_log";
$status = $to->post('statuses/update', ['status' => $twitext]);

//---test---end

//$close_flag = pg_close($link);