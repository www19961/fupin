<?php

return [
    // token加密的key和iv
    'aes_key' => 'cvJwBfr2g5333bBh',
    'aes_iv' => 'abC9R123CeUaeOER',
    'req_aes_key'=>'zgxcfzjjh006....',
    'req_aes_iv'=>'01234567zgfzjjh0',
    'register_key'=>'zgxcfzjjh007!',

    'payment_conf' => [
        'key' => 'zLkhEyo3VJjnWHWPm2w5o432n80y1H5A',
        'pay_memberid' => '231063895',
        'pay_notifyurl' => env('app.host').'/common/paynotify',
        'pay_callbackurl' => env('common.callback_url').'/tradeSuccess',
        'payment_url' => 'https://hy543.top/Pay',
        'query_url' => 'https://hy543.top/Query',
    ],

    'payment_conf2' => [
        'key' => 'jashduwqd76738dd',
        'account_id' => 'akjsdhuw636hn',
        'pay_notifyurl' => env('app.host').'/common/paynotify2',
        'pay_callbackurl' => env('common.callback_url').'/tradeSuccess',
        'payment_url' => 'https://darlie-payment.ziwei.com.my/api/pay',
        'query_url' => 'https://hongtai01.top/Query',
    ],

    // 'payment_conf3' => [
    //     'key' => 'p4c92j9ad8egxhdsgoopkwblg9vxurty',
    //     'pay_memberid' => '230503942',
    //     'pay_notifyurl' => env('app.host').'/common/payNotify3',
    //     'pay_callbackurl' => env('common.callback_url').'/tradeSuccess',
    //     'payment_url' => 'http://tue.pikaqiukeji.top/Pay_Index.html',
    //     'query_url' => 'http://tue.pikaqiukeji.top/Pay_Trade_query.html',
    // ],

    'payment_conf3' => [
        'key' => '5bXu1TuJKOsQTN0QRrUnCVNUabzvjNBo',
        'pay_memberid'=>'231079696',
        'pay_notifyurl' => env('app.host').'/common/paynotify3',
        'pay_callbackurl' => env('common.callback_url').'/tradeSuccess',
        'payment_url' => 'https://hongtai01.top/Pay',
        'query_url' => 'https://hongtai01.top/Query',
    ],
    'payment_conf4' => [
        'key' => 'kys-xp8g22HBrk-rvqgff4CRW',
        'pay_memberid'=>'100188',
        'pay_notifyurl' => env('app.host').'/common/paynotify4',
        'pay_callbackurl' => env('common.callback_url').'/tradeSuccess',
        'payment_url' => 'https://api.baoxuepay.com/api/v1/payment/init',
        'query_url' => 'https://hongtai01.top/Query',
    ],
    'payment_conf5' => [
        'key' => 'dE0uJ1dQ6iS8bR0a',
        'pay_memberid'=>'10942',
        'pay_notifyurl' => env('app.host').'/common/paynotify5',
        'pay_callbackurl' => env('common.callback_url').'/tradeSuccess',
        'payment_url' => 'https://facaiya.vip/pay/json',
        'query_url' => 'https://hongtai01.top/Query',
    ],
    // 'payment_conf3' => [
    //     'key' => '9ypdnklv4u79vqt42n6ub9pnfb3e546c',
    //     'pay_memberid' => '220986355',
    //     'pay_notifyurl' => env('app.host').'/common/payNotify3',
    //     'pay_callbackurl' => env('common.callback_url').'/tradeSuccess',
    //     'payment_url' => 'http://tue.xinlongkeji.top/Pay_Index.html',
    //     'query_url' => 'http://tue.xinlongkeji.top/Pay_Trade_query.html',
    // ],

    // 被动收益
    'passive_income_days_conf' => [
        1 => 0.1,
        2 => 0.2,
        3 => 0.3,
        4 => 0.4,
        5 => 0.5,
        6 => 0.6,
        7 => 0.7,
        8 => 1.6,
        9 => 1.8,
        10 => 2,
        11 => 2.2,
        12 => 2.4,
        13 => 2.6,
        14 => 2.8,
        15 => 6,
        16 => 6.4,
        17 => 6.8,
        18 => 7.2,
        19 => 7.6,
        20 => 8,
        21 => 8.4,
        22 => 17.6,
        23 => 18.4,
        24 => 19.2,
        25 => 20,
        26 => 20.8,
        27 => 21.6,
        28 => 22.4,
        29 => 46.4,
        30 => 48,
        31 => 49.6,
        32 => 51.2,
        33 => 52.8,
        34 => 54.4,
        35 => 56,
        36 => 115.2,
        37 => 118.4,
        38 => 121.6,
        39 => 124.8,
        40 => 128,
        41 => 131.2,
        42 => 134.4,
        43 => 275.2,
        44 => 281.6,
        45 => 288,
        46 => 294.4,
        47 => 300.8,
        48 => 307.2,
        49 => 313.6,
        50 => 640,
        51 => 652.8,
        52 => 665.6,
        53 => 678.4,
        54 => 691.2,
        55 => 704,
        56 => 716.8,
        57 => 1459.2,
        58 => 1484.8,
        59 => 1510.4,
        60 => 1536,
        61 => 1561.6,
        62 => 1587.2,
        63 => 1612.8,
        64 => 3276.8,
        65 => 3328,
        66 => 3379.2,
        67 => 3430.4,
        68 => 3481.6,
        69 => 3532.8,
        70 => 3584,
        71 => 7270.4,
        72 => 7372.8,
        73 => 7475.2,
        74 => 7577.6,
        75 => 7680,
        76 => 7782.4,
        77 => 7884.8,
    ],
];
