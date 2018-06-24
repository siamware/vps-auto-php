<?php

$_SERVER["HTTP_HOST"] = "wallet_bot";
include_once __DIR__ . '/../../engine/autoload.php';
include_once __DIR__ . '/lib/class.truewallet.php';

$wallet = new TrueWallet2(config('truewallet_phone'), config('truewallet_pin'), 'mobile');
$token = $wallet->GetToken();
//var_dump($token);
$token = json_decode($token, true);
if(!isset($token['data'])) {
    var_dump($token);
    exit();
}else{
    if(!isset($token['data']['accessToken'])) {
        var_dump($token);
        exit();
    }else{
        $token = $token['data']['accessToken']; 
    }
}

$start_date = date_create(date('Y-m-d'));
$start_date = date_add($start_date, date_interval_create_from_date_string('-1 days'));
$start_date = date_format($start_date, 'Y-m-d');

$end_date = date_create(date('Y-m-d'));
$end_date = date_add($end_date, date_interval_create_from_date_string('1 days'));
$end_date = date_format($end_date, 'Y-m-d');

$res = $wallet->getTran($token, $start_date, $end_date);

$transac = json_decode($res, true)['data']['activities'];
$debugs = ['transac' => [], 'report' => []];

foreach ($transac as $t) {
    $q = query("SELECT * FROM `{$engine->config['prefix']}statement_tw` WHERE `reportID`=?;", [$t['reportID']]);
    $debugs['transac'][] = $t;
    if ($q->rowCount() == 0) {
        $d = $wallet->CheckTran($token, $t['reportID']);
        $report = json_decode($d, true);
        if ($t['text3En'] == "creditor") {
            $d = $report['data'];
            $reportID = $t['reportID'];
            $transID = $d['section4']['column2']['cell1']['value'];
            $amout = $d['amount'];
            $phone = $d['ref1'];
            $date = $d['section4']['column1']['cell1']['value'];

            $debugs['report'][] = $report;
            query("INSERT INTO `{$engine->config['prefix']}statement_tw` (`reportID`,`transactionID`,`status`,`amount`,`phone`,`date`) VALUES (?,?,?,?,?,?);", [$reportID, $transID, 'revice', $amout, $phone, $date]);
        }
    }
}