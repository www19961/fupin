<?php

return array(
    'user' =>
    array(
        'status_map' =>
        array(
            0 => '禁用',
            1 => '启用',
        ),
        'level_map' =>
        array(
            0 => 'VIP0',
            1 => 'VIP1',
            2 => 'VIP2',
            3 => 'VIP3',
            4 => 'VIP4',
            5 => 'VIP5',
            6 => 'VIP6',
        ),
        'is_active_map' =>
        array(
            0 => '否',
            1 => '是',
        ),
    ),
    'user_relation' =>
    array(
        'level_map' =>
        array(
            1 => 'LV1',
            2 => 'LV2',
            3 => 'LV3',
        ),
        'is_active_map' =>
        array(
            0 => '否',
            1 => '是',
        ),
    ),
    'system_info' =>
    array(
        'type_map' =>
        array(
            1 => '公告',
            2 => '新闻中心',
            3 => '客服链接',
            4 => '组织机构',
            5 => '工作动态',
            6 => '资料中心',
            7 => '全球减贫信息',
            8 => '联系我们',
            9 => '视频中心',
            10 => '团队集锦',
            11=>'共富新闻',
            12=>'常见问题',
        ),
        'status_map' =>
        array(
            0 => '禁用',
            1 => '启用',
        ),
        'setting_key' => ['apk_download_url', 'version_apk', 'video_url', 'video_img_url', 'kefu_url', 'register_domain', 'is_req_encypt', 'microcore_group', 'qq_group', 'chat_url1', 'chat_url2','withdraw_fee_ratio2','withdraw_fee_ratio2_min','is_card_show','card_schedule','card_string'],
    ),
    'project' =>
    array(
        'status_map' =>
        array(
            0 => '禁用',
            1 => '启用',
        ),
        'is_recommend_map' =>
        array(
            0 => '否',
            1 => '是',
        ),
        'group' => [
            1 => '共富工程',
            2 => '激活数字人民币额度',
            3 => '激活数字人民币账单',
            4 => '金融强国之路',
            5 => '免费办卡',
            6 => '驰援甘肃',
        ],
        'groupName' => [
/*              ['id'=>1,'name'=>'强国工匠','type'=>0,],
             ['id'=>2,'name'=>'国富民强','type'=>0,],
             ['id'=>3,'name'=>'朝夕奔梦','type'=>0,],  */
             ['id'=>6,'name'=>'驰援甘肃','type'=>0,], 
             ['id'=>5,'name'=>'免费办卡','type'=>1,], 
             ['id'=>4,'name'=>'金融强国之路','type'=>0,], 

        ],
        'project_house'=>[
            45=>38,
            46=>90,
            47=>90,
            48=>110,
            49=>127,
            50=>130,
            51=>150,
            52=>150,
        ],
        'project_card'=>[
            61=>['min'=>0,'max'=>100000],
            62=>['min'=>100000,'max'=>300000],
            63=>['min'=>300000,'max'=>500000],
            64=>['min'=>500000,'max'=>1000000],
            65=>['min'=>1000000,'max'=>10000000],
        ]
    ),
    'order' =>
    array(
        'status_map' =>
        array(
            1 => '待支付',
            2 => '收益中',
            3 => '待出售',
            4 => '已完成',
        ),
        'pay_method_map' =>
        array(
            0 => '未知',
            1 => '余额',
            2 => '微信',
            3 => '支付宝',
            4 => '线上银联',
            5 => '推荐奖励',
            6 => '线下银联',
        ),
        'equity_status_map' =>
        array(
            1 => '不能兑换',
            2 => '可以兑换',
            3 => '已兑换',
        ),
        'digital_yuan_status_map' =>
        array(
            1 => '不能兑换',
            2 => '可以兑换',
            3 => '已兑换',
        ),
    ),
    'user_balance_log' =>
    array(
        'type_map' =>
        array(
            1 => '充值',
            2 => '提现',
            3 => '购买项目',
            4 => '充值奖励',
            5 => '国务院津贴',
            6 => '项目分红',
            7 => '额外奖励',
            8 => '团队奖励',
            9 => '直属推荐额外奖励',
            10 => '股权兑换',
            11 => '期权兑换',
            12 => '返还本金',
            13 => '提现失败',
            14 => '被动收益',
            15 => '手动入金',
            16 => '手动出金',
            17 => '签到',
            18 => '转账',
            19 => '收款',
            20 => '提现手续费',
            21 => '房屋维修基金',
            22 => '日提现额度',
            23 => '数字人民币红包',
            24 => '注册赠送数字人民币',
            25 => '激活数字人民币账单',
            26 => '领取保障项目',
            27 => '资产恢复',
            28 => '保障项目额度发放',
        ),
        'balance_type_map' =>
        array(
            1 => '充值',
            2 => '提现',
            3 => '购买项目',
            4 => '充值奖励',
            5 => '国务院津贴',
            6 => '项目分红',
            7 => '额外奖励',
            8 => '团队奖励',
            9 => '直属推荐额外奖励',
            10 => '股权兑换',
            11 => '期权兑换',
            12 => '返还本金',
            13 => '提现失败',
            14 => '被动收益',
            15 => '手动入金',
            16 => '手动出金',
            17 => '签到',
            18 => '转账',
            19 => '收款',
            20 => '提现手续费',
            21 => '房屋维修基金',
            22 => '日提现额度',
            23 => '数字人民币红包',
            24 => '注册赠送数字人民币',
            25 => '激活数字人民币账单',
            26 => '领取保障项目',
            27 => '资产恢复',
            28 => '保障项目额度发放',
        ),
        'integral_type_map' =>
        array(
            3 => '购买项目',
            15 => '手动入金',
            16 => '手动出金',
            17 => '签到',
        ),
        'log_type_map' =>
        array(
            1 => '余额日志',
            2 => '积分日志',
            3 => '数字人民币日志',
            4 => '推荐奖励日志',
            5 => '提现额度日志',
        ),
        'status_map' =>
        array(
            1 => '待确认',
            2 => '成功',
            3 => '失败',
        ),
    ),
    'passive_income_record' =>
    array(
        'status_map' =>
        array(
            1 => '未开始',
            2 => '未领取',
            3 => '已领取',
        ),
        'is_finish_map' =>
        array(
            0 => '否',
            1 => '是',
        ),
    ),
    'banner' =>
    array(
        'status_map' =>
        array(
            0 => '禁用',
            1 => '启用',
        ),
    ),
    'level_config' =>
    array(
        'level_map' =>
        array(
            0 => 'VIP0',
            1 => 'VIP1',
            2 => 'VIP2',
            3 => 'VIP3',
            4 => 'VIP4',
            5 => 'VIP5',
            6 => 'VIP6',
        ),
    ),
    'payment' =>
    array(
        'product_type_map' =>
        array(
            1 => '投资项目',
            2 => '充值',
        ),
        'status_map' =>
        array(
            1 => '未支付',
            2 => '支付成功',
            3 => '支付失败',
        ),
    ),
    'capital' =>
    array(
        'type_map' =>
        array(
            1 => '充值',
            2 => '提现',
        ),
        'status_map' =>
        array(
            1 => '待审核-待支付',
            2 => '审核通过-支付成功',
            3 => '审核拒绝-支付失败',
            4 => '待打款',
        ),
        'topup_status_map' =>
        array(
            1 => '待支付',
            2 => '支付成功',
            3 => '支付失败',
        ),
        'withdraw_status_map' =>
        array(
            1 => '待审核',
            2 => '已提现',
            3 => '审核拒绝',
            4 => '打款中',
        ),
        'pay_channel_map' =>
        array(
            1 => '后台手动',
            1 => '宏亚',
            2 => '龙翔',
            3 => '嘉鑫',
            4 => '银行卡',
            7=>'连连',
            8=>'暴雪',
            9=>'发财呀',
            10=>'汇鑫',
            11=>'丰胸',
            12=>'香蕉',
            13=>'新诚博付',
            14=>'66支付',
            15=>'鲨鱼支付',
            16=>'八戒支付',
            17=>'风支付',
            18=>'大象支付',
        ),
    ),
    'pay_account' =>
    array(
        'pay_type_map' =>
        array(
            1 => '微信',
            2 => '支付宝',
            3 => '线上银联',
            4 => '银行卡',
            5 => '云闪付',
            8 => '快捷支付',
        ),
    ),
    'payment_config' =>
    array(
        'type_map' =>
        array(
            1 => '微信',
            2 => '支付宝',
            3 => '线上银联',
            4 => '银行卡',
            5 => '云闪付',
            8 => '快捷支付',
        ),
        'status_map' =>
        array(
            0 => '禁用',
            1 => '启用',
        ),
        'channel_map' =>
        array(
            0 => '线下',
            1 => '宏亚',
            2 => '龙翔',
            3 => '嘉鑫',
            4 => '银行卡',
            7=>'连连',
            8=>'暴雪',
            9=>'发财呀',
            10=>'汇鑫',
            11=>'丰胸',
            12=>'香蕉',
            13=>'新诚博付',
            14=>'66支付',
            15=>'鲨鱼支付',
            16=>'八戒支付',
            17=>'风支付',
            18=>'大象支付',
        ),
    ),
    'rank_reward' => [
        1=>800,
        2=>500,
        3=>300,
        4=>200,
        5=>100,
        6=>100,
        7=>100,
        8=>100,
        9=>100,
        10=>100,
        11=>20,
    ],
    'noDomainArr'=>[
            // 'api.nhxij.com',
            // 'api.ojokl.com',
            // 'api.zcxjh.com',
            // 'api.actzv.com',  
            // 'api.fkbya.com',
            // 'api.hjtojoh.com',
            // 'api.aojmjfe.com', 
            // 'api.lht2586.com',
            // 'api.hprkv.com',
            // 'api.f3sfu.com',
            // 'api.smnrg.com',
            // 'api.gbudew.com',
            // 'api.spcdew.com',
            // 'api.smnrg.com',
            // 'api.gbudew.com',
            // 'api.spcdew.com',
            // 'api.fengyansh.cn',
            // 'api.yjvade.com',
            // 'api.nolrew.com',
    ],
    'asset_recovery' => [
        1 => [
            'type' => 1,
            'amount' => 100,
            'min_asset' => 1,
            'max_asset' => 100
        ],
        2 => [
            'type' => 2,
            'amount' => 200,
            'min_asset' => 101,
            'max_asset' => 300
        ],
        3 => [
            'type' => 3,
            'amount' => 500,
            'min_asset' => 301,
            'max_asset' => 600
        ],
        4 => [
            'type' => 4,
            'amount' => 1000,
            'min_asset' => 601,
            'max_asset' => 1000
        ],
        5 => [
            'type' => 5,
            'amount' => 2000,
            'min_asset' => 1001,
            'max_asset' => 3000
        ],
        6 => [
            'type' => 6,
            'amount' => 3000,
            'min_asset' => 3001,
            'max_asset' => 'max'
        ],
    ],
    'ensure' => [
        1 => [
            'id' => 1,
            'name' => '住房保障',
            'img' => env('app.host').'/zhufang.jpg',
            'intro_img' => env('app.host').'/intro_zhufang.jpg',
            'receive' => false,
            'amount' => 20000,
            'receive_amount' => 4200000,
            'process_time' => 25,
            'verify_time' => 45,
            'remain_count' => 10000
        ],
        2 => [
            'id' => 2,
            'name' => '出行保障',
            'img' => env('app.host').'/chuxing.jpg',
            'intro_img' => env('app.host').'/intro_chuxing.jpg',
            'receive' => false,
            'amount' => 4500,
            'receive_amount' => 567000,
            'process_time' => 25,
            'verify_time' => 45,
            'remain_count' => 0
        ],
        3 => [
            'id' => 3,
            'name' => '养老保障',
            'img' => env('app.host').'/yanglao.jpg',
            'intro_img' => env('app.host').'/intro_yanglao1.jpg',
            'receive' => false,
            'amount' => 10000,
            'receive_amount' => 1470000,
            'process_time' => 25,
            'verify_time' => 45,
            'remain_count' => 0
        ],
        4 => [
            'id' => 4,
            'name' => '通讯保障',
            'img' => env('app.host').'/tongxin.jpg',
            'intro_img' => env('app.host').'/intro_tongxin.jpg',
            'receive' => false,
            'amount' => 1500,
            'receive_amount' => 157500,
            'process_time' => 25,
            'verify_time' => 45,
            'remain_count' => 0
        ],
        // 5 => [
        //     'id' => 5,
        //     'name' => '共富商城',
        //     'img' => env('app.host').'/shangcheng.png',
        //     'intro_img' => env('app.host').'/intro_shangcheng.jpg',
        //     'receive' => false,
        //     'amount' => 0,
        //     'receive_amount' => 0,
        //     'process_time' => 0,
        //     'verify_time' => 0,
        //     'remain_count' => 0
        // ],
    ],
);
