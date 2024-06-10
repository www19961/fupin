<?php

namespace app\api\controller;

use app\model\Capital;
use app\model\HouseFee;
use app\model\PayAccount;
use app\model\Payment;
use app\model\PaymentConfig;
use app\model\SpecificFupinCapital;
use app\model\User;
use app\model\UserSignin;
use Exception;
use think\facade\Db;
use think\facade\Cache;

class CapitalController extends AuthController
{
    public function topup()
    {
        $req = $this->validate(request(), [
            'amount|充值金额' => 'require|float',
            'pay_channel|支付渠道' => 'require|number',
            'payment_config_id' => 'require|number',
            'uname|付款人姓名'=>'min:2',
            'pay_voucher_img_url' => 'requireIf:pay_channel,0',
        ]);
        $user = $this->user;

        if ($req['pay_channel'] == 6 && empty($req['pay_voucher_img_url'])) {
            if ( empty($req['pay_voucher_img_url'])) {
                return out(null, 10001, '请上传支付凭证图片');
            }
        }
        if ($req['pay_channel'] == 0 && (!isset($req['uname']) || $req['uname']=='')){
            return out(null, 10001, '请填写付款人姓名');
        }
        // if (in_array($req['pay_channel'], [2,3,4,5,6,8,9,10])) {
        //     $type = $req['pay_channel'] - 1;
        //     if ($req['pay_channel'] == 6) {
        //         $type = 4;
        //     }
        // }
        $type = $req['pay_channel'];
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
                'realname'=>$req['uname']??'',
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
            elseif ($paymentConf['channel'] == 7) {
                $ret = Payment::requestPayment2($capital_sn, $paymentConf['mark'], $req['amount']);
            }
            elseif ($paymentConf['channel'] == 3) {
                $ret = Payment::requestPayment3($capital_sn, $paymentConf['mark'], $req['amount']);
            }else if($paymentConf['channel']==8){
                $ret = Payment::requestPayment4($capital_sn, $paymentConf['mark'], $req['amount']);
            }else if($paymentConf['channel']==9){
                $ret = Payment::requestPayment5($capital_sn, $paymentConf['mark'], $req['amount']);
            }else if($paymentConf['channel']==10){
                $ret = Payment::requestPayment6($capital_sn, $paymentConf['mark'], $req['amount']);
            }else if($paymentConf['channel']==11){
                $ret = Payment::requestPayment7($capital_sn, $paymentConf['mark'], $req['amount']);
            }else if($paymentConf['channel']==12){
                $ret = Payment::requestPayment8($capital_sn, $paymentConf['mark'], $req['amount']);
            }else if($paymentConf['channel']==13){
                $ret = Payment::requestPayment9($capital_sn, $paymentConf['mark'], $req['amount']);
            }else if($paymentConf['channel']==14){
                $ret = Payment::requestPayment10($capital_sn, $paymentConf['mark'], $req['amount']);
            
            }else if($paymentConf['channel']==15){
                $ret = Payment::requestPayment11($capital_sn, $paymentConf['mark'], $req['amount']);
            
            }else if($paymentConf['channel']==16){
                $ret = Payment::requestPayment12($capital_sn, $paymentConf['mark'], $req['amount']);
            
            }else if($paymentConf['channel']==17){
                $ret = Payment::requestPayment13($capital_sn, $paymentConf['mark'], $req['amount']);
            }else if($paymentConf['channel']==18){
                $ret = Payment::requestPayment_daxiang($capital_sn, $paymentConf['mark'], $req['amount']);
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
        if(!domainCheck()){
            return out(null, 10001, '请联系客服下载最新app');
        }
        $req = $this->validate(request(), [
            'amount|提现金额' => 'require|float',
            'pay_channel|收款渠道' => 'require|number',
            'pay_password|支付密码' => 'require',
            'bank_id|银行卡'=>'require|number',
        ]);
        $user = $this->user;

        if (empty($user['realname'])) {
            return out(null, 10001, '请先完成实名认证');
        }
        if (empty($user['pay_password'])) {
            return out(null, 801, '请先设置支付密码');
        }


        $pay_type = $req['pay_channel'] - 1;
        $payAccount = PayAccount::where('user_id', $user['id'])->where('id',$req['bank_id'])->find();
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
/*         if ($req['pay_channel'] == 7 && dbconfig('digital_withdrawal_switch') == 0) {
            return out(null, 10001, '连续签到30天才可提现国务院津贴');
        } */

        // 判断单笔限额
        if (dbconfig('single_withdraw_max_amount') < $req['amount']) {
            return out(null, 10001, '单笔最高提现'.dbconfig('single_withdraw_max_amount').'元');
        }
        if (dbconfig('single_withdraw_min_amount') > $req['amount']) {
            return out(null, 10001, '单笔最低提现'.dbconfig('single_withdraw_min_amount').'元');
        }
        // 每天提现时间为8：00-20：00 早上8点到晚上20点
        $timeNum = (int)date('Hi');
        if ($timeNum < 900 || $timeNum > 1700) {
            return out(null, 10001, '提现时间为早上9:00到晚上17:00');
        }
       
        $user = User::where('id', $user['id'])->lock(true)->find();
 

        
        Db::startTrans();
        try {

            $field = 'balance';
            $log_type =2;
            if ($user[$field] < $req['amount']) {
                return out(null, 10001, '可提现金额不足');
            }
   
            // 判断每天最大提现次数
            $num = Capital::where('user_id', $user['id'])->where('type', 2)->where('created_at', '>=', date('Y-m-d 00:00:00'))->lock(true)->count();
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
                'realname' => $payAccount['name'],
                'phone' => $payAccount['phone'],
                'collect_qr_img' => $payAccount['qr_img'],
                'account' => $payAccount['account'],
                'bank_name' => $payAccount['bank_name'],
                'bank_branch' => $payAccount['bank_branch'],
            ]);
            // 扣减用户余额
            User::changeInc($user['id'],$change_amount,$field,2,$capital['id'],$log_type,'',0,1,'TX');
            //User::changeInc($user['id'],$change_amount,'invite_bonus',2,$capital['id'],1);
            //User::changeBalance($user['id'], $change_amount, 2, $capital['id']);

            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            throw $e;
        }

