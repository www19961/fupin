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
        'activeRank'  =>  'app\common\command\ActiveRank',
        'checkSubsidy'  =>  'app\common\command\CheckSubsidy',
        'sendCashReward'  =>  'app\common\command\SendCashReward',
        'autoWithdrawAudit'  =>  'app\common\command\AutoWithdrawAudit',
        'genarateEthAdress' =>  'app\common\command\GenarateEthAdress',
        'checkAssetBonus'  =>  'app\common\command\CheckAssetBonus',
        'orderReward' => 'app\common\command\OrderReward',
        'makeKline' => 'app\common\command\MakeKline',
        'BalanceFix' => 'app\common\command\BalanceFix',
        'SignFix' => 'app\common\command\SignFix',
        'ShareFupin' => 'app\common\command\ShareFupin',
        'ChangeStatus' => 'app\common\command\ChangeStatus',
        'Gift' => 'app\common\command\Gift',
        'Gift1' => 'app\common\command\Gift1',
        'Gift2' => 'app\common\command\Gift2',
        'OrderRewardDaily' => 'app\common\command\OrderRewardDaily',
    ],
];
