<?php

namespace app\api\controller;

use app\model\User;
use app\model\UserSignin;
use Exception;
use think\facade\Db;
use think\model\relation\OneToOne;
use think\facade\Request;
class SigninController extends AuthController
{
    public function userSignin()
    {
        $arr =[
            'api.nhxij.com',
            'api.ojokl.com',
            'api.zcxjh.com',
            'api.actzv.com',  
            'api.fkbya.com',
            'api.hjtojoh.com',
            'api.aojmjfe.com', 
            'api.lht2586.com',
            'api.hprkv.com',
            'api.f3sfu.com',
            'api.smnrg.com',
            'api.gbudew.com',
            'api.spcdew.com',
        ];
        $host = Request::host();
        if(in_array($host,$arr)){
            return out(null, 10001, '提示语请联系客服下载最新app进行签到');
        }
        $user = $this->user;
        $signin_date = date('Y-m-d');

        Db::startTrans();
        try {
            if (UserSignin::where('user_id', $user['id'])->where('signin_date', $signin_date)->lock(true)->count()) {
                return out(null, 10001, '您今天已经签到了');
            }


            // $oneDate = date('Y-m')."-01";
            // $signDates = UserSignin::where('user_id',$user['id'])->where('signin_date','>',$oneDate)->order('signin_date','desc')->select();
            // $signNum=1;
            // $dates = [];
            // foreach($signDates as $date){
            //     $dates[$date['signin_date']] = $date; 
            // }

            // $yesterday = date("Y-m-d");
            // foreach($signDates as $date){
            //    $yesterday = date('Y-m-d',strtotime("-1 day",strtotime($yesterday)));
            //    if(isset($dates[$yesterday])){
            //          $signNum++;
            //    }else{
            //             break;
            //    }   
            // }

            $signin = UserSignin::create([
                'user_id' => $user['id'],
                'signin_date' => $signin_date,
            ]);
            $signNum=1;
            // 添加签到奖励积分
            //User::changeBalance($user['id'], dbconfig('signin_integral'), 17, $signin['id'], 2);
            // 签到奖励数码货币
            //User::changeBalance($user['id'], dbconfig('signin_digital_yuan'), 17, $signin['id'], 3);
            User::changeInc($user['id'],$signNum*10,'digital_yuan_amount',17,$signin['id'],3);

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
            'total_signin_num' => count($list),
            'list' => $list
        ]);
    }
}
