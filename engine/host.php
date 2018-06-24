<?php

class PHUMIN_STUDIO_Host_Server {

      public function get($full = false) {
            global $engine;

            $r = [];
            
            if($engine->user->admin || $full) {
                  $xen = query("SELECT `h`.*, `h`.`ram_total` - SUM(`v`.`ram`) as `ram_free`, 'a' as `type` FROM `{$engine->config['prefix']}xen_host` AS `h`, `{$engine->config['prefix']}xen_vm` AS `v` WHERE `h`.`id` = `v`.`server`")->fetchAll(PDO::FETCH_ASSOC);
            }else{
                  $xen = query("SELECT `h`.`id`, `h`.`cpu`, `h`.`ram_total` - SUM(`v`.`ram`) as `ram_free`, 'a' as `type` FROM `{$engine->config['prefix']}xen_host` AS `h`, `{$engine->config['prefix']}xen_vm` AS `v` WHERE `h`.`id` = `v`.`server`")->fetchAll(PDO::FETCH_ASSOC);
            }

            $r = array_merge($xen, $r);
            return $r;
      }

      public function ip($host) {
            global $engine;

            return query("SELECT * FROM `{$engine->config['prefix']}ip` WHERE `host` = ? ORDER BY `ip` ASC;", [$host])->fetchAll(PDO::FETCH_ASSOC);
      }

      public function ip_add($host, $ip, $subnet, $gateway) {
            global $engine;

            if(empty($host) || empty($ip) || empty($subnet) || empty($gateway)) {
                  return false;
            }
            query("INSERT INTO `{$engine->config['prefix']}ip` (`host`, `ip`, `subnet`, `gateway`, `useby`) VALUES (?,?,?,?,?);", [$host, $ip, $subnet, $gateway, 0]);
            return $this->ip($host);
      }

      public function ip_remove($host, $id) {
            global $engine;

            query("DELETE FROM `{$engine->config['prefix']}ip` WHERE `id` = ?;", [$id]);
            return $this->ip($host);
      }

      public function vm($host) {
            global $engine;

            $vps = query("SELECT * FROM `{$engine->config['prefix']}vps` WHERE `host` = ? ORDER BY `name` ASC;", [$host])->fetchAll(PDO::FETCH_ASSOC);
            foreach($vps as $k => $v) {
                  $vps[$k]['package'] = $engine->package->get($v['package']);
                  $vps[$k]['owner'] = $engine->user->get($v['owner']);
            }
            return $vps;
      }
}