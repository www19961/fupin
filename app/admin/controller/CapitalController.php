<?php

namespace app\admin\controller;

use app\model\Capital;
use app\model\Payment;
use app\model\PaymentConfig;
use app\model\User;
use app\model\SpecificFupinCapital;
use Exception;
use GuzzleHttp\Client;
use think\facade\Db;
use think\facade\Cache;

class CapitalController extends AuthController
{
    public function topupList()
    {
        $req = request()->param();
        $req['type'] = 1;
        $data = $this->capitalList($req);
        $paymentConfig = PaymentConfig::select();
        $pconfig = [];
        foreach ($paymentConfig as $v) {
            $pconfig[$v['id']] = $v;
        }
        foreach ($data as $k => &$v) {
            $v['chanel_text'] = '';
            if (isset($v['payment'])) {
                if(isset($pconfig[$v->payment->payment_config_id])){
                    $payConfig = $pconfig[$v->payment->payment_config_id];
                    $chanel_name = config('map.payment_config.channel_map')[$payConfig['channel']];
                    $v['chanel_text'] = $chanel_name . '-' . $payConfig['mark'];
                    $v['pay_type'] = config('map.payment_config.type_map')[$payConfig['type']];
                }else{
                    $v['chanel_text'] = '未知';
                    $v['pay_type'] = '未知';
                }
            }
        }
        $this->assign('req', $req);
        $this->assign('data', $data);
        $this->assign('auth_check', $this->adminUser['authGroup'][0]['title']);
        $this->assign('count',$data->total());

        return $this->fetch();
    }

    public function withdrawList()
    {
        $req = request()->param();
        $req['type'] = 2;
        if (!empty($req['export']) && $req['export'] == '支付宝导出' && $req['pay_channel'] != 3) {
            $this->error('请筛选支付宝的支付渠道');
        }

        $data = $this->capitalList($req);
        $logTypeList = [0=>'现金余额'];
        $this->assign('logTypeList', $logTypeList);
        $this->assign('req', $req);
        $this->assign('data', $data);

        return $this->fetch();
    }

