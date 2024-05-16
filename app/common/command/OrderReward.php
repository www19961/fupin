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

class OrderReward extends Command
{
    /**
        /10 * * * * cd /www/wwwroot/aaa && php think orderReward
     */
    protected function configure()
    {
        $this->setName('order')->setDescription('产品到期收益');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln('==========' . date('Y-m-d H:i:s') . " start ==========");
        Db::name('order')->where('end_time', '<=', time())->where('status', 1)->order('id', 'asc')->chunk(500, function($orders) use($output) {
            foreach ($orders as $order) {
                Db::startTrans();
                try {

                    //专项基金收益
                    User::changeInc($order['user_id'],$order['reward'],'specific_balance',31,0);
                    //国家扶贫金
                    User::changeInc($order['user_id'], $order['fupin_reward'], 'specific_fupin_balance', 37, $order['id'], 3);

                    if ($order['is_circle'] == 0) {
                        Order::where('id', $order['id'])->update(['status' => 2]);
                        $output->writeln("order_id:【{$order['id']}】完成");
                    } else {
                        $nextTime = $order['end_time'] + 86400 * $order['days'];
                        Order::where('id', $order['id'])->update(['end_time' => $nextTime]);
                        $output->writeln("order_id:【{$order['id']}】周期产品 完成");
                    }

                    Db::commit();
                } catch (Exception $e) {
                    Db::rollback();
                    throw $e;
                }
            }
        });
        $output->writeln('==========' . date('Y-m-d H:i:s') . " done  ==========");
    }
}