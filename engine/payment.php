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

      public function history_invoice($page = 1, $per_page = 7, $owner = null) {
            global $engine;

            $page_amount = 0;
            $page_data = [];
            if($owner === null) {
                  $page_amount = query("SELECT * FROM `{$engine->config['prefix']}invoice` ORDER BY `date` DESC;")->rowCount();
                  $page_amount = ceil($page_amount / $per_page);

                  $page_start = ($per_page * $page - $per_page);
                  $is = query("SELECT * FROM `{$engine->config['prefix']}invoice` ORDER BY `date` DESC LIMIT {$page_start}, {$per_page};")->fetchAll(PDO::FETCH_ASSOC);
                  foreach($is as $i) {
                        $page_data[] = [
                              "id" => $i['id'],
                              "owner" => json_decode($i['owner_detail'], true),
                              "product" => json_decode($i['product'], true),
                              "time" => $i['date'],
                        ];
                  }
            } else {
                  $page_amount = query("SELECT * FROM `{$engine->config['prefix']}invoice` WHERE `owner` = ? ORDER BY `date` DESC;", ['success'])->rowCount();
                  $page_amount = ceil($page_amount / $per_page);

                  $page_start = ($per_page * $page - $per_page);
                  $is = query("SELECT * FROM `{$engine->config['prefix']}invoice` WHERE `owner` = ? ORDER BY `date` DESC LIMIT {$page_start}, {$per_page};", [$owner])->fetchAll(PDO::FETCH_ASSOC);
                  foreach($is as $i) {
                        $page_data[] = [
                              "id" => $i['id'],
                              "owner" => json_decode($i['owner_detail'], true),
                              "product" => json_decode($i['product'], true),
                              "time" => $i['date'],
                        ];
                  }
            }

            return [
                  "page_amount" => $page_amount,
                  "page_current" => $page,
                  "page_data" => $page_data,
            ];
      }

      public function summary_month() {
            global $engine;

            $hs = query("SELECT 
                  MONTH(FROM_UNIXTIME(`time`)) as `month`, 
                  YEAR(FROM_UNIXTIME(`time`)) as `year`, 
                  SUM(`amount`) as `total`
                  FROM `{$engine->config['prefix']}payment`
                  WHERE `status` = ? AND `gateway` <> ?
                  GROUP BY 1,2;", ['success', 'refer'])->fetchAll(PDO::FETCH_ASSOC);
            $current_month = date('m');
            $current_year = date('Y');
            $month = [1 => 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
            $data = [
                  "label" => [],
                  "data" => [],
            ];

            for($i = 1; $i <= 12; $i++) {
                  if($i <= $current_month) {
                        $data['label'][] = $month[$i] . " " . $current_year;
                        $amount = 0;
                        foreach($hs as $h) {
                              if($h['month'] == $i && $h['year'] == $current_year) {
                                    $amount = $h['total'];
                                    break;
                              }
                        }
                        $data['data'][] = (float) $amount;
                  } else {
                        $data['label'][] = $month[$i] . " " . ($current_year - 1);
                        $amount = 0;
                        foreach($hs as $h) {
                              if($h['month'] == $i && $h['year'] == $current_year - 1) {
                                    $amount = $h['total'];
                                    break;
                              }
                        }
                        $data['data'][] = (float) $amount;
                  }
            }
            return $data;
      }

      public function history($page = 1, $per_page = 7, $owner = null) {
            global $engine;

            $page_amount = 0;
            $page_data = [];
            $field = "`p`.`id`, `p`.`amount`, `p`.`gateway`, `p`.`status`, `p`.`time`";
            if($engine->user->admin) {
                  $field = "`p`.*";
            }
            if($owner === null) {
                  $page_amount = query("SELECT {$field} FROM `{$engine->config['prefix']}payment` AS `p` WHERE `p`.`status` = ? ORDER BY `p`.`time` DESC;", ['success'])->rowCount();
                  $page_amount = ceil($page_amount / $per_page);

                  $page_start = ($per_page * $page - $per_page);
                  $page_data = query("SELECT {$field}, `u`.`id`, `u`.`email`, `u`.`name`, `u`.`phone`, `u`.`address`, `u`.`company`, `u`.`verify_email`, `u`.`verify_phone` FROM `{$engine->config['prefix']}payment` AS `p` JOIN `{$engine->config['prefix']}user` AS `u` ON `p`.`owner` = `u`.`id` WHERE `p`.`status` = ? ORDER BY `p`.`time` DESC LIMIT {$page_start}, {$per_page};", ['success'])->fetchAll(PDO::FETCH_ASSOC);
            } else {
                  $page_amount = query("SELECT {$field} FROM `{$engine->config['prefix']}payment` AS `p` WHERE `p`.`owner` = ? AND `p`.`status` = ? ORDER BY `p`.`time` DESC;", [$owner, 'success'])->rowCount();
                  $page_amount = ceil($page_amount / $per_page);

                  $page_start = ($per_page * $page - $per_page);
                  $page_data = query("SELECT {$field}, `u`.`id`, `u`.`email`, `u`.`name`, `u`.`phone`, `u`.`address`, `u`.`company`, `u`.`verify_email`, `u`.`verify_phone` FROM `{$engine->config['prefix']}payment` AS `p` JOIN `{$engine->config['prefix']}user` AS `u` ON `p`.`owner` = `u`.`id` WHERE `p`.`owner` = ? AND `p`.`status` = ? ORDER BY `p`.`time` DESC LIMIT {$page_start}, {$per_page};", [$owner, 'success'])->fetchAll(PDO::FETCH_ASSOC);
            }

            return [
                  "page_amount" => $page_amount,
                  "page_current" => $page,
                  "page_data" => $page_data,
            ];
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
                                    'amount' => $transaction['amount'] * 0.85,
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