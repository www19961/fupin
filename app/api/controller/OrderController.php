<?php

namespace app\api\controller;

use app\model\AssetOrder;
use app\model\EnsureOrder;
use app\model\Order;
use app\model\Payment;
use app\model\PaymentConfig;
use app\model\Project;
use app\model\PassiveIncomeRecord;
use app\model\User;
use app\model\UserRelation;
use app\model\UserSignin;
use think\facade\Cache;
use Exception;
use think\facade\Db;

class OrderController extends AuthController
{

    public function placeOrder()
    {
        $req = $this->validate(request(), [
            'project_id' => 'require|number',
            'price' => 'require|number',
        ]);

        $user = $this->user;

        $clickRepeatName = 'placeOrder-' . $user->id;
        if (Cache::get($clickRepeatName)) {
            return out(null, 10001, '操作频繁，请稍后再试');
        }
        Cache::set($clickRepeatName, 1, 5);

        // if (empty($user['pay_password'])) {
        //     return out(null, 801, '请先设置支付密码');
        // }
        // if (!empty($req['pay_password']) && $user['pay_password'] !== sha1(md5($req['pay_password']))) {
        //     return out(null, 10001, '支付密码错误');
        // }

        $project = Project::where('id', $req['project_id'])->where('price', $req['price'])->where('status',1)->find();
        if(!$project){
            return out(null, 10001, '项目不存在');
        }

        Db::startTrans();
        try {
            $user = User::where('id', $user['id'])->lock(true)->find();

            $pay_amount = $project['price'];

            if ($pay_amount > $user['balance']) {
                exit_out(null, 10090, '余额不足');
            }

            $order_sn = 'QG'.build_order_sn($user['id']);

            $project['user_id'] = $user['id'];
            $project['up_user_id'] = $user['up_user_id'];
            $project['order_sn'] = $order_sn;
            $project['buy_num'] = 1;
            $project['price'] = $pay_amount;
            $project['buy_amount'] = $pay_amount;
            $project['start_time'] = time();
            $project['end_time'] = time() + 86400 * $project['days'];

            $order = Order::create($project);

            // 扣余额
            User::changeInc($user['id'],-$pay_amount,'balance',3,$order['id'],1,$project['project_id'],0,1);

            $userRelation = UserRelation::where('sub_user_id', $user['id'])->select();
            foreach ($userRelation as $value) {
                $rate = User::$rewardRate['level'];
                $reward = bcmul($pay_amount, $rate, 2);
                User::changeInc($value['user_id'],$reward,'balance',8,$order['id'],1,$order['id'],0,1);
            }

            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            throw $e;
        }

        return out(['order_id' => $order['id']]);

    }

    public function orderList()
    {
        $req = $this->validate(request(), [
            'status' => 'number',
            'project_group_id' => 'number',
        ]);
        $user = $this->user;

        $builder = Order::where('user_id', $user['id'])->where('status', '>', 1);
        
        if (!empty($req['status'])) {
            $builder->where('status', $req['status']);
        }
        if (!empty($req['project_group_id'])) {
            $builder->where('project_group_id', $req['project_group_id']);
        }
        $data = $builder->order('id', 'desc')->append(['buy_amount', 'total_bonus', 'equity', 'digital_yuan', 'wait_receive_passive_income', 'total_passive_income', 'pay_date', 'sale_date', 'end_date', 'exchange_equity_date', 'exchange_yuan_date'])->paginate(10,false,['query'=>request()->param()]);

        return out($data);
    }


}
