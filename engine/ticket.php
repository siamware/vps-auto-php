<?php

class PHUMIN_STUDIO_Ticket {

      public function get_room($uid = null) {
            global $engine;

            $r = [];
            if($uid === null) {
                  $rooms = query("SELECT * FROM `{$engine->config['prefix']}ticket` ORDER BY `opened` DESC")->fetchAll(PDO::FETCH_ASSOC);
            }else{
                  $rooms = query("SELECT * FROM `{$engine->config['prefix']}ticket` WHERE `from` = ? ORDER BY `opened` DESC", [$uid])->fetchAll(PDO::FETCH_ASSOC);
            }

            foreach($rooms as $room) {
                  if($engine->user->admin) {
                        $uid = $engine->user->id;
                  }

                  $r[] = [
                        "id" => $room['id'],
                        "category" => $room['category'],
                        "title" => $room['title'],
                        "created" => $room['opened'],
                        "closed" => $room['closed'],
                        "unread" => query("SELECT * FROM `{$engine->config['prefix']}ticket` WHERE `id` = ? AND `from` = ?;", [$room['id'], $uid])->rowCount(),
                        "lock" => $room['lock'] == 1,
                  ];
            }

            return $r;
      }

      public function get_chat($roomId) {
            global $engine;

            return query("SELECT * FROM `{$engine->config['prefix']}ticket_chat` WHERE `ticket` = ? ORDER BY `time` ASC", [$roomId])->fetchAll(PDO::FETCH_ASSOC);
      }

      public function get_chats($uid = null) {
            global $engine;

            $r = [];
            $rooms = $this->get_room($uid);
            foreach($rooms as $room) {
                  $r[$room['id']] = $this->get_chat($room['id']);
            }
            return $r;
      }

      public function read($ticket) {
            global $engine;

            query("UPDATE `{$engine->config['prefix']}ticket_chat` SET `read` = ? WHERE `time` < ? AND `read` = ? AND `owner` <> ?", [time(), time(), "", $engine->user->id]);
            return [
                  "chat" => $this->get_chats($engine->user->id),
            ];
      }

      public function open($category, $title) {
            global $engine;

            query("INSERT INTO `{$engine->config['prefix']}ticket` (`from`,`title`,`category`,`opened`) VALUES (?,?,?,?);", [$engine->user->id, $title, $category, time()]);
            $id = $engine->sql->lastInsertId();
            
            query("INSERT INTO `{$engine->config['prefix']}ticket_chat` (`ticket`,`owner`,`owner_name`,`message`,`time`) VALUES (?,?,?,?,?);", [$id, $engine->user->id, $engine->user->name, $title, time()]);

            return [
                  "room" => $this->get_room($engine->user->id),
                  "chat" => $this->get_chats($engine->user->id),
            ];
      }

      public function chat($ticket, $message) {
            global $engine;

            query("INSERT INTO `{$engine->config['prefix']}ticket_chat` (`ticket`,`owner`,`owner_name`,`message`,`time`) VALUES (?,?,?,?,?);", [$ticket, $engine->user->id, $engine->user->name, $message, time()]);
            $this->read($ticket);

            return [
                  "room" => $this->get_room($engine->user->id),
                  "chat" => $this->get_chats($engine->user->id),
            ];
      }
}