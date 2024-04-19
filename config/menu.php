<?php

return array(
    '控制台' =>
        array(
            'icon' => 'fa-home',
            'url' => 'admin/Home/index',
        ),
    '常规管理' =>
        array(
            'icon' => 'fa-cubes',
            'url' =>
                array(
                    '会员实名认证' =>
                        array(
                            'icon' => 'fa-circle-o',
                            'url' => 'admin/User/authentication',
                        ),
                    '奖品设置' =>
                        array(
                            'icon' => 'fa-circle-o',
                            'url' => 'admin/GoldEgg/setting',
                        ),
                    // '砸金蛋中奖人预设' =>
                    //     array(
                    //         'icon' => 'fa-circle-o',
                    //         'url' => 'admin/GoldEgg/luckyUser',
                    //     ),
                    '中奖记录' =>
                        array(
                            'icon' => 'fa-circle-o',
                            'url' => 'admin/GoldEgg/PrizeUserLog',
                        ),
                    // '许愿树设置' =>
                    //     array(
                    //         'icon' => 'fa-circle-o',
                    //         'url' => 'admin/WishTree/setting',
                    //     ),
                    // '许愿树领奖记录' =>
                    //     array(
                    //         'icon' => 'fa-circle-o',
                    //         'url' => 'admin/WishTree/WishTreePrizeLog',
                    //     ),
                    // '龙头币' =>
                    //     array(
                    //         'icon' => 'fa-circle-o',
                    //         'url' => 'admin/Coin/list',
                    //     ),
                    // '龙头币K线图' =>
                    //     array(
                    //         'icon' => 'fa-circle-o',
                    //         'url' => 'admin/Coin/klineChart',
                    //     ),
                    // '龙头币持币列表' =>
                    //     array(
                    //         'icon' => 'fa-circle-o',
                    //         'url' => 'admin/Coin/coinHold',
                    //     ),
                    // '龙头币转账记录' =>
                    //     array(
                    //         'icon' => 'fa-circle-o',
                    //         'url' => 'admin/Coin/transferLog',
                    //     ),
                    '后台账号管理' =>
                        array(
                            'icon' => 'fa-users',
                            'url' => 'admin/AdminUser/adminUserList',
                        ),
                ),
        ),
        '交易管理' =>
        array(
            'icon' => 'fa-cubes',
            'url' =>
                array(
                    '项目管理' =>
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
                    // '修改分红天数'=>array(
                    //     'icon' => 'fa-circle-o',
                    //     'url' => 'admin/Order/addTime',
                    // ),

               	    // '流程审核' =>
                    //     array(
                    //         'icon' => 'fa-circle-o',
                    //         'url' => 'admin/Process/processList',
                    //     ),
                ),
        ),
    '用户管理' =>
        array(
            'icon' => 'fa-user',
            'url' =>
                array(
                    '用户管理' =>
                        array(
                            'icon' => 'fa-circle-o',
                            'url' => 'admin/User/userList',
                        ),
                    '用户资金明细' =>
                        array(
                            'icon' => 'fa-circle-o',
                            'url' => 'admin/UserBalanceLog/userBalanceLogList',
                        ),
                    // '用户积分记录' =>
                    //     array(
                    //         'icon' => 'fa-circle-o',
                    //         'url' => 'admin/UserBalanceLog/userIntegralLogList',
                    //     ),
                    // '收货地址' =>
                    // array(
                    //     'icon' => 'fa-circle-o',
                    //     'url' => 'admin/UserDelivery/userDeliveryList',
                    // ),
                ),
        ),
    '充值管理' =>
        array(
            'icon' => 'fa-gears',
            'url' =>
                array(
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
                    // '股权K线图' =>
                    //     array(
                    //         'icon' => 'fa-circle-o',
                    //         'url' => 'admin/KlineChart/klineChart',
                    //     ),
                    // '会员等级管理' =>
                    //     array(
                    //         'icon' => 'fa-circle-o',
                    //         'url' => 'admin/LevelConfig/levelConfigList',
                    //     ),
                    // '轮播图设置' =>
                    //     array(
                    //         'icon' => 'fa-circle-o',
                    //         'url' => 'admin/Banner/bannerList',
                    //     ),
                    // '公司动态' =>
                    //     array(
                    //         'icon' => 'fa-circle-o',
                    //         'url' => 'admin/SystemInfo/companyInfoList',
                    //     ),
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



);
