<?php

namespace app\model;

use think\Model;
use think\facade\Db;

use Exception;
class Order extends Model
{
    public function getStatusTextAttr($value, $data)
    {
        $map = config('map.order')['status_map'];
        return $map[$data['status']] ?? '';
    }

    public function getPayMethodTextAttr($value, $data)
    {
        $map = config('map.order')['pay_method_map'];
        return $map[$data['pay_method']] ?? '';
    }

    public function getEquityStatusTextAttr($value, $data)
    {
        $map = config('map.order')['equity_status_map'];
        return $map[$data['equity_status']] ?? '';
    }

    public function getDigitalYuanStatusTextAttr($value, $data)
    {
        $map = config('map.order')['digital_yuan_status_map'];
        return $map[$data['digital_yuan_status']] ?? '';
    }

    public function getPayDateAttr($value, $data)
    {
        if (!empty($data['pay_time'])) {
            return date('Y-m-d H:i:s', $data['pay_time']);
        }
        return '';
    }

    public function getSaleDateAttr($value, $data)
    {
        if (!empty($data['sale_time'])) {
            return date('Y-m-d H:i:s', $data['sale_time']);
        }
        return '';
    }

    public function getEndDateAttr($value, $data)
    {
        if (!empty($data['end_time'])) {
            return date('Y-m-d H:i:s', $data['end_time']);
        }
        return '';
    }

    public function getExchangeEquityDateAttr($value, $data)
    {
        if (!empty($data['exchange_equity_time'])) {
            return date('Y-m-d H:i:s', $data['exchange_equity_time']);
        }
        return '';
    }

    public function getExchangeYuanDateAttr($value, $data)
    {
        if (!empty($data['exchange_yuan_time'])) {
            return date('Y-m-d H:i:s', $data['exchange_yuan_time']);
        }
        return '';
    }

    public function getPayStatusTextAttr($value, $data)
    {
        if ($data['status'] > 1) {
            if ($data['is_admin_confirm'] == 1) {
                return '成功,未通知';
            }
            return '成功,已通知';
        }
        return '未支付';
    }

