<?php
$_SERVER["HTTP_HOST"] = "kbank_bot";
include_once __DIR__ . '/../../engine/autoload.php';
include_once __DIR__ . '/lib/simple_html_dom.php';
include_once __DIR__ . '/lib/class.php';

function clean ($text) {
	$text = trim($text);
	$text = str_replace("&nbsp;", "", $text);
	return $text;
}

$config = config('bank_kbank');
$config = json_decode($config, true);
$res = BANK_KBANK::check($config['user'], $config['pass'], $config['account']);

var_dump($res);
foreach($res as $s) {
      $date = $s['date']['day']['day'] . "/" . $s['date']['day']['month'] . "/" . $s['date']['day']['year'];
      $time = $s['date']['time']['hour'] . ":" . $s['date']['time']['minute'];
      $q = query("SELECT * FROM `{$engine->config['prefix']}statement_kbank` WHERE `date` = ? AND `time` = ?", [$date, $time]);
      if($q->rowCount() == 0) {
            query("INSERT INTO `{$engine->config['prefix']}statement_kbank` (`date`, `time`, `in`, `out`, `info`) VALUES (?,?,?,?,?);", [$date, $time, $s['in'], $s['out'], $s['info']]);
      }
}