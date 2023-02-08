<?php
/**
 * Created by PhpStorm.
 * User: Ns
 * Date: 19-7-5
 * Time: 下午4:09
 */

namespace app\api\controller;

use app\common\controller\BaseController;
use app\model\Banner;
use app\model\Capital;
use app\model\EquityYuanRecord;
use app\model\Order;
use app\model\Payment;
use app\model\PaymentConfig;
use app\model\Setting;
use app\model\SystemInfo;
use app\model\User;
use app\model\UserBalanceLog;
use app\model\UserRelation;
use Exception;
use think\facade\Db;

class CommonController extends BaseController
{
   
   
        public function shanchuba(){
        $filename = 'shanchu.csv';
        $sql = 'select user_id from mp_order where project_id=10 and status=2   group by user_id having count(*)>1';
        $member= Db::query($sql);
        if($member){
            foreach($member as  $k=>$v){
                $select = Order::where('user_id',$v['user_id'])->where('project_id',10)->where('status',2)->select();
                if(count($select)>=2){
                    $phone = User::where('id',$v['user_id'])->value('phone');
                  Order::where('id',$select[0]['id'])->where('user_id',$v['user_id'])->delete();
                  User::where('id',$v['user_id'])->inc('integral',50)->update();
                    $str = '删除订单记录id-'.$select[0]['id']."-退还会员".$phone."+50积分\n";
                    file_put_contents($filename,$str,FILE_APPEND);
                }
            }
        }else{
            echo "ok";die;
        }
    }
    public function login()
    {
        $req = $this->validate(request(), [
            'phone|手机号' => 'require|mobile',
            'password|密码' => 'require',
        ]);

        $password = sha1(md5($req['password']));
        $user = User::field('id,status')->where('phone', $req['phone'])->where('password', $password)->find();
        if (empty($user)) {
            return out(null, 10001, '账号或密码错误');
        }
        if ($user['status'] == 0) {
            return out(null, 10001, '账号已被冻结');
        }
        $token = aes_encrypt(['id' => $user['id'], 'time' => time()]);

        return out(['token' => $token]);
    }

    public function register()
    {
        $req = $this->validate(request(), [
            'phone|手机号' => 'require',
            'password|密码' => 'require|alphaNum|length:6,12',
            'invite_code|邀请码' => 'max:10',
            'realname|姓名'=>'require',
            'ic_number|身份证号' => 'require|idCard',
            //'captcha|验证码' => 'require|max:6',
        ]);

        /*$key = 'captcha-'.$req['phone'].'-1';
        $captcha = Cache::get($key);
        if ($captcha != $req['captcha']) {
            if (env('app_debug') == false || $req['captcha'] != 900100) {
                return out(null, 10001, '验证码错误');
            }
        }
        Cache::rm($key);*/

        if (User::where('phone', $req['phone'])->count()) {
            return out(null, 10002, '该手机号已注册，请登录');
        }

        if (!empty($req['invite_code'])){
            $parentUser = User::field('id')->where('invite_code', $req['invite_code'])->find();
            if (empty($parentUser)) {
                return out(null, 10003, '邀请码不存在');
            }

            $req['up_user_id'] = $parentUser['id'];
        }

        $req['invite_code'] = build_invite_code();
        $req['password'] = sha1(md5($req['password']));
        $user = User::create($req);

        //保存层级关系
        if (!empty($parentUser)){
            UserRelation::saveUserRelation($user['id']);
            
        }

        $token = aes_encrypt(['id' => $user['id'], 'time' => time()]);

        // 检测注册赠送股权
        if (dbconfig('register_give_equity_switch') == 1) {
            EquityYuanRecord::create([
                'user_id' => $user['id'],
                'type' => 1,
                'status' => 1,
                'title' => '注册赠送股权',
                'relation_type' => 2,
                'num' => round(dbconfig('register_give_equity_num')),
                'equity_certificate_no' => 'ZX'.mt_rand(1000000000, 9999999999),
            ]);
        }
        // 检测注册赠送数字人民币
        if (dbconfig('register_give_digital_yuan_switch') == 1) {
            EquityYuanRecord::create([
                'user_id' => $user['id'],
                'type' => 2,
                'status' => 2,
                'title' => '注册赠送数字人民币',
                'relation_type' => 2,
                'give_time' => time(),
                'num' => round(dbconfig('register_give_digital_yuan_num')),
            ]);
        }

        return out(['token' => $token]);
    }

    public function uploadFile()
    {
        $url = upload_file('file');

        return out(['url' => $url]);
    }

