<?php

class PHUMIN_STUDIO_License {

      public $protocal = "http";
      public $server = "43.229.135.77";
      public $port = "16425";
      
      public function get_detail() {
            global $engine;

            $data = [
                  'license_key' => config('license_key'),
            ];
            $ch = curl_init("{$this->protocal}://{$this->server}:{$this->port}/get_license");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

            // execute!
            $response = curl_exec($ch);

            // close the connection, release resources used
            curl_close($ch);

            return json_decode($response, true);
      }
}