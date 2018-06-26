<?php
class PHUMIN_STUDIO_VPS {
      
      public function get($id = null, $full = false) {
            global $engine;

            $vps = query("SELECT `{$engine->config['prefix']}vps`.*, `{$engine->config['prefix']}xen_vm`.`powerState` as `state` FROM `{$engine->config['prefix']}vps` INNER JOIN `{$engine->config['prefix']}xen_vm` ON `{$engine->config['prefix']}vps`.`ref` = `{$engine->config['prefix']}xen_vm`.`opaqueRef` WHERE `{$engine->config['prefix']}vps`.`id` = ?;", [$id])->fetch(PDO::FETCH_ASSOC);
            $package = $engine->package->get($vps['package']);
            if ($vps) {
                  if ($full) {
                        return $vps;
                  } else {
                        return [
                              "id" => $vps['id'],
                              "name" => $vps['name'],
                              "cpu" => $package['cpu'],
                              "use_cpu" => 100,
                              "ram" => $package['ram'],
                              "use_ram" => $package['ram'],
                              "disk" => $package['disk'],
                              "created" => $vps['created'],
                              "expire" => $vps['expire'],
                              "delete" => $vps['delete'],
                              "status" => $vps['status'],
                              "promotion" => $engine->code->getPromotionById($vps['promo_code']),
                              "state" => strtolower($vps['state']),
                              "package" => $engine->package->get($vps['package']),
                              "auto_expand" => $vps['auto_expand'],
                        ];
                  }
            } else {
                  return false;
            }
      }

      public function getAll($owner = null, $full = false) {
            global $engine;
            if($owner === null) {
                  $owner = $engine->user->id;
            }

            if($engine->user->id == $owner) {
                  $vs = query("SELECT `id` FROM `{$engine->config['prefix']}vps` WHERE `owner` = ?;", [$owner])->fetchAll(PDO::FETCH_ASSOC);
                  $vms = [];
                  foreach($vs as $v) {
                        $vms[] = $this->get($v['id'], $full);
                  }
                  return $vms;
            } else {
                  if($engine->user->admin) {
                        $vs = query("SELECT `id` FROM `{$engine->config['prefix']}vps`;")->fetchAll(PDO::FETCH_ASSOC);
                        $vms = [];
                        foreach($vs as $v) {
                              $vms[] = $this->get($v['id'], $full);
                        }
                        return $vms;
                  }else{
                        return false;
                  }
            }

      }

      public function countByPlan ($plan) {
            global $engine;

            return query("SELECT * FROM `{$engine->config['prefix']}vps` WHERE `plan` = ?;", [$plan])->rowCount();
      }

