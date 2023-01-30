<?php

namespace app\common\command;

use app\model\Order;
use app\model\SubsidyIncomeRecord;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class CheckSubsidy extends Command
{
    protected function configure()
    {
        $this->setName('checkSubsidy')->setDescription('二期项目数字建设补贴，每天的0点2分执行');
    }

    protected function execute(Input $input, Output $output)
    {   
        //数字建设补贴
        $execute_day = date('Ymd');
        $data = SubsidyIncomeRecord::alias('s')->join('order o','s.order_id = o.id')->field('s.*,o.status as ostatus,o.sum_amount,o.period')->where('s.status','<',3)->where('s.execute_day', '<', $execute_day)->where('s.is_finish', 0)->select();
        if(!empty($data)){
            foreach($data as $v){
                if($v['ostatus'] != 2){
                    SubsidyIncomeRecord::where('id',$v['id'])->update(['is_finish' => 1]);
                }else{
                   $new_days = $v['days'] + 1;
                   $amount = round($v['sum_amount'] / $v['period'],2);
                   SubsidyIncomeRecord::where('id',$v['id'])->update([
                        'status' => 2,
                        'execute_day' => $execute_day,
                        'days' => $new_days,
                        'amount' => $v['amount']+$amount,
                   ]);
                }
            }
        }

        return true;
    }
}
