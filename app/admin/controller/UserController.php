<?php

namespace app\admin\controller;

use app\model\Capital;
use app\model\EquityYuanRecord;
use app\model\User;
use app\model\UserRelation;

class UserController extends AuthController
{
    public function userList()
    {
        $req = request()->param();

        $builder = User::order('id', 'desc');
        if (isset($req['user_id']) && $req['user_id'] !== '') {
            $builder->where('id', $req['user_id']);
        }
        if (isset($req['up_user']) && $req['up_user'] !== '') {
            $user_ids = User::where('phone', $req['up_user'])->column('id');
            $user_ids[] = $req['up_user'];
            $builder->whereIn('up_user_id', $user_ids);
        }
        if (isset($req['phone']) && $req['phone'] !== '') {
            $builder->where('phone', $req['phone']);
        }
        if (isset($req['invite_code']) && $req['invite_code'] !== '') {
            $builder->where('invite_code', $req['invite_code']);
        }
        if (isset($req['realname']) && $req['realname'] !== '') {
            $builder->where('realname', $req['realname']);
        }
        if (isset($req['level']) && $req['level'] !== '') {
            $builder->where('level', $req['level']);
        }
        if (isset($req['is_active']) && $req['is_active'] !== '') {
            if ($req['is_active'] == 0) {
                $builder->where('is_active', 0);
            }
            else {
                $builder->where('is_active', 1);
            }
        }

        $data = $builder->paginate(['query' => $req]);

        $this->assign('req', $req);
        $this->assign('data', $data);

        return $this->fetch();
    }

    public function showUser()
    {
        $req = request()->param();
        $data = [];
        if (!empty($req['id'])) {
            $data = User::where('id', $req['id'])->find();
        }
        $this->assign('data', $data);

        return $this->fetch();
    }

    public function editUser()
    {
        $req = $this->validate(request(), [
            'id' => 'require|number',
            'password|登录密码' => 'max:50',
            'pay_password|支付密码' => 'max:50',
            'realname|实名认证姓名' => 'max:50',
            'ic_number|身份证号' => 'max:50',
        ]);

        if (empty($req['password'])) {
            unset($req['password']);
        }
        else {
            $req['password'] = sha1(md5($req['password']));
        }

        if (empty($req['pay_password'])) {
            unset($req['pay_password']);
        }
        else {
            $req['pay_password'] = sha1(md5($req['pay_password']));
        }
        if (empty($req['realname']) && !empty($req['ic_number'])) {
            return out(null, 10001, '实名和身份证号必须同时为空或同时不为空');
        }
        if (!empty($req['realname']) && empty($req['ic_number'])) {
            return out(null, 10001, '实名和身份证号必须同时为空或同时不为空');
        }

        // 判断给直属上级额外奖励
        if (!empty($req['realname']) && !empty($req['ic_number'])) {
            if (User::where('ic_number', $req['ic_number'])->where('id', '<>', $req['id'])->count()) {
                return out(null, 10001, '该身份证号已经实名过了');
            }
            
            $user = User::where('id', $req['id'])->find();
            if (!empty($user['up_user_id']) && empty($user['ic_number'])) {
                User::changeBalance($user['up_user_id'], dbconfig('direct_recommend_reward_amount'), 7, $user['id']);
            }
        }

        User::where('id', $req['id'])->update($req);

        // 把注册赠送的股权给用户
        EquityYuanRecord::where('user_id', $req['id'])->where('type', 1)->where('status', 1)->where('relation_type', 2)->update(['status' => 2, 'give_time' => time()]);

        return out();
    }

    public function changeUser()
    {
        $req = $this->validate(request(), [
            'id' => 'require|number',
            'field' => 'require',
            'value' => 'require',
        ]);

        User::where('id', $req['id'])->update([$req['field'] => $req['value']]);

        return out();
    }

    public function editPhone(){
        if(request()->isPost()){
            $req = $this->validate(request(), [
                'user_id'=>'require',
                'phone|手机号' => 'require|mobile',
            ]);
            $new = User::where('phone',$req['phone'])->find();
            if($new){
                return out(null,10001,'已有的手机号');
            }
            $user = User::where('id',$req['user_id'])->find();
            $ret = User::where('id',$req['user_id'])->update(['phone'=>$req['phone'],'prev_phone'=>$user['phone']]);
            return out();
        }else{
            $req = $this->validate(request(), [
                'user_id'=>'require',
            ]);
            $user = User::where('id',$req['user_id'])->find();
            $this->assign('data', $user);

            return $this->fetch();
        }
    }

