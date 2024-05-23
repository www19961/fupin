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
        Db::name('user')->where('is_get_reward_continuous_signin', 1)->chunk(1000, function($users) {
            foreach ($users as $user) {
                Db::startTrans();
                try {

                    $totalSigninCount = UserSignin::where('user_id', $user['id'])->count();
                    // if ($totalSigninCount + 1 >= 30) {
                    //     //发放连签30天奖励
                    //     User::where('id', $user['id'])->data(['is_get_reward_continuous_signin' => 1])->update();
                    //     User::changeInc($user['id'], 28000, 'specific_fupin_balance', 35, $user['id'], 3);
                    //     echo "{$user['id']}\n";
                    // }
                    if ($totalSigninCount == 29) {
                        if ($user['specific_fupin_balance'] >= 28000) {
                            User::where('id', $user['id'])->data(['is_get_reward_continuous_signin' => 0])->update();
                            User::where('id', $user['id'])->inc('specific_fupin_balance',-28000)->update();
                            $log = UserBalanceLog::where('user_id', $user['id'])->where('type', '35')->where('created_at', '>', '2024-05-23 11:00:00')->find();
                            UserBalanceLog::where('id', $log['id'])->delete();
                            echo "{$user['id']}|";
                        } else {
                            echo "\n{$user['id']}";
                        }

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