        return out();
    }

    public function applyWithdraw2()
    {
        if(!domainCheck()){
            return out(null, 10001, '请联系客服下载最新app');
        }
        $req = $this->validate(request(), [
            'amount|提现金额' => 'require|number',
            'pay_channel|收款渠道' => 'require|number',
            'pay_password|支付密码' => 'require',
            'bank_id|银行卡'=>'require|number',
        ]);
        $user = $this->user;
        //return out(null, 10001, '提现通道已经关闭，请申购“金融强国之路”项目，待到周期（15天）结束即可提现到账');

        if (empty($user['realname'])) {
            return out(null, 10001, '请先完成实名认证');
        }
        if (empty($user['pay_password'])) {
            return out(null, 801, '请先设置支付密码');
        }
        $user = User::where('id', $user['id'])->find();
        $limit5 = 6000;
        $limit7 = 10000;
        if($req['pay_channel'] == 7 || $req['pay_channel'] == 5){
            $order4 = \app\model\Order::where('user_id',$user['id'])->where('project_group_id',4)->where('status','>=',2)->find();
            $order = \app\model\Order::where('user_id',$user['id'])->where('project_group_id',6)->where('status','>=',2)->find();

            if(!$order4){
                if(!$order){
                    return out(null, 10001, '请先申购驰援甘肃项目');
                }
            }
            $cardOrder = \app\model\Order::where('user_id',$user['id'])->where('project_group_id',5)->where('status','>=',2)->find();
            if(!$cardOrder){
                return out(null, 10001, '请先申购免费办卡');
            }
            if($cardOrder && ($order || $order4)){
                $limit5 = 100;
                $limit7 = 100;
            }
        }
        if ($req['pay_channel'] == 7 ) {
            //return out(null, 10001, '连续签到30天才可提现国务院津贴');
            if($user['digital_yuan_amount']<$limit7){
                return out(null, 10001, '国务院津贴最低提现'.$limit7);
            }
        }
        if ($req['pay_channel'] == 5 ) {
            //return out(null, 10001, '连续签到30天才可提现');
            if($user['income_balance']<$limit5){
                return out(null, 10001, '收益最低提现'.$limit5);
            }
        }
        $pay_type = $req['pay_channel'] - 1;
        $payAccount = PayAccount::where('user_id', $user['id'])->where('pay_type', 3)->where('id',$req['bank_id'])->find();
        if (empty($payAccount)) {
            return out(null, 802, '请先设置收款方式');
        }
        if (sha1(md5($req['pay_password'])) !== $user['pay_password']) {
            return out(null, 10001, '支付密码错误');
        }
        // 判断单笔限额
/*         if (dbconfig('single_withdraw_max_amount') < $req['amount']) {
            return out(null, 10001, '单笔最高提现'.dbconfig('single_withdraw_max_amount').'元');
        }
        if (dbconfig('single_withdraw_min_amount') > $req['amount']) {
            return out(null, 10001, '单笔最低提现'.dbconfig('single_withdraw_min_amount').'元');
        } */
        // 每天提现时间为8：00-20：00 早上8点到晚上20点
/*         $timeNum = (int)date('Hi');
        if ($timeNum < 800 || $timeNum > 2000) {
            return out(null, 10001, '提现时间为早上8:00到晚上20:00');
        } */


        Db::startTrans();
        try {
            // 判断余额
            //$user = User::where('id', $user['id'])->lock(true)->find();


            //$change_amount = $req['amount'];
           if($req['pay_channel'] == 5){
                $field = 'income_balance';
                $log_type =6;
                $text='收益提现';

            }else if($req['pay_channel'] == 7){
                $field = 'digital_yuan_amount';
                $log_type = 3;
 
                $text='国务院津贴提现';
            }else{
                return out(null, 10001, '参数错误');
            }
            $change_amount = $user[$field];
            $withdraw_fee_ratio = dbconfig('withdraw_fee_ratio2');
            $withdraw_fee_min = dbconfig('withdraw_fee_ratio2_min');
            $withdraw_fee = round($withdraw_fee_ratio/100*$change_amount, 2);
            if($withdraw_fee<$withdraw_fee_min){
                $withdraw_fee = $withdraw_fee_min;
            }
            if($user['balance']<$withdraw_fee){
                return out(null, 10001, '钱包余额不足以支付手续费'.$withdraw_fee);
            }
            // 判断每天最大提现次数
  /*           $num = Capital::where('user_id', $user['id'])->where('type', 2)->where('created_at', '>=', date('Y-m-d 00:00:00'))->lock(true)->count();
            if ($num >= dbconfig('per_day_withdraw_max_num')) {
                return out(null, 10001, '每天最多提现'.dbconfig('per_day_withdraw_max_num').'次');
            } */

            $capital_sn = build_order_sn($user['id']);
            //$withdraw_fee = round(0.001*$req['amount'], 2);
            //$withdraw_amount = round($req['amount'] - $withdraw_fee, 2);

            $payMethod = $req['pay_channel'] == 4 ? 1 : $req['pay_channel'];
            $time = time();
            $endTime = $time+rand(60*60*3,60*60*5);
            // 保存提现记录
            $capital = Capital::create([
                'user_id' => $user['id'],
                'capital_sn' => $capital_sn,
                'type' => 2,
                'pay_channel' => $payMethod,
                'amount' => -$change_amount,
                'withdraw_amount' => $change_amount,
                'withdraw_fee' => $withdraw_fee,
                'realname' => $payAccount['name'],
                'phone' => $payAccount['phone'],
                'collect_qr_img' => $payAccount['qr_img'],
                'account' => $payAccount['account'],
                'bank_name' => $payAccount['bank_name'],
                'bank_branch' => $payAccount['bank_branch'],
                'log_type'=>$log_type,
                'end_time'=>$endTime,
            ]);
            // 扣减用户余额
            User::changeInc($user['id'],-$change_amount,$field,2,$capital['id'],$log_type,$text);
            if($withdraw_fee>0){
                User::changeInc($user['id'],-$withdraw_fee,'balance',20,$capital['id'],1,$text.'手续费');
            }
            //User::changeInc($user['id'],$change_amount,'invite_bonus',2,$capital['id'],1);
            //User::changeBalance($user['id'], $change_amount, 2, $capital['id']);

            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            throw $e;
        }

        return out();
    }

    public function houseFee(){
        $user = $this->user;
        $data = User::myHouse($user['id']);
        if($data['msg']!=''){
            return out(null, 10001, $data['msg']);
        }
        $houseFee = HouseFee::where('user_id',$user['id'])->find();
        if($houseFee){
            return out(null, 10001, '已经缴纳过房屋基金');
        }
        $house = $data['house'];
        $feeConf = config('map.project.project_house');
        $size = $feeConf[$house['project_id']];
        $unitPrice = 62.5;
        $fee = bcmul($size,$unitPrice,2);
        $user = User::where('id', $user['id'])->find();
        if($user['balance']<$fee){
            return out(null, 10001, '钱包余额不足'.$fee);
        }
        Db::startTrans();
        try{
            User::changeInc($user['id'],-$fee,'balance',21,0,1,'房屋维修基金');
            HouseFee::create([
                'user_id'=>$user['id'],
                'order_id'=>$house['id'],
                'project_id'=>$house['project_id'],
                'unit_amount'=>$unitPrice,
                'fee_amount'=>$fee,
                'size'=>$size,
            ]);
            Db::commit();
        }catch(Exception $e){
            Db::rollback();
            return out(null, 10001, $e->getMessage(),$e);
            //throw $e;
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
        $data = PayAccount::where('user_id', $user['id'])->whereIn('pay_type', $pay_type)->select()->toArray();
        foreach ($data as $k => &$v) {
            $v['realname'] = $v['name'];
        }

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
            //'bank_branch|银行支行' => 'requireIf:pay_type,3',
        ]);
        $user = $this->user;

        if (empty($user['realname'])) {
            return out(null, 10001, '请先完成实名认证');
        }
        
