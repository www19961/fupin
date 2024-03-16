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
        Db::name('order')->where('end_time', '<=', time())->where('status', 1)->order('id', 'asc')->chunk(500, function($orders) {
            foreach ($orders as $order) {
                Db::startTrans();
                try {

                    $project = Project::find($order['project_id']);
                    User::changeBalance($order['user_id'], $project['reward'], 6, $order['user_id']);
                    Order::where('id', $order['id'])->update(['status' => 2]);

                    Db::commit();
                } catch (Exception $e) {
                    Db::rollback();
                    throw $e;
                }
            }
        });
    }
}