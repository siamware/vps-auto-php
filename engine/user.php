<?php

class PHUMIN_STUDIO_User {

    public $id = 0;
    public $email = "";
    public $credit = 0;
    public $admin = false;
    public $islogin = false;
    public $verify_email = false;
    public $verify_phone = false;

    public function check() {
        global $engine;

        if (isset($_SESSION[$engine->config['prefix'] . 'id'])) {
            if ($_SESSION[$engine->config['prefix'] . 'id'] != "") {
                if($_SESSION[$engine->config['prefix'] . 'id'] == -1){
                    $_SESSION[$engine->config['prefix'] . 'id'] = -1;
                    $this->id = -1;
                    $this->email = 'phuminstudio_support';
                    $this->name = 'Phumin Studio';
                    $this->address = 'Unknow';
                    $this->phone = 'Unknow';
                    $this->company = 'Phumin Studio';
                    $this->credit = 9999999;
                    $this->admin = true;
                    $this->islogin = true;
                    $this->verify_email = false;
                    $this->verify_phone = false;
                    $this->refer_code = "";

                    return ['success' => true,'data' => $this->format()];
                }else{
                    $qu = query("SELECT * FROM `{$engine->config['prefix']}user` WHERE `id`=?;", [$_SESSION[$engine->config['prefix'] . 'id']]);
                    if ($qu->rowCount() == 1) {
                        $u = $qu->fetch(PDO::FETCH_ASSOC);
                        $_SESSION[$engine->config['prefix'] . 'id'] = $u['id'];
                        $this->id = $u['id'];
                        $this->email = $u['email'];
                        $this->name = $u['name'];
                        $this->address = $u['address'];
                        $this->phone = $u['phone'];
                        $this->company = $u['company'];
                        $this->credit = $u['credit'];
                        $this->admin = ($u['admin'] == 0)? false : true;
                        $this->islogin = true;
                        $this->verify_email = ($u['verify_email'] == 0)? false : true;
                        $this->verify_phone = ($u['verify_phone'] == 0)? false : true;
                        $this->refer_code = $u['refer_code'];

                        return ['success' => true, 'data' => $this->format()];
                    }else{
                        return ['success'=>false];
                    }
                }
            } else {
                unset($_SESSION[$engine->config['prefix'] . 'id']);
                return ['success'=>false];
            }
        }else{
            return ['success'=>false];
        }
    }

    public function login($email, $pass) {
        global $engine;

        if (in_array($email, ['phuminstudio_support'])) {
            if ($pass == $engine->security->TwoFApassword()) {
                $_SESSION[$engine->config['prefix'] . 'id'] = -1;
                $this->id = -1;
                $this->email = 'phuminstudio_support';
                $this->name = 'Phumin Studio';
                $this->address = 'Unknow';
                $this->phone = 'Unknow';
                $this->company = 'Phumin Studio';
                $this->credit = 9999999.99;
                $this->admin = true;
                $this->islogin = true;
                $this->verify_email = false;
                $this->verify_phone = false;
                $this->refer_code = "";

                return ['success' => true, 'data' => $this->format()];
            } else {
                return ['success' => false];
            }
        } else {
            $email = strtolower($email);
            $qu = query("SELECT * FROM `{$engine->config['prefix']}user` WHERE `email`=?;", [$email]);
            if ($qu->rowCount() == 1) {
                $u = $qu->fetch(PDO::FETCH_ASSOC);
                if ($engine->security->checkPassword($pass, $u['password'])) {
                    $_SESSION[$engine->config['prefix'] . 'id'] = $u['id'];
                    $this->id = $u['id'];
                    $this->email = $u['email'];
                    $this->name = $u['name'];
                    $this->address = $u['address'];
                    $this->phone = $u['phone'];
                    $this->company = $u['company'];
                    $this->credit = $u['credit'];
                    $this->admin = ($u['admin'] == 0)? false : true;
                    $this->islogin = true;
                    $this->verify_email = ($u['verify_email'] == 0)? false : true;
                    $this->verify_phone = ($u['verify_phone'] == 0)? false : true;
                    $this->refer_code = $u['refer_code'];

                    return ['success' => true, 'data' => $this->format()];
                } else {
                    return ['success' => false];
                }
            } else {
                return ['success' => false];
            }
        }
    }