      public function create ($package, $host_type, $host, $template, $promo_code = "") {
            global $engine;

            $package = $engine->package->get($package);
            $price = $package['price'];
            // Calculate price
            if($promo_code != "") {
                  $promotion = $engine->code->check("discount", $promo_code, ["price" => $package['price']]);
                  if($promotion && !is_string($promotion)) {
                        if($promotion['type'] == "percent") {
                              $price = $package['price'] * (1 - ($promotion['amount'] / 100));
                        }elseif($promotion['type'] == "amount") {
                              $price = $package['price'] - $promotion['amount'];
                        }
                        // Redeem code!!
                        $engine->code->redeem($promo_code);
                        // Already redeem code!!
                        $promotion = $engine->code->getByCode($promo_code);
                        if($engine->code->getUseable($promotion['id']) == 0) {
                              // ถ้าโค้ดส่วนลดใช้ไม่ได้อีก จะไม่บันทึกลงไปกับข้อมูล VPS
                              $promotion = [
                                    'id' => 0,
                                    'type' => '',
                              ];
                        }
                  } else {
                        $promotion = [
                              'id' => 0,
                              'type' => '',
                        ];
                  }
            } else {
                  $promotion = [
                        'id' => 0,
                        'type' => '',
                  ];
            }

            // Check user credit
            if($engine->user->credit < $price) {
                  return false;
            }
            // Take credit
            query("UPDATE `{$engine->config['prefix']}user` SET `credit` = `credit` - ? WHERE `id` = ?;", [$price, $engine->user->id]);

            $ip = [];
            if($host_type == "a") {
                  $type = "xen";
                  $ip = query("SELECT * FROM `{$engine->config['prefix']}ip` WHERE `host` = ? AND `useby` = ? LIMIT 1;", [$host, 0])->fetch(PDO::FETCH_ASSOC);
                  $host = query("SELECT * FROM `{$engine->config['prefix']}xen_host` WHERE `id` = ?;", [$host])->fetch(PDO::FETCH_ASSOC);
                  $template = query("SELECT * FROM `{$engine->config['prefix']}xen_template` WHERE `id` = ?;", [$template])->fetch(PDO::FETCH_ASSOC);
            } else {
                  return false;
            }
            // Take ip
            query("UPDATE `{$engine->config['prefix']}ip` SET `useby` = ? WHERE `id` = ?;", [-1, $ip['id']]);

            // Create vm
            $res = $engine->xenserver->clone_vm($engine->user, $package, $host, $template, $ip);
            // Calculate expire date
            $now = time();
            if(config('round_day') == 0) {
                  $expire = $now + ($package['time'] * 86400);
            }else{
                  $_m = 86400 - (($now + ($package['time'] * 86400)) % 86400); // หารเอาเศษวินาทีที่เหลือของวัน
                  $_m = $_m - (3600 * 7); // ลบออก 7 ชม ให้กลายเป็นเวลาตาม UTC
                  $expire = $now + ($package['time'] * 86400) + $_m - 1; // ลบ 1 เพื่อให้เวลาเป็น 23:59:59
            }
            
            // Check that create vps with refer or not
            if($promotion['type'] == "refer") {
                  // Refer
                  $data = [$engine->user->id, $package['id'], $promotion['id'], $ip['ip'], $type, $host['id'], $res['opaqueRef'], json_encode($template), $now, $expire, $now, 99, $promotion['promotion']['referer']];
                  query("INSERT INTO `{$engine->config['prefix']}vps` (`owner`, `package`, `promo_code`, `name`, `type`, `host`, `ref`, `template`, `created`, `expire`, `expanded`, `status`, `refer`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?);", $data);
                  // Give credit to referer
                  $refer_payout = $price * (1 - (config('refer_share') / 100));
                  $refer_id = $promotion['promotion']['referer'];
                  query("UPDATE `{$engine->config['prefix']}user` SET `credit` = `credit` + ? WHERE `id` = ?;", [$refer_payout, $refer_id]);
                  // Create payment to referer
                  query("INSERT INTO `{$engine->config['prefix']}payment` (`owner`,`amount`,`gateway`,`transaction`,`data`,`debug`,`status`,`time`) VALUES (?,?,?,?,?,?,?,?);", [$refer_id, $refer_payout, 'refer', 'new', json_encode($data), json_encode($data), 'success', time()]);
            } else {
                  // No refer
                  $data = [$engine->user->id, $package['id'], $promotion['id'], $ip['ip'], $type, $host['id'], $res['opaqueRef'], json_encode($template), $now, $expire, $now, 99];
                  query("INSERT INTO `{$engine->config['prefix']}vps` (`owner`, `package`, `promo_code`, `name`, `type`, `host`, `ref`, `template`, `created`, `expire`, `expanded`, `status`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?);", $data);
            }
            // Get vps id
            $vps_id = $engine->sql->lastInsertId();
            // Update ip
            query("UPDATE `{$engine->config['prefix']}ip` SET `useby` = ? WHERE `id` = ?;", [$vps_id, $ip['id']]);

            // Invoice
            query("INSERT INTO `{$engine->config['prefix']}invoice` (`owner`, `owner_detail`, `product`, `date`) VALUES (?,?,?,?);", [$engine->user->id, json_encode([
                  'email' => $engine->user->email,
                  'name' => $engine->user->name,
                  'phone' => $engine->user->phone,
                  'address' => $engine->user->address,
                  'company' => $engine->user->company,
            ]), json_encode([
                  [
                        "id" => $vps_id,
                        "name" => $ip['ip'],
                        "cpu" => $package['cpu'],
                        "ram" => $package['ram'],
                        "disk" => $package['disk'],
                        "promotion" => $promotion,
                        "package" => $package
                  ],
            ]), time()]);
            
            return [
                  "last" => $vps_id,
                  "vm" => $this->getAll(),
            ];
      }

