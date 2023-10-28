<?php

namespace app\admin\controller;

use app\model\SystemInfo;

class SystemInfoController extends AuthController
{
    public function systemInfoList()
    {
        $req = request()->param();

        $builder = SystemInfo::order('id', 'desc')->where('type', '<>', 2);
        if (isset($req['system_info_id']) && $req['system_info_id'] !== '') {
            $builder->where('id', $req['system_info_id']);
        }
        if (isset($req['type']) && $req['type'] !== '') {
            $builder->where('type', $req['type']);
        }
        if (isset($req['status']) && $req['status'] !== '') {
            $builder->where('status', $req['status']);
        }

        $data = $builder->paginate(['query' => $req]);

        $this->assign('req', $req);
        $this->assign('data', $data);

        return $this->fetch();
    }

    public function showSystemInfo()
    {
        $req = request()->param();
        $data = [];
        if (!empty($req['id'])) {
            $data = SystemInfo::where('id', $req['id'])->find();
        }
        $this->assign('data', $data);

        return $this->fetch();
    }

    public function addSystemInfo()
    {
        $req = $this->validate(request(), [
            'type|类型' => 'require|integer',
            'title|标题' => 'require|max:100',
            'content|内容' => 'require',
            'sort|排序号' => 'integer',
            'created_at|创建时间' => 'date',
        ]);
        
        $req['cover_img'] = upload_file('cover_img', false,false);

        SystemInfo::create($req);

        return out();
    }

    public function editSystemInfo()
    {
        $req = $this->validate(request(), [
            'id' => 'require|number',
            'type|类型' => 'require|integer',
            'title|标题' => 'require|max:100',
            'content|内容' => 'require',
            'sort|排序号' => 'integer',
            'created_at|创建时间' => 'date',
        ]);
        if ($cover_img = upload_file('cover_img', false,false)) {
            $req['cover_img'] = $cover_img;
        }
        SystemInfo::where('id', $req['id'])->update($req);

        return out();
    }

    public function changeSystemInfo()
    {
        $req = $this->validate(request(), [
            'id' => 'require|number',
            'field' => 'require',
            'value' => 'require',
        ]);

        SystemInfo::where('id', $req['id'])->update([$req['field'] => $req['value']]);

        return out();
    }

    public function delSystemInfo()
    {
        $req = $this->validate(request(), [
            'id' => 'require|number'
        ]);

        SystemInfo::destroy($req['id']);

        return out();
    }

    public function companyInfoList()
    {
        $req = request()->param();

        $builder = SystemInfo::where('type', 2);
        if (isset($req['system_info_id']) && $req['system_info_id'] !== '') {
            $builder->where('id', $req['system_info_id']);
        }
        if (isset($req['type']) && $req['type'] !== '') {
            $builder->where('type', $req['type']);
        }
        if (isset($req['status']) && $req['status'] !== '') {
            $builder->where('status', $req['status']);
        }

        $data = $builder->order(['sort' => 'asc', 'created_at' => 'desc'])->paginate(['query' => $req]);

        $this->assign('req', $req);
        $this->assign('data', $data);

        return $this->fetch();
    }

    public function addCompanyInfo()
    {
        $req = $this->validate(request(), [
            'title|标题' => 'require|max:100',
            'content|内容' => 'max:9000000',
            'sort|排序号' => 'integer',
            'created_at|创建时间' => 'date',
        ]);

        $req['type'] = 2;
        $req['cover_img'] = upload_file('cover_img');
        if ($video_url = upload_file('video_url', false,false)) {
            $req['video_url'] = $video_url;
        }
        SystemInfo::create($req);

        return out();
    }

    public function editCompanyInfo()
    {
        $req = $this->validate(request(), [
            'id' => 'require|number',
            'title|标题' => 'require|max:100',
            'content|内容' => 'max:9000000',
            'sort|排序号' => 'integer',
            'created_at|创建时间' => 'date',
        ]);

        $req['type'] = 2;
        if ($cover_img = upload_file('cover_img', false,false)) {
            $req['cover_img'] = $cover_img;
        }
/*         if ($video_url = upload_file('video_url', false)) {
            $req['video_url'] = $video_url;
        } */
        $req['video_url'] = request()->param('video_url');
        SystemInfo::where('id', $req['id'])->update($req);

        return out();
    }

    public function showCompanyInfo()
    {
        $req = request()->param();
        $data = [];
        if (!empty($req['id'])) {
            $data = SystemInfo::where('id', $req['id'])->find();
        }
        $this->assign('data', $data);

        return $this->fetch();
    }
}
