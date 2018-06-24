<?php

set_time_limit(0);
ini_set('display_errors', 'on');
ini_set('post_max_size', '128M');
date_default_timezone_set('Asia/Bangkok');

!isset($_SERVER) ? $_SERVER = ["SERVER_ADDR" => ""] : '';
!isset($_SERVER['SERVER_ADDR']) ? $_SERVER["SERVER_ADDR"] = '' : '';

if ($_SERVER['SERVER_ADDR'] == "::1" || $_SERVER['SERVER_ADDR'] == "localhost" || $_SERVER['SERVER_ADDR'] == "127.0.0.1") {
    $config = [
        "debug" => true,
        "prefix" => "tb_",
        "sql_host" => "localhost",
        "sql_db" => "vps",
        "sql_user" => "root",
        "sql_pass" => "",
    ];
} else {
    $config = [
        "debug" => true,
        "prefix" => "tb_",
        "sql_host" => "localhost",
        "sql_db" => "",
        "sql_user" => "",
        "sql_pass" => "",
    ];

    /*$config = [
        "debug" => false,
        "prefix" => "tb_",
        "sql_host" => "localhost",
        "sql_db" => "managets_sinusbot",
        "sql_user" => "managets_mysql",
        "sql_pass" => "T=EeONR-1kqd",
    ];*/
}
