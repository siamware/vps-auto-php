<?php
class PHUMIN_STUDIO_Plan {

      public function get ($id = null) {
            global $engine;

            if($id === null)
                  $plan = query("SELECT * FROM `{$engine->config['prefix']}plan` WHERE `will_delete` = 0;")->fetchAll(PDO::FETCH_ASSOC);
            else
                  $plan = query("SELECT * FROM `{$engine->config['prefix']}plan` WHERE `id` = ? AND `will_delete` = 0;", [$id])->fetch(PDO::FETCH_ASSOC);
            return $plan;
      }

      public function add ($data) {
            global $engine;

            query("INSERT INTO `{$engine->config['prefix']}plan` (`name`, `price`, `maxclients`, `day`) VALUES (?,?,?,?);", [$data['name'], $data['price'], $data['maxclients'], $data['day']]);
            return $this->get();
      }

      public function edit ($data) {
            global $engine;

            query("UPDATE `{$engine->config['prefix']}plan` SET `name` = ?, `price` = ?, `maxclients` = ?, `day` = ? WHERE `id` = ?;", [$data['name'], $data['price'], $data['maxclients'], $data['day'], $data['id']]);
            return $this->get();
      }

      public function delete ($id) {
            global $engine;

            if($engine->server->countByPlan($id) == 0) {
                  query("DELETE FROM `{$engine->config['prefix']}plan` WHERE `id` = ?;", [$id]);
            }else{
                  query("UPDATE `{$engine->config['prefix']}plan` SET `will_delete` = 1 WHERE `id` = ?;", [$id]);
            }
            return $this->get();
      }
}