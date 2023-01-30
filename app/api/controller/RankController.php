<?php

namespace app\api\controller;

use app\model\User;
use app\model\UserRelation;
use think\facade\Db;

class RankController extends AuthController
{
    public function teamRankList()
    {
        $relation = UserRelation::alias('r')->field(['count(r.sub_user_id) as team_num', 'r.user_id'])->join('user u', 'u.id = r.sub_user_id')->where('r.level', 1)->where('u.ic_number', '<>', '')->whereTime('r.created_at','month')->group('r.user_id')->order('team_num', 'desc')->limit(20)->select()->toArray();
        $users = [];
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

        return out($users);
    }
}
