
-- MySQL dump 10.13  Distrib 5.5.23, for osx10.5 (i386)
--
-- Host: localhost    Database: anjoyo_www
-- ------------------------------------------------------
-- Server version	5.5.23

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `attachments`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attachments` (
  `aid` int(11) NOT NULL AUTO_INCREMENT COMMENT '活动附件ID',
  `rel_id` int(11) NOT NULL COMMENT '关联ID',
  `school_id` int(11) NOT NULL DEFAULT '0' COMMENT '分校id',
  `attach_type` int(11) NOT NULL COMMENT '类型1为图片2为视频3上传图片',
  `attach_url` varchar(200) NOT NULL COMMENT '地址',
  `file_name` varchar(100) NOT NULL,
  `file_type` varchar(20) NOT NULL,
  `file_size` int(11) NOT NULL,
  `file_path` varchar(100) NOT NULL,
  `comment` varchar(200) NOT NULL COMMENT '备注说明',
  `create_time` datetime NOT NULL COMMENT '纪录创建时间',
  `update_time` datetime NOT NULL COMMENT '纪录更新时间',
  `remove_time` datetime NOT NULL COMMENT '纪录删除时间',
  `status` tinyint(1) NOT NULL COMMENT '状态',
  PRIMARY KEY (`aid`)
) ENGINE=MyISAM AUTO_INCREMENT=58 DEFAULT CHARSET=utf8 COMMENT='附件表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `category`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `category` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '编号',
  `category_name` varchar(20) NOT NULL COMMENT '分类名称',
  `create_time` date NOT NULL COMMENT '创建时间',
  `update_time` date NOT NULL COMMENT '更新时间',
  `remove_time` date NOT NULL COMMENT '删除时间',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='分类';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `certification`
--

--
-- Table structure for table `courses`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `friend_links` (
  `site_id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL DEFAULT '0' COMMENT '分校id',
  `site_name` varchar(100) NOT NULL COMMENT '站点名称',
  `site_url` varchar(100) NOT NULL COMMENT '站点URL',
  `display_order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `comment` varchar(200) NOT NULL COMMENT '描述',
  `create_time` datetime NOT NULL COMMENT '纪录创建时间',
  `update_time` datetime NOT NULL COMMENT '纪录更新时间',
  `remove_time` datetime NOT NULL COMMENT '纪录删除时间',
  `status` tinyint(1) NOT NULL COMMENT '状态',
  PRIMARY KEY (`site_id`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COMMENT='友情链接';
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news` (
  `news_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '编号',
  `cid` int(11) NOT NULL COMMENT '所属分类',
  `title` varchar(50) NOT NULL COMMENT '标题',
  `description` varchar(200) NOT NULL COMMENT '描述',
  `pic_url` varchar(100) NOT NULL COMMENT '图片',
  `content` text NOT NULL COMMENT '内容',
  `click_time` int(11) NOT NULL DEFAULT '1' COMMENT '点击次数',
  `create_time` date NOT NULL COMMENT '创建时间',
  `update_time` date NOT NULL COMMENT '更新时间',
  `remove_time` date NOT NULL COMMENT '删除时间',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '记录状态',
  PRIMARY KEY (`news_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='新闻';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `settings`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `k` varchar(50) NOT NULL COMMENT 'key',
  `v` longtext NOT NULL COMMENT '数值',
  `create_time` date NOT NULL COMMENT '创建时间',
  `update_time` date NOT NULL COMMENT '更新时间',
  `remove_time` date NOT NULL COMMENT '删除时间',
  `status` tinyint(1) DEFAULT NULL COMMENT '状态',
  PRIMARY KEY (`k`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='设置表';
/*!40101 SET character_set_client = @saved_cs_client */;

create table `slide_show`(
  `pic_id` int(11) not null auto_increment,
  `pic_url` varchar(50) not null comment '图片',
  `url` varchar(50) not null comment 'URL',
  `display_order` int(11) not null comment '排序',
  `school_id` int(11) not null comment '所属院校',
  `create_time` datetime NOT NULL COMMENT '纪录创建时间',
  `update_time` datetime NOT NULL COMMENT '纪录更新时间',
  `remove_time` datetime NOT NULL COMMENT '纪录删除时间',
  `status` tinyint(1) NOT NULL COMMENT '状态',
  primary key(`pic_id`)
)  ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='幻灯片';

CREATE TABLE `articles` (
    `article_id` int(11) NOT NULL AUTO_INCREMENT,
    `cat_id` int(11) NOT NULL COMMENT '分类ID',
    `view_count` int(5) NOT NULL DEFAULT '0',
    `display_order` int(5) NOT NULL DEFAULT '0' COMMENT '排序',
    `title` varchar(50) NOT NULL COMMENT '标题',
    `description` varchar(255) NOT NULL,
    `pic_url` varchar(200) NOT NULL COMMENT '图片',
    `content` longtext NOT NULL COMMENT '文章内容',
    `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间',
    `update_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
    `remove_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '删除时间',
    `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '记录状态',
    PRIMARY KEY (`article_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='文章';

CREATE TABLE `users` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL COMMENT '用户名',
  `password` varchar(50) NOT NULL COMMENT '密码',
  `phone` varchar(100) NOT NULL COMMENT '电话',
  `address` varchar(200) NOT NULL COMMENT '地址',
  `create_time` datetime NOT NULL COMMENT '纪录创建时间',
  `update_time` datetime NOT NULL COMMENT '纪录更新时间',
  `remove_time` datetime NOT NULL COMMENT '纪录删除时间',
  `status` tinyint(1) NOT NULL COMMENT '状态',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户表';

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2012-08-12  8:10:13