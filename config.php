<?php
//メールマガジン送信
$url = "mailmaga_send.php";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo "エラー: " . curl_error($ch);
} else {
    //echo $response;
}
curl_close($ch);
//メールマガジン送信ここまで
?>