<?php

namespace app\api\controller;

use app\model\Order;
use app\model\Payment;
use app\model\PaymentConfig;
use app\model\Project;
use app\model\PassiveIncomeRecord;
use app\model\User;
use app\model\UserSignin;
use Exception;
use think\facade\Db;

class OrderController extends AuthController
{
    public function placeOrder()
    {
        $req = $this->validate(request(), [
            'project_id' => 'require|number',
            'buy_num' => 'require|number|>:0',
            'pay_method' => 'require|number',
            'payment_config_id' => 'requireIf:pay_method,2|requireIf:pay_method,3|requireIf:pay_method,4|requireIf:pay_method,6|number',
            'pay_password|支付密码' => 'requireIf:pay_method,1|requireIf:pay_method,5',
        ]);
        $user = $this->user;

/*         if (empty($user['ic_number'])) {
            return out(null, 10001, '请先完成实名认证');
        } */
        if (empty($user['pay_password'])) {
            return out(null, 801, '请先设置支付密码');
        }
        if (!empty($req['pay_password']) && $user['pay_password'] !== sha1(md5($req['pay_password']))) {
            return out(null, 10001, '支付密码错误');
        }

        $project = Project::where('id', $req['project_id'])->find();
        if (!in_array($req['pay_method'], $project['support_pay_methods'])) {
            return out(null, 10001, '不支持该支付方式');
        }

        Db::startTrans();
        try {
            $user = User::where('id', $user['id'])->lock(true)->find();
            $project = Project::field('id project_id,name project_name,class,cover_img,single_amount,single_integral,total_num,daily_bonus_ratio,sum_amount,dividend_cycle,period,single_gift_equity,single_gift_digital_yuan,sham_buy_num,progress_switch,bonus_multiple,settlement_method')->where('id', $req['project_id'])->lock(true)->append(['all_total_buy_num'])->find()->toArray();

            $pay_amount = round($project['single_amount']*$req['buy_num'], 2);
            $pay_integral = 0;

            if ($req['pay_method'] == 1 && $pay_amount > ($user['topup_balance'] + $user['balance'])) {
                exit_out(null, 10002, '余额不足');
            }
            if ($req['pay_method'] == 5) {
                $pay_integral = $project['single_integral'] * $req['buy_num'];
                if ($pay_integral > $user['integral']) {
                    exit_out(null, 10003, '积分不足');
                }
            }

            if (in_array($req['pay_method'], [2,3,4,6])) {
                $type = $req['pay_method'] - 1;
                if ($req['pay_method'] == 6) {
                    $type = 4;
                }
                $paymentConf = PaymentConfig::userCanPayChannel($req['payment_config_id'], $type, $pay_amount);
            }

            if ($project['progress_switch'] == 1 && ($req['buy_num'] + $project['all_total_buy_num'] > $project['total_num'])) {
                exit_out(null, 10004, '超过了项目最大所需份数');
            }

            if (isset(config('map.order')['pay_method_map'][$req['pay_method']]) === false) {
                exit_out(null, 10005, '支付渠道不存在');
            }

            if (empty($req['pay_method'])) {
                exit_out(null, 10005, '支付渠道不存在');
            }
            if($req['project_id'] == 10){
                $one = Order::where('user_id',$user['id'])->where('project_id',10)->find();            	    
		        
		        $one != null ? exit_out(null, 10001, '该项目每人限购一份') : '';
	
            }
            if($req['project_id'] == 10 && $req['buy_num'] != 1){
                exit_out(null, 10001, '该项目每人限购一份');
            }
            $order_sn = build_order_sn($user['id']);

            // 创建订单
            if($project['class']==1){
                $project['sum_amount2'] = round($project['period']*$project['daily_bonus_ratio']*$req['buy_num']*$project['bonus_multiple'], 2);
            }else{
                $project['sum_amount'] = round($project['sum_amount']*$req['buy_num']*$project['bonus_multiple'], 2);
            }

            $project['user_id'] = $user['id'];
            $project['up_user_id'] = $user['up_user_id'];
            $project['order_sn'] = $order_sn;
            $project['buy_num'] = $req['buy_num'];
            $project['pay_method'] = $req['pay_method'];
            $project['equity_certificate_no'] = 'ZX'.mt_rand(1000000000, 9999999999);
            $project['daily_bonus_ratio'] = round($project['daily_bonus_ratio']*$project['bonus_multiple'], 2);
            //$project['monthly_bonus_ratio'] = round($project['monthly_bonus_ratio']*$project['bonus_multiple'], 2);

            $project['single_gift_equity'] = round($project['single_gift_equity']*$req['buy_num']*$project['bonus_multiple'], 2);
            $project['single_gift_digital_yuan'] = round($project['single_gift_digital_yuan']*$req['buy_num']*$project['bonus_multiple'], 2);
            $project['price'] = $pay_amount;

            $order = Order::create($project);

            if (in_array($req['pay_method'], [1, 5])) {
                // 扣余额或积分
                $change_balance = $req['pay_method'] == 1 ? (0 - $pay_amount) : (0 - $pay_integral);
                $log_type = $req['pay_method'] == 1 ? 1 : 2;    //2积分 5充值余额
                User::changeBalance($user['id'], $change_balance, 3, $order['id'], $log_type);
                
                // 累计总收益和赠送数字人民币
                 User::changeBalance($user['id'], $project['single_gift_digital_yuan'], 3, $order['id'], 3, '项目购买赠送数码港元');
                //User::where('id', $user['id'])->inc('poverty_subsidy_amount', $project['sum_amount2'])->inc('digital_yuan_amount', $project['single_gift_digital_yuan'])->update();
                User::where('id', $user['id'])->inc('poverty_subsidy_amount', $project['sum_amount2'])->update();
                // 订单支付完成
                Order::orderPayComplete($order['id']);

            }
            // 发起第三方支付
            if (in_array($req['pay_method'], [2,3,4,6])) {
                $card_info = '';
                if (!empty($paymentConf['card_info'])) {
                    $card_info = json_encode($paymentConf['card_info']);
                    if (empty($card_info)) {
                        $card_info = '';
                    }
                }
                // 创建支付记录
                Payment::create([
                    'user_id' => $user['id'],
                    'trade_sn' => $order_sn,
                    'pay_amount' => $pay_amount,
                    'order_id' => $order['id'],
                    'payment_config_id' => $paymentConf['id'],
                    'channel' => $paymentConf['channel'],
                    'mark' => $paymentConf['mark'],
                    'type' => $paymentConf['type'],
                    'card_info' => $card_info,
                ]);
                // 发起支付
                if ($paymentConf['channel'] == 1) {
                    $ret = Payment::requestPayment($order_sn, $paymentConf['mark'], $pay_amount);
                }
                elseif ($paymentConf['channel'] == 2) {
                    $ret = Payment::requestPayment2($order_sn, $paymentConf['mark'], $pay_amount);
                }
                elseif ($paymentConf['channel'] == 3) {
                    $ret = Payment::requestPayment3($order_sn, $paymentConf['mark'], $pay_amount);
                }
            }

            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            throw $e;
        }

        return out(['order_id' => $order['id'] ?? 0, 'trade_sn' => $trade_sn ?? '', 'type' => $ret['type'] ?? '', 'data' => $ret['data'] ?? '']);
    }
    
