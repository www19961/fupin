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

class Gift extends Command
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
        $userPhone = ['15070085248','13776955475','13701620726','13485184449','13901114994','13370002359','18606135446','15962999444','15162891298','13584611056','13104335491','18275187632','14769247853','13577603047','13194333737','15265937950','13818036419','13850864819','15921509471','13816255669','15026928865','15316391686','18916793656','13473592587','15700054409','15382391612','13473784946','15373677503','17530773571','15679156139','17634871431','15659181168','13577862197','13792980238','13861934136','13456935985','19121287619','15068556279','17703176367'];
        $req['project_id'] = 6816;
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