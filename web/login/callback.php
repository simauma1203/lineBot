<?php

define('TWITTER_API_KEY', 'アプリのAPIキー'); 　//Consumer Key (API Key)
define('TWITTER_API_SECRET', 'アプリのAPIシークレット');　　//Consumer Secret (API Secret)
define('CALLBACK_URL', 'http:// ・・サイトのドメイン・・ /callback.php');　 //Twitterから認証した時に飛ぶページ場所

//アクセストークンからユーザの情報を取得する
$user_connect = new TwitterOAuth(TWITTER_API_KEY, TWITTER_API_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
$user_info = $user_connect->get('account/verify_credentials');//アカウントの有効性を確認するためのエンドポイント
//ユーザ情報が取得できればcomplete.html、それ以外はerror.htmlに移動する
if(isset($user_info['id_str'])){
 $_SESSION['user_info'] = $user_info;
 header("Location:complete.html");
 exit;
}
else{
 header("Location:error.html");
 exit; 
}