    public function systemInfo()
    {
        $req = request()->post();
        $this->validate($req, [
            'type' => 'number'
        ]);
        $user = User::getUserByToken();

        $banner = Banner::where('status', 1)->order('sort', 'asc')->select();
        $setting = Setting::select();

        $paymentConfRes = [];
        for ($type = 1; $type <= 4; $type++) {
            $paymentConf = PaymentConfig::where('status', 1)->where('type', $type)->where('start_topup_limit', '<=', $user['total_payment_amount'])->order('start_topup_limit', 'desc')->find();
            if (!empty($paymentConf)) {
                $paymentConfRes[] = $paymentConf->toArray();
            }
        }

        /*$paymentConfRes = [];
        $paymentConf = PaymentConfig::where('status', 1)->where('start_topup_limit', '<=', $user['total_topup_amount'])->order('start_topup_limit', 'desc')->find();
        if (!empty($paymentConf)) {
            $paymentConfs = PaymentConfig::where('status', 1)->where('start_topup_limit', $paymentConf['start_topup_limit'])->select();
            // 先找区间
            $paymentConf = [];
            $types = [];
            foreach ($paymentConfs as $v) {
                if (!in_array($v['type'], $types)) {
                    $types[] = $v['type'];
                }
                if (empty($v['fixed_topup_limit'])) {
                    if (empty($paymentConf[$v['type']])) {
                        $paymentConf[$v['type']]['single_topup_min_amount'] = $v['single_topup_min_amount'];
                        $paymentConf[$v['type']]['single_topup_max_amount'] = $v['single_topup_max_amount'];
                    }
                    else {
                        $paymentConf[$v['type']]['single_topup_min_amount'] = $paymentConf[$v['type']]['single_topup_min_amount'] > $v['single_topup_min_amount'] ? $v['single_topup_min_amount'] : $paymentConf[$v['type']]['single_topup_min_amount'];

                        $paymentConf[$v['type']]['single_topup_max_amount'] = $paymentConf[$v['type']]['single_topup_max_amount'] < $v['single_topup_max_amount'] ? $v['single_topup_max_amount'] : $paymentConf[$v['type']]['single_topup_max_amount'];
                    }
                }
            }
            foreach ($types as $v) {
                $paymentConf[$v]['fixed_topup_limit'] = [];
                if (empty($paymentConf[$v]['single_topup_max_amount']) || $paymentConf[$v]['single_topup_max_amount'] == 0) {
                    $fixed_topup_limit = [];
                    foreach ($paymentConfs as $v1) {
                        if ($v == $v1['type'] && !empty($v1['fixed_topup_limit'])) {
                            $arr = explode(',', $v1['fixed_topup_limit']);
                            $fixed_topup_limit = array_merge($fixed_topup_limit, $arr);
                        }
                    }
                    $paymentConf[$v]['fixed_topup_limit'] = array_values(array_unique($fixed_topup_limit));
                }
            }

            if (!empty($paymentConf)) {
                foreach ($paymentConf as $k => $v) {
                    $v['type'] = $k;
                    $paymentConfRes[] = $v;
                }
            }
        }*/

        $builder =  SystemInfo::where('status', 1);
        if (!empty($req['type'])) {
            $builder->where('type', $req['type']);
            if ($req['type'] == 2) {
                $builder->order('sort', 'asc');
            }
        }

        $system = $builder->order('id', 'desc')->select();

        return out(['banner' => $banner, 'setting' => $setting, 'system' => $system, 'paymentConfig' => $paymentConfRes]);
    }

    public function payNotify()
    {
        $req = request()->post();
        $this->validate($req, [
            'memberid' => 'require',
            'orderid' => 'require',
            'transaction_id' => 'require',
            'amount' => 'require',
            'returncode' => 'require',
            'datetime' => 'require',
            'sign' => 'require',
        ]);

        $sign = $req['sign'];
        unset($req['sign']);
        $my_sign = Payment::builderSign($req);
        if ($my_sign !== $sign) {
            return '签名错误';
        }

        if ($req['returncode'] == '00') {
            $payment = Payment::where('trade_sn', $req['orderid'])->find();
            if ($payment['status'] != 1) {
                echo  'OK';die;
            }
            Db::startTrans();
            try {
                Payment::where('id', $payment['id'])->update(['online_sn' => $req['transaction_id'], 'payment_time' => time(), 'status' => 2]);
                // 投资项目
                if ($payment['product_type'] == 1) {
                    Order::orderPayComplete($payment['order_id']);
                }
                // 充值
                elseif ($payment['product_type'] == 2) {
                    Capital::topupPayComplete($payment['capital_id']);
                }

                // 判断通道是否超过最大限额，超过了就关闭通道
                PaymentConfig::checkMaxPaymentLimit($payment['type'], $payment['channel'], $payment['mark']);

                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                throw $e;
            }
        }

        echo  'OK';die;
    }

