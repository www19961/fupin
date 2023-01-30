<?php

namespace app\model;

use think\Model;

class UserRelation extends Model
{
    public function getLevelTextAttr($value, $data)
    {
        $map = config('map.user_relation')['level_map'];
        return $map[$data['level']];
    }

    public function getIsActiveTextAttr($value, $data)
    {
        $map = config('map.user_relation')['is_active_map'];
        return $map[$data['is_active']];
    }

    public static function saveUserRelation($user_id)
    {
        $upUserIds = User::getThreeUpUserId($user_id);
        foreach ($upUserIds as $k => $v) {
            UserRelation::create([
                'user_id' => $v,
                'sub_user_id' => $user_id,
                'level' => $k
            ]);
        }

        return true;
    }

    public function subUser()
    {
        return $this->belongsTo(User::class, 'sub_user_id')->field('id,realname,phone');
    }
}
