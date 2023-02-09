<?php

namespace app\model;

use think\Model;
use think\facade\Db;

class User extends Model
{
    public function getStatusTextAttr($value, $data)
    {
        $map = config('map.user')['status_map'];
        return $map[$data['status']];
    }

    public function getLevelTextAttr($value, $data)
    {
        $map = config('map.user')['level_map'];
        return $map[$data['level']];
    }

    public function getIsActiveTextAttr($value, $data)
    {
        $map = config('map.user')['is_active_map'];
        return $map[$data['is_active']];
    }

    public function getActiveDateAttr($value, $data)
    {
        if (!empty($data['active_time'])) {
            return date('Y-m-d H:i:s', $data['active_time']);
        }
        return '';
    }



    public function upUser()
    {
        return $this->belongsTo(User::class, 'up_user_id');
    }

    // 持有股权
    public function getEquityAttr($value, $data)
    {
        if (!empty($data['id'])) {
            return round(EquityYuanRecord::where('user_id', $data['id'])->where('status', 2)->where('type', 1)->sum('num'), 2);
        }
        return 0;
    }

    //推荐奖励总计
    public function getInviteBonus($value,$data){
        if (!empty($data['id'])) {
            return round(UserBalanceLog::where('user_id', $data['id'])->where('type', 9)->sum('change_balance'), 2);
        }
        return 0;
    }

    // 持有数字人民币
    public function getDigitalYuanAttr($value, $data)
    {
        if (!empty($data['id'])) {
            return round(EquityYuanRecord::where('user_id', $data['id'])->where('status', 2)->where('type', 2)->sum('num'), 2);
        }
        return 0;
    }

    // 我的分红
    public function getMyBonusAttr($value, $data)
    {
        if (!empty($data['id'])) {
            return round(Order::where('user_id', $data['id'])->whereIn('status', [2,3,4])->sum('gain_bonus'), 2);
        }
        return 0;
    }

    // 累计总分红
    public function getTotalBonusAttr($value, $data)
    {
        if (!empty($data['id'])) {
            return round(Order::where('user_id', $data['id'])->where('status', 4)->sum('gain_bonus'), 2);
        }
        return 0;
    }

    // 分红收益中
    public function getProfitingBonusAttr($value, $data)
    {
        if (!empty($data['id'])) {
            //return round(Order::where('user_id', $data['id'])->whereIn('status', [2,3,4])->sum('gain_bonus'), 2);
            $money1= Order::where('user_id', $data['id'])->whereIn('status', [2,3,4])->sum('sum_amount2');
            $money2= Order::where('user_id', $data['id'])->whereIn('status', [2,3,4])->sum('sum_amount');
            return round($money1+$money2,2);
        }
        return 0;
    }

    // 已兑换股权
    public function getExchangeEquityAttr($value, $data)
    {
        if (!empty($data['id'])) {
            return round(EquityYuanRecord::where('user_id', $data['id'])->where('status', 3)->where('type', 1)->sum('num'), 2);
        }
        return 0;
    }

    // 已兑换数字人民币
    public function getExchangeDigitalYuanAttr($value, $data)
    {
        if (!empty($data['id'])) {
            return round(EquityYuanRecord::where('user_id', $data['id'])->where('status', 3)->where('type', 2)->sum('num'), 2);
        }
        return 0;
    }

    // 总被动收益
    public function getPassiveTotalIncomeAttr($value, $data)
    {
        if (!empty($data['id'])) {
            return round(PassiveIncomeRecord::where('user_id', $data['id'])->whereIn('status', [2, 3])->sum('amount'), 2);
        }
        return 0;
    }
    
    // 总补贴
    public function getSubsidyTotalIncomeAttr($value, $data)
    {
        if (!empty($data['id'])) {
            return round(SubsidyIncomeRecord::where('user_id', $data['id'])->whereIn('status', [2, 3])->sum('amount'), 2);
        }
        return 0;
    }
    
    // 已领取的被动收益
    public function getPassiveReceiveIncomeAttr($value, $data)
    {
        if (!empty($data['id'])) {
            return round(PassiveIncomeRecord::where('user_id', $data['id'])->where('status', 3)->sum('amount'), 2);
        }
        return 0;
    }

    // 未领取的被动收益
    public function getPassiveWaitIncomeAttr($value, $data)
    {
        if (!empty($data['id'])) {
            return round(PassiveIncomeRecord::where('user_id', $data['id'])->where('status', 2)->sum('amount'), 2);
        }
        return 0;
    }

    // 团队人数
    public function getTeamUserNumAttr($value, $data)
    {
        return UserRelation::where('user_id', $data['id'])->count();
    }

    // 团队业绩
    public function getTeamPerformanceAttr($value, $data)
    {
    	$sub_user_ids = UserRelation::where('user_id', $data['id'])->column('sub_user_id');

        if (!empty($sub_user_ids)) {
            //$sql = "select sum(invest_amount) as invest_amount from mp_user where  id in (".join(',', $sub_user_ids).")";
            $sql = "select sum(a.invest_amount)as invest_amount from mp_user as a inner join mp_user_relation as b on a.id=b.sub_user_id where b.user_id=".$data['id'];
            $users = Db::query($sql);
            return round($users[0]['invest_amount'], 2);
        }
        return 0;
    }
// 团队业绩
//    public function getTeamPerformanceAttr($value, $data)
//    {
//        $sub_user_ids = UserRelation::where('user_id', $data['id'])->column('sub_user_id');

//        if (!empty($sub_user_ids)) {
//            $sql = "select sum(a.invest_amount)as invest_amount from mp_user as a inner join mp_user_relation as b on a.id=b.sub_user_id where b.user_id=".$data['id'];
//            $users = Db::query($sql);
//            return round($users[0]['invest_amount'], 2);
//        }
//        return 0;
//	}
    // 用户最大连续签到天数
    public function getMaxContinuitySigninNumAttr($value, $data)
    {
        $signin_dates = UserSignin::where('user_id', $data['id'])->order('id', 'asc')->column('signin_date');
        if (!empty($signin_dates)) {
            return continue_days($signin_dates);
        }
        return 0;
    }

