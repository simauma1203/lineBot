<?php

$today=getdate();
$h=$today[hours];

//if($h!=5 && $h!=11 && $h!=17 && $h!=23)exit();

//init
$my_screen_name="tamaroning";


//--------------------------------------auth init
//twitter init
require 'TwistOAuth.phar';

$to=new TwistOAuth(
  //getenv('twiCK'),//twiCK
  "vyruFEtyPHuGTJqcf9cOd8fzm",
  //getenv('twiCS'),//twiCS
  "PnsDxvEsKeOlC6HLNVckvf0w39sEwTp95kVfJdHdiGrq6xFxBV",
  //getenv('twiAT'),//twiAT
  "843521372437409793-nKWdt11J1RThTouhkGmwGnrPYozQWsm",
  //getenv('twiATS')//twiATS
  "nogg2UYDMnCwJP7lLbaVPxN4swiDVqkuJ2WGHlSTvSqe2"
);
$msg = "tweet from twitter api";

$params = array(
        'status' => $msg
);
$result = $conn->post('statuses/update', $params);
  
