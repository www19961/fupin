<?php

namespace app\api\controller;

use app\model\AssetOrder;
use app\model\User;
use app\model\UserReceive;
use app\model\UserSignin;
use Exception;
use think\facade\Db;
use think\facade\Cache;
use think\model\relation\OneToOne;
use think\facade\Request;
class SigninController extends AuthController
{
    public function userSignin()
    {   
        // 每天签到时间为8：00-20：00 早上8点到晚上21点
/*         $timeNum = (int)date('Hi');
        if ($timeNum < 800 || $timeNum > 2100) {
            return out(null, 10001, '签到时间为早上8:00到晚上21:00');
        } */

        $user = $this->user;

        $clickRepeatName = __FUNCTION__ . '-' . $user->id;
        if (Cache::get($clickRepeatName)) {
            return out(null, 10001, '操作频繁，请稍后再试');
        }
        Cache::set($clickRepeatName, 1, 5);

        $user = User::where('id', $user['id'])->find();
        $signin_date = date('Y-m-d');

        Db::startTrans();
        try {
            if (UserSignin::where('user_id', $user['id'])->where('signin_date', $signin_date)->lock(true)->count()) {
                return out(null, 10001, '您今天已经签到了');
            }

            //连签3天获得抽奖机会
            $is_get_reward_times = 0;
            $isSigninYesterday = UserSignin::where('user_id', $user['id'])->where('signin_date', date('Y-m-d', strtotime("-1 day")))->find();
            if ($isSigninYesterday && $isSigninYesterday['is_get_reward_times'] == 0) {
                $isSigninTheDayBeforeYesterday = UserSignin::where('user_id', $user['id'])->where('signin_date', date('Y-m-d', strtotime("-2 day")))->find();
                if ($isSigninTheDayBeforeYesterday && $isSigninTheDayBeforeYesterday['is_get_reward_times'] == 0) {
                    $is_get_reward_times = 1;
                    User::where('id', $user['id'])->inc('reward_times')->update();
                }
            }

            //维护连续签到天数
            if ($isSigninTheDayBeforeYesterday) {
                User::where('id', $user['id'])->inc('continuous_signin')->update();
            } else {
                User::where('id', $user['id'])->date(['continuous_signin' => 1])->update();
                //连签30天奖励
                if ($user['continuous_signin'] + 1 >= 30 && $user['is_get_reward_continuous_signin'] == 0) {
                    User::where('id', $user['id'])->data(['is_get_reward_continuous_signin' => 1])->update();
                    //发放连签30天奖励
                    User::changeBalance($user['id'], 28000, 7);
                }
            }
            

            $signin = UserSignin::create([
                'user_id' => $user['id'],
                'signin_date' => $signin_date,
                'is_get_reward_times' => $is_get_reward_times,
            ]);

            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            throw $e;
        }

        return out();
    }

    /**
     * 首先看恢复资产用户选择的先富后富
     * 没有恢复资产的用户，看是否充值了1500
     */
    public function dayReceive(){
        $user = $this->user;
        $user = User::where('id', $user['id'])->find();
        $signin_date = date('Y-m-d');
        if($user['level'] == 0){
            return out(null, 10001, '共富等级一级才有奖励');
        }
        $is_rich=0;
        $assetOrder = AssetOrder::where('user_id',$user['id'])->where('status',2)->find();
        if($assetOrder){
            $is_rich = $assetOrder['rich'] ==1 ? 1 : 0;
        }

        if($is_rich == 0 && !$assetOrder){
            if($user['invest_amount']>=1500){
                $is_rich = 1;
            }
        }

        Db::startTrans();
        try {
            if (UserReceive::where('user_id', $user['id'])->where('signin_date', $signin_date)->lock(true)->count()) {
                return out(null, 10001, '您今天已经领取了');
            }
            $signin = UserReceive::create([
                'user_id' => $user['id'],
                'signin_date' => $signin_date,
            ]);
            $amount = $is_rich == 1 ? 14 : 1;
            User::changeInc($user['id'],$amount,'signin_balance',17,$signin['id'],3,'',0,1,'QD');
            Db::commit();
           
        } catch (Exception $e) {
            Db::rollback();
            throw $e;
            return out(null,200,$e->getMessage());
        }
        return out();
    }

    public function signinRecord()
    {
        $user = $this->user;
        $time = date("Y-m")."-01";

        $list = UserSignin::where('user_id', $user['id'])->where("signin_date",'>=',$time)->order('id', 'desc')->select()->toArray();
        foreach ($list as &$item) {
            $item['day'] = date('d', strtotime($item['signin_date']));
        }
        return out([
            'total_signin_num' => count($list),
            'list' => $list
        ]);
    }
}
