<?php

namespace app\admin\controller;

use app\model\Setting;
use think\facade\Cache;


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
        $setting_conf=[];
        Setting::where('id', $req['id'])->update($req);
        $key = Setting::where('id',$req['id'])->value('key');
/*         if($key=='is_req_encypt'){
            config('config.is_req_encypt',$req['value']);
        } */
        $confArr=config('map.system_info.setting_key');
        $setting = Setting::whereIn("key",$confArr)->select();
        foreach($setting as $item){
            $setting_conf[$item['key']] = $item['value'];
        }
        Cache::set('setting_conf', json_decode(json_encode($setting_conf, JSON_UNESCAPED_UNICODE),true), 300);
        return out();
    }

    public function qrcode()
    {
        $req = request()->param();
        $data = [];
        $data = Setting::where('key', 'qrcode')->find();
        $this->assign('data', $data['value'] ?? '');

        return $this->fetch();
    }

    public function editQrcode()
    {
        $req = request()->param();
        $url = upload_file3('qrcode');
        Setting::where('key', 'qrcode')->update(['value' => $url]);
        return out();
    }
}
