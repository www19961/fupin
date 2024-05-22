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

            //连签3天获得抽奖机会 改为 每天签到获得抽奖机会
            // $is_get_reward_times = 0;   
            $isSigninYesterday = UserSignin::where('user_id', $user['id'])->where('signin_date', date('Y-m-d', strtotime("-1 day")))->find();
            // if ($isSigninYesterday && $isSigninYesterday['is_get_reward_times'] == 0) {
            //     $isSigninTheDayBeforeYesterday = UserSignin::where('user_id', $user['id'])->where('signin_date', date('Y-m-d', strtotime("-2 day")))->find();
            //     if ($isSigninTheDayBeforeYesterday && $isSigninTheDayBeforeYesterday['is_get_reward_times'] == 0) {
            //         $is_get_reward_times = 1;
                    User::where('id', $user['id'])->inc('reward_times')->update();
            //     }
            // }

            User::changeInc($user['id'], 50, 'specific_fupin_balance', 17, $user['id'], 3);

            //维护连续签到天数
            if ($isSigninYesterday) {
                User::where('id', $user['id'])->inc('continuous_signin')->update();
                //连签30天奖励
                if ($user['continuous_signin'] + 1 >= 30 && $user['is_get_reward_continuous_signin'] == 0) {
                    User::where('id', $user['id'])->data(['is_get_reward_continuous_signin' => 1])->update();
                    //发放连签30天奖励
                    User::changeInc($user['id'], 28000, 'specific_fupin_balance', 35, $user['id'], 3);
                }
            } else {
                User::where('id', $user['id'])->data(['continuous_signin' => 1])->update();
            }
            
            $signin = UserSignin::create([
                'user_id' => $user['id'],
                'signin_date' => $signin_date,
                // 'is_get_reward_times' => $is_get_reward_times,
                'is_get_reward_times' => 0,
            ]);

            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            throw $e;
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
            'total_signin_num' => UserSignin::where('user_id', $user['id'])->count(),
            'list' => $list
        ]);
    }
}
