<?php

namespace app\common\command;

use think\facade\Db;
use app\model\User;
use app\model\Project;
use app\model\Order;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use Exception;
use think\facade\Cache;

class BalanceFix extends Command
{
    /**
        /10 * * * * cd /www/wwwroot/aaa && php think orderReward
     */
    protected function configure()
    {
        $this->setName('BalanceFix')->setDescription('充值团队奖励 team_bonus_balance -> balance');
    }

    protected function execute(Input $input, Output $output)
    {
        Db::name('user_balance_log')->where('log_type', 2)->where('remark', '团队奖励')->order('id', 'asc')->chunk(500, function($orders) {
            foreach ($orders as $order) {
                Db::startTrans();
                try {

                    $user = User::find($order['user_id']);
                    if ($user['team_bonus_balance'] >= $order['change_balance']) {
                        Db::name('user')->where('id', $user['id'])->dec('team_bonus_balance', $order['change_balance'])->update();
                        Db::name('user')->where('id', $user['id'])->inc('balance', $order['change_balance'])->update();
                        Db::name('user_balance_log')->where('id', $order['id'])->update(['log_type' => 1]);
                    }

                    echo "{$order['id']}\n";

                    Db::commit();
                } catch (Exception $e) {
                    Db::rollback();
                    throw $e;
                }
            }
        });
    }
}