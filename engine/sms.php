<?php
class PHUMIN_STUDIO_SMS {

      public function send($phone, $message = '') {
            global $engine;

            if(is_numeric($this->credit()) && $this->credit() <= 0) {
                  return false;
            }

            $gateway = config('sms_gateway');
            if($gateway == "thaibulk") {
                  $config = config('sms_config_thaibulk');
                  $config = json_decode($config, true);

                  include_once __DIR__ . "/lib/sms/thaibulksms.class.php";
                  $sms = new sms;
                  
                  $time = date("ymdHi");
                  $sms->send_sms($config['user'], $config['pass'], $phone, $message, $config['sender'], $time, "standard");
                  return true;
            }elseif($gateway == "thsms") {
                  $config = config('sms_config_thsms');
                  $config = json_decode($config, true);

                  include_once __DIR__ . "/lib/sms/thsms.class.php";
                  $sms = new thsms();
                  
                  $sms->username   = $config['user'];
                  $sms->password   = $config['pass'];
                  
                  $a = $sms->getCredit();
                  
                  $b = $sms->send($config['sender'], $phone, $message);
                  return $b;
            }elseif($gateway == "molink") {
                  $config = config('sms_config_molinksms');
                  $config = json_decode($config, true);

                  include_once __DIR__ . "/lib/sms/molink.class.php";
                  $sms = new sms();
                  $b = $sms->send($config['user'], $config['pass'], $config['sender'], $phone, $message);
                  return $b;
            }else{
                  return false;
            }
      }

      public function credit() {
            global $engine;

            $gateway = config('sms_gateway');
            if($gateway == "thaibulk") {
                  $config = config('sms_config_thaibulk');
                  $config = json_decode($config, true);

                  include_once __DIR__ . "/lib/sms/thaibulksms.class.php";
                  $sms = new sms;

                  $credit = $sms->check_credit($config['user'], $config['pass']);
                  $credit = str_replace("จำนวนเครดิตคงเหลือ ", "", $credit);
                  $credit = str_replace(" เครดิต", "", $credit);
                  $credit = floatval($credit);
                  return $credit;
            }elseif($gateway == "thsms") {
                  $config = config('sms_config_thsms');
                  $config = json_decode($config, true);

                  include_once __DIR__ . "/lib/sms/thsms.class.php";
                  $sms = new thsms();
                  
                  $sms->username   = $config['user'];
                  $sms->password   = $config['pass'];
                  
                  $a = $sms->getCredit();
                  if($a[0]) {
                        return floatval($a[1]);
                  }else{
                        return false;
                  }
            }else{
                  return false;
            } 
      }
}