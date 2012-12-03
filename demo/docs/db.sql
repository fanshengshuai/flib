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

CREATE TABLE `blocks` (
  `bid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `page` int(10) unsigned NOT NULL DEFAULT '0',
  `area` int(10) unsigned NOT NULL DEFAULT '0',
  `block_type` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `title` text NOT NULL,
  `summary` text NOT NULL,
  `ext_fields` text NOT NULL COMMENT '扩展项目',
  `shownum` smallint(6) unsigned NOT NULL DEFAULT '0',
  `picwidth` smallint(6) unsigned NOT NULL DEFAULT '0',
  `picheight` smallint(6) unsigned NOT NULL DEFAULT '0',
  `target` varchar(255) NOT NULL DEFAULT '',
  `param` text NOT NULL,
  `cache_time` int(10) unsigned NOT NULL DEFAULT '0',
  `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间',
  `update_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  `remove_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '删除时间',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
  PRIMARY KEY (`bid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE `block_items` (
  `item_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `id` int(10) unsigned NOT NULL DEFAULT '0',
  `idtype` varchar(255) NOT NULL DEFAULT '',
  `itemtype` tinyint(1) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `pic` varchar(255) NOT NULL DEFAULT '',
  `picflag` tinyint(1) NOT NULL DEFAULT '0',
  `makethumb` tinyint(1) NOT NULL DEFAULT '0',
  `summary` text NOT NULL,
  `showstyle` text NOT NULL,
  `related` text NOT NULL,
  `fields` text NOT NULL,
  `displayorder` smallint(6) NOT NULL DEFAULT '0',
  `note` text NOT NULL COMMENT '注释',
  `startdate` int(10) unsigned NOT NULL DEFAULT '0',
  `enddate` int(10) unsigned NOT NULL DEFAULT '0',
  `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间',
  `update_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  `remove_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '删除时间',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
  PRIMARY KEY (`item_id`),
  KEY `bid` (`bid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '编号',
  `pid` int(11) NOT NULL COMMENT 'parent id',
  `category_name` varchar(20) NOT NULL COMMENT '分类名称',
  `create_time` date NOT NULL COMMENT '创建时间',
  `update_time` date NOT NULL COMMENT '更新时间',
  `remove_time` date NOT NULL COMMENT '删除时间',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='分类';

--
-- Table structure for table `certification`
--

--
-- Table structure for table `courses`
--

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

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `k` varchar(50) NOT NULL COMMENT 'key',
  `v` longtext NOT NULL COMMENT '数值',
  `create_time` date NOT NULL COMMENT '创建时间',
  `update_time` date NOT NULL COMMENT '更新时间',
  `remove_time` date NOT NULL COMMENT '删除时间',
  `status` tinyint(1) DEFAULT NULL COMMENT '状态',
  PRIMARY KEY (`k`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='设置表';

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
