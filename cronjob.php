<?php
$_SERVER["HTTP_HOST"] = "cronjob";
header('Content-Type: text/json; charset=UTF-8');
require_once __DIR__ . '/engine/autoload.php';
$config['debug'] = false;

// TrueWallet
//include_once __DIR__ . '/payment/wallet/auto.php';

// Notify expire date
// 7 day before (1)
$expiring = $engine->vps->getExpire(86400 * 7, 0);
if($config['debug']) {
      echo "Expire 7 day : ";
      var_dump($expiring);
}
foreach($expiring as $vm) {
      if($vm['expire'] - $vm['expanded'] > 86400 * 7) {
            $user = $engine->user->get($vm['owner']);
            if($vm['auto_expand'] == 0) {
                  // Not active auto expand
                  send_mail($user['email'], "VPS {$vm['name']} จะหมดอายุใน 7 วัน", [
                        "template" => "button",
                        "head" => "VPS {$vm['name']} จะหมดอายุใน 7 วัน",
                        "description" => "หากหมดอายุการใช้งาน คุณจะมีเวลา 12 ชั่วโมงก่อนที่ข้อมูลจะถูกลบถาวร<br>หากคุณต้องการใช้งานต่อกรุณาอายุการใช้งานด้วยตนเองหรือเปิดใช้งานระบบต่ออายุอัตโนมัติ<br><font color='red'>หากครบกำหนดแล้วยังไม่มีการชำระเงิน ระบบจะทำการระงับการใช้งาน VPS อัตโนมัติ</font>",
                        "btn-link" => "https://studio.phumin.in.th/panel/#/billing",
                        "btn-text" => "ชำระเงิน",
                  ]);
                  query("UPDATE `{$engine->config['prefix']}vps` SET `notif` = ?  WHERE `id` = ?;", [1, $vm['id']]);
            }else{
                  // Auto expand VPS
                  $res = $engine->vps->expand($vm['id']);
                  if($res) {
                        if(config('sms_notification') == 1 && $user['verify_phone'] > 0) {
                              $engine->sms->send($user['phone'], "VPS {$vm['name']} ได้ต่ออายุอัตโนมัติแล้ว ขอบคุณที่ใช้บริการ");
                        }
                        send_mail($user['email'], "VPS {$vm['name']} ได้ต่ออายุอัตโนมัติแล้ว", [
                              "template" => "text",
                              "head" => "ระบบได้ทำการต่ออายุอัตโนมัติให้กับ VPS {$vm['name']} แล้ว",
                              "description" => "ขอบคุณที่ใช้บริการกับเรา 😊",
                        ]);
                  }else{
                        send_mail($user['email'], "VPS {$vm['name']} จะหมดอายุใน 7 วัน", [
                              "template" => "button",
                              "head" => "VPS {$vm['name']} จะหมดอายุใน 7 วัน",
                              "description" => "ระบบไม่สามารถต่ออายุอัตโนมัติให้คุณได้เนื่องจากยอดเงินในบัญชีไม่เพียงพอ<br>หากคุณต้องการใช้งานต่อกรุณาเติมเงินเข้าบัญชีผู้ใช้ของคุณ<br><font color='red'>หากครบกำหนดแล้วยังไม่มีการชำระเงิน ระบบจะทำการระงับการใช้งาน VPS อัตโนมัติ</font>",
                              "btn-link" => "https://studio.phumin.in.th/panel/#/billing",
                              "btn-text" => "ชำระเงิน",
                        ]);
                        query("UPDATE `{$engine->config['prefix']}vps` SET `notif` = ?  WHERE `id` = ?;", [1, $vm['id']]);
                  }
            }
      }else{
            query("UPDATE `{$engine->config['prefix']}vps` SET `notif` = ?  WHERE `id` = ?;", [1, $vm['id']]);
      }
}
// 3 day before (2)
$expiring = $engine->vps->getExpire(86400 * 3, 1);
if($config['debug']) {
      echo "Expire 3 day : ";
      var_dump($expiring);
}
foreach($expiring as $vm) {
      if($vm['expire'] - $vm['expanded'] > 86400 * 3) {
            $user = $engine->user->get($vm['owner']);
            if($vm['auto_expand'] == 0) {
                  // Not active auto expand
                  send_mail($user['email'], "VPS {$vm['name']} จะหมดอายุใน 3 วัน", [
                        "template" => "button",
                        "head" => "VPS {$vm['name']} จะหมดอายุใน 3 วัน",
                        "description" => "หากหมดอายุการใช้งาน คุณจะมีเวลา 12 ชั่วโมงก่อนที่ข้อมูลจะถูกลบถาวร<br>หากคุณต้องการใช้งานต่อกรุณาอายุการใช้งานด้วยตนเองหรือเปิดใช้งานระบบต่ออายุอัตโนมัติ<br><font color='red'>หากครบกำหนดแล้วยังไม่มีการชำระเงิน ระบบจะทำการระงับการใช้งาน VPS อัตโนมัติ</font>",
                        "btn-link" => "https://studio.phumin.in.th/panel/#/billing",
                        "btn-text" => "ชำระเงิน",
                  ]);
                  query("UPDATE `{$engine->config['prefix']}vps` SET `notif` = ?  WHERE `id` = ?;", [2, $vm['id']]);
            }else{
                  // Auto expand VPS
                  $res = $engine->vps->expand($vm['id']);
                  if($res) {
                        if(config('sms_notification') == 1 && $user['verify_phone'] > 0) {
                              $engine->sms->send($user['phone'], "VPS {$vm['name']} ได้ต่ออายุอัตโนมัติแล้ว ขอบคุณที่ใช้บริการ");
                        }
                        send_mail($user['email'], "VPS {$vm['name']} ได้ต่ออายุอัตโนมัติแล้ว", [
                              "template" => "text",
                              "head" => "ระบบได้ทำการต่ออายุอัตโนมัติให้กับ VPS {$vm['name']} แล้ว",
                              "description" => "ขอบคุณที่ใช้บริการกับเรา 😊",
                        ]);
                  }else{
                        send_mail($user['email'], "VPS {$vm['name']} จะหมดอายุใน 3 วัน", [
                              "template" => "button",
                              "head" => "VPS {$vm['name']} จะหมดอายุใน 3 วัน",
                              "description" => "ระบบไม่สามารถต่ออายุอัตโนมัติให้คุณได้เนื่องจากยอดเงินในบัญชีไม่เพียงพอ<br>หากคุณต้องการใช้งานต่อกรุณาเติมเงินเข้าบัญชีผู้ใช้ของคุณ<br><font color='red'>หากครบกำหนดแล้วยังไม่มีการชำระเงิน ระบบจะทำการระงับการใช้งาน VPS อัตโนมัติ</font>",
                              "btn-link" => "https://studio.phumin.in.th/panel/#/billing",
                              "btn-text" => "ชำระเงิน",
                        ]);
                        query("UPDATE `{$engine->config['prefix']}vps` SET `notif` = ?  WHERE `id` = ?;", [2, $vm['id']]);
                  }
            }
      }else{
            query("UPDATE `{$engine->config['prefix']}vps` SET `notif` = ?  WHERE `id` = ?;", [2, $vm['id']]);
      }
}
// 1 day before (3)
$expiring = $engine->vps->getExpire(86400 * 1, 2);
if($config['debug']) {
      echo "Expire 1 day : ";
      var_dump($expiring);
}
foreach($expiring as $vm) {
      if($vm['expire'] - $vm['expanded'] > 86400 * 1) {
            $user = $engine->user->get($vm['owner']);
            if($vm['auto_expand'] == 0) {
                  // Not active auto expand
                  if(config('sms_notification') == 1 && $user['verify_phone'] > 0) {
                        $engine->sms->send($user['phone'], "VPS {$vm['name']} กำลังจะหมดอายุวันนี้ รีบต่ออายุเพื่อใช้งานต่อไป");
                  }
                  send_mail($user['email'], "VPS {$vm['name']} จะหมดอายุใน 1 วัน", [
                        "template" => "button",
                        "head" => "VPS {$vm['name']} จะหมดอายุใน 1 วัน",
                        "description" => "หากหมดอายุการใช้งาน คุณจะมีเวลา 12 ชั่วโมงก่อนที่ข้อมูลจะถูกลบถาวร<br>หากคุณต้องการใช้งานต่อกรุณาอายุการใช้งานด้วยตนเองหรือเปิดใช้งานระบบต่ออายุอัตโนมัติ<br><font color='red'>หากครบกำหนดแล้วยังไม่มีการชำระเงิน ระบบจะทำการระงับการใช้งาน VPS อัตโนมัติ</font>",
                        "btn-link" => "https://studio.phumin.in.th/panel/#/billing",
                        "btn-text" => "ชำระเงิน",
                  ]);
                  query("UPDATE `{$engine->config['prefix']}vps` SET `notif` = ?  WHERE `id` = ?;", [3, $vm['id']]);
            }else{
                  // Auto expand VPS
                  $res = $engine->vps->expand($vm['id']);
                  if($res) {
                        if(config('sms_notification') == 1 && $user['verify_phone'] > 0) {
                              $engine->sms->send($user['phone'], "VPS {$vm['name']} ได้ต่ออายุอัตโนมัติแล้ว ขอบคุณที่ใช้บริการ");
                        }
                        send_mail($user['email'], "VPS {$vm['name']} ได้ต่ออายุอัตโนมัติแล้ว", [
                              "template" => "text",
                              "head" => "ระบบได้ทำการต่ออายุอัตโนมัติให้กับ VPS {$vm['name']} แล้ว",
                              "description" => "ขอบคุณที่ใช้บริการกับเรา 😊",
                        ]);
                  }else{
                        if(config('sms_notification') == 1 && $user['verify_phone'] > 0) {
                              $engine->sms->send($user['phone'], "VPS {$vm['name']} กำลังจะหมดอายุวันนี้ รีบต่ออายุเพื่อใช้งานต่อไป");
                        }
                        send_mail($user['email'], "VPS {$vm['name']} จะหมดอายุใน 1 วัน", [
                              "template" => "button",
                              "head" => "VPS {$vm['name']} จะหมดอายุใน 1 วัน",
                              "description" => "ระบบไม่สามารถต่ออายุอัตโนมัติให้คุณได้เนื่องจากยอดเงินในบัญชีไม่เพียงพอ<br>หากคุณต้องการใช้งานต่อกรุณาเติมเงินเข้าบัญชีผู้ใช้ของคุณ<br><font color='red'>หากครบกำหนดแล้วยังไม่มีการชำระเงิน ระบบจะทำการระงับการใช้งาน VPS อัตโนมัติ</font>",
                              "btn-link" => "https://studio.phumin.in.th/panel/#/billing",
                              "btn-text" => "ชำระเงิน",
                        ]);
                        query("UPDATE `{$engine->config['prefix']}vps` SET `notif` = ?  WHERE `id` = ?;", [3, $vm['id']]);
                  }
            }
      }else{
            query("UPDATE `{$engine->config['prefix']}vps` SET `notif` = ?  WHERE `id` = ?;", [3, $vm['id']]);
      }
}
// 12 hr before (4)
$expiring = $engine->vps->getExpire(86400 * 0.5, 3);
if($config['debug']) {
      echo "Expire 12 hr : ";
      var_dump($expiring);
}
foreach($expiring as $vm) {
      if($vm['expire'] - $vm['expanded'] > 86400 * 0.5) {
            $user = $engine->user->get($vm['owner']);
            if($vm['auto_expand'] == 0) {
                  // Not active auto expand
                  send_mail($user['email'], "VPS {$vm['name']} จะหมดอายุใน 12 ชั่วโมง", [
                        "template" => "button",
                        "head" => "VPS {$vm['name']} จะหมดอายุใน 12 ชั่วโมง",
                        "description" => "หากหมดอายุการใช้งาน คุณจะมีเวลา 12 ชั่วโมงก่อนที่ข้อมูลจะถูกลบถาวร<br>หากคุณต้องการใช้งานต่อกรุณาอายุการใช้งานด้วยตนเองหรือเปิดใช้งานระบบต่ออายุอัตโนมัติ<br><font color='red'>หากครบกำหนดแล้วยังไม่มีการชำระเงิน ระบบจะทำการระงับการใช้งาน VPS อัตโนมัติ</font>",
                        "btn-link" => "https://studio.phumin.in.th/panel/#/billing",
                        "btn-text" => "ชำระเงิน",
                  ]);
                  query("UPDATE `{$engine->config['prefix']}vps` SET `notif` = ?  WHERE `id` = ?;", [4, $vm['id']]);
            }else{
                  // Auto expand VPS
                  $res = $engine->vps->expand($vm['id']);
                  if($res) {
                        if(config('sms_notification') == 1 && $user['verify_phone'] > 0) {
                              $engine->sms->send($user['phone'], "VPS {$vm['name']} ได้ต่ออายุอัตโนมัติแล้ว ขอบคุณที่ใช้บริการ");
                        }
                        send_mail($user['email'], "VPS {$vm['name']} ได้ต่ออายุอัตโนมัติแล้ว", [
                              "template" => "text",
                              "head" => "ระบบได้ทำการต่ออายุอัตโนมัติให้กับ VPS {$vm['name']} แล้ว",
                              "description" => "ขอบคุณที่ใช้บริการกับเรา 😊",
                        ]);
                  }else{
                        send_mail($user['email'], "VPS {$vm['name']} จะหมดอายุใน 12 ชั่วโมง", [
                              "template" => "button",
                              "head" => "VPS {$vm['name']} จะหมดอายุใน 12 ชั่วโมง",
                              "description" => "ระบบไม่สามารถต่ออายุอัตโนมัติให้คุณได้เนื่องจากยอดเงินในบัญชีไม่เพียงพอ<br>หากคุณต้องการใช้งานต่อกรุณาเติมเงินเข้าบัญชีผู้ใช้ของคุณ<br><font color='red'>หากครบกำหนดแล้วยังไม่มีการชำระเงิน ระบบจะทำการระงับการใช้งาน VPS อัตโนมัติ</font>",
                              "btn-link" => "https://studio.phumin.in.th/panel/#/billing",
                              "btn-text" => "ชำระเงิน",
                        ]);
                        query("UPDATE `{$engine->config['prefix']}vps` SET `notif` = ?  WHERE `id` = ?;", [4, $vm['id']]);
                  }
            }
      }else{
            query("UPDATE `{$engine->config['prefix']}vps` SET `notif` = ?  WHERE `id` = ?;", [4, $vm['id']]);
      }
}
// on time (5)
$expiring = $engine->vps->getExpire(0, 4);
if($config['debug']) {
      echo "Expire on time : ";
      var_dump($expiring);
}
foreach($expiring as $vm) {
      $user = $engine->user->get($vm['owner']);
      if($vm['auto_expand'] == 0) {
            // Not active auto expand
            if(config('sms_notification') == 1 && $user['verify_phone'] > 0) {
                  $engine->sms->send($user['phone'], "VPS {$vm['name']} ถูกระงับแล้ว รีบต่ออายุเพื่อใช้งานต่อไป");
            }
            send_mail($user['email'], "VPS {$vm['name']} หมดอายุแล้ว", [
                  "template" => "button",
                  "head" => "VPS {$vm['name']} หมดอายุแล้ว",
                  "description" => "คุณจะมีเวลา 12 ชั่วโมงก่อนที่ข้อมูลจะถูกลบถาวร<br>หากคุณต้องการใช้งานต่อกรุณาอายุการใช้งานด้วยตนเองหรือเปิดใช้งานระบบต่ออายุอัตโนมัติ<br><font color='red'>หากครบกำหนดแล้วยังไม่มีการชำระเงิน ระบบจะทำการระงับการใช้งาน VPS อัตโนมัติ</font>",
                  "btn-link" => "https://studio.phumin.in.th/panel/#/billing",
                  "btn-text" => "ชำระเงิน",
            ]);
            query("UPDATE `{$engine->config['prefix']}vps` SET `delete` = `expire` + ?, `status` = ?, `notif` = ?  WHERE `id` = ?;", [config('time_before_remove'), 1, 5, $vm['id']]);
      }else{
            // Auto expand VPS
            $res = $engine->vps->expand($vm['id']);
            if($res) {
                  if(config('sms_notification') == 1 && $user['verify_phone'] > 0) {
                        $engine->sms->send($user['phone'], "VPS {$vm['name']} ได้ต่ออายุอัตโนมัติแล้ว ขอบคุณที่ใช้บริการ");
                  }
                  send_mail($user['email'], "VPS {$vm['name']} ได้ต่ออายุอัตโนมัติแล้ว", [
                        "template" => "text",
                        "head" => "ระบบได้ทำการต่ออายุอัตโนมัติให้กับ VPS {$vm['name']} แล้ว",
                        "description" => "ขอบคุณที่ใช้บริการกับเรา 😊",
                  ]);
            }else{
                  if(config('sms_notification') == 1 && $user['verify_phone'] > 0) {
                        $engine->sms->send($user['phone'], "VPS {$vm['name']} ถูกระงับแล้ว รีบต่ออายุเพื่อใช้งานต่อไป");
                  }
                  send_mail($user['email'], "VPS {$vm['name']} หมดอายุแล้ว", [
                        "template" => "button",
                        "head" => "VPS {$vm['name']} หมดอายุแล้ว",
                        "description" => "ระบบไม่สามารถต่ออายุอัตโนมัติให้คุณได้เนื่องจากยอดเงินในบัญชีไม่เพียงพอ<br>หากคุณต้องการใช้งานต่อกรุณาเติมเงินและสั่งชำระด้วยตนเอง<br><font color='red'>หากไม่มีการชำระเงินภายใน 24 ชั่วโมง ระบบจะทำการลบข้อมูลอย่างถาวร</font>",
                        "btn-link" => "https://studio.phumin.in.th/panel/#/billing",
                        "btn-text" => "ชำระเงิน",
                  ]);
                  $vps = $engine->vps->get($vm['id']);
                  if($vps['state'] == "running") {
                        $engine->vps->pause($vm['id']);
                  }
                  query("UPDATE `{$engine->config['prefix']}vps` SET `delete` = `expire` + ?, `status` = ?, `notif` = ?  WHERE `id` = ?;", [config('time_before_remove'), 1, 5, $vm['id']]);
            }
      }
}

