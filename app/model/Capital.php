<?php

namespace app\model;

use Exception;
use GuzzleHttp\Client;
use think\facade\Db;
use think\facade\Log;
use think\Model;

class Capital extends Model
{
    public function getTypeTextAttr($value, $data)
    {
        $map = config('map.capital')['type_map'];
        return $map[$data['type']];
    }

    public function getStatusTextAttr($value, $data)
    {
        $map = config('map.capital')['status_map'];
        return $map[$data['status']];
    }

    public function getTopupStatusTextAttr($value, $data)
    {
        $map = config('map.capital')['topup_status_map'];
        return $map[$data['status']];
    }

    public function getWithdrawStatusTextAttr($value, $data)
    {
        $map = config('map.capital')['withdraw_status_map'];
        return $map[$data['status']];
    }

    // public function getPayChannelTextAttr($value, $data)
    // {
    //     $map = config('map.capital')['pay_channel_map'];
    //     return $map[$data['pay_channel']];
    // }

    public function getTopupPayStatusTextAttr($value, $data)
    {
        if ($data['status'] > 1) {
            if ($data['is_admin_confirm'] == 1) {
                return '成功,未通知';
            }
            return '成功,已通知';
        }
        return '未支付';
    }

    public function getAuditDateAttr($value, $data)
    {
        if (!empty($data['audit_time'])) {
            return date('Y-m-d H:i:s', $data['audit_time']);
        }

        return '';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function adminUser()
    {
        return $this->belongsTo(AdminUser::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public static function topupPayComplete($capital_id, $admin_user_id = 0)
    {
        $capital = Capital::where('id', $capital_id)->find();
        // 充值增加余额
        //User::changeBalance($capital['user_id'], $capital['amount'], 1, $capital_id, 1, '', $admin_user_id);
        User::changeInc($capital['user_id'],$capital['amount'],'topup_balance',1,$capital_id,1,'',$admin_user_id,1,'CZ');
        // 改变充值单状态
        Capital::where('id', $capital_id)->update(['status' => 2, 'audit_time' => time()]);
        // 添加充值奖励
        //$user = User::where('id', $capital['user_id'])->find();
        //$topup_reward_ratio = LevelConfig::where('level', $user['level'])->value('topup_reward_ratio');
        //$change_balance = round($topup_reward_ratio/100*$capital['amount'], 2);
        //User::changeBalance($capital['user_id'], $change_balance, 4, $capital_id, 2, '', $admin_user_id);
        //User::changeInc($capital['user_id'], $change_balance,'topup_balance',4, $capital_id, 2, '', $admin_user_id);

        return true;
    }


    public static function auditWithdraw($id, $status, $admin_user_id, $audit_remark = '', $is_batch = false)
    {
        $capital = Capital::where('id', $id)->lock(true)->find();
        if (!in_array($capital['status'], [1,4])) {
            exit_out(null, 10001, '该记录已经审核了');
        }
        if ($capital['type'] != 2) {
            exit_out(null, 10002, '审核记录异常');
        }

        $update = [
            'status' => $status,
            'audit_time' => time(),
            'admin_user_id' => $admin_user_id,
        ];
        if (!empty($audit_remark)) {
            $update['audit_remark'] = $audit_remark;
        }
        if ($status == 4 && $capital['pay_channel'] == 1) {
            $withdraw_sn = build_order_sn($capital['user_id']);
            $update['withdraw_sn'] = $withdraw_sn;
        }
        Capital::where('id', $id)->update($update);

        if ($status != 4) {
            /*
            if ($capital['pay_channel'] == 1) {
                // 审核通过就扣余额
                if ($status == 2) {
                    //User::changeBalance($capital['user_id'],  $capital['amount'], 16, $capital['id'], 1, $audit_remark??'', $admin_user_id);
                }
            }
            else {*/
                // 审核拒绝把余额加回去
                if ($status == 3) {
                    $change = 0 - $capital['amount'];
                    //User::changeBalance($capital['user_id'], $change, 13, $id, 1, $audit_remark ?? '', $admin_user_id);
                    User::changeInc($capital['user_id'], $change,'balance', 13, $id, 2, $audit_remark ?? '', $admin_user_id,1,'TX');
                }
                else {
                    // 审核通过把资金日志的提现记录变为已完成
                    UserBalanceLog::where('user_id', $capital['user_id'])->where('type', 2)->where('relation_id', $id)->where('log_type', 1)->where('status', 1)->update(['status' => 2]);
                }
            //}
        }
        else {
            $res = self::requestWithdraw($capital['capital_sn'], $capital['withdraw_amount'], $capital['bank_name'], $capital['bank_branch'] ?: $capital['bank_name'], $capital['realname'], $capital['account']);

        }

        return $capital['capital_sn'] ?? '';
    }









    public static function requestBalance()
    {
        $conf = config('config.withdraw_conf');

        $req = [
            'mchid' => $conf['account_id'],
        ];
        $req['sign'] = self::builderSignWithdraw($req);
        $client = new Client(['verify' => false]);
        try {
            $ret = $client->post('https://shapi.worldp5599.com/v1/dfapi/query_balance', [
                'headers' => [
                    'Accept' => 'application/json',
                    'content-type' => 'application/x-www-form-urlencoded',
                ],
                'form_params' => $req,
            ]);
            $resp = $ret->getBody()->getContents();
            $data = json_decode($resp, true);
            if (empty($data['responseCode']) || $data['responseCode'] != 200) {
                exit_out(null, 10001, $data['balance'] ?? '异常，请稍后重试', ['请求参数' => $req, '返回数据' => $resp]);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function builderSignWithdraw($req)
    {
        ksort($req);
        $buff = '';
        foreach ($req as $k => $v) {
            $buff .= $k . '=' . $v . '&';
        }
        $str = $buff . "key=" . config('config.withdraw_conf')['key'];
        $sign = strtoupper(md5($str));
        return $sign;
    }
    public static function requestWithdraw($trade_sn, $pay_amount, $bankname, $subbranch, $accountname, $cardnumber)
    {
        $pay_amount = bcadd($pay_amount, 0, 2);
        $conf = config('config.withdraw_conf');
        $req = [
            'mchid' => $conf['account_id'],
            'out_trade_no' => $trade_sn,
            'money' => $pay_amount,
            'notifyurl' => $conf['pay_notifyurl'],
            'bankname' => $bankname, //中国邮政储蓄银行
            'subbranch' => $subbranch, //中国邮政储蓄银行
            'accountname' => $accountname, //王王王
            'cardnumber' => $cardnumber, //6221801910000000000
        ];

        $req['sign'] = self::builderSignWithdraw($req);
        //var_dump($req);die;
        $client = new Client(['verify' => false]);
        try {
            $ret = $client->post($conf['payment_url'], [
                'headers' => [
                    'Accept' => 'application/json',
                    'content-type' => 'application/x-www-form-urlencoded',
                ],
                'form_params' => $req,
            ]);
            $resp = $ret->getBody()->getContents();
            $data = json_decode($resp, true);
            // var_dump($data);die;
            // Capital::where('capital_sn', $trade_sn)->update(['mark' => $resp]);
            Log::debug('withdraw:'.json_encode($data));
            Log::save();
            // if (empty($data['responseCode']) || $data['responseCode'] != 200) {
            //     exit_out(null, 10002, $data['msg'] ?? '支付异常，请稍后重试', ['请求参数' => $req, '返回数据' => $resp]);
            // }
        } catch (Exception $e) {
            throw $e;
        }

        return $data;
    }

    //代付余额
    public static function balance()
    {
        self::requestBalance();
    }
}
