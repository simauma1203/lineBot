<?php
//this is the program for @tamaron_bot

$today=getdate();
$h=$today[hours];
//if($h!=5 && $h!=11 && $h!=17 && $h!=23)exit();


$twitext="";
//--------------------------------------auth init
//twitter init
require 'TwistOAuth.phar';

$to=new TwistOAuth(
  "ProI0Gqar4WCc4gL6KSROxiyM",//twiCK
  "5Die1lvgPH08i4lyIynwxUgnVm514aHrBJBbVHUHLt9n2J5iAz",//twiCS
  "947516229731868672-nRK1xsvozjg1r9qvWQr3kZF286D3DNa",//twiAT
  "ODcIGr71wisFZDrOjuSIl1KBCrRBqmF0Hn35hES7GO7pz"//twiATS
);

//--------------------------------------function

function retweet(){
  global $to,$pdo,$rt_count,$rt_max,$follow; 
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
        $sql="INSERT INTO follow2 (id) VALUES (' ".$tweet->user->screen_name."');";
        $count=$pdo->exec($sql);
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
        $sql="INSERT INTO follow2 (id) VALUES (' ".$tweet->user->screen_name."');";
        $count=$pdo->exec($sql);
      }

      catch(TwistException $e){
        echo $e->getMessage().PHP_EOL;
      }
      if($rt_count==$rt_max){
        //break;
      } 
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
    $head=$tweet->user->name."(".$tweet->user->screen_name.")".PHP_EOL;
    $mes.=$head.$tweet->text.PHP_EOL.PHP_EOL;
  }
  $response_format_text = array(
    "type" => "text",
    "text" => '@'.$id.PHP_EOL.$mes
  );
}

$res = $to->get('https://api.twitter.com/1.1/users/show.json',['screen_name'=>"tamaron_bot"]);
$follow=0;
$follow=$res->friends_count;
echo $follow.PHP_EOL;
$amari=0;
$amari=$follow-700;//maxかいてね
if($amari<0)$amari=0;

$url=parse_url(getenv('DATABASE_URL'));
$dsn=sprintf('pgsql:host=%s;dbname=%s',$url['host'],substr($url['path'],1));
$pdo=new PDO($dsn,$url['user'],$url['pass']);

//$sql="DELETE FROM follow2 ORDER BY add_time LIMIT ".$amari.";";
//$count=$pdo->exec($sql);

try{
  retweet();
}catch(Exception $e){
  //nop
}


$twitext="twi2.php is being runned.".PHP_EOL."--result--".PHP_EOL."RT:".$rt_count.PHP_EOL."MaxRT:".$rt_max.PHP_EOL."following:".$follow.PHP_EOL."#tamaronbot2_log";
$status = $to->post('statuses/update', ['status' => $twitext]);

//-----test---start


$my_screen_name="tamaron_bot";
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



$close_flag = pg_close($link);