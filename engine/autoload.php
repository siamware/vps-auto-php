<?php

session_start();
date_default_timezone_set('Asia/Bangkok');
header('X-Powered-By: Phumin Studio');

require_once __DIR__ . '/../config.php';

// Use library
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include library
require_once __DIR__ . '/lib/phpwee/phpwee.php';
require_once __DIR__ . '/lib/host/xenserver.php';
require_once __DIR__ . '/lib/phpmailer/PHPMailer.php';
require_once __DIR__ . '/lib/phpmailer/SMTP.php';
require_once __DIR__ . '/lib/phpmailer/Exception.php';

// Include class
require_once __DIR__ . '/cache.php';
require_once __DIR__ . '/essentials.php';
require_once __DIR__ . '/host.php';
require_once __DIR__ . '/package.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/setting.php';
require_once __DIR__ . '/template.php';
require_once __DIR__ . '/license.php';
require_once __DIR__ . '/sms.php';
require_once __DIR__ . '/payment.php';
require_once __DIR__ . '/user.php';
require_once __DIR__ . '/vps.php';
require_once __DIR__ . '/ticket.php';
require_once __DIR__ . '/code.php';

// Library

// Application setting
$config['version'] = '0.0.10';
$config['engine_number'] = 'PHUMIN-STUDIO-CLOUD'; // Important! Don't edit this line

// Engine start
$engine = (object) [
            "cache" => new PHUMIN_STUDIO_Cache(),
            "config" => $config,
            "package" => new PHUMIN_STUDIO_Package(),
            "security" => new PHUMIN_STUDIO_Security(),
            "setting" => new PHUMIN_STUDIO_Setting(),
            "license" => new PHUMIN_STUDIO_License(),
            "sql" => new PDO("mysql:host=" . $config['sql_host'] . "; dbname=" . $config['sql_db'] . ";", $config['sql_user'], $config['sql_pass']),
            "template" => new PHUMIN_STUDIO_Template(),
            "ticket" => new PHUMIN_STUDIO_Ticket(),
            "payment" => new PHUMIN_STUDIO_Payment(),
            "user" => new PHUMIN_STUDIO_User(),
            "sms" => new PHUMIN_STUDIO_SMS(),
            "vps" => new PHUMIN_STUDIO_VPS(),
            "host" => new PHUMIN_STUDIO_Host_Server(),
            "code" => new PHUMIN_STUDIO_Code(),

            "xenserver" => new PHUMIN_STUDIO_Xenserver(),
];

// Set UTF-8 for mysql
$engine->sql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$engine->sql->exec("SET NAMES 'UTF8';");
$engine->sql->exec("SET CHARACTER SET utf8");
$engine->sql->exec("SET character_set_results=utf8");
$engine->sql->exec("SET character_set_client=utf8");
$engine->sql->exec("SET character_set_connection=utf8");

// Error handler
set_error_handler('PHUMIN_STUDIO_ERROR_HANDLER');
register_shutdown_function('PHUMIN_STUDIO_FATAL_ERROR_HANDLER');

function PHUMIN_STUDIO_ERROR_HANDLER($code, $message, $file, $line) {
    $row = query("SELECT * FROM `error_php` WHERE `host`=? AND `code`=? AND `message`=? AND `file`=? AND `line`=?;", [$_SERVER['HTTP_HOST'], $code, $message, $file, $line])->fetchAll(PDO::FETCH_ASSOC);
    if (count($row) == 0) {
        query("INSERT INTO `error_php` (`host`,`code`,`message`,`file`,`line`,`count`,`time`) VALUES (?,?,?,?,?,?,?);", [$_SERVER['HTTP_HOST'], $code, $message, $file, $line, 1, date('Y/m/d H:i:s', time())]);
    } else {
        query("UPDATE `error_php` SET `count`=`count`+1,`time`=? WHERE `id`=?;", [date('Y/m/d H:i:s', time()), $row[0]['id']]);
    }
}

function PHUMIN_STUDIO_FATAL_ERROR_HANDLER() {
    $last_error = error_get_last();
    if ($last_error['type'] === E_ERROR) {
        PHUMIN_STUDIO_ERROR_HANDLER(E_ERROR, $last_error['message'], $last_error['file'], $last_error['line']);
    }
}
// End error handler

// Update program
if(version_number(config('current_version')) <= version_number($config['version'])) {
    require_once __DIR__ . "/../update.php";
}

// Check session login
$engine->user->check();