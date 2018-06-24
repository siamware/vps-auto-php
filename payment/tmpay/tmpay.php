<?php
require_once __DIR__ . '/../../engine/autoload.php';

$transaction_id = $_GET['transaction_id'];
$password = $_GET['password'];
$amount = (int) $_GET['real_amount'];
$status = (int) $_GET['status'];

query("UPDATE `{$engine->config['prefix']}payment` SET `query` = ? WHERE `transaction` = ?;", [json_encode($_GET), $transaction_id]);
$t = query("SELECT * FROM `{$engine->config['prefix']}payment` WHERE `transaction` = ?;", [$transaction_id])->fetch(PDO::FETCH_ASSOC);
if ($t['status'] == "pending") {
    if ($status == 1) {
        $t = query("SELECT * FROM `{$engine->config['prefix']}payment` WHERE `transaction` = ?;", [$transaction_id])->fetch(PDO::FETCH_ASSOC);
        $u = query("SELECT * FROM `{$engine->config['prefix']}user` WHERE `id` = ?;", [$t['owner']])->fetch(PDO::FETCH_ASSOC);
        query("UPDATE `{$engine->config['prefix']}payment` SET `status` = ?, `amount` = ? WHERE `transaction` = ?;", ["success", $amount, $transaction_id]);
        query("UPDATE `{$engine->config['prefix']}user` SET `credit` = `credit` + ? WHERE `id` = ?;", [$amount * 0.85, $t['owner']]);
        echo "SUCCESS";
    } elseif ($status == 3 || $status == 4) {
        query("UPDATE `{$engine->config['prefix']}payment` SET `status` = ? WHERE `transaction` = ?;", ["used", $transaction_id]);
        echo "SUCCESS";
    } else {
        query("UPDATE `{$engine->config['prefix']}payment` SET `status` = ? WHERE `transaction` = ?;", ["error", $transaction_id]);
        echo "SUCCESS";
    }
} else {
    echo "ERROR|REPLACE";
}
close_connection();
exit();