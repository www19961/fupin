<?php

namespace app\api\controller;

use app\model\Capital;
use app\model\PayAccount;
use app\model\Payment;
use app\model\PaymentConfig;
use app\model\User;
use Exception;
use think\facade\Db;

class CapitalController extends AuthController
{
    public function topup()
    {
        $req = $this->validate(request(), [
            'amount|充值金额' => 'require|float',
            'pay_channel|支付渠道' => 'require|number',
            'payment_config_id' => 'require|number',
            'pay_voucher_img_url' => 'url',
        ]);
        $user = $this->user;

        if ($req['pay_channel'] == 4 && empty($req['pay_voucher_img_url'])) {
            if ( empty($req['pay_voucher_img_url'])) {
                return out(null, 10001, '请上传支付凭证图片');
            }
        }
        if($req['pay_channel']==6){
            $type = 4;
        }else{
            //$type = $req['pay_channel'] - 1;
            $type = $req['pay_channel'];
        }
        $paymentConf = PaymentConfig::userCanPayChannel($req['payment_config_id'], $type, $req['amount']);

        Db::startTrans();
        try {
            $capital_sn = build_order_sn($user['id']);
            // 创建充值单
            $capital = Capital::create([
                'user_id' => $user['id'],
                'capital_sn' => $capital_sn,
                'type' => 1,
                'pay_channel' => $req['pay_channel'],
                'amount' => $req['amount'],
            ]);

            $card_info = json_encode($paymentConf['card_info']);
            if (empty($card_info)) {
                $card_info = '';
            }
            // 创建支付记录
            Payment::create([
                'user_id' => $user['id'],
                'trade_sn' => $capital_sn,
                'pay_amount' => $req['amount'],
                'product_type' => 2,
                'capital_id' => $capital['id'],
                'payment_config_id' => $paymentConf['id'],
                'channel' => $type,
                'mark' => $paymentConf['mark'],
                'type' => $paymentConf['type'],
                'card_info' => $card_info,
                'pay_voucher_img_url' => $req['pay_voucher_img_url'] ?? '',
            ]);
            // 发起支付
            if ($paymentConf['channel'] == 1) {
                $ret = Payment::requestPayment($capital_sn, $paymentConf['mark'], $req['amount']);
            }
            elseif ($paymentConf['channel'] == 2) {
                $ret = Payment::requestPayment2($capital_sn, $paymentConf['mark'], $req['amount']);
            }
            elseif ($paymentConf['channel'] == 3) {
                $ret = Payment::requestPayment3($capital_sn, $paymentConf['mark'], $req['amount']);
            }

            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            throw $e;
        }

        return out(['trade_sn' => $capital_sn ?? '', 'type' => $ret['type'] ?? '', 'data' => $ret['data'] ?? '']);
    }

    public function applyWithdraw()
    {
        $req = $this->validate(request(), [
            'amount|提现金额' => 'require|number',
            'pay_channel|收款渠道' => 'require|number',
            'pay_password|支付密码' => 'require',
            'bank_id|银行卡'=>'require|number',
        ]);
        $user = $this->user;

        if (empty($user['ic_number'])) {
            return out(null, 10001, '请先完成实名认证');
        }
        if (empty($user['pay_password'])) {
            return out(null, 801, '请先设置支付密码');
        }
        $pay_type = $req['pay_channel'] - 1;
        $payAccount = PayAccount::where('user_id', $user['id'])->where('pay_type', $pay_type)->where('id',$req['bank_id'])->find();
        if (empty($payAccount)) {
            return out(null, 802, '请先设置此收款方式');
        }
        if (sha1(md5($req['pay_password'])) !== $user['pay_password']) {
            return out(null, 10001, '支付密码错误');
        }
        if ($req['pay_channel'] == 4 && dbconfig('bank_withdrawal_switch') == 0) {
            return out(null, 10001, '暂未开启银行卡提现');
        }
        if ($req['pay_channel'] == 3 && dbconfig('alipay_withdrawal_switch') == 0) {
            return out(null, 10001, '暂未开启支付宝提现');
        }
        if ($req['pay_channel'] == 7 && dbconfig('digital_withdrawal_switch') == 0) {
            return out(null, 10001, '暂未开启数字人民币提现');
        }
        // 判断单笔限额
        if (dbconfig('single_withdraw_max_amount') < $req['amount']) {
            return out(null, 10001, '单笔最高提现'.dbconfig('single_withdraw_max_amount').'元');
        }
        if (dbconfig('single_withdraw_min_amount') > $req['amount']) {
            return out(null, 10001, '单笔最低提现'.dbconfig('single_withdraw_min_amount').'元');
        }
        // 每天提现时间为9：00-18：00
        $timeNum = (int)date('Hi');
/*         if ($timeNum < 900 || $timeNum > 1800) {
            return out(null, 10001, '每天提现时间为9：00-18：00');
        } */
        Db::startTrans();
        try {
            // 判断余额
            $user = User::where('id', $user['id'])->lock(true)->find();
            // if ($user['invite_bonus'] < $req['amount']) {
            //     return out(null, 10001, '可提现余额不足');
            // }
           // if($req['pay_channel'] < 7){
                $field = 'team_bonus_balance';
                $log_type = '1';
                if ($user['team_bonus_balance'] < $req['amount']) {
                    return out(null, 10001, '团队奖励余额不足');
                }
            //}
            // }elseif($req['pay_channel'] == 7){
            //     $field = 'digital_yuan_amount';
            //     $log_type = '3';
            //     if ($user['digital_yuan_amount'] < $req['amount']) {
            //         return out(null, 10001, '可提现数字人民币不足');
            //     }
            // }
            // 判断每天最大提现次数
            $num = Capital::where('user_id', $user['id'])->where('type', 2)->where('pay_channel', '>', 1)->where('created_at', '>=', date('Y-m-d 00:00:00'))->lock(true)->count();
            if ($num >= dbconfig('per_day_withdraw_max_num')) {
                return out(null, 10001, '每天最多提现'.dbconfig('per_day_withdraw_max_num').'次');
            }

            $capital_sn = build_order_sn($user['id']);
            $change_amount = 0 - $req['amount'];
            $withdraw_fee = round(dbconfig('withdraw_fee_ratio')/100*$req['amount'], 2);
            $withdraw_amount = round($req['amount'] - $withdraw_fee, 2);

            $payMethod = $req['pay_channel'] == 4 ? 1 : $req['pay_channel'];
            // 保存提现记录
            $capital = Capital::create([
                'user_id' => $user['id'],
                'capital_sn' => $capital_sn,
                'type' => 2,
                'pay_channel' => $payMethod,
                'amount' => $change_amount,
                'withdraw_amount' => $withdraw_amount,
                'withdraw_fee' => $withdraw_fee,
                'realname' => $user['realname'],
                'phone' => $payAccount['phone'],
                'collect_qr_img' => $payAccount['qr_img'],
                'account' => $payAccount['account'],
                'bank_name' => $payAccount['bank_name'],
                'bank_branch' => $payAccount['bank_branch'],
            ]);
            // 扣减用户余额
            User::changeInc($user['id'],$change_amount,$field,2,$capital['id'],$log_type);
            //User::changeInc($user['id'],$change_amount,'invite_bonus',2,$capital['id'],1);
            //User::changeBalance($user['id'], $change_amount, 2, $capital['id']);

            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            throw $e;
        }

        return out();
    }

