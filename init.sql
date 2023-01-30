-- ----------------------------
-- Table structure for mp_admin_handle_log
-- ----------------------------
DROP TABLE IF EXISTS `mp_admin_handle_log`;
CREATE TABLE `mp_admin_handle_log` (
                                       `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
                                       `admin_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '后台用户ID',
                                       `auth_rule_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '权限ID',
                                       `request_body` varchar(5000) NOT NULL DEFAULT '' COMMENT '请求内容',
                                       `response_body` varchar(5000) NOT NULL DEFAULT '' COMMENT '响应内容',
                                       `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                                       `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
                                       PRIMARY KEY (`id`) USING BTREE,
                                       KEY `admin_user_id` (`admin_user_id`) USING BTREE,
                                       KEY `auth_rule_id` (`auth_rule_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='后台操作日志表';

-- ----------------------------
-- Table structure for mp_admin_user
-- ----------------------------
DROP TABLE IF EXISTS `mp_admin_user`;
CREATE TABLE `mp_admin_user` (
                                 `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
                                 `account` varchar(50) NOT NULL DEFAULT '' COMMENT '账号',
                                 `password` varchar(100) NOT NULL DEFAULT '' COMMENT '密码(加密方式： md5(shal(''123'')))',
                                 `nickname` varchar(50) NOT NULL DEFAULT '' COMMENT '昵称',
                                 `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '状态(0.禁用 1.正常)',
                                 `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                                 `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
                                 PRIMARY KEY (`id`) USING BTREE,
                                 UNIQUE KEY `account` (`account`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='后台用户表';

-- ----------------------------
-- Table structure for mp_auth_group
-- ----------------------------
DROP TABLE IF EXISTS `mp_auth_group`;
CREATE TABLE `mp_auth_group` (
                                 `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
                                 `title` varchar(100) NOT NULL DEFAULT '' COMMENT '角色名称',
                                 `desc` varchar(200) NOT NULL DEFAULT '' COMMENT '角色描述',
                                 `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '状态(0.禁用 1.正常)',
                                 `rules` text COMMENT '规则name集合(json格式)',
                                 `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                                 `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
                                 PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='后台角色表';

-- ----------------------------
-- Table structure for mp_auth_group_access
-- ----------------------------
DROP TABLE IF EXISTS `mp_auth_group_access`;
CREATE TABLE `mp_auth_group_access` (
                                        `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
                                        `admin_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '后台用户ID',
                                        `auth_group_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '角色ID',
                                        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                                        `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
                                        PRIMARY KEY (`id`) USING BTREE,
                                        UNIQUE KEY `aid_2` (`admin_user_id`,`auth_group_id`) USING BTREE,
                                        KEY `aid` (`admin_user_id`) USING BTREE,
                                        KEY `group_id` (`auth_group_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户角色关联表';

-- ----------------------------
-- Table structure for mp_auth_rule
-- ----------------------------
DROP TABLE IF EXISTS `mp_auth_rule`;
CREATE TABLE `mp_auth_rule` (
                                `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
                                `name` varchar(100) NOT NULL DEFAULT '' COMMENT '规则标识',
                                `title` varchar(100) NOT NULL DEFAULT '' COMMENT '规则名称',
                                `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '状态(0.禁用 1.正常)',
                                `type` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '类型分组',
                                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                                `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
                                PRIMARY KEY (`id`) USING BTREE,
                                UNIQUE KEY `name` (`name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='后台权限表';

-- ----------------------------
-- Table structure for mp_banner
-- ----------------------------
DROP TABLE IF EXISTS `mp_banner`;
CREATE TABLE `mp_banner` (
                             `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
                             `img_url` varchar(255) NOT NULL DEFAULT '' COMMENT '轮播图',
                             `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '状态(0.禁用 1.启用)',
                             `sort` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '排序号(从小到大)',
                             `jump_url` varchar(255) NOT NULL DEFAULT '' COMMENT '跳转链接',
                             `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                             `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
                             PRIMARY KEY (`id`),
                             KEY `status` (`status`),
                             KEY `sort` (`sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='轮播图表';

-- ----------------------------
-- Table structure for mp_capital
-- ----------------------------
DROP TABLE IF EXISTS `mp_capital`;
CREATE TABLE `mp_capital` (
                              `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
                              `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
                              `capital_sn` varchar(30) NOT NULL DEFAULT '' COMMENT '订单号',
                              `type` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '类型(1.充值 2.提现)',
                              `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '状态(1.待审核-待支付 2.审核通过-支付成功 3.审核拒绝-支付失败)',
                              `pay_channel` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '支付渠道(1.后台手动 2.微信 3.支付宝 4.银联)',
                              `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额(充值为正 ， 提现为负)',
                              `withdraw_amount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '提现应到账金额',
                              `withdraw_fee` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '提现手续费',
                              `audit_remark` varchar(200) NOT NULL DEFAULT '' COMMENT '审核备注',
                              `audit_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '审核时间',
                              `admin_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '审核用户ID',
                              `realname` varchar(50) NOT NULL DEFAULT '' COMMENT '实名',
                              `account` varchar(200) NOT NULL DEFAULT '' COMMENT '收款账号',
                              `phone` varchar(20) NOT NULL DEFAULT '' COMMENT '手机号',
                              `collect_qr_img` varchar(250) NOT NULL DEFAULT '' COMMENT '收款二维码',
                              `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                              `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
                              PRIMARY KEY (`id`) USING BTREE,
                              KEY `user_id` (`user_id`),
                              KEY `type` (`type`),
                              KEY `capital_sn` (`capital_sn`),
                              KEY `status` (`status`),
                              KEY `pay_channel` (`pay_channel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='充值提现记录表';

-- ----------------------------
-- Table structure for mp_kline_chart
-- ----------------------------
DROP TABLE IF EXISTS `mp_kline_chart`;
CREATE TABLE `mp_kline_chart` (
                                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
                                  `date` varchar(20) NOT NULL DEFAULT '' COMMENT '日期(格式：2022-08-04)',
                                  `max_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '最高价',
                                  `min_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '最低价',
                                  `float_ratio` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '浮动比例(百分比的分子)',
                                  `open_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '开盘价',
                                  `close_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '收盘价',
                                  `chart_data` text NOT NULL COMMENT '图表数据(json数据)',
                                  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                                  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
                                  PRIMARY KEY (`id`),
                                  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='K线图数据表';

-- ----------------------------
-- Table structure for mp_level_config
-- ----------------------------
DROP TABLE IF EXISTS `mp_level_config`;
CREATE TABLE `mp_level_config` (
                                   `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
                                   `level` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '等级(0.VIP0 1.VIP1 2.VIP2 3.VIP3 4.VIP4 5.VIP5 6.VIP6)',
                                   `min_topup_amount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '最小充值金额',
                                   `min_direct_sub_active_num` int(8) unsigned NOT NULL DEFAULT '0' COMMENT '直属下级激活人数',
                                   `topup_reward_ratio` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '充值奖励(百分比的分子)',
                                   `cash_reward_amount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '现金奖励金额',
                                   `direct_recommend_reward_ratio` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '直属推荐奖(百分比分子)',
                                   `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                                   `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
                                   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for mp_order
-- ----------------------------
DROP TABLE IF EXISTS `mp_order`;
CREATE TABLE `mp_order` (
                            `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
                            `up_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '上级用户ID',
                            `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
                            `order_sn` varchar(50) NOT NULL DEFAULT '' COMMENT '订单号',
                            `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '订单状态(1.待支付 2.收益中 3.待出售 4.已完成)',
                            `buy_num` int(8) unsigned NOT NULL DEFAULT '0' COMMENT '购买的份数',
                            `project_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '项目ID',
                            `project_name` varchar(100) NOT NULL DEFAULT '' COMMENT '项目名称',
                            `single_amount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '单份投资金额',
                            `single_integral` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '单份积分',
                            `cover_img` varchar(250) NOT NULL DEFAULT '' COMMENT '封面图',
                            `total_num` int(8) unsigned NOT NULL DEFAULT '0' COMMENT '总份数',
                            `daily_bonus_ratio` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '每日分红比例(百分比的分子)',
                            `period` int(4) unsigned NOT NULL DEFAULT '0' COMMENT '周期',
                            `single_gift_equity` int(8) unsigned NOT NULL DEFAULT '0' COMMENT '单份赠送股权',
                            `single_gift_digital_yuan` int(8) unsigned NOT NULL DEFAULT '0' COMMENT '单份赠送数字人民币',
                            `pay_method` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '支付方式(1.余额 2.微信 3.支付宝 4.银联 5.积分兑换)',
                            `pay_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '支付时间',
                            `end_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '收益结束时间',
                            `sale_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '出售时间',
                            `gain_bonus` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '已获得分红',
                            `next_bonus_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '下次分红时间',
                            `equity_status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '股权兑换状态(1.不能兑换 2.可以兑换 3.已兑换)',
                            `digital_yuan_status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '数字人民币兑换状态(1.不能兑换 2.可以兑换 3.已兑换)',
                            `exchange_equity_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '股权兑换时间',
                            `exchange_yuan_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '数字人民币兑换时间',
                            `equity_exchange_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '股权兑换单价',
                            `equity_certificate_no` varchar(20) NOT NULL DEFAULT '' COMMENT '股权证书编号',
                            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                            `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
                            PRIMARY KEY (`id`) USING BTREE,
                            KEY `up_user_id` (`up_user_id`),
                            KEY `user_id` (`user_id`),
                            KEY `order_sn` (`order_sn`),
                            KEY `status` (`status`),
                            KEY `project_name` (`project_name`),
                            KEY `pay_method` (`pay_method`),
                            KEY `pay_time` (`pay_time`),
                            KEY `project_id` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='订单表';

-- ----------------------------
-- Table structure for mp_passive_income_record
-- ----------------------------
DROP TABLE IF EXISTS `mp_passive_income_record`;
CREATE TABLE `mp_passive_income_record` (
                                            `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
                                            `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
                                            `order_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '订单ID',
                                            `amount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '金额',
                                            `days` int(2) unsigned NOT NULL DEFAULT '0' COMMENT '天数',
                                            `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '状态(1.未开始 2.未领取 3.已领取)',
                                            `is_finish` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否结束收益(0.否 1.是)',
                                            `execute_day` int(8) unsigned NOT NULL DEFAULT '0' COMMENT '执行日期(格式：20220728)',
                                            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                                            `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
                                            PRIMARY KEY (`id`),
                                            KEY `user_id` (`user_id`),
                                            KEY `order_id` (`order_id`),
                                            KEY `status` (`status`),
                                            KEY `execute_day` (`execute_day`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='被动收益记录表';

-- ----------------------------
-- Table structure for mp_pay_account
-- ----------------------------
DROP TABLE IF EXISTS `mp_pay_account`;
CREATE TABLE `mp_pay_account` (
                                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
                                  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
                                  `pay_type` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '支付类型(1.微信 2.支付宝 3.银行卡)',
                                  `account` varchar(200) NOT NULL DEFAULT '' COMMENT '账号',
                                  `phone` varchar(20) NOT NULL DEFAULT '' COMMENT '手机号',
                                  `qr_img` varchar(250) NOT NULL DEFAULT '' COMMENT '收款二维码',
                                  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                                  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
                                  PRIMARY KEY (`id`),
                                  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户支付账号表';

-- ----------------------------
-- Table structure for mp_payment
-- ----------------------------
DROP TABLE IF EXISTS `mp_payment`;
CREATE TABLE `mp_payment` (
                              `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
                              `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
                              `trade_sn` varchar(50) NOT NULL DEFAULT '' COMMENT '交易单号',
                              `type` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '支付类型(1.微信 2.支付宝)',
                              `pay_amount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '支付总价',
                              `product_type` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '产品类型(1.投资项目 2.充值)',
                              `order_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '订单ID',
                              `capital_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '充值ID',
                              `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '状态(1.未支付 2.支付成功 3.支付失败)',
                              `online_sn` varchar(50) NOT NULL DEFAULT '' COMMENT '第三方支付系统单号',
                              `payment_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '支付时间',
                              `remark` varchar(250) NOT NULL DEFAULT '' COMMENT '备注',
                              `payment_config_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '支付配置ID',
                              `channel` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '渠道(1.宏亚)',
                              `mark` varchar(200) NOT NULL DEFAULT '' COMMENT '渠道标识',
                              `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                              `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
                              PRIMARY KEY (`id`) USING BTREE,
                              KEY `user_id` (`user_id`),
                              KEY `trade_sn` (`trade_sn`),
                              KEY `status` (`status`),
                              KEY `type` (`type`),
                              KEY `mark` (`mark`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='支付记录表';

-- ----------------------------
-- Table structure for mp_payment_config
-- ----------------------------
DROP TABLE IF EXISTS `mp_payment_config`;
CREATE TABLE `mp_payment_config` (
                                     `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
                                     `channel` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '渠道(1.宏亚)',
                                     `mark` varchar(200) NOT NULL DEFAULT '' COMMENT '渠道标识',
                                     `type` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '支付类型(1.微信 2.支付宝 3.银联)',
                                     `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '状态(0.禁用 1.启用)',
                                     `single_topup_min_amount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '单笔充值最小金额',
                                     `single_topup_max_amount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '单笔充值最大金额',
                                     `topup_max_limit` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '总充值金额上限',
                                     `start_topup_limit` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '用户达到该充值金额才能使用此渠道',
                                     `fixed_topup_limit` varchar(250) NOT NULL DEFAULT '' COMMENT '固定金额限额(多个金额逗号分隔)',
                                     `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                                     `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
                                     PRIMARY KEY (`id`),
                                     KEY `type` (`type`),
                                     KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='后台支付收款配置';

-- ----------------------------
-- Table structure for mp_project
-- ----------------------------
DROP TABLE IF EXISTS `mp_project`;
CREATE TABLE `mp_project` (
                              `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
                              `cover_img` varchar(250) NOT NULL DEFAULT '' COMMENT '封面图',
                              `name` varchar(100) NOT NULL DEFAULT '' COMMENT '项目名称',
                              `single_amount` decimal(18,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '单份投资金额',
                              `single_integral` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '单份积分',
                              `total_num` int(8) unsigned NOT NULL DEFAULT '0' COMMENT '总份数',
                              `daily_bonus_ratio` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '每日分红比例(百分比的分子)',
                              `period` int(4) unsigned NOT NULL DEFAULT '0' COMMENT '周期',
                              `single_gift_equity` int(8) unsigned NOT NULL DEFAULT '0' COMMENT '单份赠送股权',
                              `single_gift_digital_yuan` int(8) unsigned NOT NULL DEFAULT '0' COMMENT '单份赠送数字人民币',
                              `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '状态(0.禁用 1.启用)',
                              `is_recommend` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否推荐(0.否 1.是)',
                              `support_pay_methods` varchar(100) NOT NULL DEFAULT '' COMMENT '支持的支付方式(json字符串，例：[1,2,3]  (1.余额 2.微信 3.支付宝 4.银联 5.积分兑换))',
                              `sham_buy_num` int(8) NOT NULL DEFAULT '0' COMMENT '虚拟购买份数',
                              `sort` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '排序(从小到大排)',
                              `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                              `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
                              `bonus_multiple` varchar(10) NOT NULL DEFAULT '1' COMMENT '奖励倍数',
                              PRIMARY KEY (`id`),
                              KEY `name` (`name`),
                              KEY `status` (`status`),
                              KEY `is_recommend` (`is_recommend`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='项目表';

-- ----------------------------
-- Table structure for mp_setting
-- ----------------------------
DROP TABLE IF EXISTS `mp_setting`;
CREATE TABLE `mp_setting` (
                              `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
                              `name` varchar(255) NOT NULL DEFAULT '' COMMENT '配置名称',
                              `key` varchar(100) NOT NULL DEFAULT '' COMMENT '配置key',
                              `value` varchar(500) NOT NULL DEFAULT '' COMMENT '配置值',
                              `data` varchar(500) NOT NULL DEFAULT '' COMMENT '配置补充数据',
                              `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                              `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
                              PRIMARY KEY (`id`),
                              KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='配置表';

-- ----------------------------
-- Table structure for mp_system_info
-- ----------------------------
DROP TABLE IF EXISTS `mp_system_info`;
CREATE TABLE `mp_system_info` (
                                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
                                  `type` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '类型(1.公告 2.关于我们 3.客服链接)',
                                  `status` tinyint(21) unsigned NOT NULL DEFAULT '1' COMMENT '状态(0.禁用 1.启用)',
                                  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '标题',
                                  `content` text NOT NULL COMMENT '内容',
                                  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                                  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
                                  PRIMARY KEY (`id`),
                                  KEY `type` (`type`),
                                  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统信息表';

-- ----------------------------
-- Table structure for mp_user
-- ----------------------------
DROP TABLE IF EXISTS `mp_user`;
CREATE TABLE `mp_user` (
                           `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
                           `up_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '上级用户ID',
                           `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '启用状态(0.禁用 1.启用)',
                           `avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '头像',
                           `phone` varchar(20) NOT NULL DEFAULT '' COMMENT '手机号',
                           `password` varchar(50) NOT NULL DEFAULT '' COMMENT '密码(加密方式：sha1(md5(''xxx'')))',
                           `pay_password` varchar(50) NOT NULL DEFAULT '' COMMENT '密码(加密方式：sha1(md5(''xxx'')))',
                           `balance` decimal(18,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '充值余额',
                           `topup_balance` decimal(18,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '充值余额',
                           `integral` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '积分',
                           `invite_code` varchar(10) NOT NULL DEFAULT '' COMMENT '邀请码',
                           `realname` varchar(50) NOT NULL DEFAULT '' COMMENT '实名认证姓名',
                           `ic_number` varchar(50) NOT NULL DEFAULT '' COMMENT '身份证号',
                           `level` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '等级(0.VIP0 1.VIP1 2.VIP2 3.VIP3 4.VIP4 5.VIP5 6.VIP6)',
                           `is_active` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否激活(0.否 1.是)',
                           `active_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '激活时间',
                           `invest_amount` decimal(14,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '总投资金额',
                           `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                           `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
                           PRIMARY KEY (`id`),
                           KEY `up_user_id` (`up_user_id`),
                           KEY `phone` (`phone`),
                           KEY `invite_code` (`invite_code`),
                           KEY `realname` (`realname`),
                           KEY `level` (`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户表';

-- ----------------------------
-- Table structure for mp_user_balance_log
-- ----------------------------
DROP TABLE IF EXISTS `mp_user_balance_log`;
CREATE TABLE `mp_user_balance_log` (
                                       `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
                                       `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
                                       `type` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '类型(1.充值 2.提现 3.购买项目（积分参与投资） 4.充值奖励 5.数字生活补贴 6.项目分红 7.额外奖励 8.团队奖励 9.推荐奖励 10.股权兑换 11.数字人民币兑换 12.返还本金 13.提现失败 14.被动收益 15.手动入金 16.手动出金 17.签到（仅积分）)',
                                       `log_type` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '日志类型(1.余额日志 2.积分日志)',
                                       `relation_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '关联ID',
                                       `before_balance` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '变化前的余额',
                                       `change_balance` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '变动金额(增为正，减为负)',
                                       `after_balance` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '变化后的余额',
                                       `remark` varchar(100) NOT NULL DEFAULT '' COMMENT '备注',
                                       `admin_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '后台用户ID',
                                       `status` tinyint(2) unsigned NOT NULL DEFAULT '2' COMMENT '状态(1.待确认 2.成功 3.失败)',
                                       `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                                       `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
                                       PRIMARY KEY (`id`),
                                       KEY `user_id` (`user_id`),
                                       KEY `type` (`type`),
                                       KEY `relation_id` (`relation_id`),
                                       KEY `log_type` (`log_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户资金日志表';

-- ----------------------------
-- Table structure for mp_user_delivery
-- ----------------------------
DROP TABLE IF EXISTS `mp_user_delivery`;
CREATE TABLE `mp_user_delivery` (
                                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
                                    `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
                                    `name` varchar(50) NOT NULL DEFAULT '' COMMENT '收货人名称',
                                    `phone` varchar(20) NOT NULL DEFAULT '' COMMENT '手机号',
                                    `address` varchar(250) NOT NULL DEFAULT '' COMMENT '详细地址',
                                    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                                    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
                                    PRIMARY KEY (`id`),
                                    KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for mp_user_relation
-- ----------------------------
DROP TABLE IF EXISTS `mp_user_relation`;
CREATE TABLE `mp_user_relation` (
                                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
                                    `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
                                    `sub_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '下级用户ID',
                                    `level` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '层级(1.LV1 2.LV2 3.LV3)',
                                    `is_active` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '下级用户是否激活(0.否 1.是)',
                                    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                                    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
                                    PRIMARY KEY (`id`) USING BTREE,
                                    KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户层级关系表';

-- ----------------------------
-- Table structure for mp_user_signin
-- ----------------------------
DROP TABLE IF EXISTS `mp_user_signin`;
CREATE TABLE `mp_user_signin` (
                                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
                                  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
                                  `signin_date` varchar(20) NOT NULL DEFAULT '' COMMENT '签到日期(格式：2020-04-13)',
                                  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                                  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
                                  PRIMARY KEY (`id`),
                                  KEY `user_id` (`user_id`),
                                  KEY `signin_day` (`signin_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户签到表';


INSERT INTO `mp_admin_user` (`id`, `account`, `password`, `nickname`, `status`, `created_at`, `updated_at`) VALUES (1, '111111', 'c78b6663d47cfbdb4d65ea51c104044e', '超级管理员', 1, '2022-08-01 22:44:58', '2022-08-01 22:44:58');
INSERT INTO `mp_auth_group` (`id`, `title`, `desc`, `status`, `rules`, `created_at`, `updated_at`) VALUES (1, '超级管理员', '超级管理员', 1, '[\"adminuser\\/adminuserlist\",\"user\\/userlist\",\"user\\/adduser\",\"user\\/edituser\",\"user\\/deluser\",\"user\\/changeuser\",\"userrelation\\/userrelationlist\",\"userrelation\\/adduserrelation\",\"userrelation\\/edituserrelation\",\"userrelation\\/deluserrelation\",\"userrelation\\/changeuserrelation\",\"systeminfo\\/systeminfolist\",\"systeminfo\\/addsysteminfo\",\"systeminfo\\/editsysteminfo\",\"systeminfo\\/delsysteminfo\",\"systeminfo\\/changesysteminfo\",\"project\\/projectlist\",\"project\\/addproject\",\"project\\/editproject\",\"project\\/delproject\",\"project\\/changeproject\",\"order\\/orderlist\",\"order\\/addorder\",\"order\\/editorder\",\"order\\/delorder\",\"order\\/changeorder\",\"userbalancelog\\/userbalanceloglist\",\"userbalancelog\\/adduserbalancelog\",\"userbalancelog\\/edituserbalancelog\",\"userbalancelog\\/deluserbalancelog\",\"userbalancelog\\/changeuserbalancelog\",\"passiveincomerecord\\/passiveincomerecordlist\",\"passiveincomerecord\\/addpassiveincomerecord\",\"passiveincomerecord\\/editpassiveincomerecord\",\"passiveincomerecord\\/delpassiveincomerecord\",\"passiveincomerecord\\/changepassiveincomerecord\",\"usersignin\\/usersigninlist\",\"usersignin\\/addusersignin\",\"usersignin\\/editusersignin\",\"usersignin\\/delusersignin\",\"usersignin\\/changeusersignin\",\"setting\\/settinglist\",\"setting\\/addsetting\",\"setting\\/editsetting\",\"setting\\/delsetting\",\"setting\\/changesetting\",\"banner\\/bannerlist\",\"banner\\/addbanner\",\"banner\\/editbanner\",\"banner\\/delbanner\",\"banner\\/changebanner\",\"levelconfig\\/levelconfiglist\",\"levelconfig\\/addlevelconfig\",\"levelconfig\\/editlevelconfig\",\"levelconfig\\/dellevelconfig\",\"levelconfig\\/changelevelconfig\",\"userdelivery\\/userdeliverylist\",\"userdelivery\\/adduserdelivery\",\"userdelivery\\/edituserdelivery\",\"userdelivery\\/deluserdelivery\",\"userdelivery\\/changeuserdelivery\",\"klinechart\\/klinechartlist\",\"klinechart\\/addklinechart\",\"klinechart\\/editklinechart\",\"klinechart\\/delklinechart\",\"klinechart\\/changeklinechart\",\"payment\\/paymentlist\",\"payment\\/addpayment\",\"payment\\/editpayment\",\"payment\\/delpayment\",\"payment\\/changepayment\",\"capital\\/capitallist\",\"capital\\/addcapital\",\"capital\\/editcapital\",\"capital\\/delcapital\",\"capital\\/changecapital\",\"payaccount\\/payaccountlist\",\"payaccount\\/addpayaccount\",\"payaccount\\/editpayaccount\",\"payaccount\\/delpayaccount\",\"payaccount\\/changepayaccount\",\"paymentconfig\\/paymentconfiglist\",\"paymentconfig\\/addpaymentconfig\",\"paymentconfig\\/editpaymentconfig\",\"paymentconfig\\/delpaymentconfig\",\"paymentconfig\\/changepaymentconfig\"]', '2022-08-01 22:44:58', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_group` (`id`, `title`, `desc`, `status`, `rules`, `created_at`, `updated_at`) VALUES (2, '财务', '财务', 1, NULL, '2022-08-16 22:52:24', '2022-08-16 22:52:24');
INSERT INTO `mp_auth_group_access` (`id`, `admin_user_id`, `auth_group_id`, `created_at`, `updated_at`) VALUES (1, 1, 1, '2022-08-03 00:37:43', '2022-08-03 00:37:43');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (1, 'adminuser', '后台账户管理', 2, 1, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (2, 'adminuser/adminuserlist', '查看后台账号列表', 1, 1, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (3, 'user', '用户管理', 2, 2, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (4, 'user/userlist', '查看用户列表', 1, 2, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (5, 'user/adduser', '添加用户', 1, 2, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (6, 'user/edituser', '编辑用户', 1, 2, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (7, 'user/deluser', '删除用户', 1, 2, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (8, 'user/changeuser', '改变用户状态', 1, 2, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (9, 'userrelation', '用户层级关系管理', 2, 3, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (10, 'userrelation/userrelationlist', '查看用户层级关系列表', 1, 3, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (11, 'userrelation/adduserrelation', '添加用户层级关系', 1, 3, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (12, 'userrelation/edituserrelation', '编辑用户层级关系', 1, 3, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (13, 'userrelation/deluserrelation', '删除用户层级关系', 1, 3, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (14, 'userrelation/changeuserrelation', '改变用户层级关系状态', 1, 3, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (15, 'systeminfo', '系统信息管理', 2, 4, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (16, 'systeminfo/systeminfolist', '查看系统信息列表', 1, 4, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (17, 'systeminfo/addsysteminfo', '添加系统信息', 1, 4, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (18, 'systeminfo/editsysteminfo', '编辑系统信息', 1, 4, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (19, 'systeminfo/delsysteminfo', '删除系统信息', 1, 4, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (20, 'systeminfo/changesysteminfo', '改变系统信息状态', 1, 4, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (21, 'project', '项目管理', 2, 5, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (22, 'project/projectlist', '查看项目列表', 1, 5, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (23, 'project/addproject', '添加项目', 1, 5, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (24, 'project/editproject', '编辑项目', 1, 5, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (25, 'project/delproject', '删除项目', 1, 5, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (26, 'project/changeproject', '改变项目状态', 1, 5, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (27, 'order', '订单管理', 2, 6, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (28, 'order/orderlist', '查看订单列表', 1, 6, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (29, 'order/addorder', '添加订单', 1, 6, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (30, 'order/editorder', '编辑订单', 1, 6, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (31, 'order/delorder', '删除订单', 1, 6, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (32, 'order/changeorder', '改变订单状态', 1, 6, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (33, 'userbalancelog', '用户资金日志管理', 2, 7, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (34, 'userbalancelog/userbalanceloglist', '查看用户资金日志列表', 1, 7, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (35, 'userbalancelog/adduserbalancelog', '添加用户资金日志', 1, 7, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (36, 'userbalancelog/edituserbalancelog', '编辑用户资金日志', 1, 7, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (37, 'userbalancelog/deluserbalancelog', '删除用户资金日志', 1, 7, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (38, 'userbalancelog/changeuserbalancelog', '改变用户资金日志状态', 1, 7, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (39, 'passiveincomerecord', '被动收益记录管理', 2, 8, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (40, 'passiveincomerecord/passiveincomerecordlist', '查看被动收益记录列表', 1, 8, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (41, 'passiveincomerecord/addpassiveincomerecord', '添加被动收益记录', 1, 8, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (42, 'passiveincomerecord/editpassiveincomerecord', '编辑被动收益记录', 1, 8, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (43, 'passiveincomerecord/delpassiveincomerecord', '删除被动收益记录', 1, 8, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (44, 'passiveincomerecord/changepassiveincomerecord', '改变被动收益记录状态', 1, 8, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (45, 'usersignin', '用户签到管理', 2, 9, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (46, 'usersignin/usersigninlist', '查看用户签到列表', 1, 9, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (47, 'usersignin/addusersignin', '添加用户签到', 1, 9, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (48, 'usersignin/editusersignin', '编辑用户签到', 1, 9, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (49, 'usersignin/delusersignin', '删除用户签到', 1, 9, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (50, 'usersignin/changeusersignin', '改变用户签到状态', 1, 9, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (51, 'setting', '配置管理', 2, 10, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (52, 'setting/settinglist', '查看配置列表', 1, 10, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (53, 'setting/addsetting', '添加配置', 1, 10, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (54, 'setting/editsetting', '编辑配置', 1, 10, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (55, 'setting/delsetting', '删除配置', 1, 10, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (56, 'setting/changesetting', '改变配置状态', 1, 10, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (57, 'banner', '轮播图管理', 2, 11, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (58, 'banner/bannerlist', '查看轮播图列表', 1, 11, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (59, 'banner/addbanner', '添加轮播图', 1, 11, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (60, 'banner/editbanner', '编辑轮播图', 1, 11, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (61, 'banner/delbanner', '删除轮播图', 1, 11, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (62, 'banner/changebanner', '改变轮播图状态', 1, 11, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (63, 'levelconfig', '用户等级管理', 2, 12, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (64, 'levelconfig/levelconfiglist', '查看等级列表', 1, 12, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (65, 'levelconfig/addlevelconfig', '添加等级', 1, 12, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (66, 'levelconfig/editlevelconfig', '编辑等级', 1, 12, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (67, 'levelconfig/dellevelconfig', '删除等级', 1, 12, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (68, 'levelconfig/changelevelconfig', '改变等级状态', 1, 12, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (69, 'userdelivery', '收货地址管理', 2, 13, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (70, 'userdelivery/userdeliverylist', '查看列表', 1, 13, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (71, 'userdelivery/adduserdelivery', '添加', 1, 13, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (72, 'userdelivery/edituserdelivery', '编辑', 1, 13, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (73, 'userdelivery/deluserdelivery', '删除', 1, 13, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (74, 'userdelivery/changeuserdelivery', '改变状态', 1, 13, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (75, 'klinechart', 'K线图数据管理', 2, 14, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (76, 'klinechart/klinechartlist', '查看K线图数据列表', 1, 14, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (77, 'klinechart/addklinechart', '添加K线图数据', 1, 14, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (78, 'klinechart/editklinechart', '编辑K线图数据', 1, 14, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (79, 'klinechart/delklinechart', '删除K线图数据', 1, 14, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (80, 'klinechart/changeklinechart', '改变K线图数据状态', 1, 14, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (81, 'payment', '支付记录管理', 2, 15, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (82, 'payment/paymentlist', '查看支付记录列表', 1, 15, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (83, 'payment/addpayment', '添加支付记录', 1, 15, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (84, 'payment/editpayment', '编辑支付记录', 1, 15, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (85, 'payment/delpayment', '删除支付记录', 1, 15, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (86, 'payment/changepayment', '改变支付记录状态', 1, 15, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (87, 'capital', '充值提现记录管理', 2, 16, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (88, 'capital/capitallist', '查看充值提现记录列表', 1, 16, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (89, 'capital/addcapital', '添加充值提现记录', 1, 16, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (90, 'capital/editcapital', '编辑充值提现记录', 1, 16, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (91, 'capital/delcapital', '删除充值提现记录', 1, 16, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (92, 'capital/changecapital', '改变充值提现记录状态', 1, 16, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (93, 'payaccount', '用户支付账号管理', 2, 17, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (94, 'payaccount/payaccountlist', '查看用户支付账号列表', 1, 17, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (95, 'payaccount/addpayaccount', '添加用户支付账号', 1, 17, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (96, 'payaccount/editpayaccount', '编辑用户支付账号', 1, 17, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (97, 'payaccount/delpayaccount', '删除用户支付账号', 1, 17, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (98, 'payaccount/changepayaccount', '改变用户支付账号状态', 1, 17, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (99, 'paymentconfig', '后台支付收款配置管理', 2, 18, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (100, 'paymentconfig/paymentconfiglist', '查看后台支付收款配置列表', 1, 18, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (101, 'paymentconfig/addpaymentconfig', '添加后台支付收款配置', 1, 18, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (102, 'paymentconfig/editpaymentconfig', '编辑后台支付收款配置', 1, 18, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (103, 'paymentconfig/delpaymentconfig', '删除后台支付收款配置', 1, 18, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_auth_rule` (`id`, `name`, `title`, `status`, `type`, `created_at`, `updated_at`) VALUES (104, 'paymentconfig/changepaymentconfig', '改变后台支付收款配置状态', 1, 18, '2022-08-18 19:35:21', '2022-08-18 19:35:21');
INSERT INTO `mp_level_config` (`id`, `level`, `min_topup_amount`, `min_direct_sub_active_num`, `topup_reward_ratio`, `cash_reward_amount`, `direct_recommend_reward_ratio`, `created_at`, `updated_at`) VALUES (1, 0, 0.00, 0, 0.00, 0.00, 0.00, '2022-07-30 10:24:37', '2022-07-30 10:24:37');
INSERT INTO `mp_level_config` (`id`, `level`, `min_topup_amount`, `min_direct_sub_active_num`, `topup_reward_ratio`, `cash_reward_amount`, `direct_recommend_reward_ratio`, `created_at`, `updated_at`) VALUES (2, 1, 1000.00, 5, 0.00, 2.00, 0.20, '2022-07-30 10:26:03', '2022-08-17 21:44:37');
INSERT INTO `mp_level_config` (`id`, `level`, `min_topup_amount`, `min_direct_sub_active_num`, `topup_reward_ratio`, `cash_reward_amount`, `direct_recommend_reward_ratio`, `created_at`, `updated_at`) VALUES (3, 2, 5000.00, 10, 0.00, 5.00, 0.50, '2022-07-30 10:26:34', '2022-08-17 21:45:38');
INSERT INTO `mp_level_config` (`id`, `level`, `min_topup_amount`, `min_direct_sub_active_num`, `topup_reward_ratio`, `cash_reward_amount`, `direct_recommend_reward_ratio`, `created_at`, `updated_at`) VALUES (4, 3, 10000.00, 30, 0.00, 13.00, 1.00, '2022-07-30 10:27:03', '2022-08-17 21:47:15');
INSERT INTO `mp_level_config` (`id`, `level`, `min_topup_amount`, `min_direct_sub_active_num`, `topup_reward_ratio`, `cash_reward_amount`, `direct_recommend_reward_ratio`, `created_at`, `updated_at`) VALUES (5, 4, 20000.00, 60, 0.00, 36.00, 1.50, '2022-07-30 10:27:33', '2022-08-17 21:47:48');
INSERT INTO `mp_level_config` (`id`, `level`, `min_topup_amount`, `min_direct_sub_active_num`, `topup_reward_ratio`, `cash_reward_amount`, `direct_recommend_reward_ratio`, `created_at`, `updated_at`) VALUES (6, 5, 50000.00, 150, 0.00, 98.00, 3.00, '2022-07-30 10:28:03', '2022-08-17 21:48:32');
INSERT INTO `mp_level_config` (`id`, `level`, `min_topup_amount`, `min_direct_sub_active_num`, `topup_reward_ratio`, `cash_reward_amount`, `direct_recommend_reward_ratio`, `created_at`, `updated_at`) VALUES (7, 6, 100000.00, 300, 0.00, 268.00, 6.00, '2022-07-30 10:28:35', '2022-08-17 21:49:24');
INSERT INTO `mp_setting` (`id`, `name`, `key`, `value`, `data`, `created_at`, `updated_at`) VALUES (1, '签到奖励积分数', 'signin_integral', '20', '', '2022-07-29 22:11:10', '2022-08-03 16:38:50');
INSERT INTO `mp_setting` (`id`, `name`, `key`, `value`, `data`, `created_at`, `updated_at`) VALUES (2, '一级团队奖励比例', 'first_team_reward_ratio', '22', '', '2022-07-30 00:13:11', '2022-08-18 11:16:43');
INSERT INTO `mp_setting` (`id`, `name`, `key`, `value`, `data`, `created_at`, `updated_at`) VALUES (3, '二级团队奖励比例', 'second_team_reward_ratio', '3', '', '2022-07-30 00:13:29', '2022-08-18 11:16:48');
INSERT INTO `mp_setting` (`id`, `name`, `key`, `value`, `data`, `created_at`, `updated_at`) VALUES (4, '三级团队奖励比例', 'third_team_reward_ratio', '2', '', '2022-07-30 00:14:06', '2022-08-18 11:16:55');
INSERT INTO `mp_setting` (`id`, `name`, `key`, `value`, `data`, `created_at`, `updated_at`) VALUES (5, '直推实名成功后奖励金额', 'direct_recommend_reward_amount', '1', '', '2022-07-30 11:55:19', '2022-08-18 16:57:02');
INSERT INTO `mp_setting` (`id`, `name`, `key`, `value`, `data`, `created_at`, `updated_at`) VALUES (6, '单笔最大提现限额', 'single_withdraw_max_amount', '50000', '', '2022-08-09 12:37:52', '2022-08-18 11:18:48');
INSERT INTO `mp_setting` (`id`, `name`, `key`, `value`, `data`, `created_at`, `updated_at`) VALUES (7, '单笔最小提现限额', 'single_withdraw_min_amount', '10', '', '2022-08-09 12:38:03', '2022-08-09 14:44:25');
INSERT INTO `mp_setting` (`id`, `name`, `key`, `value`, `data`, `created_at`, `updated_at`) VALUES (8, '单天最多提现次数', 'per_day_withdraw_max_num', '1', '', '2022-08-09 12:39:29', '2022-08-18 11:18:24');
INSERT INTO `mp_setting` (`id`, `name`, `key`, `value`, `data`, `created_at`, `updated_at`) VALUES (9, '提现手续费比例', 'withdraw_fee_ratio', '0', '', '2022-08-09 12:43:26', '2022-08-18 11:18:34');
INSERT INTO `mp_setting` (`id`, `name`, `key`, `value`, `data`, `created_at`, `updated_at`) VALUES (10, '股权兑换开关', 'equity_switch', '1', '', '2022-08-18 19:01:02', '2022-08-18 19:01:02');
INSERT INTO `mp_setting` (`id`, `name`, `key`, `value`, `data`, `created_at`, `updated_at`) VALUES (11, '数字人民币兑换开关', 'digital_yuan_switch', '1', '', '2022-08-18 19:02:05', '2022-08-18 19:02:05');
INSERT INTO `mp_user` (`id`, `up_user_id`, `status`, `avatar`, `phone`, `password`, `pay_password`, `balance`, `topup_balance`, `integral`, `invite_code`, `realname`, `ic_number`, `level`, `is_active`, `active_time`, `invest_amount`, `created_at`, `updated_at`) VALUES (1, 0, 1, '', '18888888888', 'd8406e8445cc99a16ab984cc28f6931615c766fc', 'd8406e8445cc99a16ab984cc28f6931615c766fc', 0.00, 0.00, 0, '111111', '内部用户', '', 0, 0, 0, 0.00, '2022-07-26 22:31:22', '2022-08-18 19:43:09');

ALTER TABLE `mp_project`
    ADD COLUMN `progress_switch` tinyint(2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '进度开关' AFTER `sort`;

ALTER TABLE `mp_payment_config`
    ADD COLUMN `name` varchar(200) NOT NULL DEFAULT '' COMMENT '支付名称' AFTER `id`;

ALTER TABLE `mp_payment_config`
    ADD COLUMN `card_info` varchar(500) NOT NULL DEFAULT '' COMMENT '银行卡信息(json格式)' AFTER `fixed_topup_limit`;

ALTER TABLE `mp_payment`
    ADD COLUMN `card_info` varchar(500) NOT NULL DEFAULT '' COMMENT '卡信息' AFTER `mark`;

ALTER TABLE `mp_payment_config`
    ADD COLUMN `pay_voucher_img_url` varchar(250) NOT NULL DEFAULT '' COMMENT '支付凭证图片' AFTER `card_info`;

ALTER TABLE `mp_payment_config`
DROP COLUMN `pay_voucher_img_url`;

ALTER TABLE `mp_payment`
    ADD COLUMN `pay_voucher_img_url` varchar(250) NOT NULL DEFAULT '' COMMENT '支付凭证图片' AFTER `card_info`;

ALTER TABLE `mp_capital`
    ADD COLUMN `withdraw_sn` varchar(50) NOT NULL DEFAULT '' COMMENT '提现单号' AFTER `user_id`;

ALTER TABLE `mp_capital`
    ADD COLUMN `online_status` varchar(50) NOT NULL DEFAULT '' COMMENT '第三方系统订单状态' AFTER `collect_qr_img`;

ALTER TABLE `mp_system_info`
    ADD COLUMN `sort` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '排序(从小到大排序)' AFTER `content`;

ALTER TABLE `mp_system_info`
ADD COLUMN `cover_img` varchar(250) NOT NULL DEFAULT '' COMMENT '封面图片' AFTER `status`,
ADD COLUMN `video_url` varchar(250) NOT NULL DEFAULT '' COMMENT '视频链接' AFTER `content`;

ALTER TABLE `mp_system_info`
DROP COLUMN `sort`;

ALTER TABLE `mp_system_info`
ADD COLUMN `sort` int(11) NOT NULL DEFAULT 0 COMMENT '排序号(从小到大排序)' AFTER `video_url`;

INSERT INTO `mp_setting` (`id`, `name`, `key`, `value`, `data`, `created_at`, `updated_at`) VALUES (12, '注册赠送股权开关', 'register_give_equity_switch', '0', '', '2022-08-18 19:01:02', '2022-08-18 19:16:34');
INSERT INTO `mp_setting` (`id`, `name`, `key`, `value`, `data`, `created_at`, `updated_at`) VALUES (13, '注册赠送股权数量', 'register_give_equity_num', '10', '', '2022-08-18 19:01:02', '2022-09-02 00:12:37');
INSERT INTO `mp_setting` (`id`, `name`, `key`, `value`, `data`, `created_at`, `updated_at`) VALUES (14, '注册赠送数字人民币开关', 'register_give_digital_yuan_switch', '1', '', '2022-08-18 19:02:05', '2022-09-02 00:12:43');
INSERT INTO `mp_setting` (`id`, `name`, `key`, `value`, `data`, `created_at`, `updated_at`) VALUES (15, '注册赠送数字人民币数量', 'register_give_digital_yuan_num', '5', '', '2022-08-18 19:02:05', '2022-09-02 00:12:21');

CREATE TABLE `mp_equity_yuan_record` (
                                         `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
                                         `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
                                         `type` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '类型(1.股权 2.数字人民币)',
                                         `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '状态(1.未持有 2.已持有 3.已兑换)',
                                         `relation_type` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '关联类型(1.购买项目 2.注册赠送 3.后台赠送)',
                                         `relation_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '关联ID',
                                         `num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '数量',
                                         `exchange_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '兑换价格',
                                         `give_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '获得时间',
                                         `exchange_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '兑换时间',
                                         `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                                         `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
                                         PRIMARY KEY (`id`),
                                         KEY `user_id` (`user_id`),
                                         KEY `type` (`type`),
                                         KEY `status` (`status`),
                                         KEY `relation_type` (`relation_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='股权数字人民币记录表';

ALTER TABLE `mp_equity_yuan_record`
    ADD COLUMN `title` varchar(200) NOT NULL DEFAULT '' COMMENT '标题' AFTER `status`;

ALTER TABLE `mp_equity_yuan_record`
    ADD COLUMN `equity_certificate_no` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '股权证书编号' AFTER `exchange_time`;

ALTER TABLE `mp_pay_account`
    ADD COLUMN `bank_name` varchar(100) NOT NULL DEFAULT '' COMMENT '银行名称' AFTER `pay_type`,
    ADD COLUMN `bank_branch` varchar(100) NOT NULL DEFAULT '' COMMENT '银行支行' AFTER `bank_name`;

ALTER TABLE `mp_capital`
    ADD COLUMN `bank_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '银行名称' AFTER `online_status`,
    ADD COLUMN `bank_branch` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '银行支行' AFTER `bank_name`;

ALTER TABLE `mp_order`
    ADD COLUMN `is_admin_confirm` tinyint(2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否是后台确认支付(0.否 1.是)' AFTER `equity_certificate_no`;

ALTER TABLE `mp_capital`
    ADD COLUMN `is_admin_confirm` tinyint(2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否是后台确认支付(0.否 1.是)' AFTER `bank_branch`;

INSERT INTO `mp_setting` (`id`, `name`, `key`, `value`, `data`, `created_at`, `updated_at`) VALUES (16, '银行卡提现开关', 'bank_withdrawal_switch', '1', '', '2022-09-10 00:26:15', '2022-09-10 00:26:15');
INSERT INTO `mp_setting` (`id`, `name`, `key`, `value`, `data`, `created_at`, `updated_at`) VALUES (17, '支付宝提现开关', 'alipay_withdrawal_switch', '1', '', '2022-09-10 00:26:34', '2022-09-10 00:26:42');

INSERT INTO `mp_setting` (`id`, `name`, `key`, `value`, `data`, `created_at`, `updated_at`) VALUES (18, '提现自动打款开关', 'automatic_withdrawal_switch', '1', '', '2022-09-10 21:05:35', '2022-09-10 21:05:46');

ALTER TABLE `mp_admin_user`
    ADD COLUMN `google_auth_secret` varchar(100) NOT NULL DEFAULT '' COMMENT '谷歌验证码密钥' AFTER `status`;

INSERT INTO `mp_setting` (`id`, `name`, `key`, `value`, `data`, `created_at`, `updated_at`) VALUES (19, '奖励倍数', 'bonus_multiple', '1', '', '2022-09-20 23:31:06', '2022-09-20 23:31:06');
