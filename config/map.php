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
                ),
            'status_map' =>
                array(
                    0 => '禁用',
                    1 => '启用',
                ),
            'setting_key'=>['apk_download_url','version_apk','video_url','video_img_url','kefu_url','register_domain','is_req_encypt','microcore_group','qq_group','chat_url1','chat_url2'],
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
            'group'=>[
                1=>'强国工匠',
                2=>'国富民强',
                3=>'朝夕奔梦',
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
                    2 => '微信',
                    3 => '支付宝',
                    4 => '线上银联',
                    5 => '银行卡',
                    6 => '云闪付',
                    7 => '数字人民币',
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
                    6 => '数字人民币',
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
                    6 => '数字人民币',
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
                ),
        ),
);
