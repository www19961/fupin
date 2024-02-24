<?php

namespace app\api\controller;

use app\model\Prize;
use think\facade\Cache;
use app\model\UserRelation;
use app\model\User;
use app\model\GoldEggLuckyUser;
use app\model\PrizeUserLog;
use Exception;
use think\facade\Db;

class SigninLotteryController extends AuthController
{
    /**
     * 大转盘抽奖
     */
    public function lottery()
    {
        $user = $this->user;
        $clickRepeatName = __FUNCTION__ . '-' . $user->id;
        if (Cache::get($clickRepeatName)) {
            return out(null, 10001, '操作频繁，请稍后再试');
        }
        Cache::set($clickRepeatName, 1, 5);

        //是否有砸蛋机会
        if ($user['reward_times'] <= 0) {
            return out(null, 10001, '无抽奖机会');
        }

        $prizeArr = Prize::select()->toArray();
        foreach ($prizeArr as $key => $val) 
        { 
            $arr[$val['id']] = $val['rate'] * 100000; 
        } 
        $rid = $this->getRand($arr);
        $prize = $prizeArr[$rid - 1];

        PrizeUserLog::insert([
            'user_id' => $user->id, 
            'prize_id' => $prize['id'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $data = ['prize_id' => $prize['id'], 'name' => $prize['name']];
        return out($data);
    }

    //抽奖
    public function getRand($proArr) { 
        $result = ''; 
        $proSum = array_sum($proArr);  
        foreach ($proArr as $key => $proCur) { 
            $randNum = mt_rand(1, $proSum); 
            if ($randNum <= $proCur) { 
                $result = $key; 
                break; 
            } else { 
                $proSum -= $proCur; 
            }    
        } 
        unset ($proArr); 
        return $result; 
    }

    /**
     * 可抽奖次数
     */
    public function rewardTimes()
    {
        $user = $this->user;
        return out($user['reward_times']);
    }
}