    private function capitalList($req)
    {
        $builder = Capital::alias('c')->field('c.*')->order('c.id', 'desc');
        if ($req['type'] == 1) {
            $builder->join('payment p', 'p.capital_id = c.id');
        }
        if (isset($req['capital_id']) && $req['capital_id'] !== '') {
            $builder->where('c.id', $req['capital_id']);
        }
        if (isset($req['user']) && $req['user'] !== '') {
            $user_ids = User::where('phone', $req['user'])->column('id');
            $user_ids[] = $req['user'];
            $builder->whereIn('c.user_id', $user_ids);
        }
        if (isset($req['capital_sn']) && $req['capital_sn'] !== '') {
            $builder->where('c.capital_sn', $req['capital_sn']);
        }
        if (isset($req['type']) && $req['type'] !== '') {
            $builder->where('c.type', $req['type']);
        }
        if (isset($req['status']) && $req['status'] !== '') {
            $builder->where('c.status', $req['status']);
        }
        if (isset($req['pay_channel']) && $req['pay_channel'] !== '') {
            $builder->where('c.pay_channel', $req['pay_channel']);
        }
        if (!empty($req['channel'])) {
            $builder->where('p.channel', $req['channel']);
        }
        if (!empty($req['mark'])) {
            $builder->where('p.mark', $req['mark']);
        }
        if (!empty($req['start_date'])) {
            $builder->where('c.created_at', '>=', $req['start_date'] . ' 00:00:00');
        }
        if (!empty($req['end_date'])) {
            $builder->where('c.created_at', '<=', $req['end_date'] . ' 23:59:59');
        }

        if(isset($req['log_type']) && $req['log_type'] !== ''){
            $builder->where('c.log_type', $req['log_type']);
        }

        $builder1 = clone $builder;
        $total_amount = round($builder1->sum('amount'), 2);
        $this->assign('total_amount', $total_amount);
        if ($req['type'] == 2) {
            $builder2 = clone $builder;
            $total_withdraw_amount = round($builder2->sum('withdraw_amount'), 2);
            $this->assign('total_withdraw_amount', $total_withdraw_amount);
        }

        //1充值 2提现
        if (!empty($req['export'])) {
            $list = $builder->select();
            if ($req['type'] == 1) {
                foreach ($list as $v) {
                    $v->account_type = $v['user']['phone'] ?? '';
                    $v->realname=$v['user']['realname'] ?? '';
                }
                create_excel($list, [
                    'id' => '序号',
                    'account_type' => '用户',
                    'realname'=>'姓名',  
                    'capital_sn' => '单号',
                    'topup_status_text' => '充值状态',
                    'topup_pay_status_text' => '支付状态',
                    'pay_channel_text' => '支付渠道',
                    'amount' => '充值金额',
                    'audit_date' => '支付时间',
                    'created_at' => '创建时间'
                ], '充值记录-' . date('YmdHis'));
            } elseif ($req['type'] == 2) {
                foreach ($list as $v) {
                    $v->account_type = $v['user']['phone'] ?? '';
                    $v->amountCapital = round(0 - $v['amount'], 2);
                    if ($v->pay_channel == 4) {
                        $v->payMethod = '银行：' . $v['bank_name'] ?? '';
                        $v->payMethod .= "\n" . '卡号：' . $v['account'] ?? '';
                        $v->payMethod .= "\n" . '分行：' . $v['bank_branch'] ?? '';
                    } else {
                        $v->payMethod = $v['account'];
                    }
                    $v->shenheUser = $v['adminUser']['nickname'] ?? '';
                }
                create_excel($list, [
                    'id' => '序号',
                    // 'account_type' => '用户',
                    // 'capital_sn' => '单号',
                    // 'withdraw_status_text' => '状态',
                    // 'pay_channel_text' => '支付渠道',
                    'amountCapital' => '提现金额',
                    'withdraw_amount' => '到账金额',
                    'realname' => '收款人实名',
                    'payMethod' => '收款账号',
                    'bank_name' => '银行',
                    // 'shenheUser' => '审核用户',
                    // 'audit_remark' => '拒绝理由',
                    // 'audit_date' => '审核时间',
                    'created_at' => '创建时间'
                ], '提现记录-' . date('YmdHis'));
            }
        }

        $data = $builder->paginate(['query' => $req]);

        return $data;
    }

    public function auditWithdraw()
    {
        $req = $this->validate(request(), [
            'id' => 'require|number',
            'status' => 'require|in:2,3,4',
            'audit_remark' => 'max:200',
        ]);
        $adminUser = $this->adminUser;


        Db::startTrans();
        try {
            $withdraw_sn = Capital::auditWithdraw($req['id'], $req['status'], $adminUser['id'], $req['audit_remark'] ?? '');
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            throw $e;
        }

        $withdraw_sn = dbconfig('automatic_withdrawal_switch') == 1 ? $withdraw_sn : '';
        return out(['withdraw_sn' => $withdraw_sn ?? '']);
    }

    public function auditTopup()
    {
        $req = $this->validate(request(), [
            'id' => 'require|number',
            'status' => 'require|in:2',
        ]);
        $adminUser = $this->adminUser;


        $topup = Cache::get('topup_' . $req['id'], '');
        if ($topup == '1') {
            return out(null, 10001, '重复操作');
        }
        Cache::set('topup_' . $req['id'], 1, 5);

        $capital = Capital::where('id', $req['id'])->find();
        if ($capital['status'] != 1) {
            return out(null, 10001, '该记录状态异常');
        }
        if ($capital['type'] != 1) {
            return out(null, 10002, '审核记录异常');
        }

        Db::startTrans();
        try {
            Payment::where('capital_id', $capital['id'])->update(['payment_time' => time(), 'status' => 2]);

            Capital::where('id', $capital['id'])->update(['is_admin_confirm' => 1]);
            $userModel = new User();
            //$userModel->teamBonus($capital['user_id'], $capital['amount'], $capital['id']);

            Capital::topupPayComplete($capital['id'], $adminUser['id']);

            // 判断通道是否超过最大限额，超过了就关闭通道
            $payment = Payment::where('capital_id', $capital['id'])->find();
            PaymentConfig::checkMaxPaymentLimit($payment['type'], $payment['channel'], $payment['mark']);

            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            throw $e;
        }

        return out();
    }

