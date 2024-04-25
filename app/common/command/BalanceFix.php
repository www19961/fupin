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
        Db::name('user_balance_log')->where('remark', '团队奖励')->where('origin_pay', 0)->order('id', 'asc')->chunk(500, function($orders) {
            foreach ($orders as $order) {
                Db::startTrans();
                try {

                    $user = User::find($order['user_id']);
                    if ($order['change_balance'] <= $user['balance']) {
                        Db::name('user')->where('id', $user['id'])->dec('balance', $order['change_balance'])->update();
                    } elseif ($order['change_balance'] > $user['balance']) {
                        $dec = $user['balance'];
                        if ($dec > 0) {
                            Db::name('user')->where('id', $user['id'])->data(['balance' => 0])->update();
                        }
                        $c = $order['change_balance'] - $dec;
                        echo "{$order['user_id']}-欠[{$c}]\n";
                    }

                    Db::name('order')->where('id', $order['id'])->delete();

                    Db::commit();
                } catch (Exception $e) {
                    //var_dump($e);
                    Db::rollback();
                    throw $e;
                }
            }
        });
    }
}