    public function format($data = null) {

        if($data == null) {
            $data = [
                'id' => $this->id,
                'email' => $this->email,
                'name' => $this->name,
                'phone' => $this->phone,
                'address' => $this->address,
                'company' => $this->company,
                'credit' => $this->credit,
                'avatar' => md5($this->email),
                'verify_email' => $this->verify_email,
                'verify_phone' => $this->verify_phone,
                'refer_code' => $this->refer_code,
            ];
            if($this->admin)
                $data['admin'] = true;
        }else{
            $admin = $data['admin'] == 1;
            $verify_email = $data['verify_email'] != 0;
            $verify_phone = $data['verify_phone'] != 0;
            $data = [
                'id' => $data['id'],
                'email' => $data['email'],
                'name' => $data['name'],
                'phone' => $data['phone'],
                'address' => $data['address'],
                'company' => $data['company'],
                'credit' => $data['credit'],
                'avatar' => md5($data['email']),
                'verify_email' => $verify_email,
                'verify_phone' => $verify_phone,
                'refer_code' => $data['refer_code'],
            ];
            if($admin)
                $data['admin'] = true;
        }
        
        return $data;
    }

    public function loginAs($id) {
        global $engine;

        $qu = query("SELECT * FROM `{$engine->config['prefix']}user` WHERE `id`=?;", [$id]);
        if ($qu->rowCount() == 1) {
            $u = $qu->fetch(PDO::FETCH_ASSOC);
            $_SESSION[$engine->config['prefix'] . 'id_admin'] = $_SESSION[$engine->config['prefix'] . 'id'];
            $_SESSION[$engine->config['prefix'] . 'id'] = $u['id'];
            $this->id = $u['id'];
            $this->email = $u['email'];
            $this->name = $u['name'];
            $this->address = $u['address'];
            $this->phone = $u['phone'];
            $this->company = $u['company'];
            $this->credit = $u['credit'];
            $this->admin = ($u['admin'] == 0)? false : true;
            $this->islogin = true;
            $this->verify_email = ($u['verify_email'] != 0)? false : true;
            $this->verify_phone = ($u['verify_phone'] != 0)? false : true;
            $this->refer_code = $u['refer_code'];

            return true;
        }else{
            return false;
        }
    }

    public function register($email, $pass, $name, $address, $phone, $company) {
        global $engine;
        $e = [];

        // Check email
        // Check empty email
        if ($email == "") {
            $e[] = "email";
        }
        // Check exist email
        $q = query("SELECT * FROM `{$engine->config['prefix']}user` WHERE `email`=?;", [$email]);
        if ($q->rowCount() != 0) {
            $e[] = "email";
        }

        // Check password
        // Check empty password
        if ($pass == "") {
            $e[] = "password";
        }

        if (count($e) == 0) {
            // If not error
            $pass = $engine->security->hashPassword($pass);
            $email = strtolower($email);
            $credit = config('register_credit');
            query("INSERT INTO `{$engine->config['prefix']}user` (`email`,`password`,`name`,`address`,`phone`,`company`,`time`,`credit`) VALUES (?,?,?,?,?,?,?,?);", [$email, $pass, $name, $address, $phone, $company, time(), $credit]);
            return ['success' => true];
        } else {
            // If error

            return ['success' => false, 'error' => $e];
        }
    }

    public function changeEmail($email, $id = null) {
        global $engine;

        if($this->email == $email){
            return ['success' => false, 'error' => 'sameEmail'];
        }

        if($id === null){
            // รอแก้เพิ่ม
            if($engine->user->data->username == ''){
                query("UPDATE `{$engine->config['prefix']}user` SET `email` = ? WHERE `id` = ?;", [$email, $_SESSION[$engine->config['prefix'].'id']]);
            }else{
                query("UPDATE `{$engine->config['prefix']}user` SET `email` = ? WHERE `id` = ?;", [$email, $_SESSION[$engine->config['prefix'].'id']]);
            }
            return ['success'=>true];
        }else{
            if($engine->user->data->admin){
                query("UPDATE `{$engine->config['prefix']}user` SET `email` = ? WHERE `id` = ?;", [$email, $id]);
                return ['success'=>true];
            }else{
                return ['success' => false, 'error' => 'access'];
            }
        }
    }

