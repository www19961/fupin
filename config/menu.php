<?php

return array(
    '主页' =>
        array(
            'icon' => 'fa-home',
            'url' => 'admin/Home/index',
        ),
    '会员中心' =>
        array(
            'icon' => 'fa-user',
            'url' =>
                array(
                    '会员管理' =>
                        array(
                            'icon' => 'fa-circle-o',
                            'url' => 'admin/User/userList',
                        ),
                    '会员资金明细' =>
                        array(
                            'icon' => 'fa-circle-o',
                            'url' => 'admin/UserBalanceLog/userBalanceLogList',
                        ),
                    '会员积分记录' =>
                        array(
                            'icon' => 'fa-circle-o',
                            'url' => 'admin/UserBalanceLog/userIntegralLogList',
                        ),
                    '收货地址' =>
                    array(
                        'icon' => 'fa-circle-o',
                        'url' => 'admin/UserDelivery/userDeliveryList',
                    ),
                ),
        ),
    '交易中心' =>
        array(
            'icon' => 'fa-cubes',
            'url' =>
                array(
                    '项目管理一期' =>
                        array(
                            'icon' => 'fa-circle-o',
                            'url' => 'admin/Project/projectList',
                        ),
/*                     '项目管理二期' =>
                        array(
                            'icon' => 'fa-circle-o',
                            'url' => 'admin/Projects/projectsList',
                        ), */
                    '交易订单' =>
                        array(
                            'icon' => 'fa-circle-o',
                            'url' => 'admin/Order/orderList',
                        ),
                    '修改分红天数'=>array(
                        'icon' => 'fa-circle-o',
                        'url' => 'admin/Order/addTime',
                    ),
                    '充值记录' =>
                        array(
                            'icon' => 'fa-circle-o',
                            'url' => 'admin/Capital/topupList',
                        ),
                    '提现记录' =>
                        array(
                            'icon' => 'fa-circle-o',
                            'url' => 'admin/Capital/withdrawList',
                        ),
               	    '流程审核' =>
                        array(
                            'icon' => 'fa-circle-o',
                            'url' => 'admin/Process/processList',
                        ),
                ),
        ),
    '设置中心' =>
        array(
            'icon' => 'fa-gears',
            'url' =>
                array(
                    '支付渠道配置' =>
                        array(
                            'icon' => 'fa-circle-o',
                            'url' => 'admin/PaymentConfig/paymentConfigList',
                        ),
                    '股权K线图' =>
                        array(
                            'icon' => 'fa-circle-o',
                            'url' => 'admin/KlineChart/klineChart',
                        ),
                    '会员等级管理' =>
                        array(
                            'icon' => 'fa-circle-o',
                            'url' => 'admin/LevelConfig/levelConfigList',
                        ),
                    '轮播图设置' =>
                        array(
                            'icon' => 'fa-circle-o',
                            'url' => 'admin/Banner/bannerList',
                        ),
                    '公司动态' =>
                        array(
                            'icon' => 'fa-circle-o',
                            'url' => 'admin/SystemInfo/companyInfoList',
                        ),
                    '系统信息设置' =>
                        array(
                            'icon' => 'fa-circle-o',
                            'url' => 'admin/SystemInfo/systemInfoList',
                        ),
                    '常规配置' =>
                        array(
                            'icon' => 'fa-circle-o',
                            'url' => 'admin/Setting/settingList',
                        ),
                ),
        ),
    '后台账号管理' =>
        array(
            'icon' => 'fa-users',
            'url' => 'admin/AdminUser/adminUserList',
        ),
);