    public function queryWithdrawResult()
    {
        $req = $this->validate(request(), [
            'withdraw_sn' => 'require',
        ]);

        $capital = Capital::where('withdraw_sn', $req['withdraw_sn'])->find();
        if (empty($capital)) {
            return out(null, 10001, '提现记录不存在');
        }
        if ($capital['status'] == 2) {
            return out(['is_success' => 1]);
        }
        if ($capital['status'] != 4) {
            return out(null, 10001, '提现失败');
        }

        return out(['is_success' => 0]);
    }

    public function batchAuditCapital()
    {
        $req = $this->validate(request(), [
            'ids' => 'require|array',
            'status' => 'require|in:2,3,4',
        ]);
        $adminUser = $this->adminUser;

        Db::startTrans();
        try {
            foreach ($req['ids'] as $v) {
                // Capital::auditWithdraw($v, $req['status'], $adminUser['id'], '', true);
                Capital::auditWithdraw($v, $req['status'], $adminUser['id'], $req['audit_remark'] ?? '');
                // $capital = Capital::find($v);
                // if ($capital['bank_name'] == '支付宝') {
                //     $res = Capital::requestWithdraw($capital['capital_sn'], $capital['withdraw_amount'], 1, 1, $capital['realname'], $capital['account'], 6);
                // } else {
                //     $res = Capital::requestWithdraw($capital['capital_sn'], $capital['withdraw_amount'], $capital['bank_name'], $capital['bank_branch'] ?: $capital['bank_name'], $capital['realname'], $capital['account'], 0);
                // }
            }

            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            throw $e;
        }

        return out();
    }


    public function specificfupinwithdrawList()
    {
        $req = request()->param();
        $req['type'] = 2;
        if (!empty($req['export']) && $req['export'] == '支付宝导出' && $req['pay_channel'] != 3) {
            $this->error('请筛选支付宝的支付渠道');
        }

        $data = $this->spcificfupincapitalList($req);
        $logTypeList = [0=>'现金余额'];
        $this->assign('logTypeList', $logTypeList);
        $this->assign('req', $req);
        $this->assign('data', $data);

        return $this->fetch();
    }

