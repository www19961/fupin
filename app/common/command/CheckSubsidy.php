<?php

namespace app\common\command;

use app\model\Order;
use app\model\User;
use app\model\UserRelation;
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
        $this->test();
        //数字建设补贴
/*         $execute_day = date('Ymd');
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
        } */

        return true;
    }

    protected function test(){
        $data = User::field('id,realname,phone')->whereIn('invite_code',['4421900','4263164','7318805','3631948','8762543','6526978'])->select();
        $countData = [];
        foreach($data as $user){
            $countData[$user['realname']]['have']=0;
            $countData[$user['realname']]['no']=0;
            $countData[$user['realname']]['id']=$user['id'];
            $countData[$user['realname']]['phone']=$user['phone'];
            $sub = UserRelation::where('user_id',$user['id'])->where('level',1)->select();
            foreach($sub as $item){
                $count = UserRelation::where('user_id',$item['sub_user_id'])->count();
                if($count<=0){
                    $countData[$user['realname']]['no']++;
                    echo $user['realname']."的下级".$item['sub_user_id']."没有下级了\n";
                }else{
                    $countData[$user['realname']]['have']++;
                }
            }
        }
        //print_r($countData);
        foreach($countData as $k=>$v){
            echo "{$v['id']} {$v['phone']} $k {$v['no']} {$v['have']}\n";
        }
    }
}