    // 用户充值总金额
    public function getTotalTopupAmountAttr($value, $data)
    {
        return round(Capital::where('user_id', $data['id'])->where('status', 2)->where('type', 1)->sum('amount'), 2);
    }

    // 用户总入金金额
    public function getTotalDepositAmountAttr($value, $data)
    {
        return round(UserBalanceLog::where('user_id', $data['id'])->where('log_type', 1)->whereIn('type', [1,15])->sum('change_balance'), 2);
    }

    // 用户支付总金额 包括后台入金
    public function getTotalPaymentAmountAttr($value, $data)
    {
        $amount1 = Payment::where('user_id', $data['id'])->where('status', 2)->sum('pay_amount');
        $amount2 = UserBalanceLog::where('user_id', $data['id'])->where('log_type', 1)->where('type', 15)->sum('change_balance');
        return round($amount1 + $amount2, 2);
    }

    // 用户可提现余额
    public function getCanWithdrawBalanceAttr($value, $data)
    {
        return round($data['balance'] - $data['topup_balance'], 2);
    }

    // 获取直属下级已实名的人数
    public function getRealSubUserNumAttr($value, $data)
    {
        return User::where('up_user_id', $data['id'])->where('ic_number', '<>', '')->count();
    }

    //通过token获取用户信息
    public static function getUserByToken($is_exit = true)
    {
        if ($token = request()->header('token')) {
            $arr = aes_decrypt($token);
            if (!empty($arr['id'])) {
                $user = User::where('id', $arr['id'])->find();
                if (empty($user)){
                    exit_out(null, 601, '账号不存在');
                }
                if ($user['status'] == 0){
                    exit_out(null, 602, '账号已被冻结');
                }

                return $user;
            }
        }

        if ($is_exit) {
            exit_out(null, 403, '认证失效，请重新登录');
        }

        return [];
    }

    // 改变用户余额或积分,加$change_balance为正数 ，减$change_balance为负数
    public static function changeBalance($user_id, $change_balance, $type, $relation_id = 0, $log_type = 1, $remark = '', $admin_user_id = 0)
    {
        if (!empty($change_balance) && $change_balance != 0) {
            $user = User::where('id', $user_id)->find();

            $field = $log_type == 1 ? 'balance' : 'integral';
            $after_balance = $user[$field] + $change_balance;
            if ($after_balance < 0) {
                $after_balance = 0;
                $change_balance = 0 - $user[$field];
            }

            // 如果是充值 就要添加充值金额
            if ($log_type == 1 && in_array($type, [1, 15])) {
                User::where('id', $user_id)->inc($field, $change_balance)->inc('topup_balance', $change_balance)->update();
            }
            // 如果是购买项目，优先扣充值余额里面的钱
            elseif ($log_type == 1 && $type == 3) {
                $real_balance = 0 - $change_balance;
                $change_topup_balance = $user['topup_balance'] > $real_balance ? $real_balance : $user['topup_balance'];
                $change_topup_balance = 0 - $change_topup_balance;
                User::where('id', $user_id)->inc($field, $change_balance)->inc('topup_balance', $change_topup_balance)->update();
            }
            // 手动出金 先扣可提现金额，再扣充值金额
            elseif ($log_type == 1 && $type == 16) {
                $real_balance = 0 - $change_balance;
                if ($real_balance > $user['can_withdraw_balance']) {
                    $real_topup_balance = $real_balance - $user['can_withdraw_balance'];
                    $real_topup_balance = 0 - $real_topup_balance;
                    User::where('id', $user_id)->inc($field, $change_balance)->inc('topup_balance', $real_topup_balance)->update();
                }
                else {
                    User::where('id', $user_id)->inc($field, $change_balance)->update();
                }
            }
            else {
                User::where('id', $user_id)->inc($field, $change_balance)->update();
            }

            $status = $type == 2 ? 1 : 2;
            if ($type == 13) {
                $status = 3;
            }
            UserBalanceLog::create([
                'user_id' => $user_id,
                'type' => $type,
                'log_type' => $log_type,
                'relation_id' => $relation_id,
                'before_balance' => $user[$field],
                'change_balance' => $change_balance,
                'after_balance' => $after_balance,
                'remark' => $remark,
                'admin_user_id' => $admin_user_id,
                'status' => $status,
            ]);

            // 判断用户升级逻辑
           //shanchu
        }

        return true;
    }

    // 获取用户的上3级的用户id
    public static function getThreeUpUserId($user_id)
    {
        $level = [];
        $user = User::field('id,up_user_id')->where('id', $user_id)->find();
        if (!empty($user['up_user_id'])) {
            $upUser1 = User::field('id,up_user_id')->where('id', $user['up_user_id'])->find();
            $level[1] = $upUser1['id'];
            if (!empty($upUser1['up_user_id'])) {
                $level[2] = $upUser1['up_user_id'];
                $upUser2 = User::field('id,up_user_id')->where('id', $upUser1['up_user_id'])->find();
                if (!empty($upUser2['up_user_id'])) {
                    $level[3] = $upUser2['up_user_id'];
                }
            }
        }

        return $level;
    }
}
