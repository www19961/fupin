<?php

namespace app\common\command;

use app\model\AssetOrder;
use app\model\Capital;
use app\model\EnsureOrder;
use app\model\Order;
use app\model\PassiveIncomeRecord;
use app\model\User;
use app\model\UserRelation;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Db;

use Exception;
use think\facade\Log;

class CheckBonus extends Command
{
    protected function configure()
    {
        $this->setName('checkBonus')->setDescription('项目分红收益和被动收益，每天的0点1分执行');
    }

    public function execute(Input $input, Output $output)
    {
        $cur_time = strtotime(date('Y-m-d 00:00:00'));
        $time = strtotime(date('Y-m-d 00:00:00'));
        $data = Order::whereIn('project_group_id',[1])->where('status',2)->where('end_time', '<=', $cur_time)
         ->chunk(100, function($list) {
            foreach ($list as $item) {
                $this->bonus($item);
            }
        });

        //资产恢复
        $data = AssetOrder::where('status',2)->where('next_return_time', '<=', $time)
        ->chunk(100, function($list) {
           foreach ($list as $item) {
               $this->bonus_asset_return($item);
           }
       });

       $data = AssetOrder::where('reward_status',0)->where('next_reward_time', '<=', time())
       ->chunk(100, function($list) {
          foreach ($list as $item) {
              $this->bonus_asset_reward($item);
          }
      });

       //共富保障
       $data = EnsureOrder::where('status',2)->where('next_return_time', '<=', $time)
       ->chunk(100, function($list) {
          foreach ($list as $item) {
              $this->bonus_ensure_return($item);
          }
      });

      $data = EnsureOrder::where('reward_status',0)->where('next_reward_time', '<=', $time)
      ->chunk(100, function($list) {
         foreach ($list as $item) {
             $this->bonus_ensure_reward($item);
         }
     });
    }

    // public function execute(Input $input, Output $output)
    // {   


    //     $cur_time = strtotime(date('Y-m-d 00:00:00'));
    //      $data2 = Order::whereIn('project_group_id',[1,2,3])->where('status',2)->where('next_bonus_time', '<=', $cur_time)
    //     ->chunk(100, function($list) {
    //         foreach ($list as $item) {
    //             $this->digiYuan($item);
    //         }
    //     }); 

    //     // 分红收益
    //     $data = Order::whereIn('project_group_id',[1,2,3])->where('status',2)->where('end_time', '<=', $cur_time)
    //     ->chunk(100, function($list) {
    //         foreach ($list as $item) {
    //             $this->bonus($item);
    //         }
    //     });

    //     //456期项目
    //     $data = Order::whereIn('project_group_id',[4,6])->where('status',2)->where('end_time', '<=', $cur_time)
    //     ->chunk(100, function($list) {
    //         foreach ($list as $item) {
    //             $this->bonus4($item);
    //         }
    //     });
    //     //二期新项目结束之后每月分红
    //     $this->secondBonus();
    //     //$this->widthdrawAudit();
    //     return true;
    // }

    public function rank(){
        $data = UserRelation::rankList();
        foreach($data as $item){
            Db::startTrans();
            try{
                User::changeInc($item['user_id'],$item['reward'],'team_bonus_balance',8,0,2,'共富功臣奖励');
                Db::commit();
            }catch(Exception $e){
                Db::rollback();
                Log::error('团队排名奖励异常：'.$e->getMessage(),$e);
                throw $e;
            }
        }

    }

    public function widthdrawAudit(){
        Capital::where('status',1)->where('type',2)->whereIn('log_type',[3,6])->where('end_time','<=',time())->update(['status'=>2]);
    }

    public function secondBonus(){
        $yesterday = date("Y-m-d",strtotime("-1 day"));
        $day = date("d",strtotime($yesterday));
        $month = date("m",strtotime($yesterday));
        Order::where('status',4)->where('project_group_id',2)->whereRaw("DAYOFMONTH(created_at)=$day")->chunk(100, function($list) use ($month) {
            $time = time();
            $nowMonth = intval(date("m",$time));
            
            foreach ($list as $item) {
                $endMonth = intval(date("m",$item['end_time']));
               if($nowMonth>$endMonth){
                    $passiveIncome = PassiveIncomeRecord::where('order_id',$item['id'])->where('user_id',$item['user_id'])->where('execute_day',date('Ymd'))->where('type',2)->find();
                    if(!empty($passiveIncome)){
                        //已经分红
                        return;
                    }
                    $passiveIncome = PassiveIncomeRecord::where('order_id',$item['id'])->where('user_id',$item['user_id'])->order('execute_day','desc')->where('type',2)->find();
                    if(!$passiveIncome){
                        $day=0;
                    }else{
                        $day=$passiveIncome['days'];
                    }
                    $day+=1;
                    Db::startTrans();
                    try {
                        $amount = $item['sum_amount'];
                        PassiveIncomeRecord::create([
                                'project_group_id'=>$item['project_group_id'],
                                'user_id' => $item['user_id'],
                                'order_id' => $item['id'],
                                'execute_day' => date('Ymd'),
                                'amount'=>$amount,
                                'days'=>$day,
                                'is_finish'=>1,
                                'status'=>3,
                                'type'=>2,
                            ]); 
                        $gain_bonus = bcadd($item['gain_bonus'],$amount,2);
                        Order::where('id', $item['id'])->update(['gain_bonus'=>$gain_bonus]);
                        User::changeInc($item['user_id'],$amount,'income_balance',6,$item['id'],6,'二期项目每月分红');
                        Db::commit();
                    } catch (Exception $e) {
                        Log::error('二期项目每月分红异常：'.$e->getMessage(),$e);
                        Db::rollback();
                        throw $e;
                    }
                    //return true;
               }
            }
        });
    }

