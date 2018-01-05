/*
Navicat MySQL Data Transfer

Source Server         : fusion2
Source Server Version : 50551
Source Host           : fusioncomunicacao.com:3306
Source Database       : fusio792_cdpv

Target Server Type    : MYSQL
Target Server Version : 50551
File Encoding         : 65001

Date: 2018-01-05 01:55:52
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `clients`
-- ----------------------------
DROP TABLE IF EXISTS `clients`;
CREATE TABLE `clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `comments` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=509 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of clients
-- ----------------------------
INSERT INTO `clients` VALUES ('502', '2018-01-04 22:43:38', '2018-01-04 22:43:38', 'e673d75d7dcb3951d1f52a9d2d36f9f4.png', null, '');
INSERT INTO `clients` VALUES ('503', '2018-01-04 22:43:47', '2018-01-04 22:43:47', '64828048d9eece17472f8fa074473670.png', null, '');
INSERT INTO `clients` VALUES ('504', '2018-01-04 22:43:56', '2018-01-04 22:43:56', '5bed96dfb7ca448d2071dc127edca88c.png', null, '');
INSERT INTO `clients` VALUES ('505', '2018-01-04 22:44:08', '2018-01-04 22:44:08', 'bb9dfea91203d62faf1973b3226812a8.png', null, '');
INSERT INTO `clients` VALUES ('506', '2018-01-04 22:44:24', '2018-01-04 22:44:24', 'f7a208fcd48f07ffb56e5aec4ec5bc70.png', null, '');
INSERT INTO `clients` VALUES ('507', '2018-01-04 22:44:37', '2018-01-05 02:53:27', 'eef5a2869149c894ed31036eee02609c.png', null, '');

-- ----------------------------
-- Table structure for `event_categories`
-- ----------------------------
DROP TABLE IF EXISTS `event_categories`;
CREATE TABLE `event_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of event_categories
-- ----------------------------
INSERT INTO `event_categories` VALUES ('1', null, null, 'Eventos Comerciais');

-- ----------------------------
-- Table structure for `event_comments`
-- ----------------------------
DROP TABLE IF EXISTS `event_comments`;
CREATE TABLE `event_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `comment` text,
  `event_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of event_comments
-- ----------------------------
INSERT INTO `event_comments` VALUES ('1', '2018-01-05 01:37:34', '2018-01-05 01:37:34', 'Alan Quidornne de Souza', 'aquidornne@gmail.com', 'Teste de comentário...', '500');

-- ----------------------------
-- Table structure for `events`
-- ----------------------------
DROP TABLE IF EXISTS `events`;
CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `cover` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text,
  `resume` varchar(255) DEFAULT NULL,
  `event_category_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=503 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of events
-- ----------------------------
INSERT INTO `events` VALUES ('500', '2018-01-05 00:00:32', '2018-01-05 00:00:32', '840208625ebc2fe51dc226ceb5cafe4e.jpg', 'Evento 01', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', null);
INSERT INTO `events` VALUES ('501', '2018-01-05 00:01:00', '2018-01-05 00:01:00', '2db76b60b06303dcb7fff5933473c10e.jpg', 'Evento 02', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.', null);
INSERT INTO `events` VALUES ('502', '2018-01-05 00:01:33', '2018-01-05 00:01:33', '37fe602570fc101dedd9789d28156595.jpg', 'Evento 03', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.', null);

-- ----------------------------
-- Table structure for `role_permissions`
-- ----------------------------
DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `modified_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`role_id`,`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AVG_ROW_LENGTH=240;

-- ----------------------------
-- Records of role_permissions
-- ----------------------------
INSERT INTO `role_permissions` VALUES ('1', '0', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '1', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '2', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '3', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '4', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '5', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '6', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '8', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '9', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '10', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '11', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '12', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '13', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '14', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '15', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '16', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '17', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '18', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '19', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '20', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '21', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '22', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '23', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '24', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '25', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '26', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '27', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '28', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '29', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '30', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '31', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '32', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '33', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '34', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '35', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '36', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '37', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '38', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '39', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '40', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '41', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '42', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '43', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '44', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '45', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '46', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '47', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '48', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '49', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '50', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '51', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '52', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '53', '2', null);
INSERT INTO `role_permissions` VALUES ('1', '54', '2', null);
INSERT INTO `role_permissions` VALUES ('2', '0', null, null);
INSERT INTO `role_permissions` VALUES ('2', '1', null, null);
INSERT INTO `role_permissions` VALUES ('2', '2', null, null);
INSERT INTO `role_permissions` VALUES ('2', '3', null, null);
INSERT INTO `role_permissions` VALUES ('2', '4', null, null);
INSERT INTO `role_permissions` VALUES ('2', '5', null, null);
INSERT INTO `role_permissions` VALUES ('2', '6', null, null);
INSERT INTO `role_permissions` VALUES ('2', '7', null, null);
INSERT INTO `role_permissions` VALUES ('2', '8', null, null);
INSERT INTO `role_permissions` VALUES ('2', '9', null, null);
INSERT INTO `role_permissions` VALUES ('3', '0', null, null);
INSERT INTO `role_permissions` VALUES ('3', '1', null, null);
INSERT INTO `role_permissions` VALUES ('3', '2', null, null);
INSERT INTO `role_permissions` VALUES ('6', '0', null, null);
INSERT INTO `role_permissions` VALUES ('6', '2', null, null);
INSERT INTO `role_permissions` VALUES ('6', '3', null, null);

-- ----------------------------
-- Table structure for `roles`
-- ----------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `modified_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 AVG_ROW_LENGTH=16384;

-- ----------------------------
-- Records of roles
-- ----------------------------
INSERT INTO `roles` VALUES ('1', 'Administrador', null, '2');

-- ----------------------------
-- Table structure for `users`
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL,
  `active` tinyint(4) DEFAULT NULL,
  `photo` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES ('4', '2015-05-07 09:55:14', '2017-03-17 02:28:47', 'Alan', 'aquidornne@gmail.com', '6edfe3caf10528cbed1599e5913b7cf513f75ce9', '1', '1', '86208d4e152ef52c2da09f3f798c2ff2.JPG');