// Delete VPS
$delete = $engine->vps->getDelete();
if($config['debug']) {
      echo "Delete : ";
      var_dump($delete);
}
foreach($delete as $vm) {
      $user = $engine->user->get($vm['owner']);
      $expand = false;
      if($vm['auto_expand'] == 1) {
            $res = $engine->vps->expand($vm['id']);
            if($res) {
                  if(config('sms_notification') == 1 && $user['verify_phone'] > 0) {
                        $engine->sms->send($user['phone'], "VPS {$vm['name']} ได้ต่ออายุอัตโนมัติแล้ว ขอบคุณที่ใช้บริการ");
                  }
                  send_mail($user['email'], "VPS {$vm['name']} ได้ต่ออายุอัตโนมัติแล้ว", [
                        "template" => "text",
                        "head" => "ระบบได้ทำการต่ออายุอัตโนมัติให้กับ VPS {$vm['name']} แล้ว",
                        "description" => "ขอบคุณที่ใช้บริการกับเรา 😊",
                  ]);
                  $expand = true;
            }
      }

      if(!$expand) {
            send_mail($user['email'], "VPS {$vm['name']} ถูกลบจากระบบอย่างถาวร", [
                  "template" => "text",
                  "head" => "VPS {$vm['name']} ถูกลบออกจากระบบ",
                  "description" => "เนื่องจากคุณไม่ชำระค่าบริการตามเวลาที่กำหนด ระบบจึงทำการลบ VPS ของคุณอย่างถาวร<br>ขอบคุณที่ใช้บริการกับเรา 😊<br>",
            ]);
            $res = $engine->vps->remove($vm['id']);
      }
}