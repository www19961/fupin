<?php

namespace app\common\command;

use think\facade\Db;
use app\model\User;
use app\model\ProjectItem;
use app\model\Project;
use app\model\Order;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use Exception;
use think\facade\Cache;

class Gift1 extends Command
{
    /**
        /10 * * * * cd /www/wwwroot/aaa && php think orderReward
     */
    protected function configure()
    {
        $this->setName('Gift')->setDescription('');
    }

    protected function execute(Input $input, Output $output)
    {
        die;
        $userPhone = ['19805853578','15106151331'];
        $req['project_id'] = 6815;
        $projectItem = ProjectItem::where('id', $req['project_id'])->find();
        $project = Project::find($projectItem['project_id']);
        $pay_amount = $projectItem['price'];

        foreach ($userPhone as $key => $value) {
            $user = User::where('phone', $value)->find();
            if (empty($user)) {
                $output->writeln("={$value} 没找到=");
                continue;
            }

            $order_sn = 'FP'.build_order_sn($user['id']);
            $order['project_id'] = $req['project_id'];
            $order['user_id'] = $user['id'];
            $order['up_user_id'] = $user['up_user_id'];
            $order['order_sn'] = $order_sn;
            $order['buy_num'] = 1;
            $order['price'] = $pay_amount;
            $order['buy_amount'] = $pay_amount;
            $order['start_time'] = time();
            $order['project_name'] = $project['name'];
            $order['type'] = $project['type'];
            $order['days'] = $projectItem['days'];
            $order['reward'] = $projectItem['reward'];
            $order['end_time'] = time() + 86400 * $projectItem['days'];
            $order['fupin_reward'] = $projectItem['fupin_reward'];
            $order['is_gift'] = $project['is_gift'];
            $order['is_circle'] = $project['is_circle'];
            $order['multiple'] = $project['multiple'];
    
            $orderRes = Order::create($order);
        }

        $output->writeln('==========' . date('Y-m-d H:i:s') . " done ==========");
    }
}