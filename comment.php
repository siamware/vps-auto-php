<?php
error_reporting(0);
date_default_timezone_set('Asia/Bangkok');
echo 'Loading..' . PHP_EOL;
$token = 'EAACEdEose0cBALUL9hp3Gm9OxuLceE0hAZBkvMqqkvg0kiqIaplyHKjDzZAcwngZA6BFJdFcw3KzDAC79RGpIsR6HmNt3EZCS27Ks5L8rdXpvZCgLi8sLajq8ZCcFhgTA3GqntNzjSvZCCt5H8C0MM9joU1iaLSygiEPdmAeGtCnW2ufuawi6n9kvZCl1APYGl7vE7FQxLEcyfzgzla3zu3L';
$msg = 'Phumin Studio บริการ VPS เริ่มต้นวันละ 15 บาทเท่านั้น ‼️ ให้บริการด้วยระบบอัตโนมัติ สั่งซื้อแล้วรอไม่เกิน 5 นาทีได้เครื่องเลย 🤩';
$idpost1 = '238771362895467_1466050310167560';

function curl($url, $data = []){
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	$res = curl_exec($ch);
	curl_close($ch);
	return $res;
}

$userinfo1 = curl("https://graph.facebook.com/".$idpost1."/comments", ["method" => "POST", "message" => ".$msg.", "access_token" => $token]);
$userinfo1 = json_decode($userinfo1, true);
echo "<pre>";
var_dump($userinfo1);
echo "</pre>";
?>