    public function getEquityExchangePriceAttr($value)
    {
        $module = app('http')->getName();
        if ($module !== 'admin' && $value == 0) {
            $chart = KlineChart::where('date', date('Y-m-d'))->find();
            if (empty($chart)) {
                return 0;
            }
            $chart_data = json_decode($chart['chart_data'], true);
            $chart_data = array_column($chart_data, 'value', 'time');
            $time = (int)date('Hi');
            if (($time >= 930 && $time <= 1130) || ($time >= 1300 && $time <= 1500)) {
                $minute = date('i');
                $hour = date('H');
                $arr = str_split($minute);
                $start = $hour. ':' .$arr[0]. '0';
                if ($arr[0] == 5) {
                    $str1 = $hour + 1;
                    $str1 = sprintf("%02d", $str1);
                    $end = $str1. ':00';
                }
                else {
                    $str1 = $arr[0] + 1;
                    $end = $hour. ':' .$str1 . '0';
                }

                $diff = $chart_data[$start] - $chart_data[$end];
                return round($chart_data[$start] + $diff/10*($arr[1]), 4);
            }
            else {
                if ($time < 930) {
                    $ychart = KlineChart::where('date', date('Y-m-d', strtotime('-1 day')))->find();
                    if (empty($ychart)) {
                        return 0;
                    }
                    $ychart_data = json_decode($ychart['chart_data'], true);
                    $ychart_data = array_column($ychart_data, 'value', 'time');
                    return $ychart_data['15:00'];
                }
                elseif ($time > 1130 && $time < 1300) {
                    return $chart_data["11:30"];
                }
                else {
                    return $chart_data["15:00"];
                }
            }
        }

        return $value;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function getDailyBonusAttr($value, $data)
    {
        if (!empty($data['daily_bonus_ratio']) && !empty($data['buy_num'])) {
            return round($data['daily_bonus_ratio']*$data['buy_num'], 2);
        }

        return 0;
    }

    public function getTotalBonusAttr($value, $data)
    {
        if (!empty($data['daily_bonus_ratio']) && !empty($data['buy_num'])) {
            return round($data['daily_bonus_ratio']*$data['buy_num']*$data['period'], 2);
        }

        return 0;
    }
    
    public function getBuyAmountAttr($value, $data)
    {
        if ($data['pay_method'] != 5) {
            return round($data['single_amount']*$data['buy_num'], 2);
        }

        return 0;
    }

    public function getBuyIntegralAttr($value, $data)
    {
        if ($data['pay_method'] == 5) {
            return round($data['single_integral']*$data['buy_num'], 2);
        }

        return 0;
    }

    public function getEquityAttr($value, $data)
    {
        return $data['single_gift_equity'] * $data['buy_num'];
    }

    // public function getDigitalYuanAttr($value, $data)
    // {
    //     return $data['single_gift_digital_yuan']*$data['buy_num'];
    // }
    public function getDigitalYuanAttr($value, $data)
    {
        return $data['single_gift_digital_yuan'];
    }

    public function getWaitReceivePassiveIncomeAttr($value, $data)
    {
        // $s = PassiveIncomeRecord::where('order_id', $data['id'])->select();
        // $result = 0;
        // if(!empty($s)){
        //     foreach($s as $v){
        //       $result += $v['amount'];
        //     }
        // }
        // return round($result,2);
        // $num = Order::where('id',$data['id'])->value('buy_num');
        // $WaitReceivePassiveIncome = PassiveIncomeRecord::where('order_id', $data['id'])->where('status', 2)->value('amount');
        
        // return round($num*$WaitReceivePassiveIncome, 2);
        return round(PassiveIncomeRecord::where('order_id', $data['id'])->where('status', 2)->value('amount')*$data['buy_num'], 2);
    }

    // public function getTotalPassiveIncomeAttr($value, $data)
    // {
    //     return round(PassiveIncomeRecord::where('order_id', $data['id'])->value('amount'), 2);
    // }
    
    public function getTotalPassiveIncomeAttr($value, $data)
    {
        return round(PassiveIncomeRecord::where('order_id', $data['id'])->value('amount')*$data['buy_num'], 2);
    }

    public static function orderPayComplete($order_id, $project, $user_id)
    {
        $order = Order::where('id', $order_id)->find();

        if($project['project_group_id'] == 1) {
            $end_time = strtotime("+{$project['period']} day", strtotime(date('Y-m-d', strtotime($project['created_at']))));
            Order::where('id', $order['id'])->update([
                'status' => 2,
                'pay_time' => time(),
                'end_time' => $end_time,    //$next_bonus_time + $order['period']*24*3600,
                'gain_bonus' => 0,
                'next_bonus_time' => $end_time,
                //'equity_status' => 2,
                //'digital_yuan_status' => 2
            ]);
        } elseif ($project['project_group_id'] == 2) {
            User::changeInc($user_id,$project['withdrawal_limit'],'withdrawal_limit',22,$order['id'],5,'',0,1,'ZS');
            User::changeInc($user_id,$project['digital_red_package'],'digital_yuan_amount',23,$order['id'],3,'',0,1,'ZS');
            Order::where('id', $order['id'])->update([
                'status' => 4,
                'pay_time' => time(),
                'end_time' => time(),    //$next_bonus_time + $order['period']*24*3600,
                'gain_bonus' => 0,
                'next_bonus_time' => time(),
            ]);
        } elseif ($project['project_group_id'] == 3) {
            User::where('id', $user_id)->update(['can_open_digital' => 1]);
            User::changeInc($user_id,$project['single_amount'],'digital_yuan_amount',12,$order['id'],3, '激活账单返还本金','',1,'JH');
            Order::where('id', $order['id'])->update([
                'status' => 4,
                'pay_time' => time(),
                'end_time' => time(),    //$next_bonus_time + $order['period']*24*3600,
                'gain_bonus' => 0,
                'next_bonus_time' => time(),
            ]);
        }

            //购买产品和恢复资产用户激活
            if ($order['user']['is_active'] == 0 ) {
                User::where('id', $order['user_id'])->update(['is_active' => 1, 'active_time' => time()]);
                // 下级用户激活
                UserRelation::where('sub_user_id', $order['user_id'])->update(['is_active' => 1]);
            }

            User::where('id',$user_id)->inc('invest_amount',$order['single_amount'])->update();
            User::upLevel($user_id);
        return !0;


        // 更新订单
        //$dividend_cycle = explode(' ',$order['dividend_cycle']);
        $next_bonus_time = strtotime(date('Y-m-d 00:00:00', strtotime('+1 day')));
        //$end_time = strtotime(date('Y-m-d 00:00:00', strtotime('+'.($dividend_cycle[0] * $order['period']).' '.$dividend_cycle[1])));
        //$end_time = strtotime(date('Y-m-d 00:00:00', strtotime('+'.$order['period'].' day')));


        // 股权和数字人民
        // if ($order['single_gift_equity'] > 0) {
        //     EquityYuanRecord::create([
        //         'user_id' => $order['user_id'],
        //         'type' => 1,
        //         'status' => 2,
        //         'title' => $order['project_name'],
        //         'relation_type' => 1,
        //         'relation_id' => $order_id,
        //         'num' => round($order['equity']),
        //         'give_time' => time(),
        //         'equity_certificate_no' => 'ZX'.mt_rand(1000000000, 9999999999),
        //     ]);
        //     User::where('id', $order['user_id'])->inc('equity', $order['equity'])->inc('equity_amount', $order['equity'])->update();
        // }
        // if ($order['single_gift_digital_yuan'] > 0) {
        //     EquityYuanRecord::create([
        //         'user_id' => $order['user_id'],
        //         'type' => 2,
        //         'status' => 2,
        //         'title' => $order['project_name'],
        //         'relation_type' => 1,
        //         'relation_id' => $order_id,
        //         'num' => round($order['digital_yuan']),
        //         'give_time' => time(),
        //     ]);
        // }
        // 添加被动|补贴收益记录
          //$amount = bcmul($project['daily_bonus_ratio'],config('config.passive_income_days_conf')[$project['period']]/100,2);
        //   $amount = $order['single_gift_digital_yuan'];
        //   Db::startTrans();
        //   try {
        //       PassiveIncomeRecord::create([
        //               'user_id' => $order['user_id'],
        //               'order_id' => $order['id'],
        //               'execute_day' => date('Ymd'),
        //               'amount'=>$amount,
        //               'days'=>1,
        //               'is_finish'=>1,
        //               'status'=>3,
        //           ]); 
        //       $gain_bonus = bcadd($order['gain_bonus'],$amount,2);
        //       Order::where('id', $order['id'])->update(['gain_bonus'=>$gain_bonus]);
        //       User::changeInc($order['user_id'],$amount,'digital_yuan_amount',5,$order['id'],3);
        //       Db::commit();
        //   } catch (Exception $e) {
        //       Db::rollback();
        //       throw $e;
        //   }
        
            // PassiveIncomeRecord::create([
            //     'user_id' => $order['user_id'],
            //     'order_id' => $order['id'],
            //     'execute_day' => date('Ymd'),
            //     'amount'=>$amount,
            //     'days'=>$project['period'],
            //     'is_finish'=>1,
            //     'status'=>3,
            // ]); 
        //增加投资金额  注意积分兑换的不算投资金额
/*         if ($order['pay_method'] != 5) {
            User::where('id', $order['user_id'])->inc('invest_amount', $order['buy_amount'])->update();
        } */
        // 判断激活
/*         $up_user_id = User::where('id', $order['user_id'])->value('up_user_id');
        if (!empty($up_user_id)) {
            $upUser = User::where('id', $up_user_id)->find();
            $now_level = $upUser['level'];
        } */


        // 如果不是积分兑换才算直推奖励和团队奖励
        // if ($order['pay_method'] != 5 && !empty($up_user_id)) {
        //     // 给直属上级推荐奖
        //     $levelConfig = LevelConfig::where('level', $now_level)->find();
        //     if (!empty($levelConfig['direct_recommend_reward_ratio'])) {
        //         $reward = round($levelConfig['direct_recommend_reward_ratio']/100*$order['buy_amount'], 2);
        //         if($reward > 0){
        //             //User::changeBalance($up_user_id, $reward, 9, $order_id);
        //             User::changeInc($up_user_id,$reward,'team_bonus_balance',9,$order_id,4,'推荐奖励',0,2);
        //             //User::changeInc($up_user_id,$reward,'balance',9,$order_id,1,'推荐奖励',0,2);
        //         }
        //     }
        //     // 给上3级团队奖
        //     $relation = UserRelation::where('sub_user_id', $order['user_id'])->select();
        //     $map = [1 => 'first_team_reward_ratio', 2 => 'second_team_reward_ratio', 3 => 'third_team_reward_ratio'];
        //     //$map = [1 => 'first_team_reward_ratio', 2 => 'second_team_reward_ratio', ];
        //     foreach ($relation as $v) {
        //         $reward = round(dbconfig($map[$v['level']])/100*$order['buy_amount'], 2);
        //         if($reward > 0){
        //             //User::changeBalance($v['user_id'], $reward, 8, $order_id);
        //             //User::changeInc($up_user_id,$reward,'invite_bonus',8,$order_id,3,'推荐奖励');
        //             User::changeInc($v['user_id'],$reward,'team_bonus_balance',8,$order_id,4,'团队奖励',0,2);
        //             //User::changeInc($v['user_id'],$reward,'balance',8,$order_id,1,'团队奖励',0,2);
        //         }
        //     }
        // }

        // // 检测用户升级
        // if (in_array($order['pay_method'], [1,2,3,4,6])) {
        // //if (in_array($order['pay_method'], [2, 3, 4, 6])) {
        //     $user = User::where('id', $order['user_id'])->find();
        //     $new_level = LevelConfig::where('min_topup_amount', '<=', $user['invest_amount'])->order('min_topup_amount', 'desc')->value('level');

        //     if ($user['level'] < $new_level) {
        //         User::where('id', $user['id'])->update(['level' => $new_level]);
        //     }
        // }
        
        //赠送项目
        // if(!empty($project['give'])){
        //     $give = json_decode($project['give'],true);
        //     for ($i=0; $i < $order['buy_num']; $i++) { 
        //         foreach($give as $k => $v){
        //             $pro = Project::where('id',$k)->find();
        //             $zs = 'ZS'.mt_rand(1000000000, 9999999999);
        //             $data['up_user_id'] = $up_user_id;
        //             $data['user_id'] = $order['user_id'];
        //             $data['order_sn'] = 'ZS_'.$order['order_sn'];
        //             $data['status'] = 2;
        //             $data['buy_num'] = $v;
        //             $data['project_id'] = $k;
        //             $data['project_name'] = '赠送:'.$pro['name'];
        //             $data['single_amount'] = $pro['single_amount'];
        //             $data['single_integral'] = $pro['single_integral'];
        //             $data['cover_img'] = $pro['cover_img'];
        //             $data['total_num'] = $pro['total_num'];
        //             $data['daily_bonus_ratio'] = $pro['daily_bonus_ratio'] * $v;
        //             $data['sum_amount'] = $pro['sum_amount'] * $v;
        //             $data['period'] = $pro['period'];
        //             $data['single_gift_equity'] = $pro['single_gift_equity'] * $v;
        //             $data['single_gift_digital_yuan'] = $pro['single_gift_digital_yuan'] * $v;
        //             $data['pay_method'] = 6;
        //             $data['pay_time'] = time();
        //             //$data['end_time'] = $next_bonus_time + $pro['period']*24*3600;
        //             //$data['next_bonus_time'] = $next_bonus_time;
        //             $data['equity_status'] = 2;
        //             $data['digital_yuan_status'] = 2;
        //             $data['equity_certificate_no'] = $zs;
        //             $data['is_admin_confirm'] = 0;
        //             $data['is_gift'] = 1;
        //             $oid = Order::create($data)->getLastInsID();
        //             if($pro['class'] == 1){
        //                PassiveIncomeRecord::create([
        //                     'user_id' => $order['user_id'],
        //                     'order_id' => $oid,
        //                     'execute_day' => date('Ymd'),
        //                 ]); 
        //             }
        //             if($pro['class'] == 2){
        //                 SubsidyIncomeRecord::create([
        //                     'user_id' => $order['user_id'],
        //                     'order_id' => $oid,
        //                     'execute_day' => date('Ymd'),
        //                 ]); 
        //             }
        //             // 股权和数字人民
        //             if ($pro['single_gift_equity'] > 0) {
        //                 EquityYuanRecord::create([
        //                     'user_id' => $order['user_id'],
        //                     'type' => 1,
        //                     'status' => 2,
        //                     'title' => '赠送:'.$pro['name'],
        //                     'relation_type' => 4,
        //                     'relation_id' => $oid,
        //                     'num' => round($pro['single_gift_equity']) * $v,
        //                     'give_time' => time(),
        //                     'equity_certificate_no' => $zs,
        //                 ]);
        //                 User::where('id', $order['user_id'])->inc('equity', $order['equity'])->update();

        //             }
        //             if ($pro['single_gift_digital_yuan'] > 0) {
        //                 EquityYuanRecord::create([
        //                     'user_id' => $order['user_id'],
        //                     'type' => 2,
        //                     'status' => 2,
        //                     'title' => '赠送:'.$pro['name'],
        //                     'relation_type' => 4,
        //                     'relation_id' => $oid,
        //                     'num' => round($pro['single_gift_digital_yuan']) * $v,
        //                     'give_time' => time(),
        //                 ]);
        //             }
        //         }
        //     }

        // }

        return true;
    }

    public static function warpOrderComplete($order_id){
        try{
            $order = Order::where('id',$order_id)->find();
            $project = Project::where('id',$order['project_id'])->find();
            self::orderPayComplete($order['id'], $project, $order['user_id']);
        }catch(Exception $e){
            \think\facade\Log::error('warpOrderComplete:'.$e->getMessage().$e->getLine().$e->getFile());
            throw $e;
        }
    }
}