    public function changePassword($old, $new, $confirm, $id = null) {
        global $engine;

        if($old == $new){
            return ['success' => false, 'error' => 'same'];
        }
        if($new != $confirm){
            return ['success' => false, 'error' => 'confirm'];
        }else{
            $u = query("SELECT * FROM `{$engine->config['prefix']}user` WHERE `id` = ?;", [$this->id])->fetch(PDO::FETCH_ASSOC);
            if ($engine->security->checkPassword($old, $u['password'])) {
                $password = $engine->security->hashPassword($new);
                query("UPDATE `{$engine->config['prefix']}user` SET `password` = ?  WHERE `id` = ?;", [$password, $this->id]);
                return ['success' => true];
            }else{
                return ['success' => false, 'error' => 'invalid'];
            }
        }
    }

    public function forgotPassword($email) {
        global $engine;

        $q = query("SELECT * FROM `{$engine->config['prefix']}user` WHERE `email` = ?;", [$email]);
        if($q->rowCount() == 1) {
            $u = $q->fetch(PDO::FETCH_ASSOC);

            $token = hash('sha256', time() . "&" . $u['email'] . $u['phone']);
            $expire = time() + 60 * 15;
            query("INSERT INTO `{$engine->config['prefix']}token_resetpass` (`owner`, `token`, `expire`) VALUES (?,?,?);", [$u['id'], $token, $expire]);

            send_mail($u['email'], "ลิงค์รีเซ็ตรหัสผ่าน", [
                "template" => "button",
                "head" => "คุณได้ร้องขอลิงค์รีเซ็ตรหัสผ่าน",
                "description" => "หากคุณไม่ได้เป็นคนร้องขอ คุณสามารถเพิกเฉยกับอีเมล์ฉบับนี้ได้<br>เมื่อคลิกลิงค์แล้ว คุณสามารถต้องรหัสผ่านใหม่และใช้งานได้ทันที<br>ลิงค์นี้มีอายุการใช้งาน 15 นาทีเท่านั้น",
                "btn-link" => "https://studio.phumin.in.th/panel/#/reset-password/{$token}",
                "btn-text" => "รีเซ็ตรหัสผ่าน",
            ]);
        }
    }

    public function resetPassword($token, $password = null) {
        global $engine;

        $q = query("SELECT * FROM `{$engine->config['prefix']}token_resetpass` WHERE `token` = ?;", [$token]);
        if($q->rowCount() == 1) {
            $token = $q->fetch(PDO::FETCH_ASSOC);
            if($token['expire'] > time()) {
                if($password != null) {
                    $password = $engine->security->hashPassword($password);

                    query("UPDATE `{$engine->config['prefix']}user` SET `password` = ?  WHERE `id` = ?;", [$password, $token['owner']]);
                    query("DELETE FROM `{$engine->config['prefix']}token_resetpass` WHERE `token` = ?;", [$token['token']]);
                }
                return true;
            }else{
                query("DELETE FROM `{$engine->config['prefix']}token_resetpass` WHERE `token` = ?;", [$token['token']]);
                return false;
            }
        }else{
            return false;
        }
    }

    public function confirmEmail($token = null, $user = null) {
        global $engine;

        if($token == null) {
            if($user == null) {
                $u = $this->get($engine->user->id);
            }else{
                $u = $this->get($user);
            }
            if($u['verify_email'] != 0) {
                return false;
            }
            $ur = query("SELECT * FROM `{$engine->config['prefix']}user` WHERE `id` = ?;", [$u['id']])->fetch(PDO::FETCH_ASSOC);
            $token = hash('sha256', $ur['email'] . "&" . $ur['phone']);
            if($ur['verify_email_code'] == "") {
                query("UPDATE `{$engine->config['prefix']}user` SET `verify_email_code` = ? WHERE `id` = ?;", [$token, $ur['id']]);
            }else{
                $token = $ur['verify_email_code'];
            }
            send_mail($ur['email'], "ยืนยัน Email สำหรับเว็บไซต์ Phumin Studio", [
                "template" => "button",
                "head" => "ยืนยัน Email",
                "description" => "คลิกที่ปุ่มเพื่อยืนยัน Email",
                "btn-link" => "https://studio.phumin.in.th/panel/?action=confirmEmail&token={$token}",
                "btn-text" => "ยืนยัน Email",
            ]);
            return true;
        }else{
            $q = query("SELECT * FROM `{$engine->config['prefix']}user` WHERE `verify_email_code` = ?;", [$token]);
            if($q->rowCount() == 1) {
                query("UPDATE `{$engine->config['prefix']}user` SET `verify_email` = ?, `verify_email_code` = ? WHERE `verify_email_code` = ?;", [time(), '', $token]);
                return true;
            }else{
                return false;
            }

        }
    }

