<?php

class sms {

      public function send ($username, $password, $sender, $phone, $message) {

            preg_match("/[0]{1}([0-9]{7,})/", $phone, $matches);
            if(count($matches) == 2) {
                  $phone = "66" . $matches[1];

                  $ch = curl_init();
                  curl_setopt($ch, CURLOPT_URL, "http://203.146.186.186/molinkservice2017/sms.asmx/SingleSMS");
                  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array(
                        "username" => $username,
                        "password" => $password,
                        "txtMobile" => $phone,
                        "sender" => $sender,
                        "txtSMS" => $message
                  )));
                  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            
                  $response  = curl_exec($ch);
                  $lastError = curl_error($ch);
                  $lastReq   = curl_getinfo($ch);
                  curl_close($ch);
            
                  return $response;
            } else {
                  return false;
            }
      }
}