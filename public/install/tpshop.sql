#
# Structure for table "ty_ad_position"
#

DROP TABLE IF EXISTS `ty_ad_position`;
CREATE TABLE `ty_ad_position` (
  `id` int(3) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `position_name` varchar(60) NOT NULL DEFAULT '' COMMENT '广告位置名称',
  `ad_width` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '广告位宽度',
  `ad_height` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '广告位高度',
  `position_desc` varchar(255) NOT NULL DEFAULT '' COMMENT '广告描述',
  `position_style` text COMMENT '模板',
  `is_open` tinyint(1) DEFAULT '0' COMMENT '0关闭1开启',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#
# Data for table "ty_ad_position"
#


#
# Structure for table "ty_admin"
#

DROP TABLE IF EXISTS `ty_admin`;
CREATE TABLE `ty_admin` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户id',
  `user_name` varchar(60) NOT NULL DEFAULT '' COMMENT '用户名',
  `email` varchar(60) NOT NULL DEFAULT '' COMMENT 'email',
  `password` varchar(60) NOT NULL DEFAULT '' COMMENT '密码',
  `ec_salt` varchar(10) DEFAULT NULL COMMENT '秘钥',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `last_ip` varchar(15) NOT NULL DEFAULT '' COMMENT '最后登录ip',
  `nav_list` text COMMENT '权限',
  `role_id` smallint(5) DEFAULT '0' COMMENT '角色id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

#
# Data for table "ty_admin"
#

INSERT INTO `ty_admin` VALUES (1,'admin','zcc_time@163.com','51A6BF408C813D238BE8C68B2D567DD8','男',1522050550,'127.0.0.1','管理员',1);

#
# Structure for table "ty_articles"
#

DROP TABLE IF EXISTS `ty_articles`;
CREATE TABLE `ty_articles` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '文章id',
  `title` varchar(150) NOT NULL DEFAULT '' COMMENT '文章标题',
  `content` longtext NOT NULL COMMENT '文章内容',
  `email` varchar(60) NOT NULL DEFAULT '' COMMENT '作者邮箱',
  `keywords` varchar(255) DEFAULT 'null' COMMENT '关键字',
  `article_type` tinyint(1) unsigned NOT NULL DEFAULT '2' COMMENT '文章类型',
  `link` varchar(255) NOT NULL DEFAULT '' COMMENT '链接地址',
  `abstract` mediumtext COMMENT '文章摘要',
  `publish_time` varchar(22) DEFAULT '0' COMMENT '发布时间',
  `thumb` varchar(255) DEFAULT '' COMMENT '缩略图',
  PRIMARY KEY (`id`),
  KEY `keywords` (`keywords`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

#
# Data for table "ty_articles"
#

#
# Structure for table "ty_banner"
#

DROP TABLE IF EXISTS `ty_banner`;
CREATE TABLE `ty_banner` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT 'bannerID',
  `type` int(11) NOT NULL DEFAULT '0' COMMENT 'banner type',
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT 'banner path',
  `time` int(11) NOT NULL DEFAULT '0' COMMENT 'banner time',
  `title` varchar(80) NOT NULL DEFAULT 'title' COMMENT 'banner title',
  `content` varchar(255) NOT NULL DEFAULT 'txt' COMMENT 'banner content',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

#
# Data for table "ty_banner"
#


#
# Structure for table "ty_classify"
#

DROP TABLE IF EXISTS `ty_classify`;
CREATE TABLE `ty_classify` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类ID',
  `path` varchar(200) NOT NULL COMMENT '全路径',
  `pid` int(11) NOT NULL COMMENT '父级ID',
  `name` varchar(30) NOT NULL COMMENT '分类名',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `level` int(10) NOT NULL DEFAULT '0' COMMENT '等级',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

#
# Data for table "ty_classify"
#

#
# Structure for table "ty_comment"
#

DROP TABLE IF EXISTS `ty_comment`;
CREATE TABLE `ty_comment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '评论id',
  `email` varchar(60) NOT NULL DEFAULT '' COMMENT 'email邮箱',
  `username` varchar(60) NOT NULL DEFAULT '' COMMENT '用户名',
  `content` text NOT NULL COMMENT '评论内容',
  `add_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `ip_address` varchar(15) NOT NULL DEFAULT '' COMMENT 'ip地址',
  `is_show` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否显示',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论用户',
  `img` text COMMENT '晒单图片',
  `deliver_rank` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '评价等级',
  `zan_num` int(10) NOT NULL DEFAULT '0' COMMENT '被赞数',
  `zan_userid` varchar(255) NOT NULL DEFAULT '' COMMENT '点赞用户id',
  `is_anonymous` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否匿名评价:0不是，1是',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#
# Data for table "ty_comment"
#


#
# Structure for table "ty_feedback"
#

DROP TABLE IF EXISTS `ty_feedback`;
CREATE TABLE `ty_feedback` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '留言ID',
  `user_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `user_name` varchar(60) NOT NULL DEFAULT '' COMMENT '用户名',
  `msg_title` varchar(200) NOT NULL DEFAULT '' COMMENT '留言标题',
  `msg_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '处理状态',
  `msg_way` varchar(30) NOT NULL DEFAULT '0' COMMENT '联系方式',
  `msg_content` text NOT NULL COMMENT '留言内容',
  `msg_time` varchar(20) NOT NULL DEFAULT '0' COMMENT '留言时间',
  `msg_img` varchar(200) NOT NULL DEFAULT '' COMMENT '留言图片',
  `msg_state` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '发布状态',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#
# Data for table "ty_feedback"
#


#
# Structure for table "ty_price"
#

DROP TABLE IF EXISTS `ty_price`;
CREATE TABLE `ty_price` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '商品id',
  `name` varchar(255) DEFAULT NULL COMMENT '商品名字',
  `link` varchar(255) DEFAULT NULL COMMENT '商品链接',
  `sketch` varchar(255) DEFAULT NULL COMMENT '商品简述',
  `tid` int(11) DEFAULT NULL COMMENT '分类id',
  `tpid` int(11) DEFAULT NULL COMMENT '分类路径',
  `unit` char(255) DEFAULT NULL COMMENT '商品单位',
  `attributes` int(11) DEFAULT NULL COMMENT '商品属性',
  `path` varchar(255) DEFAULT NULL COMMENT '商品图片',
  `curprice` int(11) DEFAULT NULL COMMENT '现价',
  `nowprice` int(11) DEFAULT NULL COMMENT '市场价',
  `inventory` int(11) DEFAULT NULL COMMENT '库存量',
  `restrict` int(11) DEFAULT NULL COMMENT '限制购买量',
  `already` int(11) DEFAULT NULL COMMENT '已经购买量',
  `freight` int(11) DEFAULT NULL COMMENT '运费',
  `status` tinyint(1) DEFAULT NULL COMMENT '默认0上架',
  `reorder` tinyint(1) DEFAULT NULL COMMENT '排序',
  `details` text COMMENT '详情信息',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

#
# Data for table "ty_price"
#