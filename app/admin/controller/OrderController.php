<?php

namespace app\admin\controller;

use app\model\Order;
use app\model\Payment;
use app\model\PaymentConfig;
use app\model\User;
use Exception;
use think\facade\Db;

class OrderController extends AuthController
{
    public function orderList()
    {
        $req = request()->param();

        if (!empty($req['channel'])||!empty($req['mark'])) {
            $builder = Order::alias('o')->leftJoin('payment p', 'p.order_id = o.id')->field('o.*')->order('o.id', 'desc');
        }else{
            $builder = Order::alias('o')->field('o.*')->order('o.id', 'desc');
        }
        if (isset($req['order_id']) && $req['order_id'] !== '') {
            $builder->where('o.id', $req['order_id']);
        }
        if (isset($req['up_user_id']) && $req['up_user_id'] !== '') {
            $builder->where('o.up_user_id', $req['up_user_id']);
        }
        if (isset($req['user']) && $req['user'] !== '') {
            $user_ids = User::where('phone', $req['user'])->column('id');
            $user_ids[] = $req['user'];
            $builder->whereIn('o.user_id', $user_ids);
        }
        if (isset($req['order_sn']) && $req['order_sn'] !== '') {
            $builder->where('o.order_sn', $req['order_sn']);
        }
        if (isset($req['status']) && $req['status'] !== '') {
            $builder->where('o.status', $req['status']);
        }
        if (isset($req['project_id']) && $req['project_id'] !== '') {
            $builder->where('o.project_id', $req['project_id']);
        }
        if (isset($req['project_name']) && $req['project_name'] !== '') {
            $builder->whereLike('o.project_name', '%'.$req['project_name'].'%');
        }
        if (isset($req['pay_method']) && $req['pay_method'] !== '') {
            $builder->where('o.pay_method', $req['pay_method']);
        }
        if (isset($req['pay_time']) && $req['pay_time'] !== '') {
            $builder->where('o.pay_time', $req['pay_time']);
        }
        if (!empty($req['channel'])) {
            $builder->where('p.channel', $req['channel']);
        }
        if (!empty($req['mark'])) {
            $builder->where('p.mark', $req['mark']);
        }

        $builder1 = clone $builder;
        $total_buy_amount = round($builder1->sum('o.buy_num*o.single_amount'), 2);
        $this->assign('total_buy_amount', $total_buy_amount);

        $builder2 = clone $builder;
        $total_buy_integral = round($builder2->sum('o.buy_num*o.single_integral'), 2);
        $this->assign('total_buy_integral', $total_buy_integral);

        $builder3 = clone $builder;
        $total_gift_equity = round($builder3->sum('o.buy_num*o.single_gift_equity'), 2);
        $this->assign('total_gift_equity', $total_gift_equity);

        $builder4 = clone $builder;
        $total_gift_digital_yuan = round($builder4->sum('o.buy_num*o.single_gift_digital_yuan'), 2);
        $this->assign('total_gift_digital_yuan', $total_gift_digital_yuan);

        $data = $builder->paginate(['query' => $req]);
        //var_dump($data);
        $this->assign('req', $req);
        $this->assign('data', $data);

        return $this->fetch();
    }

    public function auditOrder()
    {
        $req = request()->post();
        $this->validate($req, [
            'id' => 'require|number',
            'status' => 'require|in:2',
        ]);

        $order = Order::where('id', $req['id'])->find();
        if ($order['status'] != 1) {
            return out(null, 10001, '该记录状态异常');
        }
        if (!in_array($order['pay_method'], [2,3,4,6])) {
            return out(null, 10002, '审核记录异常');
        }

        Db::startTrans();
        try {
            Payment::where('order_id', $order['id'])->update(['payment_time' => time(), 'status' => 2]);

            Order::where('id', $order['id'])->update(['is_admin_confirm' => 1]);
            Order::orderPayComplete($order['id']);
            // 判断通道是否超过最大限额，超过了就关闭通道
            $payment = Payment::where('order_id', $order['id'])->find();
            $userModel = new User();
            $userModel->teamBonus($order['user_id'],$payment['pay_amount'],$payment['id']);

            PaymentConfig::checkMaxPaymentLimit($payment['type'], $payment['channel'], $payment['mark']);

            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            throw $e;
        }

        return out();
    }
}
