<?php

namespace app\api\controller;

use app\model\User;
use app\model\UserSignin;
use Exception;
use think\facade\Db;

class SigninController extends AuthController
{
    public function userSignin()
    {
        $user = $this->user;
        $signin_date = date('Y-m-d');

        Db::startTrans();
        try {
            if (UserSignin::where('user_id', $user['id'])->where('signin_date', $signin_date)->lock(true)->count()) {
                return out(null, 10001, '您今天已经签到了');
            }

            $signin = UserSignin::create([
                'user_id' => $user['id'],
                'signin_date' => $signin_date,
            ]);

            // 添加签到奖励积分
            User::changeBalance($user['id'], dbconfig('signin_integral'), 17, $signin['id'], 2);
            // 签到奖励数码货币
            User::changeBalance($user['id'], dbconfig('signin_digital_yuan'), 17, $signin['id'], 3);

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

        $list = UserSignin::where('user_id', $user['id'])->order('id', 'desc')->select()->toArray();

        return out([
            'total_signin_num' => count($list),
            'list' => $list
        ]);
    }
}
