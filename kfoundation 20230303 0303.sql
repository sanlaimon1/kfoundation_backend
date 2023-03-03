-- MySQL Administrator dump 1.4
--
-- ------------------------------------------------------
-- Server version	8.0.31


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


--
-- Create schema kfoundation
--

CREATE DATABASE IF NOT EXISTS kfoundation;
USE kfoundation;

--
-- Definition of table `admins`
--

DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '用户账号',
  `password` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '用户密码',
  `login_at` timestamp NULL DEFAULT NULL COMMENT '登录时间',
  `desc` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '备注说明',
  `status` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '状态(0禁用,1启用)',
  `is_deleted` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '删除(1删除,0未删)',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `remember_token` varchar(200) DEFAULT NULL COMMENT 'token',
  `salt` varchar(6) DEFAULT NULL COMMENT '盐',
  `tick` tinyint unsigned NOT NULL DEFAULT '5' COMMENT '尝试次数',
  `rid` int unsigned NOT NULL DEFAULT '1' COMMENT '角色表',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `Index_2` (`username`),
  KEY `FK_admins_1` (`rid`),
  CONSTRAINT `FK_admins_1` FOREIGN KEY (`rid`) REFERENCES `roles` (`rid`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=10024 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC COMMENT='系统-用户';

--
-- Dumping data for table `admins`
--

/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` (`id`,`username`,`password`,`login_at`,`desc`,`status`,`is_deleted`,`create_at`,`remember_token`,`salt`,`tick`,`rid`) VALUES 
 (10000,'admin11','b75a45fde753a2c4c818efd9ff1f2e14','2023-03-02 18:53:28','系统管理员',1,0,'2022-02-28 15:02:22',NULL,'DSER34',5,2),
 (10001,'admin22','68a59d96778986571ee6b868ac5765f3',NULL,'普通管理员1',1,0,'2023-02-28 17:04:42',NULL,'HJKKL1',5,1),
 (10002,'admin33','e20d7e7bf620df82a8d55cb41ec68c23',NULL,'普通管理员2',1,0,'2023-03-02 11:07:40',NULL,'ETrp36',5,1),
 (10022,'admin44','48ef5ae61765867210f8fa7308be324f',NULL,'新加测试用户',1,0,'2023-03-02 18:19:47',NULL,'Q6POuj',5,3),
 (10023,'admin55','33baf0f10fcc0ee99c274f96bce11a97','2023-03-02 18:24:10','新加测试用户',1,0,'2023-03-02 18:23:48',NULL,'nKz2YQ',5,1);
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;


--
-- Definition of table `articles`
--

DROP TABLE IF EXISTS `articles`;
CREATE TABLE `articles` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(45) NOT NULL COMMENT '标题',
  `content` text COMMENT '内容',
  `categoryid` int unsigned NOT NULL COMMENT '分类',
  `adminid` bigint unsigned NOT NULL COMMENT '管理员id',
  `created_at` timestamp NOT NULL COMMENT '创建时间',
  `udpated_at` timestamp NULL DEFAULT NULL COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  KEY `FK_articles_1` (`categoryid`),
  KEY `FK_articles_2` (`adminid`),
  CONSTRAINT `FK_articles_1` FOREIGN KEY (`categoryid`) REFERENCES `categories` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `FK_articles_2` FOREIGN KEY (`adminid`) REFERENCES `admins` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='文章表';

--
-- Dumping data for table `articles`
--

/*!40000 ALTER TABLE `articles` DISABLE KEYS */;
/*!40000 ALTER TABLE `articles` ENABLE KEYS */;


--
-- Definition of table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `cate_name` varchar(45) NOT NULL COMMENT '分类名称',
  `sort` int unsigned NOT NULL COMMENT '排序',
  `enable` tinyint unsigned NOT NULL COMMENT '启用 1 不启用 0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='文章分类表';

--
-- Dumping data for table `categories`
--

/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;


--
-- Definition of table `configs`
--

DROP TABLE IF EXISTS `configs`;
CREATE TABLE `configs` (
  `cid` int unsigned NOT NULL AUTO_INCREMENT,
  `config_name` varchar(45) NOT NULL COMMENT '配置名称',
  `config_value` text COMMENT '配置值',
  `cate` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '分类',
  `comment` varchar(45) NOT NULL COMMENT '备注',
  PRIMARY KEY (`cid`),
  UNIQUE KEY `Index_2` (`config_name`),
  KEY `Index_3` (`cate`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb3 COMMENT='配置';

--
-- Dumping data for table `configs`
--

/*!40000 ALTER TABLE `configs` DISABLE KEYS */;
INSERT INTO `configs` (`cid`,`config_name`,`config_value`,`cate`,`comment`) VALUES 
 (1,'website','SCHENKER',1,'网站名称'),
 (2,'domain_name','www.14kv66f.cn',1,'网站域名'),
 (3,'customer_service','https://wwwddsdfds.com',1,'客服链接'),
 (4,'min_withdrawal','100',1,'最低提现金额'),
 (5,'min_charge','100',1,'最低充值金额'),
 (6,'times_withdrawal_everyday','2',1,'每日提现次数'),
 (7,'kline_homepage','0',1,'首页K线'),
 (8,'logo','/logo.png',1,'网站logo'),
 (9,'video_homepage','/119.mp4',1,'首页视频'),
 (10,'window_details','简体',2,'弹窗详情'),
 (11,'is_shown','1',2,'是否显示');
/*!40000 ALTER TABLE `configs` ENABLE KEYS */;


--
-- Definition of table `inboxes`
--

DROP TABLE IF EXISTS `inboxes`;
CREATE TABLE `inboxes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(45) NOT NULL COMMENT '标题',
  `content` text COMMENT '内容',
  `read` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '未读 0  已读 1',
  `created_at` timestamp NOT NULL COMMENT '创建时间',
  `sort` int unsigned NOT NULL COMMENT '排序',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='站内信';

--
-- Dumping data for table `inboxes`
--

/*!40000 ALTER TABLE `inboxes` DISABLE KEYS */;
/*!40000 ALTER TABLE `inboxes` ENABLE KEYS */;


--
-- Definition of table `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `pid` int unsigned NOT NULL AUTO_INCREMENT,
  `show` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '是否显示 1 显示 0 不显示',
  `sort` int unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `level` int unsigned NOT NULL DEFAULT '0' COMMENT '成长值达标显示',
  `ptype` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '支付类型  1加密货币2支付宝3微信4银行卡',
  `rate` decimal(16,2) NOT NULL DEFAULT '0.00' COMMENT '汇率(货币->美USDT)',
  `mark` varchar(45) NOT NULL DEFAULT '$' COMMENT '货币符号',
  `logo` varchar(400) DEFAULT NULL COMMENT '支付方式LOGO',
  `give` decimal(16,2) NOT NULL DEFAULT '0.00' COMMENT '充值赠送比例',
  `description` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT '描述',
  `extra` json DEFAULT NULL COMMENT '附属属性',
  `payment_name` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT '支付名称',
  PRIMARY KEY (`pid`),
  KEY `Index_2` (`show`),
  KEY `Index_3` (`payment_name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COMMENT='支付方式';

--
-- Dumping data for table `payments`
--

/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` (`pid`,`show`,`sort`,`level`,`ptype`,`rate`,`mark`,`logo`,`give`,`description`,`extra`,`payment_name`) VALUES 
 (1,1,1,0,1,'6.80','USDT','/images/usdt.png','0.00','TRC20(USDT)',0x7B2263727970746F5F6C696E6B223A202254426A31313131313131313131313131313131313131313131313131317446222C202263727970746F5F7172636F6465223A20222F696D616765732F7172636F64652E706E67227D,'TRC20(USDT)'),
 (2,1,2,0,4,'1.00','￥','/images/icbc.png','0.00','网银充值',0x7B2262616E6B223A2022E4B8ADE59BBDE5869CE4B89AE993B6E8A18C222C202262616E6B5F6E616D65223A2022E6938DE4BDA0E5A688222C202262616E6B5F6163636F756E74223A202236323238343830303938303636383436227D,'网银支付'),
 (3,0,3,0,1,'6.80','USDT','/images/usdt.png','0.00','ERC20(USDT)',0x7B2263727970746F5F6C696E6B223A202254426A48687966644E55327236515353695435436E546F5847637250445164557446222C202263727970746F5F7172636F6465223A20222F696D616765732F7172636F64652E706E67227D,'ERC20(USDT)'),
 (4,0,4,0,1,'10000.00','BT','/images/bitcoin.png','0.00','比特币',0x7B2263727970746F5F6C696E6B223A202254426A48687966644E55327236515353695435436E546F5847637250445164557446222C202263727970746F5F7172636F6465223A20222F696D616765732F7172636F64652E706E67227D,'比特币');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;


--
-- Definition of table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `rid` int unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(45) NOT NULL COMMENT '标题',
  `status` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '状态 1 启用 0 屏蔽',
  `sort` int unsigned NOT NULL DEFAULT '1' COMMENT '排序',
  `desc` varchar(100) DEFAULT NULL COMMENT '描述',
  `created_at` timestamp NOT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  `auth` bigint unsigned NOT NULL DEFAULT '0' COMMENT '权限值',
  `auth2` bigint unsigned NOT NULL DEFAULT '0' COMMENT '子栏目权限值',
  PRIMARY KEY (`rid`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3 COMMENT='角色表';

--
-- Dumping data for table `roles`
--

/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` (`rid`,`title`,`status`,`sort`,`desc`,`created_at`,`updated_at`,`auth`,`auth2`) VALUES 
 (1,'未分配',1,0,'权限不可添加','2023-03-02 09:48:09',NULL,0,0),
 (2,'管理人员',1,1,NULL,'2023-03-02 09:49:09',NULL,0,0),
 (3,'出款审核',1,2,NULL,'2023-03-02 09:49:19',NULL,0,0),
 (4,'财务充值审核',1,3,NULL,'2023-03-02 09:49:27',NULL,0,0),
 (5,'总管理',1,4,NULL,'2023-03-02 09:49:37',NULL,0,0),
 (6,'商品上架',1,5,NULL,'2023-03-02 09:51:09',NULL,0,0),
 (7,'客服人员',1,6,NULL,'2023-03-02 09:52:09',NULL,0,0);
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;




/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
