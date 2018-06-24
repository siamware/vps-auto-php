<?php

require_once __DIR__ . '/engine/autoload.php';

if(version_number(config('current_version')) <= version_number("0.0.1")) {
      query("ALTER TABLE `tb_vps` ADD `auto_expand` INT NOT NULL DEFAULT '1' AFTER `notif`;");
      save_config('current_version', '0.0.2');
}

if(version_number(config('current_version')) <= version_number("0.0.2")) {
      query("CREATE TABLE `tb_invoice` (
            `id` int(11) NOT NULL,
            `owner` int(11) NOT NULL,
            `owner_detail` longtext CHARACTER SET utf8 NOT NULL,
            `product` longtext CHARACTER SET utf8 NOT NULL,
            `date` varchar(255) NOT NULL
          ) ENGINE=MyISAM DEFAULT CHARSET=latin1;");
      save_config('current_version', '0.0.3');
}

if(version_number(config('current_version')) <= version_number("0.0.3")) {
      query("CREATE TABLE `tb_promo_code` (
            `id` int(11) NOT NULL,
            `code` varchar(255) NOT NULL,
            `condition` longtext CHARACTER SET utf8 NOT NULL COMMENT 'เงื่อนไขของคนที่จะใช้งานได้',
            `promotion` longtext CHARACTER SET utf8 NOT NULL,
            `expire` longtext CHARACTER SET utf8 NOT NULL,
            `status` int(11) NOT NULL COMMENT '0 = ใช้งานได้, 1 = ใช้งานไม่ได้'
          ) ENGINE=MyISAM DEFAULT CHARSET=latin1;");
      save_config('current_version', '0.0.4');
}

if(version_number(config('current_version')) <= version_number("0.0.4")) {
      query("ALTER TABLE `tb_promo_code` ADD `type` VARCHAR(255) NOT NULL AFTER `code`;");
      save_config('current_version', '0.0.5');
}

if(version_number(config('current_version')) <= version_number("0.0.5")) {
      query("ALTER TABLE `tb_promo_code` DROP `expire`;");
      query("ALTER TABLE `tb_vps` ADD `promo_code` INT NOT NULL AFTER `package`;");
      save_config('current_version', '0.0.6');
}

if(version_number(config('current_version')) <= version_number("0.0.6")) {
      query("ALTER TABLE `tb_vps` ADD `refer` INT NOT NULL AFTER `promo_code`;");
      save_config('current_version', '0.0.7');
}

if(version_number(config('current_version')) <= version_number("0.0.7")) {
      query("ALTER TABLE `tb_invoice` ADD PRIMARY KEY(`id`);");
      query("ALTER TABLE `tb_invoice` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT;");
      save_config('current_version', '0.0.8');
}

if(version_number(config('current_version')) <= version_number("0.0.8")) {
      query("ALTER TABLE `tb_user` ADD `refer_code` VARCHAR(255) NOT NULL AFTER `verify_phone_code`;");
      save_config('current_version', '0.0.9');
}