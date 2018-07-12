-- phpMyAdmin SQL Dump
-- version 4.7.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 12, 2018 at 06:54 PM
-- Server version: 10.1.25-MariaDB
-- PHP Version: 5.6.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `vps`
--

-- --------------------------------------------------------

--
-- Table structure for table `error_js`
--

CREATE TABLE `error_js` (
  `id` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `err` longtext NOT NULL,
  `info` longtext NOT NULL,
  `agent` longtext NOT NULL,
  `count` int(11) NOT NULL,
  `time` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `error_php`
--

CREATE TABLE `error_php` (
  `id` int(11) NOT NULL,
  `host` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `file` text NOT NULL,
  `line` varchar(255) NOT NULL,
  `count` int(11) NOT NULL,
  `time` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tb_config`
--

CREATE TABLE `tb_config` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` text CHARACTER SET utf8 NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tb_config`
--

INSERT INTO `tb_config` (`id`, `name`, `value`) VALUES
(1, 'maintance_mode', '0'),
(2, 'sitename', 'Phumin Studio : Cloud'),
(3, 'register_credit', '0'),
(4, 'worker_updating', '0'),
(5, 'current_version', '0.0.9'),
(6, 'auto_choose_server', '0'),
(7, 'keep_before_remove', '86400'),
(8, 'sitedesc', 'Phumin Studio บริการ Hosting และ VPS สำหรับเซิฟเวอร์เกม และธุรกิจ'),
(9, 'license_key', ''),
(10, 'truemoney_gateway', 'tmpay'),
(11, 'truemoney_tmpay_merchant', ''),
(12, 'truewallet_phone', ''),
(13, 'truewallet_pin', ''),
(14, 'truemoney_tmpay_50', '50'),
(15, 'truemoney_tmpay_90', '90'),
(16, 'truemoney_tmpay_150', '150'),
(17, 'truemoney_tmpay_300', '300'),
(18, 'truemoney_tmpay_500', '500'),
(19, 'truemoney_tmpay_1000', '1000'),
(20, 'truewallet_range', '{\"50\": 1, \"300\": 1.5,\"1000\": 2}'),
(21, 'round_day', '1'),
(22, 'time_before_remove', '86400'),
(23, 'sms_notification', '0'),
(24, 'sms_gateway', 'molink'),
(25, 'sms_config_thaibulk', '{\"user\":\"\",\"pass\":\"\",\"sender\":\"\"}'),
(26, 'sms_config_thsms', '{\"user\":\"\",\"pass\":\"\",\"sender\":\"\"}'),
(27, 'sms_config_molinksms', '{\"user\": \"\", \"pass\": \"\", \"sender\": \"\"}'),
(28, 'bank_kbank', '{\"user\":\"\",\"pass\":\"\",\"account\":\"\"}'),
(29, 'refer_share', '10');

-- --------------------------------------------------------

--
-- Table structure for table `tb_invoice`
--

CREATE TABLE `tb_invoice` (
  `id` int(11) NOT NULL,
  `owner` int(11) NOT NULL,
  `owner_detail` longtext CHARACTER SET utf8 NOT NULL,
  `product` longtext CHARACTER SET utf8 NOT NULL,
  `date` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tb_ip`
--

CREATE TABLE `tb_ip` (
  `id` int(11) NOT NULL,
  `host` int(11) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `subnet` varchar(255) NOT NULL,
  `gateway` varchar(255) NOT NULL,
  `useby` int(11) NOT NULL COMMENT 'VPS ID'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tb_package`
--

CREATE TABLE `tb_package` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `cpu` int(11) NOT NULL COMMENT 'Core(s)',
  `ram` float NOT NULL COMMENT 'GB',
  `disk` int(11) NOT NULL COMMENT 'GB',
  `time` varchar(255) NOT NULL COMMENT 'Timestamp',
  `price` double(11,2) NOT NULL,
  `order` int(11) NOT NULL,
  `will_delete` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tb_payment`
--

CREATE TABLE `tb_payment` (
  `id` int(11) NOT NULL,
  `owner` int(11) NOT NULL,
  `amount` float(7,2) NOT NULL,
  `gateway` varchar(255) NOT NULL,
  `transaction` varchar(255) NOT NULL,
  `data` longtext NOT NULL,
  `debug` longtext NOT NULL,
  `status` varchar(255) NOT NULL,
  `time` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tb_promo_code`
--

CREATE TABLE `tb_promo_code` (
  `id` int(11) NOT NULL,
  `code` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `condition` longtext NOT NULL COMMENT 'เงื่อนไขของคนที่จะใช้งานได้',
  `promotion` longtext NOT NULL,
  `status` int(11) NOT NULL COMMENT '0 = ใช้งานได้, 1 = ใช้งานไม่ได้'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tb_statement_kbank`
--

CREATE TABLE `tb_statement_kbank` (
  `id` int(11) NOT NULL,
  `date` varchar(255) NOT NULL,
  `time` varchar(255) NOT NULL,
  `in` int(11) NOT NULL,
  `out` int(11) NOT NULL,
  `info` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tb_statement_tw`
--

CREATE TABLE `tb_statement_tw` (
  `id` int(11) NOT NULL,
  `reportID` varchar(255) NOT NULL,
  `transactionID` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `amount` float(11,2) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `date` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tb_ticket`
--

CREATE TABLE `tb_ticket` (
  `id` int(11) NOT NULL,
  `from` int(11) NOT NULL,
  `title` varchar(255) CHARACTER SET utf8 NOT NULL,
  `category` varchar(255) CHARACTER SET utf8 NOT NULL,
  `lock` int(11) NOT NULL,
  `opened` varchar(255) NOT NULL,
  `closed` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tb_ticket_chat`
--

CREATE TABLE `tb_ticket_chat` (
  `id` int(11) NOT NULL,
  `ticket` int(11) NOT NULL,
  `owner` int(11) NOT NULL,
  `owner_name` int(11) NOT NULL,
  `message` text CHARACTER SET utf8 NOT NULL,
  `time` varchar(255) NOT NULL,
  `read` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tb_token_resetpass`
--

CREATE TABLE `tb_token_resetpass` (
  `id` int(11) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expire` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tb_user`
--

CREATE TABLE `tb_user` (
  `id` int(11) NOT NULL,
  `email` varchar(30) NOT NULL,
  `password` varchar(73) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `company` varchar(255) NOT NULL,
  `credit` double(11,2) NOT NULL,
  `time` varchar(255) NOT NULL,
  `admin` int(11) NOT NULL,
  `verify_email` varchar(255) NOT NULL DEFAULT '0',
  `verify_email_code` varchar(255) NOT NULL,
  `verify_phone` varchar(255) NOT NULL DEFAULT '0',
  `verify_phone_code` varchar(255) NOT NULL,
  `refer_code` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tb_vps`
--

CREATE TABLE `tb_vps` (
  `id` int(11) NOT NULL,
  `owner` int(11) NOT NULL,
  `package` int(11) NOT NULL,
  `promo_code` int(11) NOT NULL,
  `refer` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `host` int(11) NOT NULL,
  `ref` varchar(255) NOT NULL,
  `created` varchar(255) NOT NULL,
  `expire` varchar(255) NOT NULL,
  `expanded` varchar(255) NOT NULL,
  `delete` varchar(255) NOT NULL,
  `status` int(11) NOT NULL COMMENT '0 = normal, 1 = expire, 2 = ban',
  `notif` int(11) NOT NULL COMMENT '0 = none, 1 = 7 day before, 2 = 3 day before, 3 = 1 day before, 4 = 12 hr before, 5 = on dead line',
  `auto_expand` int(11) NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tb_xen_host`
--

CREATE TABLE `tb_xen_host` (
  `id` int(11) NOT NULL,
  `label` varchar(255) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `cpu` int(11) NOT NULL,
  `ram_total` bigint(20) NOT NULL,
  `ram_free` bigint(20) NOT NULL,
  `api_token` varchar(255) NOT NULL,
  `last_check` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tb_xen_template`
--

CREATE TABLE `tb_xen_template` (
  `id` int(11) NOT NULL,
  `server` int(11) NOT NULL,
  `opaqueRef` varchar(255) NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `os` varchar(255) NOT NULL COMMENT 'window, ubuntu, centos',
  `cpu` int(11) NOT NULL,
  `ram` bigint(20) NOT NULL,
  `visible` int(11) NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tb_xen_vbd`
--

CREATE TABLE `tb_xen_vbd` (
  `id` int(11) NOT NULL,
  `server` int(11) NOT NULL COMMENT 'Server Id',
  `opaqueRef` varchar(255) NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `vm` varchar(255) NOT NULL,
  `vdi` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `mode` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tb_xen_vdi`
--

CREATE TABLE `tb_xen_vdi` (
  `id` int(11) NOT NULL,
  `server` int(11) NOT NULL COMMENT 'Server ID',
  `opaqueRef` varchar(255) NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `size` bigint(20) NOT NULL,
  `sr` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tb_xen_vif`
--

CREATE TABLE `tb_xen_vif` (
  `id` int(11) NOT NULL,
  `server` int(11) NOT NULL COMMENT 'Server ID',
  `opaqueRef` varchar(255) NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `vm` varchar(255) NOT NULL,
  `network` varchar(255) NOT NULL,
  `mac` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tb_xen_vm`
--

CREATE TABLE `tb_xen_vm` (
  `id` int(11) NOT NULL,
  `server` int(11) NOT NULL COMMENT 'Id from tb_xen_host',
  `opaqueRef` varchar(255) CHARACTER SET latin1 NOT NULL,
  `uuid` varchar(255) CHARACTER SET latin1 NOT NULL,
  `name` varchar(255) NOT NULL,
  `powerState` varchar(255) CHARACTER SET latin1 NOT NULL,
  `ram` bigint(11) NOT NULL,
  `cpu` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `error_js`
--
ALTER TABLE `error_js`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `error_php`
--
ALTER TABLE `error_php`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_config`
--
ALTER TABLE `tb_config`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_invoice`
--
ALTER TABLE `tb_invoice`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_ip`
--
ALTER TABLE `tb_ip`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_package`
--
ALTER TABLE `tb_package`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_payment`
--
ALTER TABLE `tb_payment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_promo_code`
--
ALTER TABLE `tb_promo_code`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_statement_kbank`
--
ALTER TABLE `tb_statement_kbank`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_statement_tw`
--
ALTER TABLE `tb_statement_tw`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_ticket`
--
ALTER TABLE `tb_ticket`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_ticket_chat`
--
ALTER TABLE `tb_ticket_chat`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_token_resetpass`
--
ALTER TABLE `tb_token_resetpass`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_user`
--
ALTER TABLE `tb_user`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_vps`
--
ALTER TABLE `tb_vps`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_xen_host`
--
ALTER TABLE `tb_xen_host`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_xen_template`
--
ALTER TABLE `tb_xen_template`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_xen_vbd`
--
ALTER TABLE `tb_xen_vbd`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_xen_vdi`
--
ALTER TABLE `tb_xen_vdi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_xen_vif`
--
ALTER TABLE `tb_xen_vif`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_xen_vm`
--
ALTER TABLE `tb_xen_vm`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `error_js`
--
ALTER TABLE `error_js`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `error_php`
--
ALTER TABLE `error_php`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `tb_config`
--
ALTER TABLE `tb_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;
--
-- AUTO_INCREMENT for table `tb_invoice`
--
ALTER TABLE `tb_invoice`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;
--
-- AUTO_INCREMENT for table `tb_ip`
--
ALTER TABLE `tb_ip`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;
--
-- AUTO_INCREMENT for table `tb_package`
--
ALTER TABLE `tb_package`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;
--
-- AUTO_INCREMENT for table `tb_payment`
--
ALTER TABLE `tb_payment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;
--
-- AUTO_INCREMENT for table `tb_promo_code`
--
ALTER TABLE `tb_promo_code`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
--
-- AUTO_INCREMENT for table `tb_statement_kbank`
--
ALTER TABLE `tb_statement_kbank`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `tb_statement_tw`
--
ALTER TABLE `tb_statement_tw`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
--
-- AUTO_INCREMENT for table `tb_ticket`
--
ALTER TABLE `tb_ticket`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tb_ticket_chat`
--
ALTER TABLE `tb_ticket_chat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tb_token_resetpass`
--
ALTER TABLE `tb_token_resetpass`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tb_user`
--
ALTER TABLE `tb_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;
--
-- AUTO_INCREMENT for table `tb_vps`
--
ALTER TABLE `tb_vps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;
--
-- AUTO_INCREMENT for table `tb_xen_host`
--
ALTER TABLE `tb_xen_host`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `tb_xen_template`
--
ALTER TABLE `tb_xen_template`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=215;
--
-- AUTO_INCREMENT for table `tb_xen_vbd`
--
ALTER TABLE `tb_xen_vbd`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=527;
--
-- AUTO_INCREMENT for table `tb_xen_vdi`
--
ALTER TABLE `tb_xen_vdi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=484;
--
-- AUTO_INCREMENT for table `tb_xen_vif`
--
ALTER TABLE `tb_xen_vif`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=296;
--
-- AUTO_INCREMENT for table `tb_xen_vm`
--
ALTER TABLE `tb_xen_vm`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=243;COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
