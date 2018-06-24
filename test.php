<?php
require_once __DIR__ . '/engine/autoload.php';

var_dump($engine->sms->credit());

var_dump($engine->sms->send('0918585234', 'Test 1111'));