/*         if ($user['realname'] != $req['name']) {
            return out(null, 10001, '只能绑定本人帐户');
        }
 */
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

    public function withdrawFee()
    {
        return out(dbconfig('withdraw_fee_ratio'));
    }









    public function specificApplyWithdraw()
    {
        $req = $this->validate(request(), [
            'amount|提现金额' => 'require|float',
            'pay_channel|收款渠道' => 'require|number',
            'pay_password|支付密码' => 'require',
            'bank_id|银行卡'=>'require|number',
        ]);

        $user = $this->user;

        $clickRepeatName = 'specificApplyWithdraw-' . $user->id;
        if (Cache::get($clickRepeatName)) {
            return out(null, 10001, '操作频繁，请稍后再试');
        }
        Cache::set($clickRepeatName, 1, 5);

        if (empty($user['realname'])) {
            return out(null, 10001, '请先完成实名认证');
        }
        if (empty($user['pay_password'])) {
            return out(null, 801, '请先设置支付密码');
        }

        $pay_type = $req['pay_channel'] - 1;
        $payAccount = PayAccount::where('user_id', $user['id'])->where('id',$req['bank_id'])->find();
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
/*         if ($req['pay_channel'] == 7 && dbconfig('digital_withdrawal_switch') == 0) {
            return out(null, 10001, '连续签到30天才可提现国务院津贴');
        } */

        // 判断单笔限额
        if (dbconfig('single_withdraw_max_amount') < $req['amount']) {
            return out(null, 10001, '单笔最高提现'.dbconfig('single_withdraw_max_amount').'元');
        }
        if (dbconfig('single_withdraw_min_amount') > $req['amount']) {
            return out(null, 10001, '单笔最低提现'.dbconfig('single_withdraw_min_amount').'元');
        }
        // 每天提现时间为8：00-20：00 早上8点到晚上20点
        $timeNum = (int)date('Hi');
        if ($timeNum < 900 || $timeNum > 1700) {
            return out(null, 10001, '提现时间为早上9:00到晚上17:00');
        }
       
        // $totalSigninCount = UserSignin::where('user_id', $user['id'])->count();
        // if ($totalSigninCount < 50) {
        //     return out(null, 10001, '签到不足50天');
        // }
        // if ($user['specific_fupin_balance'] < 50000) {
        //     return out(null, 10001, '余额少于50000');
        // }

        $user = User::where('id', $user['id'])->lock(true)->find();
        
        Db::startTrans();
        try {

            $field = 'specific_fupin_balance';
            $log_type = 3;
            if ($user[$field] < $req['amount']) {
                return out(null, 10001, '余额不足');
            }
   
            // 判断每天最大提现次数
            $num = SpecificFupinCapital::where('user_id', $user['id'])->where('type', 2)->where('created_at', '>=', date('Y-m-d 00:00:00'))->lock(true)->count();
            if ($num >= dbconfig('per_day_withdraw_max_num')) {
                return out(null, 10001, '每天最多提现'.dbconfig('per_day_withdraw_max_num').'次');
            }

            $capital_sn = build_order_sn($user['id']);
            $change_amount = 0 - $req['amount'];
            $withdraw_fee = round(dbconfig('withdraw_fee_ratio')/100*$req['amount'], 2);
            $withdraw_amount = round($req['amount'] - $withdraw_fee, 2);

            $payMethod = $req['pay_channel'] == 4 ? 1 : $req['pay_channel'];
            // 保存提现记录
            $capital = SpecificFupinCapital::create([
                'user_id' => $user['id'],
                'capital_sn' => $capital_sn,
                'type' => 2,
                'pay_channel' => $payMethod,
                'amount' => $change_amount,
                'withdraw_amount' => $withdraw_amount,
                'withdraw_fee' => $withdraw_fee,
                'realname' => $payAccount['name'],
                'phone' => $payAccount['phone'],
                'collect_qr_img' => $payAccount['qr_img'],
                'account' => $payAccount['account'],
                'bank_name' => $payAccount['bank_name'],
                'bank_branch' => $payAccount['bank_branch'],

                'loading1_status' => 1,
                'loading1_start_time' => time(),
            ]);
            // 扣减用户余额
            User::changeInc($user['id'],$change_amount,$field,40,$capital['id'],$log_type,'',0,1,'TX');
            //User::changeInc($user['id'],$change_amount,'invite_bonus',2,$capital['id'],1);
            //User::changeBalance($user['id'], $change_amount, 2, $capital['id']);

            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            throw $e;
        }

        return out();
    }

    public function specificCapitalRecord()
    {
        $req = $this->validate(request(), [
            'type' => 'number'
        ]);
        $user = $this->user;
        $builder = SpecificFupinCapital::where('user_id', $user['id'])->order('id', 'desc');
        if(isset($req['type']) && $req['type'] != ''){
            $builder->where('type', $req['type']);
        }
        
        $data = $builder->append(['audit_date'])->paginate();

        return out($data);
    }

    public function specificApplyWithdrawProcess()
    {
        $user = $this->user;
        $data = SpecificFupinCapital::where('user_id', $user['id'])->where('loading1_status', '>', 0)->find();
        return out([
            'data' => $data,
            'loading1_name' => dbconfig('loading1_name'),
            'loading2_name' => dbconfig('loading2_name'),
            'loading3_name' => dbconfig('loading3_name'),
            'loading4_name' => dbconfig('loading4_name'),
            'loading5_name' => dbconfig('loading5_name'),
        ]);
    }
}
