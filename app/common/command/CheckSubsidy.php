<?php

namespace app\common\command;

use app\model\Order;
use app\model\PassiveIncomeRecord;
use app\model\User;
use app\model\UserRelation;
use app\model\UserSignin;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Db;
use app\model\Capital;
use Exception;
use think\facade\Log;

class CheckSubsidy extends Command
{
    protected function configure()
    {
        $this->setName('checkSubsidy')->setDescription('二期项目数字建设补贴，每天的0点2分执行');
    }

    protected function execute(Input $input, Output $output)
    {   
        $this->settle();
        $this->rank();
        //$this->all();
        //$this->fixSecondBonus();
        return true;
    }

    public function settle(){
        $cur_time = strtotime(date('Y-m-d 00:00:00'));
        $time = strtotime(date('Y-m-d 00:00:00'));
        $data = Order::whereIn('project_group_id',[1])->where('status',2)
        //->where('end_time', '<=', $cur_time)
         ->chunk(100, function($list) {
            foreach ($list as $item) {
                $this->bonus($item);
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

    public function fixSecondBonus(){
        $yesterday = date("Y-m-d",strtotime("-1 day"));
        $day = date("d",strtotime($yesterday));
        $month = date("m",strtotime($yesterday));
        $sql="select *  from mp_order where project_group_id = 2 and status=4 and created_at BETWEEN '2023-11-1 00:00:00' and '2023-11-09 :23:59:59' and id not in(
            select relation_id from mp_user_balance_log where remark='二期项目每月分红'
            )";
        $data = Db::query($sql);
        foreach($data as $item){

            echo "正在处理订单{$item['id']}\n";
            $time = time();
            $nowMonth = intval(date("m",$time));
             $endMonth = intval(date("m",$item['end_time']));
             $executeDay = date('Ym').date("d",strtotime($item['created_at']));
               if($nowMonth>$endMonth){
                    $passiveIncome = PassiveIncomeRecord::where('order_id',$item['id'])->where('user_id',$item['user_id'])->where('execute_day',$executeDay)->where('type',2)->find();
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
                                'execute_day' => $executeDay,
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
              // break;
            }
            

    }


    protected function all(){
        //$this->widthdrawAudit();
        $arr = [
            71267,
            71268,
            71269,
            71270,
            71271,
            71272,
            71273,
            71274,
            71275,
            71276,
        ];
        $data = Order::where('status',2)->whereIn('id',$arr)
        ->chunk(100, function($list) {
            foreach ($list as $item) {
                $this->bonus4($item);
            }
        });
        //echo Order::getLastSql()."\n";
    }

    public function widthdrawAudit(){
        $ret = Capital::where('status',1)->where('type',2)->whereIn('log_type',[3,6])->where('created_at','<=','2023-12-10 23:59:59')->update(['status'=>2]);
        //echo Capital::getLastSql()."\n";
        echo "updated {$ret} \n";
    }

    public function bonus4($order){
        Db::startTrans();
        try{
            echo "正在处理订单{$order['id']}\n";
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

/*     protected function bonus($order){
        Db::startTrans();
        try{
            echo "正在处理订单{$order['id']}\n";
            $alreadyAmount = PassiveIncomeRecord::where('order_id',$order['id'])->sum('amount');
            $digitalYuan = bcmul($order['single_gift_digital_yuan'],$order['period']) - $alreadyAmount;
            User::changeInc($order['user_id'],$order['sum_amount'],'income_balance',6,$order['id'],6);
            $gainBonus = bcadd($order['gain_bonus'],$digitalYuan,2);
           
            if($digitalYuan>0){
                Order::where('id',$order->id)->update(['status'=>4,'gain_bonus'=>$gainBonus]);
                User::changeInc($order['user_id'],$digitalYuan,'digital_yuan_amount',5,$order['id'],3,'结算');
                PassiveIncomeRecord::create([
                    'project_group_id'=>$order['project_group_id'],
                    'user_id' => $order['user_id'],
                    'order_id' => $order['id'],
                    'execute_day' => date('Ymd'),
                    'amount'=>$digitalYuan,
                    'days'=>0,
                    'is_finish'=>1,
                    'status'=>3,
                    'type'=>1,
                ]); 
            }else{
                Order::where('id',$order->id)->update(['status'=>4]);
            }
            Db::Commit();
        }catch(\Exception $e){
            Db::rollback();
            Log::error('分红收益异常：'.$order['id'].' '.$e->getMessage(),$e);
            throw $e;
        }
     }*/

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

    public function test2(){
        $data = [
            '5'=>0,
            '10'=>0,
        ];
        $num = 0;
        User::whereRaw(' id  not in (select user_id from mp_user_relation) ')->chunk(1000,function($list) use (&$data,&$num){
            foreach($list as $key=>$item){
                $num++;
                echo "正在处理第".($num)." 个用户{$key}\n";
               $days =  $this->lianxuSignIn($item);

                if($days>=10){
                    $data['10']++;
                }else if($days>=5){
                    $data['5']++;
                }
            }
        });
        print_r($data);
    }

    public function lianxuSignIn($item){
        $signIns = UserSignin::where('user_id',$item['id'])->order('signin_date asc')->select();
        $date1 = "";
        $signMax = 0;
        $signInDays = 0;
        foreach($signIns as $signIn){
            if($signInDays >= 10){
                $signMax = $signInDays;
                break;
            }
            if($date1!=""){
                $targetDate = date('Y-m-d',strtotime("+1 day",strtotime($date1)));
                if($targetDate == $signIn['signin_date']){
                    $signInDays++;
                }else{
                    if($signInDays>$signMax){
                        $signMax = $signInDays;
                    }
                    $signInDays=0;
                }
                $date1 = $signIn['signin_date'];
            }else{
                $date1=$signIn['signin_date'];
            }

        }
        if($signInDays>$signMax){
            $signMax = $signInDays;
        }
        return $signMax+1;
    }
}
