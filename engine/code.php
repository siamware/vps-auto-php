<?php

class PHUMIN_STUDIO_Code {
      /*
      * Type of Promotion Code
      *     refer = Referral Code
      *     discount = Discount Code
      *     vps = Free VPS Code
      *     credit = Credit code
      */

      public function getById($id = 0) {
            global $engine;

            if($id == 0)
                  return false;
            else {
                  $c = query("SELECT * FROM `{$engine->config['prefix']}promo_code` WHERE `id` = ?;", [$id])->fetch(PDO::FETCH_ASSOC);
                  $c['condition'] = json_decode($c['condition'], true);
                  $c['promotion'] = json_decode($c['promotion'], true);
                  return $c;
            }
      }

      public function getByCode($code = "") {
            global $engine;

            if($code == "")
                  return false;
            else {
                  $c = query("SELECT * FROM `{$engine->config['prefix']}promo_code` WHERE `code` = ?;", [$code])->fetch(PDO::FETCH_ASSOC);
                  $c['condition'] = json_decode($c['condition'], true);
                  $c['promotion'] = json_decode($c['promotion'], true);
                  return $c;
            }
      }

      public function getPromotionById($id = 0) {
            global $engine;

            if($id == 0)
                  return false;
            else {
                  $code = $this->getById($id);
                  if(!$code)
                        return false;
                  else {
                        $promotion = $code['promotion'];
                        if ($code['type'] == "refer") {
                              return $promotion['discount'];
                        } elseif ($code['type'] == "discount") {
                              return $promotion;
                        }
                  }
            }
      }

      public function getUseable ($id = 0) {
            global $engine;

            if($id == 0)
                  return 0;
            else {
                  $code = $this->getById($id);
                  if(!$code)
                        return 0;
                  else {
                        $condition = $code['condition'];
                        if ($condition['user']['oneperone'] && in_array($engine->user->id, $condition['user']['used'])) {
                              return 0;
                        } else {
                              return $condition['user']['time'];
                        }
                  }
            }
      }
      
      public function check($type = "discount", $code = "", $option = [], $full = false, $ignore_status = false) {
            global $engine;

            if ($type == "discount") {
                  $code = query("SELECT * FROM `{$engine->config['prefix']}promo_code` WHERE (`type` = ? OR `type` = ?) AND `code` = ?;", ['refer', 'discount', $code])->fetch(PDO::FETCH_ASSOC);
            } elseif ($type == "credit") {
                  $code = query("SELECT * FROM `{$engine->config['prefix']}promo_code` WHERE `type` = ? AND `code` = ?;", ['credit', $code])->fetch(PDO::FETCH_ASSOC);
            } elseif ($type == null) {
                  $code = query("SELECT * FROM `{$engine->config['prefix']}promo_code` WHERE `code` = ?;", [$code])->fetch(PDO::FETCH_ASSOC);
            }
            if ($code) {
                  $condition = json_decode($code['condition'], true);
                  $promotion = json_decode($code['promotion'], true);

                  $pass = false;
                  if ($condition['type'] !== false) {
                        if ($condition['type'] == "price") {
                              if (isset($option['price'])) {
                                    if ($condition['condition'] == ">") {
                                          if ($option['price'] > $condition['amount'])
                                                $pass = true;
                                    } elseif ($condition['condition'] == ">=") {
                                          if ($option['price'] >= $condition['amount'])
                                                $pass = true;
                                    }
                              }
                        } elseif ($condition['type'] == "user") {
                              if (isset($condition['id'][$engine->user->id])) {
                                    if ($condition['id'][$engine->user->id] > 0) {
                                          $pass = true;
                                    }
                              }
                        }
                  } else {
                        $pass = true;
                  }
                  if ($condition['expire'] !== false) {
                        if ($condition['expire']['type'] == "time") {
                              if ($condition['expire']['expire'] <= time()) {
                                    $pass = false;
                              }
                        } elseif ($condition['expire']['type'] == "used") {
                              if ($condition['expire']['amount'] == 0) {
                                    $pass = false;
                              }
                        }
                        if ($condition['user']['time'] == 0) {
                              $pass = false;
                        } elseif ($condition['user']['oneperone'] && in_array($engine->user->id, $condition['user']['used'])) {
                              $pass = false;
                        }
                  }
                  if (!$full || $ignore_status) {
                        # Check and get full data will ignore status of code
                        if ($code['status'] == 1) {
                              $pass = false;
                        }
                  }

                  if ($pass) {
                        if ($full) {
                              return $code;
                        } elseif ($code['type'] == "refer") {
                              if ($engine->user->id == $promotion['referer']) {
                                    return "own_refer";
                              } else {
                                    return $promotion['discount'];
                              }
                        } elseif ($code['type'] == "discount") {
                              return $promotion;
                        } elseif ($code['type'] == "credit") {
                              return $promotion;
                        } else {
                              return false;
                        }
                  } else {
                        return false;
                  }
            } else {
                  return false;
            }
      }

      public function redeem($code = "") {
            global $engine;

            $code = $this->check(null, $code, [], true);
            if ($code) {
                  $condition = json_decode($code['condition'], true);
                  $promotion = json_decode($code['promotion'], true);

                  if($condition['user']['time'] != 0) {
                        $condition['user']['time'] > 0 && $condition['user']['time']--;
                        if(!in_array($engine->user->id, $condition['user']['used'])) {
                              $condition['user']['used'][] = $engine->user->id;
                        }
                  }
                  if ($condition['expire']['type'] == "used") {
                        if($condition['expire']['amount'] > 0) {
                              $condition['expire']['amount']--;
                        }
                  }

                  query("UPDATE `{$engine->config['prefix']}promo_code` SET `condition` = ? WHERE `id` = ?;", [json_encode($condition), $code['id']]);
                  if($condition['user']['time'] == 0) {
                        query("UPDATE `{$engine->config['prefix']}promo_code` SET `status` = ? WHERE `id` = ?;", [1, $code['id']]);
                  }
                  return true;
            } else {
                  return false;
            }
      }
}