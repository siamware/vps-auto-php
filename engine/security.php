<?php

class PHUMIN_STUDIO_Security {

    public function hashPassword($pass) {
        $salt = hash('crc32', time() . $pass);
        $hash = hash('sha256', $pass);
        return $salt . '&' . $hash;
    }

    public function checkPassword($pass, $hash) {
        $h = explode("&", $hash);
        if (strlen($h[0]) == 8) {
            $pass = hash('sha256', $pass);
            return $h[1] == $pass;
        } else {
            return false;
        }
    }

    public function get2FA($secret, $count) {
        $bin_counter = pack('N*', 0, $count);
        $hash = hash_hmac('sha1', $bin_counter, $secret, true);
        $offset = ord($hash[19]) & 0xf;
        $temp = unpack('N', substr($hash, $offset, 4));
        return str_pad(substr($temp[1] & 0x7fffffff, -6), 6, '0', STR_PAD_LEFT);
    }
    
    public function TwoFApassword($debug = false) {
        global $engine;

        $time = time() - time() % 60;
        $secret = hash('sha512', $engine->config['engine_number'] . '-' . $time);

        $password = $this->get2FA($secret, floor(time() / 30));

        if($debug)
            return ['secret' => $secret, 'time' => floor(time() / 30), 'password' => $password]; 
        else
            return $password;
    }
}
