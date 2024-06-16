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

class Gift2 extends Command
{
    /**
        /10 * * * * cd /www/wwwroot/aaa && php think orderReward
     */
    protected function configure()
    {
        $this->setName('Gift2')->setDescription('');
    }

    protected function execute(Input $input, Output $output)
    {
        die;
        User::where('created_at', '<', '2024-06-10 00:00:00')->order('id', 'asc')->chunk(500, function($users) use($output) {
            foreach ($users as $user) {
                User::changeInc($user['id'], 3000, 'specific_fupin_balance', 32, 0, 3);
            }
        });
        $output->writeln('==========' . date('Y-m-d H:i:s') . " done  ==========");

    }
}