    // public function ssss(){
    //     $data = Order::alias('o')->join('mp_project p','o.project_id = p.id')->field('o.*,p.sum_amount as psum,p.single_gift_equity as pequity,p.single_gift_digital_yuan as pyuan,p.daily_bonus_ratio as pratio')->where('o.buy_num','>',1)->where('p.class',2)->select()->toArray();
    //     $a = '';
    //     if(empty($data)){
    //         return '没有执行数据';
    //     }else{
    //         foreach($data as $v){
    //             $up = [];
    //             if(($v['buy_num']*$v['psum']) > $v['sum_amount']){
    //                 $up['sum_amount'] =  $v['buy_num']*$v['psum'];
    //             }
    //             if(($v['buy_num']*$v['pequity']) > $v['single_gift_equity']){
    //                 $up['single_gift_equity'] =  $v['buy_num']*$v['pequity'];
    //             }
    //             if(($v['buy_num']*$v['pyuan']) > $v['single_gift_digital_yuan']){
    //                 $up['single_gift_digital_yuan'] =  $v['buy_num']*$v['pyuan'];
    //             }
    //             if(($v['buy_num']*$v['pratio']) > $v['daily_bonus_ratio']){
    //                 $up['daily_bonus_ratio'] =  $v['buy_num']*$v['pratio'];
    //             }
    //             Order::where('id',$v['id'])->update($up);
    //             $a .= 'id:'.$v['id'].'<br>'.'总补贴:'.$v['buy_num']*$v['psum'].'<br>'.'赠送股权:'.$v['buy_num']*$v['pequity'].'<br>'.'赠送期权:'. $v['buy_num']*$v['pyuan'].'<br>'.'分红:'.$v['buy_num']*$v['pratio'].'_______________<br>';
    //         }
    //     }
    //     return $a;
    // }

