SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `cz_administrators` (
  `admin_id` int(11) NOT NULL COMMENT '管理员ID',
  `admin_name` varchar(255) DEFAULT NULL COMMENT '用户帐号',
  `admin_password` varchar(255) DEFAULT NULL COMMENT '密码',
  `admin_password_rnd` varchar(255) DEFAULT NULL COMMENT '登陆随机密码',
  `role_id` int(11) DEFAULT '0' COMMENT '权限组ID',
  `is_enabled` tinyint(1) DEFAULT '1' COMMENT '是否启用，1为启用',
  `real_name` varchar(255) DEFAULT NULL COMMENT '真实姓名',
  `tel` varchar(255) DEFAULT NULL COMMENT '电话，手机',
  `email` varchar(255) DEFAULT NULL COMMENT '邮箱',
  `wechat` varchar(255) DEFAULT NULL COMMENT '微信号',
  `qq` varchar(255) DEFAULT NULL COMMENT 'QQ号',
  `last_login_ip` varchar(50) DEFAULT NULL COMMENT '最后登陆IP',
  `last_login_time` datetime DEFAULT NULL COMMENT '最后登陆时间',
  `last_logout_time` datetime DEFAULT NULL COMMENT '最后退出时间',
  `login_times` int(11) DEFAULT NULL COMMENT '登陆次数',
  `admin_remarks` text COMMENT '备注',
  `pro_signs` text COMMENT '产品标识符，设置以后只能查看此产品标识符的订单，多个产品标识符中间用,分隔。为空时默认可以查看所有产品订单。'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `cz_administrators` (`admin_id`, `admin_name`, `admin_password`, `admin_password_rnd`, `role_id`, `is_enabled`, `real_name`, `tel`, `email`, `wechat`, `qq`, `last_login_ip`, `last_login_time`, `last_logout_time`, `login_times`, `admin_remarks`, `pro_signs`) VALUES
(1, 'caozha', '5fd9cd58f4e516bae46557b355c5208a', NULL, 1, 1, '草札', '1320000000', 'dzh188@qq.com', 'wx', '123456', '127.0.0.1', '2020-07-06 17:10:34', '2020-07-02 11:00:38', 91, 'caozha.com', '');

CREATE TABLE `cz_order` (
  `id` int(11) NOT NULL COMMENT '订单ID',
  `realname` varchar(255) DEFAULT NULL COMMENT '收货人',
  `gender` varchar(10) DEFAULT NULL COMMENT '性别',
  `tel` varchar(255) DEFAULT NULL COMMENT '电话',
  `addresss` varchar(500) DEFAULT NULL COMMENT '地址',
  `payment` tinyint(1) DEFAULT '1' COMMENT '付款方式：1=货到付款，2=款到发货，3=在线支付',
  `quantity` int(11) DEFAULT '1' COMMENT '订购数量',
  `amount` decimal(11,2) DEFAULT '0.00' COMMENT '订单金额',
  `remarks` text COMMENT '客户备注',
  `pro_name` varchar(255) DEFAULT NULL COMMENT '产品名称',
  `pro_options` text COMMENT '订购选项',
  `pro_sign` varchar(50) DEFAULT NULL COMMENT '产品标识符',
  `wechat` varchar(50) DEFAULT NULL COMMENT '微信号',
  `qq` varchar(50) DEFAULT NULL COMMENT 'QQ',
  `email` varchar(50) DEFAULT NULL COMMENT '客户邮箱',
  `postal_code` varchar(50) DEFAULT NULL COMMENT '邮政编码',
  `addtime` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '下单时间',
  `listorder` int(11) DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) DEFAULT '1' COMMENT '订单状态，1为未处理，2为已处理，3为无效单',
  `admin_remarks` text COMMENT '管理员备注',
  `ip` varchar(50) DEFAULT '' COMMENT '客户IP',
  `is_show` tinyint(2) DEFAULT '0' COMMENT '是否已经查看',
  `client` varchar(200) DEFAULT NULL COMMENT '客户端：如windows，iphone',
  `pro_url` varchar(500) DEFAULT NULL COMMENT '下单网址',
  `from_url` varchar(500) DEFAULT NULL COMMENT '访客来路',
  `is_del` tinyint(1) DEFAULT '0' COMMENT '订单是否已删除',
  `is_repeat` tinyint(1) DEFAULT '0' COMMENT '是否为重复订单，1为重复'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `cz_product` (
  `pro_id` int(11) NOT NULL,
  `pro_name` varchar(1000) DEFAULT NULL COMMENT '产品名称',
  `pro_options` text COMMENT '订购选项，依次顺序：描述|价格|是否选中，1为选中',
  `pro_sign` varchar(500) DEFAULT NULL COMMENT '产品标识符',
  `pro_payment` varchar(100) DEFAULT NULL COMMENT '付款方式序号，多个中间用,分隔',
  `pro_payment_checked` int(3) DEFAULT '1' COMMENT '付款方式默认选中项',
  `updatetime` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',
  `hits` int(11) DEFAULT '0' COMMENT '浏览次数',
  `listorder` int(11) DEFAULT '0' COMMENT '排序',
  `status` tinyint(4) DEFAULT '1' COMMENT '审核状态，1未审，2已审，3无效',
  `templates` varchar(500) DEFAULT 'default.html' COMMENT '订单模板',
  `tips_type` tinyint(1) DEFAULT '1' COMMENT '提醒类型，1=弹出成功对话框，2=跳转URL',
  `tips_text` varchar(500) DEFAULT NULL COMMENT '对话框提示语',
  `tips_url` varchar(200) DEFAULT NULL COMMENT '跳转的URL',
  `is_captcha` tinyint(1) DEFAULT '0' COMMENT '是否开启验证码，1=开启',
  `anti_time` int(11) DEFAULT '0' COMMENT '同一个IP或手机号多少分钟内允许提交',
  `anti_num` int(11) DEFAULT '0' COMMENT '同一个IP或手机号某分钟内最多提交多少次'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `cz_product` (`pro_id`, `pro_name`, `pro_options`, `pro_sign`, `pro_payment`, `pro_payment_checked`, `updatetime`, `hits`, `listorder`, `status`, `templates`, `tips_type`, `tips_text`, `tips_url`, `is_captcha`, `anti_time`, `anti_num`) VALUES
(1, '苹果iPhone11', '苹果iPhone11（买手机送充电宝2个）||3999||1\n苹果iPhone11（买2部手机送充电宝6个）||7998||0', 'iphone11', '1', 1, '2020-06-24 21:20:46', 0, 0, 2, 'default', 1, '订单提交成功！我们会尽快给您发货，谢谢您的支持！', '', 0, 0, 0),
(2, '小米10', '小米10（8GB+128GB）||3999||1||1\n小米10（8GB+256GB）||4299||0\n小米10（12GB+256GB）||4699||0', 'mi10', '1', 1, '2020-06-24 21:25:02', 0, 0, 2, 'green', 1, '订单提交成功！我们会尽快给您发货，谢谢您的支持！', '', 1, 0, 0),
(3, '华为HUAWEI P40 Pro', '华为 P40 Pro 5G全网通 8GB+128GB||5988||1\n华为 P40 Pro 5G全网通 8GB+256GB||6488||0||1\n华为 P40 Pro 5G全网通 8GB+512GB||7388||0', 'p40pro', '1', 1, '2020-06-29 19:19:19', 0, 0, 2, 'orangered', 1, '订单提交成功！我们会尽快给您发货，谢谢您的支持！', '', 1, 0, 0);

CREATE TABLE `cz_roles` (
  `role_id` int(11) NOT NULL COMMENT '权限组ID',
  `roles` text COLLATE utf8mb4_unicode_ci COMMENT '权限标识符，多个中间用,分隔',
  `role_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '权限组名称',
  `role_remarks` text COLLATE utf8mb4_unicode_ci COMMENT '备注',
  `is_enabled` tinyint(1) DEFAULT '1' COMMENT '是否启用，1为启用'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `cz_roles` (`role_id`, `roles`, `role_name`, `role_remarks`, `is_enabled`) VALUES
(1, 'config,roles,admin,log_view,log_del,mine,product,order_view,order_todo,order_del,order_export,order_recycle,order_upload,order_repeat,order_repeat_del', '超级管理员', '可使用后台所有功能', 1),
(2, 'article', '内容管理员', '测试停用', 0),
(3, 'article', '编辑', '只管理文章', 1);

CREATE TABLE `cz_syslog` (
  `log_id` int(11) NOT NULL,
  `log_content` text COMMENT '系统日志内容',
  `log_user` varchar(255) DEFAULT NULL COMMENT '操作管理员',
  `log_ip` varchar(50) DEFAULT NULL COMMENT 'IP',
  `log_datetime` datetime DEFAULT NULL COMMENT '操作时间'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='系统日志';

CREATE TABLE `cz_web_config` (
  `id` int(11) NOT NULL,
  `web_config` text COLLATE utf8mb4_unicode_ci
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `cz_web_config` (`id`, `web_config`) VALUES
(1, '{\"share_url\":\"http:\\/\\/www.caozha.com(\\u6b64URL\\u53ef\\u5728\\u7cfb\\u7edf\\u8bbe\\u7f6e\\u4e2d\\u4fee\\u6539)\",\"order_paginate\":\"1\",\"admin_limit\":\"15\",\"roles_limit\":\"15\",\"syslog_limit\":\"15\",\"order_limit\":\"10\",\"product_limit\":\"10\",\"order_upload_limit\":\"20\",\"order_upload_memory_limit\":\"1000\",\"order_repeat_check_limit\":\"1000\",\"layTableCheckbox\":\"on\",\"order_export_fields\":\"id,realname,tel,addresss,payment,quantity,amount,remarks,pro_name,pro_options,pro_sign,addtime,client,pro_url,from_url\",\"order_repeat_check_fields\":\"tel\"}');


ALTER TABLE `cz_administrators`
  ADD PRIMARY KEY (`admin_id`);

ALTER TABLE `cz_order`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `cz_product`
  ADD PRIMARY KEY (`pro_id`);

ALTER TABLE `cz_roles`
  ADD PRIMARY KEY (`role_id`);

ALTER TABLE `cz_syslog`
  ADD PRIMARY KEY (`log_id`);

ALTER TABLE `cz_web_config`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `cz_administrators`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '管理员ID', AUTO_INCREMENT=26;

ALTER TABLE `cz_order`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '订单ID';

ALTER TABLE `cz_product`
  MODIFY `pro_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `cz_roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '权限组ID', AUTO_INCREMENT=4;

ALTER TABLE `cz_syslog`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `cz_web_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
