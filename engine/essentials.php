<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function query($sql, $array = []) {
    global $engine;
    $q = $engine->sql->prepare($sql);
    $q->execute($array);
    return $q;
}

$_config_cache = [];
function config($name, $debug = false) {
    global $engine, $config, $_config_cache;
    if (isset($_config_cache[$name])) {
        return $_config_cache[$name];
    } else {
        $r = query("SELECT * FROM `{$config['prefix']}config` WHERE `name` = ?;", [$name])->fetch(PDO::FETCH_ASSOC);
        $debug && var_dump($r);
        if ($r) {
            $_config_cache[$name] = $r['value'];
            return $r['value'];
        } else
            return '';
    }
}

function save_config($name, $data = null, $debug = false) {
    global $engine, $config, $_config_cache;
    if($data === null){
        foreach($name as $key => $value){
            save_config($key, $value);
        }
    }else{
        if(query("SELECT * FROM `{$config['prefix']}config` WHERE `name` = ?;", [$name])->rowCount() == 0){
            $r = query("INSERT INTO `{$config['prefix']}config` (`value`,`name`) VALUES (?,?);", [$data, $name]);
        }else{
            $r = query("UPDATE `{$config['prefix']}config` SET `value` = ? WHERE `name` = ?;", [$data, $name]);
        }
    }
    $debug && var_dump([$name,$data,$r]);
}

function version_number($string) {
    $array = explode(".", $string);
    $number = 0;
    foreach($array as $k => $a) {
        $number += pow(10, count($array) - $k) * $a;
    }
    return $number;
}

function close_connection() {
    global $engine;
    $engine->sql = null;
}

function send_mail($to, $subject, $option) {
    global $engine;

    $smtp_config = json_decode(config('smtp_config'), true);
    $email_config = json_decode(config('email_config'), true);

    $mail = new PHPMailer(true);
    try {
        //Server settings
        $mail->isSMTP();                                        // Set mailer to use SMTP
        $mail->Host = $smtp_config['host'];                   // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                                 // Enable SMTP authentication
        $mail->Username = $smtp_config['username'];                              // SMTP username
        $mail->Password = $smtp_config['password'];                       // SMTP password
        $mail->SMTPSecure = 'tls';                              // Enable TLS encryption, `ssl` also accepted
        $mail->Port = $smtp_config['port'];                                      // TCP port to connect to
    
        //Recipients
        $mail->setFrom($email_config['email'], $email_config['name']);
        $mail->addAddress($to);

        //Content
        $mail->isHTML(true);
        $mail->CharSet = 'utf-8';
        $mail->Subject = $subject;
        
        //Important
        $mail->Priority = 1; // High (1 = High, 3 = Normal, 5 = Low)

        //Body
        $template = file_get_contents(__DIR__ . "/../template/email/{$option['template']}.html");
        $template = str_replace("{{ head }}", $option['head'], $template);
        $template = str_replace("{{ description }}", $option['description'], $template);
        if(isset($option['btn-link']))
            $template = str_replace("{{ btn-link }}", $option['btn-link'], $template);
        if(isset($option['btn-text']))
            $template = str_replace("{{ btn-text }}", $option['btn-text'], $template);
        $mail->Body = $template;
        $mail->send();
        return true;
    } catch (Exception $e) {
        echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
        return $mail->ErrorInfo;
    }
}