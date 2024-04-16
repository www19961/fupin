<?php

namespace app\admin\controller;

use app\model\GoldEggLuckyUser;
use app\model\PrizeUserLog;
use app\model\Prize;

class GoldEggController extends AuthController
{
    //砸金蛋设置
    public function setting()
    {
        $this->assign('data', Prize::select()->toArray());
        return $this->fetch();  
    }

    //砸金蛋概率设置
    public function prizeSetting()
    {
        $req = request()->param();
        $data = [];
        if (!empty($req['id'])) {
            $data = Prize::where('id', $req['id'])->find();
        }
        $this->assign('data', $data);

        return $this->fetch();
    }

    //砸金蛋概率设置提交
    public function editConfig()
    {
        $req = $this->validate(request(), [
            'id' => 'require|number',
            'rate|概率' => 'require|float',
            'name|奖品名' => 'require',
            'type|类型' => 'require|number',
            'reward|到账金额' => 'float',
        ]);

        $req['rate'] = bcdiv($req['rate'], 100, 4);

        Prize::where('id', $req['id'])->update($req);

        return out();
    }

    //砸金蛋中奖人预设
    // public function luckyUser()
    // {
    //     $req = request()->param();
    //     $builder = GoldEggLuckyUser::alias('l')->field('l.*, p.name, u.phone')->leftJoin('mp_gold_egg_prize p', 'l.prize_id = p.id')->leftJoin('mp_user u', 'l.user_id = u.id')->order('l.id', 'desc');
    //     $list = $builder->paginate(['query' => $req]);
    //     $this->assign('data', $list);
    //     return $this->fetch();
    // }

    //砸金蛋中奖人删除
    // public function luckyDelete()
    // {
    //     $req = $this->validate(request(), [
    //         'id' => 'require|number',
    //     ]);

    //     GoldEggLuckyUser::where('id', $req['id'])->delete();

    //     return out();
    // }

    //砸金蛋中奖人添加
    public function luckyUserAdd()
    {
        $prize = Prize::select();
        $this->assign('prize', $prize);
        return $this->fetch();
    }

    //砸金蛋中奖人添加提交
    // public function luckyUserAddSubmit()
    // {
    //     $req = $this->validate(request(), [
    //         'user_id|userID' => 'require',
    //         'prize_id|奖品' => 'require|float',
    //     ]);
    //     GoldEggLuckyUser::insert($req);

    //     return out();
    // }

    //中奖记录
    public function PrizeUserLog()
    {
        $req = request()->param();
        $builder = PrizeUserLog::alias('l')->field('l.*, p.name, u.phone');

        if (isset($req['prize_id']) && $req['prize_id'] !== '') {
            $builder->where('l.prize_id', $req['prize_id']);
        }

        if (isset($req['user_id']) && $req['user_id'] !== '') {
            $builder->where('l.user_id', $req['user_id']);
        }

        if (isset($req['phone']) && $req['phone'] !== '') {
            $builder->where('u.phone', $req['phone']);
        }
        
        $builder = $builder->leftJoin('mp_prize p', 'l.prize_id = p.id')->leftJoin('mp_user u', 'l.user_id = u.id')->order('l.id', 'desc');
        $list = $builder->paginate(['query' => $req]);
        $prize = Prize::select();
        $this->assign('prize', $prize);
        $this->assign('data', $list);
        $this->assign('req', $req);
        return $this->fetch();
    }
}
