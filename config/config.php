<?php

return [
    // token加密的key和iv
    'aes_key' => 'cvJwBfr2g5333bBh',
    'aes_iv' => 'abC9R123CeUaeOER',
    'req_aes_key'=>'zgxcfzjjh006....',
    'req_aes_iv'=>'01234567zgfzjjh0',
    'register_key'=>'zgxcfzjjh007!',

    'payment_conf' => [
        'key' => 'GIhthdG3yPhNvWWa8VzhLXCPEiNMCiG6',
        'pay_memberid' => '231297029',
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
        'key' => 'kystgKsLKUYw9J76zy6p9H8',
        'pay_memberid'=>'100198',
        'pay_notifyurl' => env('app.host').'/common/paynotify4',
        'pay_callbackurl' => env('common.callback_url').'/tradeSuccess',
        'payment_url' => 'https://apimp4weafv.baoxuepay.xyz/api/v1/payment/init',
        'query_url' => 'https://apimp4weafv.baoxuepay.xyz/api/v1/payment/query',
    ],
    'payment_conf5' => [
        'key' => 'dE0uJ1dQ6iS8bR0a',
        'pay_memberid'=>'10942',
        'pay_notifyurl' => env('app.host').'/common/paynotify5',
        'pay_callbackurl' => env('common.callback_url').'/tradeSuccess',
        'payment_url' => 'https://facaiya.vip/pay/json',
        'query_url' => 'https://hongtai01.top/Query',
    ],
    'payment_conf6' => [
        'key' => '6XCAGWJKQ0XQP0HF633UKVXPAIBIHIZQVN9PYCFUGIXLQ8Z9KNLUGPGHOPERUI1MZJ8FT9NGXNKYJPTQMX5MNLD2HDECACS5AGOCKFGEQV4DHWCR1CVTMSZT2VGEZGAI',
        'pay_memberid'=>'20000313',
        'pay_notifyurl' => env('app.host').'/common/paynotify6',
        'pay_callbackurl' => env('common.callback_url').'/tradeSuccess',
        'payment_url' => 'http://hxzf.xyz:56700/api/pay/create_order',
        'query_url' => 'http://hxzf.xyz:56700/api/pay/query_order',
    ],
    'payment_conf7' => [
        'key' => 'IULOPQKIQT6PYGTYVK5ZGAQPJOQCSIEIQZCZE0VLWBRYLB92IR9HYBUCKFOPMWKRCRBGEKCZMQ89KOFLJYC8YTNSWFCU0HHHPELDKC663CTMEBYRUUNIYYLJHR94FIJD',
        'pay_memberid'=>'20000508',
        'pay_notifyurl' => env('app.host').'/common/paynotify7',
        'pay_callbackurl' => env('common.callback_url').'/tradeSuccess',
        'payment_url' => 'https://dfew.fxrcn.lol/api/pay/create_order',
        'query_url' => 'https://dfew.fxrcn.lol/api/pay/query_order',
    ],
    'payment_conf8' => [
        'key' => '9079DCAEF4C877',
        'pay_memberid'=>'10180',
        'pay_notifyurl' => env('app.host').'/common/paynotify8',
        'pay_callbackurl' => env('common.callback_url').'/tradeSuccess',
        'payment_url' => 'http://8.210.18.24:405/gateway/index/checkpoint',
        'query_url' => 'http://8.210.18.24:405/gateway/index/queryorder',
    ],
    'payment_conf9' => [
        'key' => '787ea64501054d039e28139f8ad4784a',
        'pay_memberid'=>'102232',
        'pay_notifyurl' => env('app.host').'/common/paynotify9',
        'pay_callbackurl' => env('common.callback_url').'/tradeSuccess',
        'payment_url' => 'http://43.198.97.242:29110/mobile/order/created',
        'query_url' => 'http://43.198.97.242:29110/mobile/order/query',
    ],
    'payment_conf10' => [
        'key' => 'kgm3tn4up2itiih4kbmaeig54erv3aax',
        'pay_memberid'=>'231188968',
        'pay_notifyurl' => env('app.host').'/common/paynotify10',
        'pay_callbackurl' => env('common.callback_url').'/tradeSuccess',
        'payment_url' => 'http://66paytue.meisuobudamiya.com/Pay_Index.html',
        'query_url' => 'http://66paytue.meisuobudamiya.com/Pay_Trade_query.html',
    ],
    'payment_conf11' => [
        'key' => '87e7324de2690b1e4d1f50e0d11adff4',
        'pay_memberid'=>'10005',
        'pay_notifyurl' => env('app.host').'/common/paynotify11',
        'pay_callbackurl' => env('common.callback_url').'/tradeSuccess',
        'payment_url' => 'http://acrossthesea.champion999.one/api/newOrder',
        'query_url' => 'http://acrossthesea.champion999.one/api/queryOrder',
    ],
    'payment_conf12' => [
        'key' => 'F048902396CC89',
        'pay_memberid'=>'10115',
        'pay_notifyurl' => env('app.host').'/common/paynotify12',
        'pay_callbackurl' => env('common.callback_url').'/tradeSuccess',
        'payment_url' => 'http://8.210.157.79:405/gateway/index/checkpoint',
        'query_url' => 'http://8.210.157.79:405/gateway/index/queryorder',
    ],
    'payment_conf13' => [
        'key' => 'GLXCGT6KVB5B1FIT42BVX7CXMVCAD4XKILXSGYEHGQJASUL0XJS0HCHQSQYWYZDQPUINT74KXCERRW69F0UKZ09X3ZRE6H88DCFEDCGAPVGGCOOKUHENDHB1OVYZBTAY',
        'pay_memberid'=>'20000017',
        'pay_notifyurl' => env('app.host').'/common/paynotify13',
        'pay_callbackurl' => env('common.callback_url').'/tradeSuccess',
        'payment_url' => 'https://fzf.app/api/pay/create_order',
        'query_url' => 'https://fzf.app/api/pay/query_order',
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