    public function bonus($order){
        Db::startTrans();
        try{
            User::changeInc($order['user_id'],$order['sum_amount'],'digital_yuan_amount',6,$order['id'],3);
            User::changeInc($order['user_id'],$order['single_amount'],'digital_yuan_amount',12,$order['id'],3);
            //User::changeInc($order['user_id'],$order['single_gift_digital_yuan'],'digital_yuan_amount',5,$order['id'],3);
            Order::where('id',$order->id)->update(['status'=>4]);
/*             if($order['project_group_id']==2){
                
            } */
            Db::Commit();
        }catch(Exception $e){
            Db::rollback();
            
            Log::error('分红收益异常：'.$e->getMessage(),$e);
            throw $e;
        }
    }

    public function bonus_asset_return($order)
    {
        Db::startTrans();
        try{
            $amount = config('map.asset_recovery')[$order['type']]['amount'];
            User::changeInc($order['user_id'],$amount,'digital_yuan_amount',12,$order['id'],3);
            AssetOrder::where('id',$order->id)->update(['status'=>4]);
            Db::Commit();
        }catch(Exception $e){
            Db::rollback();
            
            Log::error('资产恢复退回保证金：'.$e->getMessage(),$e);
            throw $e;
        }
    }

    public function bonus_asset_reward($order)
    {
        Db::startTrans();
        try{
            // User::changeInc($order['user_id'],$order['balance'],'digital_yuan_amount',27,$order['id'],3);
            User::changeInc($order['user_id'],$order['digital_yuan_amount'],'digital_yuan_amount',27,$order['id'],3);
            // User::changeInc($order['user_id'],$order['poverty_subsidy_amount'],'poverty_subsidy_amount',27,$order['id'],3);
            AssetOrder::where('id',$order->id)->update(['reward_status'=>1]);
            Db::Commit();
        }catch(Exception $e){
            Db::rollback();
            
            Log::error('资产恢复异常：'.$e->getMessage(),$e);
            throw $e;
        }
    }

    public function bonus_ensure_return($order)
    {
        Db::startTrans();
        try{
            User::changeInc($order['user_id'],$order['amount'],'digital_yuan_amount',12,$order['id'],3);
            EnsureOrder::where('id',$order->id)->update(['status'=>4]);
            Db::Commit();
        }catch(Exception $e){
            Db::rollback();
            
            Log::error('共富保障退回保证金：'.$e->getMessage(),$e);
            throw $e;
        }
    }

    public function bonus_ensure_reward($order)
    {
        Db::startTrans();
        try{
            User::changeInc($order['user_id'],$order['receive_amount'],'digital_yuan_amount',28,$order['id'],3);
            EnsureOrder::where('id',$order->id)->update(['reward_status'=>1]);
            Db::Commit();
        }catch(Exception $e){
            Db::rollback();
            
            Log::error('共富保障异常：'.$e->getMessage(),$e);
            throw $e;
        }
    }

//     public function bonus($order){
//         Db::startTrans();
//         try{
//             User::changeInc($order['user_id'],$order['sum_amount'],'income_balance',6,$order['id'],6);
//             //User::changeInc($order['user_id'],$order['single_gift_digital_yuan'],'digital_yuan_amount',5,$order['id'],3);
//             Order::where('id',$order->id)->update(['status'=>4]);
// /*             if($order['project_group_id']==2){
                
//             } */
//             Db::Commit();
//         }catch(Exception $e){
//             Db::rollback();
            
//             Log::error('分红收益异常：'.$e->getMessage(),$e);
//             throw $e;
//         }
//     }