      public function update_status($host, $opaqueRef, $status) {
            global $engine;

            query("UPDATE `{$engine->config['prefix']}vps` SET `status` = ? WHERE `host` = ? AND `ref` = ?;", [$status, $host, $opaqueRef]);
            return true;
      }

      public function start($id) {
            global $engine;

            $vps = $this->get($id, true);
            $host = query("SELECT * FROM `{$engine->config['prefix']}xen_host` WHERE `id` = ?;", [$vps['host']])->fetch(PDO::FETCH_ASSOC);
            $res = $engine->xenserver->start_vm($host, $vps['ref']);

            if($res) {
                  return $this->getAll();
            }else{
                  return false;
            }
      }

      public function shutdown($id) {
            global $engine;

            $vps = $this->get($id, true);
            $host = query("SELECT * FROM `{$engine->config['prefix']}xen_host` WHERE `id` = ?;", [$vps['host']])->fetch(PDO::FETCH_ASSOC);
            $res = $engine->xenserver->shutdown_vm($host, $vps['ref']);

            if($res) {
                  return $this->getAll();
            }else{
                  return false;
            }
      }

      public function pause($id) {
            global $engine;

            $vps = $this->get($id, true);
            $host = query("SELECT * FROM `{$engine->config['prefix']}xen_host` WHERE `id` = ?;", [$vps['host']])->fetch(PDO::FETCH_ASSOC);
            $res = $engine->xenserver->pause_vm($host, $vps['ref']);

            if($res) {
                  return $this->getAll();
            }else{
                  return false;
            }
      }

      public function unpause($id) {
            global $engine;

            $vps = $this->get($id, true);
            $host = query("SELECT * FROM `{$engine->config['prefix']}xen_host` WHERE `id` = ?;", [$vps['host']])->fetch(PDO::FETCH_ASSOC);
            $res = $engine->xenserver->unpause_vm($host, $vps['ref']);

            if ($res) {
                  return $this->getAll();
            } else {
                  return false;
            }
      }

      public function toggle_auto_expand($id) {
            global $engine;

            $vps = $this->get($id, true);
            if ($vps) {
                  if ($vps['auto_expand'] == 1) {
                        $value = 0;
                  } else {
                        $value = 1;
                  }
                  query("UPDATE `{$engine->config['prefix']}vps` SET `auto_expand` = ? WHERE `id` = ?;", [$value, $id]);
                  return true;
            } else {
                  return false;
            }
      }

      public function setip($id) {
            global $engine;
            
            $vps = $this->get($id, true);
            $host = query("SELECT * FROM `{$engine->config['prefix']}xen_host` WHERE `id` = ?;", [$vps['host']])->fetch(PDO::FETCH_ASSOC);
            $ip = query("SELECT * FROM `{$engine->config['prefix']}ip` WHERE `host` = ? AND `useby` = ?;", [$host['id'], $vps['id']])->fetch(PDO::FETCH_ASSOC);
            $res = $engine->xenserver->setip_vm($host, $vps['ref'], $ip['ip'], $ip['subnet'], $ip['gateway']);

            return true;
      }

      public function remove($id) {
            global $engine;

            $vps = $this->get($id, true);
            $host = query("SELECT * FROM `{$engine->config['prefix']}xen_host` WHERE `id` = ?;", [$vps['host']])->fetch(PDO::FETCH_ASSOC);
            $res = $engine->xenserver->remove_vm($host, $vps['ref']);

            if($res) {
                  query("DELETE FROM `{$engine->config['prefix']}vps` WHERE `id` = ?;", [$id]);
                  query("UPDATE `{$engine->config['prefix']}ip` SET `useby` = ? WHERE `useby` = ?;", [0, $id]);
                  return $res;
            }else{
                  return false;
            }
      }

