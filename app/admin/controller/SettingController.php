<?php

namespace app\admin\controller;

use app\model\Setting;

class SettingController extends AuthController
{
    public function settingList()
    {
        $req = request()->param();

        $builder = Setting::order('id', 'asc');
        if (isset($req['setting_id']) && $req['setting_id'] !== '') {
            $builder->where('id', $req['setting_id']);
        }
        if (isset($req['key']) && $req['key'] !== '') {
            $builder->where('key', $req['key']);
        }

        $data = $builder->select();

        $this->assign('req', $req);
        $this->assign('data', $data);

        return $this->fetch();
    }

    public function showSetting()
    {
        $req = request()->param();
        $data = [];
        if (!empty($req['id'])) {
            $data = Setting::where('id', $req['id'])->find();
        }
        $this->assign('data', $data);

        return $this->fetch();
    }

    public function editSetting()
    {
        $req = $this->validate(request(), [
            'id' => 'require|number',
            'value|配置值' => 'require|max:500',
        ]);

        Setting::where('id', $req['id'])->update($req);

        return out();
    }
}
