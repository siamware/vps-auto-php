<?php
class PHUMIN_STUDIO_Payment {

      public function gateway() {
            global $engine;
            $tm_gw = config('truemoney_gateway');
            $tm_tmpay = json_decode(config('truemoney_tmpay'), true);
            $tm_2wallet = json_decode(config('truemoney_2wallet'), true);

            return [
                  'truemoney' => [
                        'select' => $tm_gw,
                        'config' => [
                              'tmpay' => $tm_tmpay,
                              '2wallet' => $tm_2wallet,
                        ]
                  ],
                  'wallet' => false
            ];
      }

      public function history($gateway = null, $owner = null) {
            global $engine;

            if($gateway === null || $gateway == "all") {
                  $r = [];
                  $his = query("SELECT * FROM `{$engine->config['prefix']}payment` ORDER BY `time` DESC;")->fetchAll(PDO::FETCH_ASSOC);
                  foreach($his as $k => $h){
                        $r[$k] = $h;
                        $r[$k]['owner'] = $engine->user->get($h['owner']);
                  }
                  return $r;
            }else{
                  if($owner === null)
                        $owner = $engine->user->id;
                  if($gateway == "truemoney")
                        $gateway = config('truemoney_gateway');
                  $his = query("SELECT * FROM `{$engine->config['prefix']}payment` WHERE `owner` = ? AND `payment` = ? ORDER BY `time` DESC;", [$owner, $gateway])->fetchAll(PDO::FETCH_ASSOC);
                  return $his;
            }
      }

      public function truemoney($number) {
            global $engine;

            $g = config('truemoney_gateway');
            if($g == "tmpay") {
                  return $this->_tmpay($number);
            }elseif($g == "2wallet"){
                  return $this->_2wallet($number);
            }
      }

      public function truemoney_check($id) {
            global $engine;

            $q = query("SELECT * FROM `{$engine->config['prefix']}payment` WHERE `id`=?;", [$id]);
            if($q->rowCount() == 0) {
                  return [
                        'success' => false,
                        'error' => 'unknow',
                  ];
            }else{
                  $transaction = $q->fetch(PDO::FETCH_ASSOC);
                  if($transaction['status'] == "success") {
                        return [
                              'success' => true,
                              'data' => [
                                    'amount' => $amount * 0.85,
                                    'user' => $engine->user->get($transaction['owner']),
                              ],
                        ];
                  }elseif($transaction['status'] == "used") {
                        return [
                              'success' => false,
                              'error' => 'used',
                        ];
                  }elseif($transaction['status'] == "pending") {
                        return [
                              'success' => false,
                        ];
                  }else{
                        return [
                              'success' => false,
                              'error' => 'unknow',
                        ];
                  }
            }
      }

      public function truewallet($transaction) {
            global $engine;

            if (query("SELECT * FROM `{$engine->config['prefix']}payment` WHERE `transaction` = ?;", [$transaction])->rowCount() == 0) {
                  $q = query("SELECT * FROM `{$engine->config['prefix']}statement_tw` WHERE `transactionID` = ?;", [$transaction]);
                  if ($q->rowCount() == 1) {
                        $data = $q->fetch(PDO::FETCH_ASSOC);
                        $amount = $data['amount'];
      
                        query("INSERT INTO `{$engine->config['prefix']}payment` (`owner`,`amount`,`gateway`,`transaction`,`data`,`debug`,`status`,`time`) VALUES (?,?,?,?,?,?,?,?);", [$engine->user->id, $amount, 'truewallet', $transaction, json_encode($data), json_encode($data), 'success', time()]);
                        query("UPDATE `{$engine->config['prefix']}user` SET `credit` = `credit` + ? WHERE `id` = ?;", [$amount, $engine->user->id]);
                        
                        return [
                              'success' => true,
                              'data' => [
                                    'amount' => $amount,
                                    'user' => $engine->user->get($engine->user->id),
                              ],
                        ];
                  }else{
                        return [
                              'success' => false,
                              'error' => 'unknow',
                        ];
                  }
            }else{
                  return [
                        'success' => false,
                        'error' => 'same'
                  ];
            }
      }