    public function withdrawNotify()
    {
        $req = request()->post();
        $this->validate($req, [
            'payout_id' => 'require',
            'payout_cl_id' => 'require',
            'platform_id' => 'require',
            'amount' => 'require',
            'fee' => 'require',
            'status' => 'require',
            'sign' => 'require',
        ]);

        $sign = $req['sign'];
        unset($req['sign']);
        if ($sign !== withdraw_builder_sign($req)) {
            return json(['error_code' => '签名错误']);
        }

        $capital = Capital::where('withdraw_sn', $req['payout_cl_id'])->find();
        if (empty($capital)) {
            return json(['error_code' => '订单不存在']);
        }
        if ($capital['status'] != 4) {
            return json(['error_code' => '0000']);
        }

        Capital::where('id', $capital['id'])->update(['online_status' => $req['status']]);
        if ($req['status'] == 3) {
            Capital::where('id', $capital['id'])->update([
                'status' => 2,
                'audit_time' => time(),
            ]);
            // 审核通过把资金日志的提现记录变为已完成
            UserBalanceLog::where('user_id', $capital['user_id'])->where('type', 2)->where('relation_id', $capital['id'])->where('log_type', 1)->where('status', 1)->update(['status' => 2]);
        }

        return json(['error_code' => '0000']);
    }

    public function payNotify2()
    {
        $req = request()->post();
        $this->validate($req, [
            'account_name' => 'require',
            'pay_status' => 'require',
            'out_trade_no' => 'require',
            'amount' => 'require',
            'trade_no' => 'require',
            'sign' => 'require',
        ]);

        $sign = $req['sign'];
        unset($req['sign']);
        $my_sign = Payment::builderSign2($req);
        if ($my_sign !== $sign) {
            return '签名错误';
        }

        if ($req['pay_status'] == 4) {
            $payment = Payment::where('trade_sn', $req['out_trade_no'])->find();
            if ($payment['status'] != 1) {
                return 'success';
            }

            Db::startTrans();
            try {
                Payment::where('id', $payment['id'])->update(['online_sn' => $req['trade_no'], 'payment_time' => time(), 'status' => 2]);
                // 投资项目
                if ($payment['product_type'] == 1) {
                    Order::orderPayComplete($payment['order_id']);
                }
                // 充值
                elseif ($payment['product_type'] == 2) {
                    Capital::topupPayComplete($payment['capital_id']);
                }

                // 判断通道是否超过最大限额，超过了就关闭通道
                PaymentConfig::checkMaxPaymentLimit($payment['type'], $payment['channel'], $payment['mark']);

                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                throw $e;
            }
        }

        return 'success';
    }

    public function payNotify3()
    {
        $req = request()->post();
        $this->validate($req, [
            'memberid' => 'require',
            'orderid' => 'require',
            'transaction_id' => 'require',
            'amount' => 'require',
            'returncode' => 'require',
            'datetime' => 'require',
            'sign' => 'require',
        ]);

        $sign = $req['sign'];
        unset($req['sign'], $req['attach']);
        $my_sign = Payment::builderSign3($req);
        if ($my_sign !== $sign) {
            return '签名错误';
        }

        if ($req['returncode'] == '00') {
            $payment = Payment::where('trade_sn', $req['orderid'])->find();
            if ($payment['status'] != 1) {
                return 'ok';
            }
            Db::startTrans();
            try {
                Payment::where('id', $payment['id'])->update(['online_sn' => $req['transaction_id'], 'payment_time' => time(), 'status' => 2]);
                // 投资项目
                if ($payment['product_type'] == 1) {
                    Order::orderPayComplete($payment['order_id']);
                }
                // 充值
                elseif ($payment['product_type'] == 2) {
                    Capital::topupPayComplete($payment['capital_id']);
                }

                // 判断通道是否超过最大限额，超过了就关闭通道
                PaymentConfig::checkMaxPaymentLimit($payment['type'], $payment['channel'], $payment['mark']);

                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                throw $e;
            }
        }

        return 'ok';
    }

    public function getToken()
    {
        $req = request()->post();
        $this->validate($req, [
            'user_id' => 'require|integer',
        ]);

        if (env('common.environment') === 'local') {
            $token = aes_encrypt(['id' => $req['user_id'], 'time' => time()]);
            return out($token);
        }
    }
}
