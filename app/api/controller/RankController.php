<?php

namespace app\api\controller;

use app\model\User;
use app\model\UserRelation;
use think\facade\Db;

class RankController extends AuthController
{

    
    public function rankList()
    {
        $reward = [
            1=>800,
            2=>500,
            3=>300,
            4=>200,
            5=>100,
            6=>100,
            7=>100,
            8=>100,
            9=>100,
            10=>100,
        ];
        $relation = UserRelation::alias('r')->field(['count(r.sub_user_id) as team_num', 'r.user_id','phone'])->join('user u', 'u.id = r.sub_user_id')->where('r.is_active',1)->group('r.user_id')->order('team_num', 'desc')->limit(100)->select()->toArray();
        $users = [];
        //$rankData = Db::table('mp_active_rank')->field('phone,num as team_num')->select();

        if (!empty($relation)) {
/*             $relation = array_column($relation, 'team_num', 'user_id');

            $user_ids = array_keys($relation);
            array_unshift($user_ids, 'id');
            $str = 'field('.implode(',', $user_ids).')';

            $users = User::field('id,phone')->whereIn('id', $user_ids)->where('status', 1)->order(Db::raw($str))->select()->toArray();
            foreach ($users as $k => $v) {
                $users[$k]['phone'] = substr_replace($v['phone'],'****', 3, 4);
                $users[$k]['team_num'] = $relation[$v['id']];
            } */
        }
        foreach ($relation as $k => &$v) {
            $v['phone'] = substr_replace($v['phone'],'****', 3, 4);
            $v['sort'] = $k+1;
            if($k<=10){
                $v['reward'] = $reward[$k+1];
            }else{
                $v['reward'] = 20;
            }
        }
/*         $column = array_column($relation,'team_num');
        array_multisort($column,SORT_DESC,$users);
        $data = array_slice($users,0,10); */
        return out($relation);
    }

    public function teamRankList2()
    {
        $relation = UserRelation::alias('r')->field(['count(r.sub_user_id) as team_num', 'r.user_id'])->join('user u', 'u.id = r.sub_user_id')->where('r.is_active',1)->whereTime('r.created_at','today')->group('r.user_id')->order('team_num', 'desc')->limit(10)->select()->toArray();
        $users = [];
        $rankData = Db::table('mp_active_rank')->field('phone,num as team_num')->select();

        if (!empty($relation)) {
            $relation = array_column($relation, 'team_num', 'user_id');

            $user_ids = array_keys($relation);
            array_unshift($user_ids, 'id');
            $str = 'field('.implode(',', $user_ids).')';

            $users = User::field('id,phone')->whereIn('id', $user_ids)->where('status', 1)->order(Db::raw($str))->select()->toArray();
            foreach ($users as $k => $v) {
                $users[$k]['phone'] = substr_replace($v['phone'],'****', 3, 4);
                $users[$k]['team_num'] = $relation[$v['id']];
            }
        }
        foreach ($rankData as $k => $v) {
            $v['id'] =0;
            $v['phone'] = substr_replace($v['phone'],'****', 3, 4);
            $users[] = $v;
        }
        $column = array_column($users,'team_num');
        array_multisort($column,SORT_DESC,$users);
        $data = array_slice($users,0,10);
        return out($data);
    }
}