      public function bank($bank, $date, $time, $amount) {
            global $engine;

            $transaction = $date . "|" . $time;
            if (query("SELECT * FROM `{$engine->config['prefix']}payment` WHERE `transaction` = ?;", [$transaction])->rowCount() == 0) {
                  if($bank == "kbank") {
                        $q = query("SELECT * FROM `{$engine->config['prefix']}statement_kbank` WHERE `date` = ? AND `time` = ? AND `in` = ?;", [$date, $time, $amount]);
                  }else{
                        return [
                              'success' => false,
                              'error' => 'bank',
                        ];
                  }
                  if ($q->rowCount() == 1) {
                        $data = $q->fetch(PDO::FETCH_ASSOC);
                        $amount = $data['in'];
      
                        query("INSERT INTO `{$engine->config['prefix']}payment` (`owner`,`amount`,`gateway`,`transaction`,`data`,`debug`,`status`,`time`) VALUES (?,?,?,?,?,?,?,?);", [$engine->user->id, $amount, 'bank|' . $bank, $transaction, json_encode($data), json_encode($data), 'success', time()]);
                        query("UPDATE `{$engine->config['prefix']}user` SET `credit` = `credit` + ? WHERE `id` = ?;", [$amount, $engine->user->id]);
                        
                        return [
                              'success' => true,
                              'data' => [
                                    'amount' => $amount,
                                    'user' => $engine->user->get($engine->user->id),
                              ],
                        ];
                  }else{
                        return [
                              'success' => false,
                              'error' => 'unknow',
                        ];
                  }
            }else{
                  return [
                        'success' => false,
                        'error' => 'same'
                  ];
            }
      }

      private function _tmpay($number, $id = null) {
            global $engine;

            $id === null ? $id = $engine->user->id : $id;
            $merchant_id = config('truemoney_tmpay_merchant');

            $url = "http://www.tmpay.net/TPG/backend.php";
            $url.= "?merchant_id=" . $merchant_id;
            $url.= "&password=" . $number;
            $url.= "&resp_url=" . $_SERVER["REQUEST_SCHEME"] . '://' . $_SERVER["SERVER_NAME"] . "/panel/payment/tmpay/tmpay.php";
    
            $result = file_get_contents($url);
            $results = explode("|", $result);

            $debug = [$url, $result];
            if ($results[0] == "SUCCEED") {
                  query("INSERT INTO `{$engine->config['prefix']}payment` (`owner`,`gateway`,`transaction`,`data`,`debug`,`status`,`time`) VALUES (?,?,?,?,?,?,?);", [$id, "tmpay", $results[1], json_encode($result), json_encode($debug), "pending", time()]);
                  $transac_id = $engine->sql->lastInsertId();
                  return [
                        "success" => true,
                        "transaction_id" => $transac_id,
                  ];
            } else {
                  return [
                        "success" => false,
                  ];
            }
      }

      public function config($config, $data){
            global $engine;

            if($data == "select"){
                  save_config('truemoney_gateway', $config);
            }else{
                  if($config == "tmpay.merchant"){
                        $c = json_decode(config('truemoney_tmpay'), true);
                        $c['merchant'] = $data;
                        save_config('truemoney_tmpay', json_encode($c));
                  }elseif($config == "2wallet.user"){
                        $c = json_decode(config('truemoney_2wallet'), true);
                        $c['user'] = $data;
                        save_config('truemoney_2wallet', json_encode($c));
                  }elseif($config == "2wallet.password"){
                        $c = json_decode(config('truemoney_2wallet'), true);
                        $c['password'] = $data;
                        save_config('truemoney_2wallet', json_encode($c));
                  }elseif($config == "2wallet.license"){
                        $c = json_decode(config('truemoney_2wallet'), true);
                        $c['license'] = $data;
                        save_config('truemoney_2wallet', json_encode($c));
                  }
            }
            return true;
      }

}