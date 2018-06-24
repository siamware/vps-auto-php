<?php

$payload = file_get_contents('php://input');
if (json_decode($payload)) {
    $payload = json_decode($payload, true);
}
header('Content-Type: text/json; charset=UTF-8');
require_once __DIR__ . '/engine/autoload.php';

$j = [
    'success' => false,
    'data' => [],
    'debug' => [
        'license_key' => $_POST['license_key'],
        'action' => $_POST['action'],
    ],
    'time' => 0,
];

if($_POST['license_key'] == config('license_key')) {
    if($_POST['action'] == "api_change") {
        query("UPDATE `{$engine->config['prefix']}xen_host` SET `api_token` = ? WHERE `api_token` = ?;", [$_POST['api_token_new'], $_POST['api_token_old']]);
        $j['success'] = true;
        $j['debug']['api_token_new'] = $_POST['api_token_new'];
        $j['debug']['api_token_old'] = $_POST['api_token_old'];
    } else if($_POST['action'] == "data_host") {
        query("UPDATE `{$engine->config['prefix']}xen_host` SET `cpu` = ?, `ram_total` = ?, `ram_free` = ? WHERE `api_token` = ?;", [$_POST['host']['cpu'], $_POST['host']['ram_total'], $_POST['host']['ram_free'], $_POST['api_token']]);
        $j['success'] = true;
    } else if($_POST['action'] == "data_vm") {
        $engine->xenserver->sync_vm();
        $j['success'] = true;
    } else if($_POST['action'] == "data_vbd") {
        $engine->xenserver->sync_vbd();
        $j['success'] = true;
    } else if($_POST['action'] == "data_vdi") {
        $engine->xenserver->sync_vdi();
        $j['success'] = true;
    } else if($_POST['action'] == "data_vif") {
        $engine->xenserver->sync_vif();
        $j['success'] = true;
    } else if($_POST['action'] == "data_template") {
        $engine->xenserver->sync_template();
        $j['success'] = true;
    } else if($_POST['action'] == "update_vm_status") {
        $engine->xenserver->update_vm_status($_POST['api_token'], $_POST['vm'], $_POST['status']);
        $j['success'] = true;
    }
}else{
    $j['error'] = "access_denined";
}



$j['time'] = time();
echo json_encode($j);
exit();