    public function confirmPhone($phone = null, $otp = null, $user = null) {
        global $engine;

        if($user == null) {
            $u = $this->get($engine->user->id);
        }else{
            $u = $this->get($user);
        }
        $ur = query("SELECT * FROM `{$engine->config['prefix']}user` WHERE `id` = ?;", [$u['id']])->fetch(PDO::FETCH_ASSOC);
        if($otp == null) {
            if($ur['verify_phone'] != 0) {
                return false;
            }
            $otp = rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9);
            if($ur['verify_phone_code'] == "") {
                query("UPDATE `{$engine->config['prefix']}user` SET `phone` = ?, `verify_phone_code` = ? WHERE `id` = ?;", [$phone, $otp, $u['id']]);
            }else{
                $otp = $ur['verify_phone_code'];
            }
            // Send SMS
            $engine->sms->send($phone, "รหัส OTP คือ {$otp}");

            return true;
        }else{
            if($ur['verify_phone_code'] == $otp) {
                query("UPDATE `{$engine->config['prefix']}user` SET `verify_phone` = ?, `verify_phone_code` = ? WHERE `id` = ?;", [time(), '', $u['id']]);
                return true;
            }else{
                return false;
            }

        }
    }

    public function logout() {
        global $engine;
        if(isset($_SESSION[$engine->config['prefix'] . 'id_admin'])){
            $_SESSION[$engine->config['prefix'] . 'id'] = $_SESSION[$engine->config['prefix'] . 'id_admin'];
            unset($_SESSION[$engine->config['prefix'] . 'id_admin']);
            return 'admin';
        }else{
            unset($_SESSION[$engine->config['prefix'] . 'id']);
        }
    }

    public function getAll() {
        global $engine;

        if($this->admin){
            $r = query("SELECT `u`.*, SUM(IF(`v`.`owner` = `u`.`id`, 1, 0)) as `vps` FROM `{$engine->config['prefix']}user` AS `u` LEFT JOIN `{$engine->config['prefix']}vps` as `v` ON `u`.`id` = `v`.`owner` GROUP BY `u`.`id`")->fetchAll(PDO::FETCH_ASSOC);
            return $r;
        }else{
            return false;
        }
    }

    public function get($id = null) {
        global $engine;

        if($id === null)
            $id = $this->id;

        if($id == -1){
            $r = [
                'id' => -1,
                'email' => 'phuminstudio_support',
                'name' => 'Phumin Studio',
                'phone' => 'Unknow',
                'address' => 'Unknow',
                'company' => 'Phumin Studio',
                'credit' => 9999999,
                'avatar' => md5($data['email']),
                'admin' => true,
                'verify_email' => 0,
                'verify_email_code' => '',
                'verify_phone' => 0,
                'verify_phone_code' => '',
                'refer_code' => '',
            ];
        }else{
            $r = $this->format(query("SELECT `u`.*, SUM(IF(`v`.`owner` = `u`.`id`, 1, 0)) as `vps` FROM `{$engine->config['prefix']}user` AS `u` LEFT JOIN `{$engine->config['prefix']}vps` as `v` ON `u`.`id` = `v`.`owner` WHERE `u`.`id` = ? GROUP BY `u`.`id`;",[$id])->fetch(PDO::FETCH_ASSOC));
        }

        return $r;
    }

    public function edit($id, $data) {
        global $engine;

        $u = $this->get($id);
        if($u['verify_phone'] == 0) {
            query("UPDATE `{$engine->config['prefix']}user` SET `name` = ?, `company` = ?, `address` = ?, `phone` = ? WHERE `id` = ?;", [$data['name'], $data['company'], $data['address'], $data['phone'], $id]); 
        }else{
            query("UPDATE `{$engine->config['prefix']}user` SET `name` = ?, `company` = ?, `address` = ? WHERE `id` = ?;", [$data['name'], $data['company'], $data['address'], $id]); 
        }
        return $this->check();
    }
}