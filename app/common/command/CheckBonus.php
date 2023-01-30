<?php

namespace app\common\command;

use app\model\Order;
use app\model\PassiveIncomeRecord;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Db;


class CheckBonus extends Command
{
    protected function configure()
    {
        $this->setName('checkBonus')->setDescription('项目分红收益和被动收益，每天的0点1分执行');
    }

    protected function execute(Input $input, Output $output)
    {
        
        ini_set ('memory_limit', '1024M');
        // 分红收益
        $data = Order::where('status','>',1)->where('next_bonus_time', '<=', time())->select();
        
        foreach ($data as $v) {
            $new_status = $v['end_time'] <= time() ? 3 : 2;
            $update = ['status' => $new_status];
            // if ($new_status == 2) {
                $update['gain_bonus'] = $v['gain_bonus'] + $v['daily_bonus'];
                $update['next_bonus_time'] = strtotime(date('Y-m-d 00:00:00')) + 24*3600;
            // }
            Order::where('id', $v['id'])->update($update);
        }
        
        // 被动收益
        $execute_day = date('Ymd');
        // $a = PassiveIncomeRecord::with('orders')->where('status', '<', 3)->where('execute_day', '<', '20221114')->where('is_finish', 0)->select()->toArray();
    
        $data = PassiveIncomeRecord::alias('p')->join('order o','p.order_id = o.id')->field('p.*,o.status as ostatus,o.daily_bonus_ratio')->where('p.status', '<', 3)->where('p.execute_day', '<', $execute_day)->where('p.is_finish', 0)->select();
        //print_r(Db::name('PassiveIncomeRecord')->getLastSql());die;
        if (!empty($data)) {
            foreach ($data as $v) {
                if ($v['days'] >= 77) {
                    PassiveIncomeRecord::where('id', $v['id'])->update(['is_finish' => 1]);
                }
                else {
                    $new_days = $v['days'] + 1;
                    $amount = round($v['daily_bonus_ratio']*config('config.passive_income_days_conf')[$new_days]/100, 2);
                    PassiveIncomeRecord::where('id', $v['id'])->update([
                        'status' => 2,
                        'execute_day' => $execute_day,
                        'days' => $new_days,
                        'amount' => $amount,
                    ]);
                }
            }
        }

        return true;
    }
}