    public function bonus4($order){
        Db::startTrans();
        try{
            //$digitalYuan = bcmul($order['single_gift_digital_yuan'],$order['period'],2);
            $digitalYuan = $order['single_gift_digital_yuan'];
            User::changeInc($order['user_id'],$order['sum_amount'],'income_balance',6,$order['id'],6);
            User::changeInc($order['user_id'],$digitalYuan,'digital_yuan_amount',5,$order['id'],3,'国务院津贴');

            //User::changeInc($order['user_id'],$order['single_gift_digital_yuan'],'digital_yuan_amount',5,$order['id'],3);
            Order::where('id',$order->id)->update(['status'=>4]);

            Db::Commit();
        }catch(Exception $e){
            Db::rollback();
            
            Log::error('分红收益异常：'.$e->getMessage(),$e);
            throw $e;
        }


    }

    protected function digiYuan($order){
        $cur_time = strtotime(date('Y-m-d 00:00:00'));
        $user = User::where('id',$order->user_id)->where('status',1)->find();
        if(is_null($user)) {
            //用户不存在,禁用
            return;
        }
        
/*         if($order->end_time < $cur_time){
            //结束分红
            Order::where('id',$order->id)->update(['status'=>4]);
            return;
        } */
        $day=0;
        $passiveIncome = PassiveIncomeRecord::where('order_id',$order['id'])->where('user_id',$order['user_id'])->where('execute_day',date('Ymd'))->find();
        if(!empty($passiveIncome)){
            //已经分红

            return;
        }
        $passiveIncome = PassiveIncomeRecord::where('order_id',$order['id'])->where('user_id',$order['user_id'])->order('execute_day','desc')->find();
        if(!$passiveIncome){
            $day=0;
        }else if($passiveIncome['days']>=$order['period']){
            //已经分红完毕
            return;
        }else{
            $day=$passiveIncome['days'];
        }
        $day+=1;
        $amount = $order['single_gift_digital_yuan'];
        Db::startTrans();
        try {
            PassiveIncomeRecord::create([
                    'project_group_id'=>$order['project_group_id'],
                    'user_id' => $order['user_id'],
                    'order_id' => $order['id'],
                    'execute_day' => date('Ymd'),
                    'amount'=>$amount,
                    'days'=>$day,
                    'is_finish'=>1,
                    'status'=>3,
                    'type'=>1,
                ]); 
            $next_bonus_time = strtotime('+1 day', strtotime(date('Y-m-d H:i:s',$order['next_bonus_time'])));
            $gain_bonus = bcadd($order['gain_bonus'],$amount,2);
            Order::where('id', $order['id'])->update(['next_bonus_time'=>$next_bonus_time,'gain_bonus'=>$gain_bonus]);
            User::changeInc($order['user_id'],$amount,'digital_yuan_amount',5,$order['id'],3,'每日国务院津贴');
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            throw $e;
        }
        return true;
    }
    
    protected function fixedMill($order)
    {
        $cur_time = strtotime(date('Y-m-d 00:00:00'));
        $user = User::where('id',$order->user_id)->where('status',1)->find();
        if(is_null($user)) {
            //用户不存在,禁用
            return;
        }
        
        if($order->end_time < $cur_time){
            //结束分红
            Order::where('id',$order->id)->update(['status'=>4]);
            return;
        }
        $max_day = PassiveIncomeRecord::where('order_id',$order['id'])->max('days');
        if($max_day >= 0){
            $max_day = $max_day + 1;
        }else{
            $max_day = 1;
        }
        $amount = bcmul($order['single_amount'],$order['daily_bonus_ratio']/100,2);
        $amount = bcmul($amount, $order['buy_num'],2);
        Db::startTrans();
        try {
            PassiveIncomeRecord::create([
                    'user_id' => $order['user_id'],
                    'order_id' => $order['id'],
                    'execute_day' => date('Ymd'),
                    'amount'=>$amount,
                    'days'=>$max_day,
                    'is_finish'=>1,
                    'status'=>3,
                ]); 
            if(empty($order['dividend_cycle'])){ 
                $dividend_cycle = '1 day'; 
            }else{
                $dividend_cycle = $order['dividend_cycle']; 
            }
            if(empty($order['next_bonus_time']) || $order['next_bonus_time'] == 0){ $order['next_bonus_time'] = $cur_time; }
            $next_bonus_time = strtotime('+'.$dividend_cycle, strtotime($order['next_bonus_time']));
            $gain_bonus = bcadd($order['gain_bonus'],$amount,2);
            Order::where('id', $order['id'])->update(['next_bonus_time'=>$next_bonus_time,'gain_bonus'=>$gain_bonus]);
            if($order->period <= $max_day){
                //结束分红
                Order::where('id',$order->id)->update(['status'=>4]);
            }
            if($order['settlement_method'] == 1)
                User::changeBalance($order['user_id'],$amount,6,$order['id'],3);
            else
                User::changeBalance($order['user_id'],$amount,6,$order['id']);
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            throw $e;
        }
        return true;
        

    }
}