    private function spcificfupincapitalList($req)
    {
        $builder = SpecificFupinCapital::alias('c')->field('c.*')->order('c.id', 'desc');
        if ($req['type'] == 1) {
            $builder->join('payment p', 'p.capital_id = c.id');
        }
        if (isset($req['capital_id']) && $req['capital_id'] !== '') {
            $builder->where('c.id', $req['capital_id']);
        }
        if (isset($req['user']) && $req['user'] !== '') {
            $user_ids = User::where('phone', $req['user'])->column('id');
            $user_ids[] = $req['user'];
            $builder->whereIn('c.user_id', $user_ids);
        }
        if (isset($req['capital_sn']) && $req['capital_sn'] !== '') {
            $builder->where('c.capital_sn', $req['capital_sn']);
        }
        if (isset($req['type']) && $req['type'] !== '') {
            $builder->where('c.type', $req['type']);
        }
        if (isset($req['status']) && $req['status'] !== '') {
            $builder->where('c.status', $req['status']);
        }
        if (isset($req['pay_channel']) && $req['pay_channel'] !== '') {
            $builder->where('c.pay_channel', $req['pay_channel']);
        }
        if (!empty($req['channel'])) {
            $builder->where('p.channel', $req['channel']);
        }
        if (!empty($req['mark'])) {
            $builder->where('p.mark', $req['mark']);
        }
        if (!empty($req['start_date'])) {
            $builder->where('c.created_at', '>=', $req['start_date'] . ' 00:00:00');
        }
        if (!empty($req['end_date'])) {
            $builder->where('c.created_at', '<=', $req['end_date'] . ' 23:59:59');
        }

        if(isset($req['log_type']) && $req['log_type'] !== ''){
            $builder->where('c.log_type', $req['log_type']);
        }

        $builder1 = clone $builder;
        $total_amount = round($builder1->sum('amount'), 2);
        $this->assign('total_amount', $total_amount);
        if ($req['type'] == 2) {
            $builder2 = clone $builder;
            $total_withdraw_amount = round($builder2->sum('withdraw_amount'), 2);
            $this->assign('total_withdraw_amount', $total_withdraw_amount);
        }

        //1充值 2提现
        if (!empty($req['export'])) {
            $list = $builder->select();
            if ($req['type'] == 1) {
                foreach ($list as $v) {
                    $v->account_type = $v['user']['phone'] ?? '';
                    $v->realname=$v['user']['realname'] ?? '';
                }
                create_excel($list, [
                    'id' => '序号',
                    'account_type' => '用户',
                    'realname'=>'姓名',  
                    'capital_sn' => '单号',
                    'topup_status_text' => '充值状态',
                    'topup_pay_status_text' => '支付状态',
                    'pay_channel_text' => '支付渠道',
                    'amount' => '充值金额',
                    'audit_date' => '支付时间',
                    'created_at' => '创建时间'
                ], '充值记录-' . date('YmdHis'));
            } elseif ($req['type'] == 2) {
                foreach ($list as $v) {
                    $v->account_type = $v['user']['phone'] ?? '';
                    $v->amountCapital = round(0 - $v['amount'], 2);
                    if ($v->pay_channel == 4) {
                        $v->payMethod = '银行：' . $v['bank_name'] ?? '';
                        $v->payMethod .= "\n" . '卡号：' . $v['account'] ?? '';
                        $v->payMethod .= "\n" . '分行：' . $v['bank_branch'] ?? '';
                    } else {
                        $v->payMethod = $v['account'];
                    }
                    $v->shenheUser = $v['adminUser']['nickname'] ?? '';
                }
                create_excel($list, [
                    'id' => '序号',
                    // 'account_type' => '用户',
                    // 'capital_sn' => '单号',
                    // 'withdraw_status_text' => '状态',
                    // 'pay_channel_text' => '支付渠道',
                    'amountCapital' => '提现金额',
                    'withdraw_amount' => '到账金额',
                    'realname' => '收款人实名',
                    'payMethod' => '收款账号',
                    'bank_name' => '银行',
                    // 'shenheUser' => '审核用户',
                    // 'audit_remark' => '拒绝理由',
                    // 'audit_date' => '审核时间',
                    'created_at' => '创建时间'
                ], '提现记录-' . date('YmdHis'));
            }
        }

        $data = $builder->paginate(['query' => $req]);

        return $data;
    }

    public function sfBatchAuditCapital()
    {
        $req = $this->validate(request(), [
            'ids' => 'require|array',
            'status' => 'require|in:2,3,4',
        ]);
        $adminUser = $this->adminUser;

        Db::startTrans();
        try {
            foreach ($req['ids'] as $v) {
                SpecificFupinCapital::auditWithdraw($v, $req['status'], $adminUser['id'], '', true);
            }

            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            throw $e;
        }

        return out();
    }
    public function sfAuditWithdraw()
    {
        $req = $this->validate(request(), [
            'id' => 'require|number',
            'status' => 'require|in:2,3,4',
            'audit_remark' => 'max:200',
        ]);
        $adminUser = $this->adminUser;


        Db::startTrans();
        try {
            $withdraw_sn = SpecificFupinCapital::auditWithdraw($req['id'], $req['status'], $adminUser['id'], $req['audit_remark'] ?? '');

            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            throw $e;
        }

        $withdraw_sn = dbconfig('automatic_withdrawal_switch') == 1 ? $withdraw_sn : '';
        return out(['withdraw_sn' => $withdraw_sn ?? '']);
    }

    public function sfQueryWithdrawResult()
    {
        $req = $this->validate(request(), [
            'withdraw_sn' => 'require',
        ]);

        $capital = SpecificFupinCapital::where('withdraw_sn', $req['withdraw_sn'])->find();
        if (empty($capital)) {
            return out(null, 10001, '提现记录不存在');
        }
        if ($capital['status'] == 2) {
            return out(['is_success' => 1]);
        }
        if ($capital['status'] != 4) {
            return out(null, 10001, '提现失败');
        }

        return out(['is_success' => 0]);
    }
}
