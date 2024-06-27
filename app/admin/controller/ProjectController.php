<?php

namespace app\admin\controller;

use app\model\Project;
use app\model\ProjectItem;

class ProjectController extends AuthController
{
    public function projectList()
    {
        $req = request()->param();

        $builder = Project::order(['id' => 'desc']);
        if (isset($req['project_id']) && $req['project_id'] !== '') {
            $builder->where('id', $req['project_id']);
        }
        if (isset($req['project_group_id']) && $req['project_group_id'] !== '') {
            $builder->where('project_group_id', $req['project_group_id']);
        }
        if (isset($req['name']) && $req['name'] !== '') {
            $builder->where('name', $req['name']);
        }
        if (isset($req['status']) && $req['status'] !== '') {
            $builder->where('status', $req['status']);
        }
        if (isset($req['is_recommend']) && $req['is_recommend'] !== '') {
            $builder->where('is_recommend', $req['is_recommend']);
        }

        $data = $builder->paginate(['query' => $req])->each(function($item) {
            $item['list'] = ProjectItem::where('project_id', $item['id'])->select()->toArray();
            return $item;
        });

        $groups = config('map.project.group');
        $this->assign('groups',$groups);

        $this->assign('req', $req);
        $this->assign('data', $data);

        return $this->fetch();
    }

    public function showProject()
    {
        $req = request()->param();
        $data = [];
        if (!empty($req['id'])) {
            $data = Project::where('id', $req['id'])->find();
            $data['list'] = ProjectItem::where('project_id', $data['id'])->select()->toArray();
        }
        //赠送项目
        $give = Project::select();
        if(!empty($data['give'])){
            $data['give'] = json_decode($data['give'],true);
        }
        $groups = config('map.project.group');
        $this->assign('groups',$groups);
        $types = config('map.project.type');
        $this->assign('types',$types);
        $this->assign('give',$give);
        $this->assign('data', $data);

        return $this->fetch();
    }

    public function addProject()
    {
        $type = request()->param('type');
        if (in_array($type, [1, 2, 3, 4])) {
            $req = $this->validate(request(), [
                'name|项目名称' => 'require|max:100',
                'price|金额' => 'require',
                'type|分类' => 'require',
                'days|周期' => 'require',
                'fupin_reward|国家扶贫金' => 'require',
                'sort|排序号' => 'integer',
                'reward|总收益金额' => 'require',
                'rate|进度' => 'require',
                'is_gift|赠送' => 'require',
                'is_circle|周期产品' => 'require',
                'multiple|倍数' => 'require',
            ]);
            $req['intro'] = request()->param('intro', '');
            
            $req['cover_img'] = upload_file('cover_img');
            $req['created_at'] = date('Y-m-d H:i:s');
            $req['details_img'] = upload_file('details_img');
            $insId = Project::insertGetId($req);
            
            foreach ($req['price'] as $k => $value) {
                $arr[$k]['price'] = $req['price'][$k];
                $arr[$k]['reward'] = $req['reward'][$k];
                $arr[$k]['days'] = $req['days'][$k];
                $arr[$k]['fupin_reward'] = $req['fupin_reward'][$k];
                $arr[$k]['project_id'] = $insId;
                $arr[$k]['is_circle'] = $req['is_circle'];
                $arr[$k]['multiple'] = $req['multiple'];
            }
            ProjectItem::insertAll($arr);
        } elseif (in_array($type, [5])) {
            $req = $this->validate(request(), [
                'name|项目名称' => 'require|max:100',
                'price|金额' => 'require',
                'type|分类' => 'require',
                'sort|排序号' => 'integer',
                'years|期限' => 'require',
                'daily_rate|日收益率' => 'require',
                'fupin_reward|国家扶贫金' => 'require',
            ]);
            $req['created_at'] = date('Y-m-d H:i:s');
            $insId = Project::insertGetId($req);
        }
        
        return out();
    }

    public function editProject()
    {
        $type = request()->param('type');
        if (in_array($type, [1, 2, 3, 4])) {
            $req = $this->validate(request(), [
                'id' => 'require|number',
                'name|项目名称' => 'require|max:100',
                'type|分类' => 'require|max:100',
                'price|金额' => 'require',
                'fupin_reward|周期' => 'require',
                'days|周期' => 'require',
                'sort|排序号' => 'integer',
                'reward|总收益金额' => 'require',
                'rate|进度' => 'require',
                'is_gift|赠送' => 'require',
                'is_circle|周期产品' => 'require',
                'multiple|倍数' => 'require',
            ]);
            if ($img = upload_file('cover_img', false,false)) {
                $req['cover_img'] = $img;
            }
            if($img = upload_file('details_img', false,false)){
                $req['details_img'] = $img;
            }
            Project::where('id', $req['id'])->update($req);

            foreach ($req['price'] as $k => $value) {
                $arr[$k]['price'] = $req['price'][$k];
                $arr[$k]['reward'] = $req['reward'][$k];
                $arr[$k]['days'] = $req['days'][$k];
                $arr[$k]['project_id'] = $req['id'];
                $arr[$k]['fupin_reward'] = $req['fupin_reward'][$k];
                $arr[$k]['is_circle'] = $req['is_circle'];
                $arr[$k]['multiple'] = $req['multiple'];
            }
            ProjectItem::where('project_id', $req['id'])->delete();
            ProjectItem::insertAll($arr);
        } elseif (in_array($type, [5])) {
            $req = $this->validate(request(), [
                'id' => 'require|number',
                'name|项目名称' => 'require|max:100',
                'price|金额' => 'require',
                'type|分类' => 'require',
                'sort|排序号' => 'integer',
                'years|期限' => 'require',
                'daily_rate|日收益率' => 'require',
                'fupin_reward|国家扶贫金' => 'require',
            ]);
            Project::where('id', $req['id'])->update($req);
        }
        return out();
    }

    public function changeProject()
    {
        $req = $this->validate(request(), [
            'id' => 'require|number',
            'field' => 'require',
            'value' => 'require',
        ]);

        Project::where('id', $req['id'])->update([$req['field'] => $req['value']]);

        return out();
    }

    public function delProject()
    {
        $req = $this->validate(request(), [
            'id' => 'require|number'
        ]);

        Project::destroy($req['id']);

        return out();
    }
}
