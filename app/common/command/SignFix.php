<?php

namespace app\common\command;

use think\facade\Db;
use app\model\User;
use app\model\Project;
use app\model\UserSignin;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use Exception;
use think\facade\Cache;

class SignFix extends Command
{
    /**
        /10 * * * * cd /www/wwwroot/aaa && php think orderReward
     */
    protected function configure()
    {
        $this->setName('SignFix')->setDescription('');
    }

    protected function execute(Input $input, Output $output)
    {
        Db::name('user')->where('is_get_reward_continuous_signin', 0)->chunk(1000, function($users) {
            foreach ($users as $user) {
                Db::startTrans();
                try {

                    $totalSigninCount = UserSignin::where('user_id', $user['id'])->count();
                    if ($totalSigninCount + 1 >= 30) {
                        //发放连签30天奖励
                        User::where('id', $user['id'])->data(['is_get_reward_continuous_signin' => 1])->update();
                        User::changeInc($user['id'], 28000, 'specific_fupin_balance', 35, $user['id'], 3);
                        echo "{$user['id']}\n";
                    }
                    

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