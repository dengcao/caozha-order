-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2020-07-03 20:54:12
-- 服务器版本： 5.7.26
-- PHP 版本： 7.3.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `caozha_order`
--

-- --------------------------------------------------------

--
-- 表的结构 `cz_administrators`
--

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

--
-- 转存表中的数据 `cz_administrators`
--

INSERT INTO `cz_administrators` (`admin_id`, `admin_name`, `admin_password`, `admin_password_rnd`, `role_id`, `is_enabled`, `real_name`, `tel`, `email`, `wechat`, `qq`, `last_login_ip`, `last_login_time`, `last_logout_time`, `login_times`, `admin_remarks`, `pro_signs`) VALUES
(1, 'caozha', '5fd9cd58f4e516bae46557b355c5208a', NULL, 1, 1, '草札', '1320000000', 'dzh188@qq.com', 'wx', '123456', '127.0.0.1', '2020-07-02 11:03:34', '2020-07-02 11:00:38', 85, 'caozha.com', '');

-- --------------------------------------------------------

--
-- 表的结构 `cz_order`
--

CREATE TABLE `cz_order` (
  `order_id` int(11) NOT NULL COMMENT '订单ID',
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
  `is_del` tinyint(2) DEFAULT '0' COMMENT '订单是否已删除',
  `is_repeat` tinyint(1) DEFAULT '0' COMMENT '是否为重复订单，1为重复',
  `is_check_repeat` tinyint(1) DEFAULT '0' COMMENT '是否已经检测过，1=已检测过了'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `cz_order`
--

INSERT INTO `cz_order` (`order_id`, `realname`, `gender`, `tel`, `addresss`, `payment`, `quantity`, `amount`, `remarks`, `pro_name`, `pro_options`, `pro_sign`, `wechat`, `qq`, `email`, `postal_code`, `addtime`, `listorder`, `status`, `admin_remarks`, `ip`, `is_show`, `client`, `pro_url`, `from_url`, `is_del`, `is_repeat`, `is_check_repeat`) VALUES
(1, '草札测试', NULL, '13286895000', '广西/@/贵港市/@/港北区/@/测试地址', 1, 1, '3999.00', '', '小米10', '小米10（8GB+128GB）', 'mi10', NULL, NULL, NULL, NULL, '2020-06-24 21:31:58', 0, 1, NULL, '127.0.0.1', 0, 'Windows 10（Safari 537.36）', 'http://order/admin/product/index.html', '', 0, 0, 1),
(2, '测试', NULL, '13212345600', '山西省/@/大同市/@/阳高县/@/测试地址', 1, 1, '7998.00', '', '苹果iPhone11', '苹果iPhone11（买2部手机送充电宝6个）', 'iphone11', NULL, NULL, NULL, NULL, '2020-06-24 21:33:17', 0, 1, NULL, '127.0.0.1', 0, 'Windows 10（Safari 537.36）', 'http://order/admin/product/index.html', '', 0, 0, 1),
(3, '张飞', NULL, '13212345612', '山西省/@/大同市/@/矿区/@/XX小区', 1, 1, '3999.00', '快点发货哦', '苹果iPhone11', '苹果iPhone11（买手机送充电宝2个）', 'iphone11', NULL, NULL, NULL, NULL, '2020-06-24 21:34:06', 0, 1, NULL, '127.0.0.1', 0, 'Windows 10（Safari 537.36）', 'http://order/admin/product/index.html', '', 0, 0, 1),
(4, '王生', NULL, '13266521121', '吉林省/@/长春市/@/朝阳区/@/南天门XX街', 1, 1, '3999.00', '急用，快发货', '苹果iPhone11', '苹果iPhone11（买手机送充电宝2个）', 'iphone11', NULL, NULL, NULL, NULL, '2020-06-24 21:48:43', 0, 1, NULL, '127.0.0.1', 0, 'Windows 10（Safari 537.36）', 'http://order/admin/product/index.html', '', 0, 0, 1),
(5, '李明', NULL, '13286895001', '江苏省/@/南京市/@/白下区/@/XX小区2', 1, 2, '8598.00', '', '小米10', '小米10（8GB+256GB）', 'mi10', NULL, NULL, NULL, NULL, '2020-06-28 20:40:42', 0, 1, NULL, '127.0.0.1', 0, 'Windows 10（Safari 537.36）', 'http://order/index/order/view/sign/mi10.html?from_url=lailu.com', 'lailu.com', 0, 0, 1),
(6, '王小丫', NULL, '13212345613', '广西/@/桂林市/@/七星区/@/测试地址', 1, 1, '0.00', '', '苹果iPhone11', '苹果iPhone11（买手机送充电宝2个）', 'iphone11', NULL, NULL, NULL, NULL, '2020-06-29 20:54:55', 0, 1, NULL, '127.0.0.1', 0, 'Windows 10（Safari 537.36）', 'http://order/admin/product/index.html', '', 1, 0, 1),
(7, '刘飞', NULL, '13266521121', '天津市/@/null/@/null/@/测试地址', 1, 1, '0.00', '', '苹果iPhone11', '苹果iPhone11（买手机送充电宝2个）', 'iphone11', NULL, NULL, NULL, NULL, '2020-06-29 20:56:09', 0, 1, NULL, '127.0.0.1', 0, 'Windows 10（Safari 537.36）', 'http://order/admin/product/index.html', '', 1, 1, 1),
(8, '测试', NULL, '13286895000', '天津市/@/天津市/@/河西区/@/测试地址', 1, 1, '0.00', '', '苹果iPhone11', '苹果iPhone11（买2部手机送充电宝6个）', 'iphone11', NULL, NULL, NULL, NULL, '2020-06-29 20:58:33', 0, 1, NULL, '127.0.0.1', 0, 'Windows 10（Safari 537.36）', 'http://order/admin/product/index.html', '', 1, 1, 1),
(9, '测试', NULL, '13212345600', '北京市/@/地级市/@/市、县级市/@/测试地址', 1, 1, '0.00', '', '苹果iPhone11', '苹果iPhone11（买手机送充电宝2个）', 'iphone11', NULL, NULL, NULL, NULL, '2020-06-29 21:01:54', 0, 1, NULL, '127.0.0.1', 0, 'Windows 10（Safari 537.36）', 'http://order/admin/product/index.html', '', 1, 1, 1),
(10, '测试', NULL, '13286895000', '上海市/@/上海市/@/黄浦区/@/测试地址', 1, 1, '5988.00', '', '华为HUAWEI P40 Pro', '华为 P40 Pro 5G全网通 8GB+128GB', 'p40pro', NULL, NULL, NULL, NULL, '2020-06-29 21:04:16', 0, 1, NULL, '127.0.0.1', 0, 'Windows 10（Safari 537.36）', 'http://order/admin/product/index.html', '', 0, 1, 1),
(12, '测试', NULL, '13286895000', '河北省/@/秦皇岛市/@/昌黎县/@/测试', 1, 1, '3999.00', '', '苹果iPhone11', '苹果iPhone11（买手机送充电宝2个）', 'iphone11', NULL, NULL, NULL, NULL, '2020-06-30 10:29:38', 0, 1, NULL, '127.0.0.1', 0, 'Windows 10（Safari 537.36）', 'http://order/index/index/demo.html', 'http%3A%2F%2Forder%2F', 0, 1, 1),
(13, 'ces', NULL, '13286895000', '上海市/@/上海市/@/卢湾区/@/测试地址', 1, 1, '7998.00', '', '苹果iPhone11', '苹果iPhone11（买2部手机送充电宝6个）', 'iphone11', NULL, NULL, NULL, NULL, '2020-06-30 11:39:45', 0, 1, NULL, '127.0.0.1', 0, 'Windows 10（Safari 537.36）', 'http://order/index/index/demo.html', 'http://order/', 0, 1, 1),
(14, '刘大铭', NULL, '13212345616', '天津市/@/市辖区/@/和平区/@/XX小区', 1, 2, '12976.00', '', '华为HUAWEI P40 Pro', '华为 P40 Pro 5G全网通 8GB+256GB', 'p40pro', NULL, NULL, NULL, NULL, '2020-06-30 15:07:31', 0, 1, NULL, '127.0.0.1', 0, 'Windows 10（Safari 537.36）', 'http://order/index/index/demo/sign/p40pro.html', 'http://order/', 0, 0, 1),
(15, '测试1', NULL, '13286890001', '广东省广州市', 1, 1, '3999.00', '', '苹果iPhone11', '苹果iPhone11（买手机送充电宝2个）', 'iphone11', NULL, NULL, NULL, NULL, '2020-06-24 21:31:58', 0, 1, NULL, '113.111.181.111', 0, NULL, '', '', 0, 0, 1),
(16, '测试2', NULL, '13286890002', '广东省广州市', 1, 1, '3999.00', '', '苹果iPhone11', '苹果iPhone11（买手机送充电宝2个）', 'iphone11', NULL, NULL, NULL, NULL, '2020-06-24 21:31:58', 0, 1, NULL, '113.111.181.222', 0, NULL, '', '', 0, 0, 1);

-- --------------------------------------------------------

--
-- 表的结构 `cz_product`
--

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

--
-- 转存表中的数据 `cz_product`
--

INSERT INTO `cz_product` (`pro_id`, `pro_name`, `pro_options`, `pro_sign`, `pro_payment`, `pro_payment_checked`, `updatetime`, `hits`, `listorder`, `status`, `templates`, `tips_type`, `tips_text`, `tips_url`, `is_captcha`, `anti_time`, `anti_num`) VALUES
(1, '苹果iPhone11', '苹果iPhone11（买手机送充电宝2个）||3999||1\n苹果iPhone11（买2部手机送充电宝6个）||7998||0', 'iphone11', '1', 1, '2020-06-24 21:20:46', 0, 0, 2, 'default', 1, '订单提交成功！我们会尽快给您发货，谢谢您的支持！', '', 0, 0, 0),
(2, '小米10', '小米10（8GB+128GB）||3999||1||1\n小米10（8GB+256GB）||4299||0\n小米10（12GB+256GB）||4699||0', 'mi10', '1', 1, '2020-06-24 21:25:02', 0, 0, 2, 'green', 1, '订单提交成功！我们会尽快给您发货，谢谢您的支持！', '', 1, 0, 0),
(3, '华为HUAWEI P40 Pro', '华为 P40 Pro 5G全网通 8GB+128GB||5988||1\n华为 P40 Pro 5G全网通 8GB+256GB||6488||0||1\n华为 P40 Pro 5G全网通 8GB+512GB||7388||0', 'p40pro', '1', 1, '2020-06-29 19:19:19', 0, 0, 2, 'orangered', 1, '订单提交成功！我们会尽快给您发货，谢谢您的支持！', '', 1, 0, 0);

-- --------------------------------------------------------

--
-- 表的结构 `cz_roles`
--

CREATE TABLE `cz_roles` (
  `role_id` int(11) NOT NULL COMMENT '权限组ID',
  `roles` text COLLATE utf8mb4_unicode_ci COMMENT '权限标识符，多个中间用,分隔',
  `role_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '权限组名称',
  `role_remarks` text COLLATE utf8mb4_unicode_ci COMMENT '备注',
  `is_enabled` tinyint(1) DEFAULT '1' COMMENT '是否启用，1为启用'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `cz_roles`
--

INSERT INTO `cz_roles` (`role_id`, `roles`, `role_name`, `role_remarks`, `is_enabled`) VALUES
(1, 'config,roles,admin,log_view,log_del,mine,product,order_view,order_todo,order_del,order_export,order_recycle,order_upload,order_repeat,order_repeat_del', '超级管理员', '可使用后台所有功能', 1),
(2, 'article', '内容管理员', '测试停用', 0),
(3, 'article', '编辑', '只管理文章', 1);

-- --------------------------------------------------------

--
-- 表的结构 `cz_syslog`
--

CREATE TABLE `cz_syslog` (
  `log_id` int(11) NOT NULL,
  `log_content` text COMMENT '系统日志内容',
  `log_user` varchar(255) DEFAULT NULL COMMENT '操作管理员',
  `log_ip` varchar(50) DEFAULT NULL COMMENT 'IP',
  `log_datetime` datetime DEFAULT NULL COMMENT '操作时间'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='系统日志';

--
-- 转存表中的数据 `cz_syslog`
--

INSERT INTO `cz_syslog` (`log_id`, `log_content`, `log_user`, `log_ip`, `log_datetime`) VALUES
(384, '删除2020-06-26 00:00:00之前的所有系统日志（共296条）（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-07-03 20:53:48'),
(299, '登陆成功（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-28 11:02:22'),
(300, '下载订单：客户订单(共1条)_20200628143029.csv，s=admin/order/get.html&is_del=1&is_export=1&keyword=&status=&payment=&starttime=2020-05-01%2000%3A00%3A00&endtime=2020-05-31%2023%3A59%3A59&keyword=&status=&payment=&starttime=2020-06-01%2000%3A00%3A00&endtime=2020-06-28%2023%3A59%3A59&export_type=csv（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-28 14:30:29'),
(301, '登陆成功（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-28 15:44:28'),
(302, '修改产品，ID：2（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-28 16:23:57'),
(303, '软删除订单(ID)：8,7,6,5（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-28 20:36:26'),
(304, '修改产品，ID：1（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-28 21:32:52'),
(305, '修改产品，ID：1（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-28 21:36:05'),
(306, '修改产品，ID：1（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-28 21:36:10'),
(307, '修改产品，ID：1（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-28 21:36:37'),
(308, '修改产品，ID：2（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-28 21:36:57'),
(309, '修改产品，ID：2（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-28 21:37:08'),
(310, '修改产品，ID：1（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-28 21:38:57'),
(311, '修改产品，ID：1（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-28 21:39:09'),
(312, '修改产品，ID：2（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-28 21:43:16'),
(313, '修改产品，ID：2（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-28 21:48:32'),
(314, '修改产品，ID：2（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-28 21:49:02'),
(315, '修改产品，ID：2（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-28 21:56:05'),
(316, '登陆成功（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-29 09:48:15'),
(317, '登陆成功（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-29 17:20:34'),
(318, '修改产品，ID：1（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-29 19:14:50'),
(319, '修改产品，ID：1（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-29 19:15:00'),
(320, '新增产品，ID：3（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-29 19:19:19'),
(321, '修改产品，ID：3（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-29 19:20:10'),
(322, '修改产品，ID：2（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-29 19:36:57'),
(323, '修改产品，ID：1（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-29 20:27:00'),
(324, '修改产品，ID：1（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-29 20:28:31'),
(325, '修改产品，ID：1（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-29 20:55:02'),
(326, '修改产品，ID：1（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-29 21:01:18'),
(327, '修改产品，ID：3（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-29 21:03:58'),
(328, '登陆成功（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-30 09:51:53'),
(329, '下载订单：客户订单(共11条)_20200630112222.csv，s=admin/order/get.html&is_export=1&export_type=csv（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-30 11:22:22'),
(330, '下载订单：客户订单(共11条)_20200630112420.xls，s=admin/order/get.html&is_export=1&export_type=xls（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-30 11:24:20'),
(331, '下载订单：客户订单(共1条)_20200630112536.csv，s=admin/order/get.html&is_export=1&keyword=&status=&payment=&starttime=&endtime=&keyword=&status=&payment=&starttime=2020-06-28%2000%3A00%3A00&endtime=2020-06-28%2023%3A59%3A59&export_type=csv（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-30 11:25:36'),
(332, '下载订单：客户订单(共11条)_20200630112622.xls，s=admin/order/get.html&is_export=1&export_type=xls（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-30 11:26:22'),
(333, '下载订单：客户订单(共11条)_20200630112630.csv，s=admin/order/get.html&is_export=1&export_type=csv（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-30 11:26:30'),
(334, '下载订单：客户订单(共11条)_20200630113033.csv，s=admin/order/get.html&is_export=1&export_type=csv（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-30 11:30:33'),
(335, '下载订单：客户订单(共11条)_20200630113041.csv，s=admin/order/get.html&is_export=1&export_type=csv（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-30 11:30:41'),
(336, '下载订单：客户订单(共11条)_20200630113404.csv，s=admin/order/get.html&is_export=1&export_type=csv（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-30 11:34:04'),
(337, '下载订单：客户订单(共12条)_20200630114005.csv，s=admin/order/get.html&is_export=1&export_type=csv（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-30 11:40:05'),
(338, '修改产品，ID：3（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-30 12:04:02'),
(339, '修改产品，ID：3（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-30 13:52:59'),
(340, '修改产品，ID：2（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-30 13:53:53'),
(341, '修改产品，ID：3（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-30 13:55:06'),
(342, '修改产品，ID：1（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-30 14:11:42'),
(343, '修改产品，ID：3（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-30 14:11:50'),
(344, '修改产品，ID：1（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-30 14:14:40'),
(345, '软删除订单(ID)：11,9,8,7（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-30 14:58:13'),
(346, '软删除订单(ID)：6（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-30 14:58:27'),
(347, '修改权限组：超级管理员，ID：1（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-06-30 18:57:49'),
(348, '登陆成功（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-07-01 10:25:27'),
(349, '登陆成功（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-07-01 15:42:06'),
(350, '批量检测重复订单。（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-07-01 20:10:57'),
(351, '批量删除3条重复订单。（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-07-01 20:11:06'),
(352, '恢复订单(ID)：13,12,10（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-07-01 20:11:40'),
(353, '恢复订单(ID)：2（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-07-01 20:42:56'),
(354, '恢复订单(ID)：9,8,7,6（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-07-01 20:43:07'),
(355, '清空订单回收站，共彻底删除了1条订单。（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-07-01 20:43:13'),
(356, '软删除订单(ID)：9,8,7,6（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-07-01 20:43:34'),
(357, '修改系统设置（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-07-02 02:15:51'),
(358, '修改系统设置（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-07-02 02:17:05'),
(359, '修改系统设置（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-07-02 02:18:18'),
(360, '修改系统设置（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-07-02 02:21:47'),
(361, '修改系统设置（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-07-02 02:22:05'),
(362, '修改系统设置（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-07-02 02:22:51'),
(363, '修改系统设置（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-07-02 02:25:10'),
(364, '修改系统设置（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-07-02 02:25:14'),
(365, '修改系统设置（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-07-02 02:25:30'),
(366, '修改系统设置（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-07-02 02:26:03'),
(367, '修改系统设置（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-07-02 02:26:07'),
(368, '修改系统设置（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-07-02 02:26:18'),
(369, '修改系统设置（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-07-02 02:32:23'),
(370, '修改系统设置（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-07-02 02:45:54'),
(371, '批量检测重复订单。（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-07-02 02:46:16'),
(372, '修改系统设置（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-07-02 09:58:56'),
(373, '修改管理员账号：caozha，ID：1（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-07-02 10:15:25'),
(374, '修改管理员账号：caozha，ID：1（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-07-02 10:48:49'),
(375, '退出登陆（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-07-02 11:00:38'),
(376, '登陆成功（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-07-02 11:03:34'),
(377, '修改管理员账号：caozha，ID：1（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-07-02 11:26:23'),
(378, '修改管理员账号：caozha，ID：1（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-07-02 11:26:29'),
(379, '修改系统设置（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-07-03 20:51:56'),
(380, '修改系统设置（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-07-03 20:52:15'),
(381, '删除管理员账号(ID)：25,24,14,12,11,10,9,8,7,6,5,4,3,2（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-07-03 20:52:58'),
(382, '修改管理员账号：caozha，ID：1（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-07-03 20:53:10'),
(383, '修改管理员账号：caozha，ID：1（Safari 537.36，Windows 10）', 'caozha（ID:1，姓名:草札）', '127.0.0.1', '2020-07-03 20:53:27');

-- --------------------------------------------------------

--
-- 表的结构 `cz_web_config`
--

CREATE TABLE `cz_web_config` (
  `id` int(11) NOT NULL,
  `web_config` text COLLATE utf8mb4_unicode_ci
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `cz_web_config`
--

INSERT INTO `cz_web_config` (`id`, `web_config`) VALUES
(1, '{\"share_url\":\"http:\\/\\/www.caozha.com(\\u6b64URL\\u53ef\\u5728\\u7cfb\\u7edf\\u8bbe\\u7f6e\\u4e2d\\u4fee\\u6539)\",\"admin_limit\":\"15\",\"roles_limit\":\"15\",\"syslog_limit\":\"15\",\"order_limit\":\"10\",\"product_limit\":\"10\",\"order_upload_limit\":\"20\",\"order_upload_memory_limit\":\"1000\",\"order_repeat_check_limit\":\"500\",\"layTableCheckbox\":\"on\",\"order_repeat_check_fields\":\"tel\"}');

--
-- 转储表的索引
--

--
-- 表的索引 `cz_administrators`
--
ALTER TABLE `cz_administrators`
  ADD PRIMARY KEY (`admin_id`);

--
-- 表的索引 `cz_order`
--
ALTER TABLE `cz_order`
  ADD PRIMARY KEY (`order_id`);

--
-- 表的索引 `cz_product`
--
ALTER TABLE `cz_product`
  ADD PRIMARY KEY (`pro_id`);

--
-- 表的索引 `cz_roles`
--
ALTER TABLE `cz_roles`
  ADD PRIMARY KEY (`role_id`);

--
-- 表的索引 `cz_syslog`
--
ALTER TABLE `cz_syslog`
  ADD PRIMARY KEY (`log_id`);

--
-- 表的索引 `cz_web_config`
--
ALTER TABLE `cz_web_config`
  ADD PRIMARY KEY (`id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `cz_administrators`
--
ALTER TABLE `cz_administrators`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '管理员ID', AUTO_INCREMENT=26;

--
-- 使用表AUTO_INCREMENT `cz_order`
--
ALTER TABLE `cz_order`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '订单ID', AUTO_INCREMENT=17;

--
-- 使用表AUTO_INCREMENT `cz_product`
--
ALTER TABLE `cz_product`
  MODIFY `pro_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `cz_roles`
--
ALTER TABLE `cz_roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '权限组ID', AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `cz_syslog`
--
ALTER TABLE `cz_syslog`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=385;

--
-- 使用表AUTO_INCREMENT `cz_web_config`
--
ALTER TABLE `cz_web_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
