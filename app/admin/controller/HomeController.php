<?php

namespace app\admin\controller;

use app\model\Capital;
use app\model\Order;
use app\model\User;
use app\model\UserSignin;

class HomeController extends AuthController
{
    public function index()
    {
        if(!session('is_admin')){
            $this->assign('data', []);
            return $this->fetch();
        }
        $data = $arr = [];

        $today = date('Y-m-d 00:00:00');

        $arr['title'] = '当日注册会员数';
        $arr['value'] = User::where('created_at', '>', $today)->count();
        $arr['title1'] = '注册总会员数';
        $arr['value1'] = User::count();
        $arr['url'] = '';
        $data[] = $arr;
        $arr = [];

        // $arr['title'] = '激活会员数';
        // $arr['value'] = User::where('is_active', 1)->count();
        // $arr['url'] = '';
        // $data[] = $arr;

        $arr['title'] = '投资总金额';
        $arr['value'] = round(Order::where('status', '>', 1)->sum('buy_amount'), 2);
        $arr['url'] = '';
        $data[] = $arr;
        $arr = [];

        $arr['title'] = '当日充值总金额';
        $arr['value'] = round(Capital::where('status', 2)->where('type', 1)->where('created_at', '>', $today)->sum('amount'), 2);
        $arr['title1'] = '充值总金额';
        $arr['value1'] = round(Capital::where('status', 2)->where('type', 1)->sum('amount'), 2);
        $arr['url'] = '';
        $data[] = $arr;
        $arr = [];

        $arr['title'] = '当日提现总金额';
        $arr['value'] = round(0 - Capital::where('status', 2)->where('type', 2)->where('log_type',0)->where('created_at', '>', $today)->sum('amount'), 2);
        $arr['title1'] = '提现总金额';
        $arr['value1'] = round(0 - Capital::where('status', 2)->where('type', 2)->where('log_type',0)->sum('amount'), 2);
        $arr['url'] = '';
        $data[] = $arr;
        $arr = [];

        $arr['title'] = '充值总次数';
        $arr['value'] = Capital::where('status', 2)->where('type', 1)->where('log_type',0)->count();
        $arr['url'] = '';
        $data[] = $arr;

        $arr['title'] = '提现总次数';
        $arr['value'] = Capital::where('status', 2)->where('type', 2)->where('log_type',0)->count();
        $arr['url'] = '';
        $data[] = $arr;
        $arr = [];

        $arr['title'] = '当日签到人数';
        $arr['value'] = UserSignin::where('signin_date', date('Y-m-d'))->count();
        $arr['title1'] = '当日激活人数';
        $arr['value1'] = User::where('active_time', '>=', strtotime(date('Y-m-d 00:00:00')))->count();
        $arr['url'] = '';
        $data[] = $arr;
        $arr = [];

        $this->assign('data', $data);

        return $this->fetch();
    }

    public function uploadSummernoteImg()
    {
        $img_url = upload_file2('img_url',true,false);

        return out(['img_url' => env('app.img_host').$img_url, 'filename' => md5(time()).'.jpg']);
    }
}
