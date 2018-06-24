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

            $vs = query("SELECT
            `p`.`cpu`,
            `p`.`ram`,
            `p`.`disk`,
            `v`.`expire`,
            `v`.`delete`
            FROM `{$engine->config['prefix']}vps` AS `v` JOIN `{$engine->config['prefix']}package` AS `p`
            ON `v`.`package` = `p`.`id`
            ORDER BY `v`.`expire` ASC")->fetchAll(PDO::FETCH_ASSOC);

            $host = $engine->host->get(true);
            $soon_time = -1;
            foreach($host as $h) {
                  foreach($vs as $v) {
                        $h['ram_free'] += $v['ram'] * 1024 * 1024 * 1024;
                        if($h['ram_free'] >= $package['ram'] * 1024 * 1024 * 1024) {
                              if($v['delete'] == "") {
                                    $expire = $v['expire'] + config('keep_before_remove');
                                    if($soon_time == -1 || $soon_time > $expire) {
                                          $soon_time = $expire;
                                    }
                              }else{
                                    if($soon_time == -1 || $soon_time > $v['delete']) {
                                          $soon_time = $v['delete'];
                                    }
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