    public function submitPayVoucher()
    {
        $req = $this->validate(request(), [
            'pay_voucher_img_url|支付凭证' => 'require|url',
            'order_id' => 'require|number'
        ]);
        $user = $this->user;

        if (!Payment::where('order_id', $req['order_id'])->where('user_id', $user['id'])->count()) {
            return out(null, 10001, '订单不存在');
        }
        $remark = null!=request()->param('remark')?request()->param('remark'):'';
        $upData = [
            'pay_voucher_img_url' => $req['pay_voucher_img_url'],
            'agent_name'=>$remark,
        ];
        Payment::where('order_id', $req['order_id'])->where('user_id', $user['id'])->update($upData);

        return out();
    }

    public function orderList()
    {
        $req = $this->validate(request(), [
            'status' => 'number',
            'search_type' => 'number',
        ]);
        $user = $this->user;

        $builder = Order::where('user_id', $user['id'])->where('status', '>', 1)->where('sum_amount',0);
        if (!empty($req['status'])) {
            $builder->where('status', $req['status']);
        }
        if (!empty($req['search_type'])) {
            if ($req['search_type'] == 1) {
                $builder->where('single_gift_equity', '>', 0);
            }
            if ($req['search_type'] == 2) {
                $builder->where('single_gift_digital_yuan', '>', 0);
            }
        }
        $data = $builder->order('id', 'desc')->append(['buy_amount', 'total_bonus', 'equity', 'digital_yuan', 'wait_receive_passive_income', 'total_passive_income', 'pay_date', 'sale_date', 'end_date', 'exchange_equity_date', 'exchange_yuan_date'])->paginate(15,false,['query'=>request()->param()])->each(function($item, $key){
            $item['p_id'] = PassiveIncomeRecord::where('order_id',$item['id'])->order('id','desc')->value('id');
            $cre = intval((time()-strtotime($item['created_at'])) / 60 / 60 / 24);
            if($cre >= 77){
                $item['back_amount'] = 1;
            }else{
                $item['back_amount'] = 0;
            }
            return $item;
        });

        return out($data);
    }

    public function ordersList2()
    {
        $req = $this->validate(request(), [
            'status' => 'number',
            'search_type' => 'number',
        ]);
        $user = $this->user;

        $builder = Order::where('user_id', $user['id'])->where('status', '>', 1)->where('sum_amount','<>',0);
        if (!empty($req['status'])) {
            $builder->where('status', $req['status']);
        }
        if (!empty($req['search_type'])) {
            if ($req['search_type'] == 1) {
                $builder->where('single_gift_equity', '>', 0);
            }
            if ($req['search_type'] == 2) {
                $builder->where('single_gift_digital_yuan', '>', 0);
            }
        }
        $data = $builder->order('id', 'desc')->append(['buy_amount', 'total_bonus', 'equity', 'digital_yuan', 'wait_receive_passive_income', 'total_passive_income', 'pay_date', 'sale_date', 'end_date', 'exchange_equity_date', 'exchange_yuan_date'])->paginate(15,false,['query'=>request()->param()])->each(function($item, $key){
            $item['p_id'] = PassiveIncomeRecord::where('order_id',$item['id'])->order('id','desc')->value('id');
            $cre = intval((time()-strtotime($item['created_at'])) / 60 / 60 / 24);
            if($cre >= 77){
                $item['back_amount'] = 1;//显示反还本金
            }else{
                $item['back_amount'] = 0;//不显示反还本金
            }
            return $item;
        });

        return out($data);
    }

    public function ordersList(){
        $user = $this->user;
        $userModel = new User();
        $data = [];
        $data['profiting_bonus'] = $userModel->getProfitingBonusAttr(0,$user);
        $list = Order::where('user_id', $user['id'])->where('status', '>', 1)->field('id,cover_img,single_amount,buy_num,project_name,sum_amount,sum_amount2,order_sn,daily_bonus_ratio,dividend_cycle,period,created_at')->order('created_at','desc')->paginate(5)->each(function($item,$key){
            if($item['sum_amount']==0 && $item['sum_amount2']>0){
                //$item['sum_amount'] = bcmul($item['daily_bonus_ratio']*config('config.passive_income_days_conf')[$item['period']]/100,2);
                $item['sum_amount'] = $item['sum_amount2'];
            }
            $daily_bonus = bcmul($item['single_amount'],$item['daily_bonus_ratio']/100,2);
           
            $daily_bonus = bcmul($daily_bonus,$item['buy_num'],2);
            $item['price'] = bcmul($item['single_amount'],$item['buy_num'],2);
            if($item['dividend_cycle'] == '1 month'){
                $day_remark = '每月';
            }else{
                $day_remark = '每日';
            }
            
            $item['text'] = "单笔认购{$item['price']}元，{$day_remark}收益{$daily_bonus}元，永久性收益！";

            return $item;
        });
        $data['list'] = $list;
        return out($data);
    }