    public function payAccountList()
    {
        $user = $this->user;

        $bank_withdrawal_switch = dbconfig('bank_withdrawal_switch');
        $alipay_withdrawal_switch = dbconfig('alipay_withdrawal_switch');
        $digital_withdrawal_switch = dbconfig('digital_withdrawal_switch');
        $pay_type = [];
        if ($bank_withdrawal_switch == 1) {
            $pay_type[] = 3;
        }
        if ($alipay_withdrawal_switch == 1) {
            $pay_type[] = 2;
        }
        if ($digital_withdrawal_switch == 1) {
            $pay_type[] = 6;
        }
        $data = PayAccount::where('user_id', $user['id'])->whereIn('pay_type', $pay_type)->append(['realname'])->select();

        return out($data);
    }

    public function payAccountDetail()
    {
        $req = $this->validate(request(), [
            'pay_account_id' => 'require|number',
        ]);
        $user = $this->user;

        $data = PayAccount::where('id', $req['pay_account_id'])->where('user_id', $user['id'])->append(['realname'])->find();
        return out($data);
    }

    public function savePayAccount()
    {
        $req = $this->validate(request(), [
            'pay_type' => 'require|number',
            'name' => 'require',
            'account' => 'requireIf:pay_type,3',
            'phone' => 'mobile',
            'qr_img' => 'url',
            'bank_name|银行名称' => 'requireIf:pay_type,3',
            'bank_branch|银行支行' => 'requireIf:pay_type,3',
        ]);
        $user = $this->user;

        if (empty($user['ic_number']) || empty($user['realname'])) {
            return out(null, 10001, '请先完成实名认证');
        }
        
        if ($user['realname'] != $req['name']) {
            return out(null, 10001, '只能绑定本人帐户');
        }

        if ($req['pay_type'] == 3 && dbconfig('bank_withdrawal_switch') == 0) {
            return out(null, 10001, '银行卡提现通道暂未开启');
        }
        if ($req['pay_type'] == 2 && dbconfig('alipay_withdrawal_switch') == 0) {
            return out(null, 10001, '支付宝提现通道暂未开启');
        }

        if (PayAccount::where('user_id', $user['id'])->where('pay_type', $req['pay_type'])->count()>2) {
            //PayAccount::where('user_id', $user['id'])->where('pay_type', $req['pay_type'])->update($req);
            return out(null, 10001, '银行卡数量超过限制');
        }
        else {
            $req['user_id'] = $user['id'];
            PayAccount::create($req);
        }

        return out();
    }

    public function payAccountDel(){
        $req = $this->validate(request(), [
            'id' => 'require|number',
        ]);
        $ret = PayAccount::where('id',$req['id'])->delete();
        return out();

    }

    public function capitalRecord()
    {
        $req = $this->validate(request(), [
            'type' => 'number'
        ]);
        $user = $this->user;
        $builder = Capital::where('user_id', $user['id'])->order('id', 'desc');
        if(isset($req['type']) && $req['type'] != ''){
            $builder->where('type', $req['type']);
        }
        
        $data = $builder->append(['audit_date'])->paginate();

        return out($data);
    }
}