    public function showChangeBalance()
    {
        $req = request()->get();
        $this->validate($req, [
            'user_id' => 'require|number',
            'type' => 'require|in:15,16',
        ]);

        $this->assign('req', $req);

        return $this->fetch();
    }

    public function addBalance()
    {
        $req = request()->post();
        $this->validate($req, [
            'user_id' => 'require|number',
            'money' => 'require|float',
            'type'=>'require|number',
            'remark' => 'max:50',
        ]);
        $adminUser = $this->adminUser;
        $filed = 'balance';
        $log_type = 0;
        $balance_type = 1;
        $text = '余额';
        switch($req['type']){
            case 1:
                $filed = 'balance';
                $log_type = 1;
                $balance_type = 15;
                break;
            case 2:
                $filed = 'team_bonus_balance';
                $log_type = 2;
                $balance_type = 8;
                $text = '团队奖励';
                break;
            case 3:
                $filed = 'income_balance';
                $log_type = 6;
                $balance_type = 6;
                $text = '收益';
                break;
            case 4:
                $filed = 'digital_yuan_amount';
                $log_type = 3;
                $balance_type = 5;
                $text = '国务院津贴';
                break;
            default:
                return out(null, 10001, '类型错误');
        }
        //User::changeBalance($req['user_id'], $req['money'], 15, 0, 1, $req['remark']??'', $adminUser['id']);
        $text = isset($req['remark']) || $req['remark']==''?'管理员充值'.$text:$req['remark'];
        User::changeInc($req['user_id'],$req['money'],$filed,$balance_type,0,$log_type,$text,$adminUser['id']);

        return out();
    }

    public function deductBalance()
    {
        $req = request()->post();
        $this->validate($req, [
            'user_id' => 'require|number',
            'money' => 'require|float',
            'remark' => 'max:50',
        ]);
        $adminUser = $this->adminUser;

        $user = User::where('id', $req['user_id'])->find();
        if ($user['balance'] < $req['money']) {
            return out(null, 10001, '用户余额不足');
        }

        if (Capital::where('user_id', $user['id'])->where('type', 2)->where('pay_channel', 1)->where('status', 1)->count()) {
            return out(null, 10001, '该用户有待审核的手动出金，请先去完成审核');
        }

        // 保存到资金记录表
        Capital::create([
            'user_id' => $user['id'],
            'capital_sn' => build_order_sn($user['id']),
            'type' => 2,
            'pay_channel' => 1,
            'amount' => 0 - $req['money'],
            'withdraw_amount' => $req['money'],
            'audit_remark' => $req['remark'] ?? '',
            'admin_user_id' => $adminUser['id'],
        ]);

        return out();
    }

    public function userTeamList()
    {
        $req = request()->get();

        $user = User::where('id', $req['user_id'])->find();

        $data = ['user_id' => $user['id'], 'phone' => $user['phone']];
        $data['total_num'] = UserRelation::where('user_id', $req['user_id'])->count();
        $data['active_num'] = UserRelation::where('user_id', $req['user_id'])->where('is_active', 1)->count();

        $data['total_num1'] = UserRelation::where('user_id', $req['user_id'])->where('level', 1)->count();
        $data['active_num1'] = UserRelation::where('user_id', $req['user_id'])->where('level', 1)->where('is_active', 1)->count();

        $data['total_num2'] = UserRelation::where('user_id', $req['user_id'])->where('level', 2)->count();
        $data['active_num2'] = UserRelation::where('user_id', $req['user_id'])->where('level', 2)->where('is_active', 1)->count();

        $data['total_num3'] = UserRelation::where('user_id', $req['user_id'])->where('level', 3)->count();
        $data['active_num3'] = UserRelation::where('user_id', $req['user_id'])->where('level', 3)->where('is_active', 1)->count();

        $this->assign('data', $data);

        return $this->fetch();
    }
    public function KKK(){
        $a = User::field('id,up_user_id,is_active')->limit(0,150000)->select()->toArray();
        // $a = User::field('id,up_user_id,is_active')->limit(150000,150000)->select()->toArray();
        // $a = User::field('id,up_user_id,is_active')->limit(300000,150000)->select()->toArray();
        // echo '<pre>';print_r($a);die;
        $re = $this->tree($a,4);
        echo count($re);
    }

    public function tree($data,$pid){
        static $arr = [];
        foreach($data as $k=>$v){
          if($v['up_user_id']==$pid && $v['is_active'] == 1){
            $arr[] = $v;
            unset($data[$k]);
            $this->tree($data,$v['id']);
          }
        }
        return $arr;
  }
}
