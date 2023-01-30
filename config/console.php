<?php
// +----------------------------------------------------------------------
// | 控制台配置
// +----------------------------------------------------------------------
return [
    // 指令定义
    'commands' => [
        'curd'	=>	'app\common\command\Curd',
        'rule'  =>  'app\common\command\Rule',
        'checkBonus'  =>  'app\common\command\CheckBonus',
        'checkSubsidy'  =>  'app\common\command\CheckSubsidy',
        'sendCashReward'  =>  'app\common\command\SendCashReward',
    ],
];
