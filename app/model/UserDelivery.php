<?php

namespace app\model;

use think\Model;

class UserDelivery extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