    public function investmentList(){
        $user = $this->user;
        $list = Order::where('user_id', $user['id'])->where('status', '>', 1)->where('is_gift',0)->field('id,project_id,single_amount,buy_num,project_name,sum_amount,sum_amount2,order_sn,daily_bonus_ratio,period,created_at')->order('created_at','desc')->paginate(5)->each(function($item,$key){
            $bonusMultiple = Project::where('id',$item['project_id'])->value('bonus_multiple');
            $item['bonus_multiple'] = $bonusMultiple;
            $item['price'] = bcmul($item['single_amount'],$item['buy_num'],2);
            $item['text'] = "{$item['price']}元投资{$bonusMultiple}倍{$item['project_name']}";
            

            return $item;
        });
        $data['list'] = $list;
        return out($data);
    }

    public function orderDetail()
    {
        $req = $this->validate(request(), [
            'order_id' => 'require|number',
        ]);
        $user = $this->user;

        $data = Order::where('id', $req['order_id'])->where('user_id', $user['id'])->append(['buy_amount', 'total_bonus', 'equity', 'digital_yuan', 'wait_receive_passive_income', 'total_passive_income', 'pay_date', 'sale_date', 'end_date', 'exchange_equity_date', 'exchange_yuan_date'])->find();
        $data['card_info'] = null;
        if (!empty($data)) {
            $payment = Payment::field('card_info')->where('order_id', $req['order_id'])->find();
            $data['card_info'] = $payment['card_info'];
        }

        return out($data);
    }

    public function saleOrder()
    {
        $req = $this->validate(request(), [
            'order_id' => 'require|number',
        ]);
        $user = $this->user;

        Db::startTrans();
        try {
            $order = Order::where('id', $req['order_id'])->where('user_id', $user['id'])->lock(true)->find();
            if (empty($order)) {
                exit_out(null, 10001, '订单不存在');
            }
            if ($order['status'] != 3) {
                exit_out(null, 10001, '订单状态异常，不能出售');
            }

            Order::where('id', $req['order_id'])->update(['status' => 4, 'sale_time' => time()]);

            User::changeBalance($user['id'], $order['gain_bonus'], 6, $req['order_id']);

            // 检查返回本金 签到累计满77天才会返还80%的本金，注意积分兑换的不返还本金
            if ($order['pay_method'] != 5) {
                $signin_num = UserSignin::where('user_id', $user['id'])->count();
                if ($signin_num >= 77) {
                //if ($signin_num >= 3) {
                    $change_amount = round($order['buy_amount']*0.8, 2);
                    User::changeBalance($user['id'], $change_amount, 12, $req['order_id']);
                }
            }

            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            throw $e;
        }

        return out();
    }
    
    public function takeDividend(){
        $user = $this->user;
  
        $isStatus = Order::where('user_id', $user['id'])->where('project_name','2022年年底分红')->find();

        if($isStatus){
            return out(null, 10001, '您已领取2022年年底分红');
        }else{
            $arr = User::where('id', $user['id'])->append(['equity'])->find()->toArray();

            $order_sn = build_order_sn($user['id']);
            // 创建分红订单
            $project['project_name'] = '2022年年底分红';
            $project['user_id'] = $user['id'];
            $project['up_user_id'] = $user['up_user_id'];
            $project['order_sn'] = $order_sn;
            $project['buy_num'] = 0;
            $project['pay_method'] = 0;//gain_bonus
            $project['gain_bonus'] = $arr['equity']*10;//
            $project['status'] = 2;//
            $project['equity_certificate_no'] = 'ZX'.mt_rand(1000000000, 9999999999);
            $project['daily_bonus_ratio'] = 0;
            $project['sum_amount'] = 2400;
            $project['single_gift_equity'] = 0;
            $project['single_gift_digital_yuan'] = 0;
            Order::create($project);
        }
        return out();
    }

    public function takeDividendstate(){
        $user = $this->user;
        $isStatus = Order::where('user_id', $user['id'])->where('project_name','2022年年底分红')->find();

        if($isStatus){
            $data['take_status']=0;
        }else{
            $arr = User::where('id', $user['id'])->append(['equity'])->find()->toArray();
            if($arr['equity']<1){
                $data['take_status']=0;
            }else{
                $data['take_status']=1;
            }
        }
        return out($data);
    }
    
}
