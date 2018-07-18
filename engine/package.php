<?php

class PHUMIN_STUDIO_Package {

      public function getAll() {
            global $engine;
            
            $a = query("SELECT `p`.*, SUM(IF(`v`.`package` = `p`.`id`, 1, 0)) as `vps`
            FROM `{$engine->config['prefix']}package` as `p`
            LEFT JOIN `{$engine->config['prefix']}vps` as `v` ON `p`.`id` = `v`.`package`
            WHERE `p`.`will_delete` = 0
            GROUP BY `p`.`id`
            ORDER BY `p`.`order` ASC, `p`.`price` ASC, `p`.`id` ASC;
            ")->fetchAll(PDO::FETCH_ASSOC);
            $r = [];
            foreach($a as $p) {
                  $p['soon'] = $this->getSoonest($p);
                  $r[] = $p;
            }
            
            return $r;
      }

      public function getSoonest($package) {
            global $engine;

            $host = $engine->host->get(true);
            $soon_time = -1;
            foreach($host as $h) {
                  $vs = query("SELECT
                  `p`.`cpu`,
                  `p`.`ram`,
                  `v`.`host`,
                  `v`.`expire`,
                  `v`.`delete`
                  FROM `{$engine->config['prefix']}vps` AS `v` JOIN `{$engine->config['prefix']}xen_vm` AS `p`
                  ON `v`.`ref` = `p`.`opaqueRef`
                  WHERE `v`.`host` = ?
                  ORDER BY `v`.`expire` ASC", [$h['id']])->fetchAll(PDO::FETCH_ASSOC);
                  //var_dump($vs);

                  // Check current free resources
                  if($h['ram_free'] >= $package['ram'] * 1024 * 1024 * 1024) {
                        //echo $package['name'] . ":{$h['ram_free']}," . ($package['ram'] * 1024 * 1024 * 1024);
                        $soon_time = -1;
                  }

                  // Check fastest ip that will be available
                  $ip_available = query("SELECT * FROM `{$engine->config['prefix']}ip` WHERE `host` = ? AND `useby` = ?", [$h['id'], 0])->rowCount();
                  
                  if($ip_available == 0) {
                        $v = $vs[0];
                        if($v['delete'] == "") {
                              $expire = $v['expire'] + config('time_before_remove');
                        }else{
                              $expire = $v['delete'];
                        }
                        if($soon_time == -1 || $expire < $soon_time) {
                              $soon_time = $expire;
                              //var_dump([$package, $soon_time]);
                        }
                  }

                  // Check soon free resources
                  $soon_ram = 0;
                  foreach($vs as $v) {
                        $soon_ram += $v['ram'];// * 1024 * 1024 * 1024;

                        if($v['delete'] == "") {
                              $expire = $v['expire'] + config('time_before_remove');
                        }else{
                              $expire = $v['delete'];
                        }

                        if($h['ram_free'] + $soon_ram >= $package['ram'] * 1024 * 1024 * 1024) {
                              //var_dump([$package['name'], $h['ram_free'], $soon_ram, $h['ram_free'] + $soon_ram, ">", $package['ram'] * 1024 * 1024 * 1024]);
                        } else {
                              if($soon_time == -1 || $expire < $soon_time) {
                                    $soon_time = $expire;
                              }
                        }
                  }
            }

            return $soon_time;
      }

      public function get($id) {
            global $engine;

            return query("SELECT * FROM `{$engine->config['prefix']}package` WHERE `id` = ?;", [$id])->fetch(PDO::FETCH_ASSOC);
      }

      public function add ($data) {
            global $engine;

            query("INSERT INTO `{$engine->config['prefix']}package` (`name`, `cpu`, `ram`, `disk`, `time`, `price`) VALUES (?,?,?,?,?,?);", [$data['name'], $data['cpu'], $data['ram'], $data['disk'], $data['time'], $data['price']]);
            return $this->getAll();
      }

      public function edit ($data) {
            global $engine;

            query("UPDATE `{$engine->config['prefix']}package` SET `name` = ?, `cpu` = ?, `ram` = ?, `disk` = ?, `time` = ?, `price WHERE `id` = ?;", [$data['name'], $data['price'], $data['maxclients'], $data['day'], $data['id']]);
            return $this->getAll();
      }

      public function delete ($id) {
            global $engine;

            /*if($engine->server->countByPlan($id) == 0) {
                  query("DELETE FROM `{$engine->config['prefix']}package` WHERE `id` = ?;", [$id]);
            }else{
                  
            }*/
            
            query("UPDATE `{$engine->config['prefix']}package` SET `will_delete` = 1 WHERE `id` = ?;", [$id]);
            return $this->getAll();
      }
}