      public function expand($id) {
            global $engine;

            $vps = $this->get($id);
            $vps_raw = $this->get($id, true);
            $user = $engine->user->get($vps_raw['owner']);

            $price = $vps['package']['price'];
            if($vps_raw['promo_code'] != 0) {
                  $promotion = $engine->code->getById($vps_raw['promo_code']);
                  $promotion = $engine->code->check("discount", $promotion['id'], ["price" => $vps['package']['price']]);
                  if($promotion) {
                        if($promotion['type'] == "percent") {
                              $price = $vps['package']['price'] * (1 - ($promotion['amount'] / 100));
                        }elseif($promotion['type'] == "amount") {
                              $price = $vps['package']['price'] - $promotion['amount'];
                        }
                        $promotion = $engine->code->redeem($promo_code);
                        $promotion = $engine->code->getByCode($promo_code);
                  } else {
                        $promotion = [
                              'id' => 0
                        ];
                  }
            } else {
                  $promotion = [
                        'id' => 0
                  ];
            }

            if($user['credit'] >= $price) {
                  $expire = $vps['expire'] + $vps['package']['time'] * 86400;
                  query("UPDATE `{$engine->config['prefix']}user` SET `credit` = `credit` - ? WHERE `id` = ?;", [$price, $vps_raw['owner']]);
                  query("UPDATE `{$engine->config['prefix']}vps` SET `expire` = ?,`expanded` = ?, `delete` = ?, `notif` = ?, `status` = ? WHERE `id` = ?;", [$expire, time(), "", 0, 0, $id]);

                  if ($vps_raw['refer'] != 0) {
                        // Give credit to referer
                        $refer_payout = $price * (1 - (config('refer_share') / 100));
                        $refer_id = $vps_raw['refer'];
                        query("UPDATE `{$engine->config['prefix']}user` SET `credit` = `credit` + ? WHERE `id` = ?;", [$refer_payout, $refer_id]);
                        // Create payment to referer
                        query("INSERT INTO `{$engine->config['prefix']}payment` (`owner`,`amount`,`gateway`,`transaction`,`data`,`debug`,`status`,`time`) VALUES (?,?,?,?,?,?,?,?);", [$refer_id, $refer_payout, 'refer', 'expand', json_encode($vps_raw), json_encode($vps_raw), 'success', time()]);
                  }

                  // Invoice
                  query("INSERT INTO `{$engine->config['prefix']}invoice` (`owner`, `owner_detail`, `product`, `date`) VALUES (?,?,?,?);", [$user['id'], json_encode([
                        'email' => $user['email'],
                        'name' => $user['name'],
                        'phone' => $user['phone'],
                        'address' => $user['address'],
                        'company' => $user['company'],
                  ]), json_encode([
                        [
                              "id" => $vps['id'],
                              "name" => $vps['name'],
                              "cpu" => $vps['cpu'],
                              "ram" => $vps['ram'],
                              "disk" => $vps['disk'],
                              "promotion" => $vps['promotion'],
                              "package" => $vps['package']
                        ],
                  ]), time()]);

                  // Unpause VPS
                  if($vps['state'] == "paused") {
                        $engine->vps->unpause($id);
                  }
                  return true;
            }else{
                  return false;
            }
      }

      public function getExpire($time_before = 0, $notify = null) {
            global $engine;

            if($time_before == 0) {
                  return query("SELECT * FROM `{$engine->config['prefix']}vps` WHERE `expire` < ? AND `status` = ?;", [time(), 0])->fetchAll(PDO::FETCH_ASSOC);
            }else{
                  if($notify === null) {
                        return query("SELECT * FROM `{$engine->config['prefix']}vps` WHERE `expire` - ? < ?;", [$time_before, time()])->fetchAll(PDO::FETCH_ASSOC);
                  }else{
                        return query("SELECT * FROM `{$engine->config['prefix']}vps` WHERE `expire` - ? < ? AND `notif` = ?;", [$time_before, time(), $notify])->fetchAll(PDO::FETCH_ASSOC);
                  }
            }
      }

      public function getDelete() {
            global $engine;

            return query("SELECT * FROM `{$engine->config['prefix']}vps` WHERE `delete` < ? AND `delete` <> ? AND `status` > ?;", [time(), "", 0])->fetchAll(PDO::FETCH_ASSOC);
      }

      public function console($id) {
            global $engine;

            $vps = $this->get($id, true);
            $host = query("SELECT * FROM `{$engine->config['prefix']}xen_host` WHERE `id` = ?;", [$vps['host']])->fetch(PDO::FETCH_ASSOC);

            $res = $engine->xenserver->console_vm($host, $vps['ref']);

            if($res) {
                  return $res;
            }else{
                  return false;
            }
      }
}