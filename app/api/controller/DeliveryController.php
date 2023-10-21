<?php

namespace app\api\controller;

use app\model\UserDelivery;

class DeliveryController extends AuthController
{
    public function userDeliveryList()
    {
        $user = $this->user;
        $data = UserDelivery::where('user_id', $user['id'])->paginate();
        return out($data);
    }

    public function userDeliveryDetail()
    {
        $req = $this->validate(request(), [
            'id' => 'require|number',
        ]);
        $user = $this->user;

        $data = UserDelivery::where('id', $req['id'])->where('user_id', $user['id'])->find();

        return out($data);
    }

    public function saveDelivery()
    {
        $req = $this->validate(request(), [
            //'id' => 'number',
            //'name|收货人' => 'require',
            //'phone|手机号' => 'require|mobile',
            'address|详细地址' => 'require',
        ]);
        $user = $this->user;
        $delivery = UserDelivery::where('user_id', $user['id'])->find();
        if ($delivery) {
            UserDelivery::where('user_id', $user['id'])->update($req);
        }
        else {
            $req['user_id'] = $user['id'];
            $req['phone'] = $user['phone'];
            $req['name'] = $user['realname'];
            UserDelivery::create($req);
        }

        return out();
    }

    public function delDelivery()
    {
        $req = $this->validate(request(), [
            'id' => 'require|number',
        ]);
        $user = $this->user;

        UserDelivery::where('id', $req['id'])->where('user_id', $user['id'])->delete();

        return out();
    }
}
