<?php

namespace app\common\command;

use think\facade\Db;
use app\model\User;
use app\model\PassiveIncomeRecord;
use app\model\Order;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use Exception;
use think\facade\Cache;

class OrderRewardDaily extends Command
{
    /**
        /10 * * * * cd /www/wwwroot/aaa && php think OrderRewardDaily
     */
    protected function configure()
    {
        $this->setName('OrderRewardDaily')->setDescription('type5 生活保障每日收益');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln('==========' . date('Y-m-d H:i:s') . " start ==========");
        Db::name('order')->where('type', 5)->order('id', 'asc')->chunk(500, function($orders) use($output) {
            foreach ($orders as $order) {
                Db::startTrans();
                try {

                    if (date('Ymd') == date('Ymd', strtotime($order['created_at']))) {
                        return;
                    }

                    $passiveIncome = PassiveIncomeRecord::where('order_id',$order['id'])->where('user_id',$order['user_id'])->where('execute_day',date('Ymd'))->where('type', $order['type'])->find();
                    if(!empty($passiveIncome)){
                        return;
                    }

                    //每日收益
                    $reward = bcmul($order['price'], bcdiv($order['daily_rate'], 100, 3));

                    Db::startTrans();
                    try {
                        PassiveIncomeRecord::create([
                                'project_group_id' => 0,
                                'user_id' => $order['user_id'],
                                'order_id' => $order['id'],
                                'execute_day' => date('Ymd'),
                                'amount' => $reward,
                                'days' => 0,
                                'is_finish' => 0,
                                'status' => 3,
                                'type' => $order['type'],
                            ]); 

                        User::changeInc($order['user_id'], $reward, 'life_balance', 43, $order['id']);

                        Db::commit();
                        $output->writeln("【{$order['id']}】日收益发放完成");
                    } catch (Exception $e) {
                        Db::rollback();
                        throw $e;
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