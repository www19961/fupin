<?php

namespace app\common\command;

use think\facade\Db;
use app\model\User;
use app\model\UserBalanceLog;
use app\model\UserSignin;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use Exception;
use think\facade\Cache;

class ShareFupin extends Command
{
    /**
        /10 * * * * cd /www/wwwroot/aaa && php think orderReward
     */
    protected function configure()
    {
        $this->setName('share_fupin')->setDescription('送800扶贫金');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln('==========' . date('Y-m-d H:i:s') . " start ==========");
        $count = Db::name('user')->count();
        $index = 0;
        Db::name('user')->order('id', 'asc')->chunk(1000, function($users) use($output, &$index, $count) {
            foreach ($users as $user) {
                $index += 1;

                User::changeInc($user['id'], 800, 'specific_fupin_balance', 37, 999, 3);

                $rate = bcdiv($index, $count, 2) * 100;
                $output->writeln("id:【{$user['id']}】【".$rate."%】");
            }
        });
        $output->writeln('==========' . date('Y-m-d H:i:s') . " done  ==========");
    }
}