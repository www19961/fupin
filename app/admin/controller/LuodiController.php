<?php

namespace app\admin\controller;

use app\model\Project;
use app\model\Luodi;
use app\model\ProjectItem;

class LuodiController extends AuthController
{
    public function list()
    {
        $req = request()->param();

        $builder = Luodi::order(['id' => 'desc']);
        
        
        if (isset($req['name']) && $req['name'] !== '') {
            $builder->where('name', $req['name']);
        }
        if (isset($req['phone']) && $req['phone'] !== '') {
            $builder->where('phone', $req['phone']);
        }
        if (isset($req['qq']) && $req['qq'] !== '') {
            $builder->where('qq', $req['qq']);
        }

        $data = $builder->paginate(['query' => $req]);

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
        $req = $this->validate(request(), [
            'name|项目名称' => 'require|max:100',
            'price|金额' => 'require',
            'type|分类' => 'require',
            // 'min_amount|最小购买金额' => 'float',
            // 'max_amount|最大购买金额' => 'float',
            //'single_integral|单份积分' => 'integer',
            //'total_num|总份数' => 'require|integer',
            //'sham_buy_num|虚拟购买份数' => 'integer',
            // 'daily_bonus_ratio|单份日分红金额' => 'float',
            // 'dividend_cycle|分红周期' => 'max:32',
            'days|周期' => 'require',
            'fupin_reward|国家扶贫金' => 'require',
            //'single_gift_equity|单份赠送股权' => 'integer',
            // 'single_gift_digital_yuan|单份赠送国家津贴' => 'integer',
            // 'is_recommend|是否推荐' => 'require|integer',
            //'give|赠送项目' => 'max:100',
            // 'support_pay_methods|支付方式' => 'require|max:100',
            'sort|排序号' => 'integer',
            'reward|总收益金额' => 'require',
            'rate|进度' => 'require',
            // 'virtually_progress|虚拟进度' => 'integer',
            // 'total_quota|总名额' => 'max:32',
            // 'remaining_quota|剩余名额' => 'max:32',
            // 'quota_level|限购等级' => 'max:32',
            // 'sale_status|销售状态' => 'max:32',
            // 'sale_time|预售时间' => 'max:32',
        ]);
        $req['intro'] = request()->param('intro', '');
        // $methods = explode(',', $req['support_pay_methods']);
/*         if (in_array(5, $methods) && empty($req['single_integral'])) {
            return out(null, 10001, '支付方式包含积分兑换，单份积分必填');
        } */
        // $req['support_pay_methods'] = json_encode($methods);
/*         if(!empty(array_filter($req['give']))){
            $req['give'] = json_encode(array_filter($req['give']));
        }else{
            $req['give'] = 0;
        } */
        // if(empty($req['sale_time'])) {
        //     $req['sale_time'] = null;
        // }
        
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
        }
        ProjectItem::insertAll($arr);
        
        return out();
    }

    public function editProject()
    {
        $req = $this->validate(request(), [
            'id' => 'require|number',
            'name|项目名称' => 'require|max:100',
            'type|分类' => 'require|max:100',
            'price|金额' => 'require',
            'fupin_reward|周期' => 'require',
            // 'min_amount|最小购买金额' => 'float',
            // 'max_amount|最大购买金额' => 'float',
            //'single_integral|单份积分' => 'integer',
            //'total_num|总份数' => 'require|integer',
            //'sham_buy_num|虚拟购买份数' => 'integer',
            // 'daily_bonus_ratio|单份日分红金额' => 'float',
            // 'dividend_cycle|分红周期' => 'max:32',
            'days|周期' => 'require',
            //'single_gift_equity|单份赠送股权' => 'integer',
            // 'single_gift_digital_yuan|单份赠送国家津贴' => 'integer',
            // 'is_recommend|是否推荐' => 'require|integer',
            //'give|赠送项目' => 'max:100',
            // 'support_pay_methods|支持的支付方式' => 'require|max:100',
            'sort|排序号' => 'integer',
            'reward|总收益金额' => 'require',
            'rate|进度' => 'require',
            //'bonus_multiple|奖励倍数' => 'require|>=:0',
            // 'virtually_progress|虚拟进度' => 'integer',
            // 'total_quota|总名额' => 'max:32',
            // 'remaining_quota|剩余名额' => 'max:32',
            // 'quota_level|限购等级' => 'max:32',
            // 'sale_status|销售状态' => 'max:32',
            // 'sale_time|预售时间' => 'max:32',
        ]);
        // $req['intro'] = request()->param('intro', '');
        // $methods = explode(',', $req['support_pay_methods']);
/*         if (in_array(5, $methods) && empty($req['single_integral'])) {
            return out(null, 10001, '支付方式包含积分兑换，单份积分必填');
        } */
        // $req['support_pay_methods'] = json_encode($methods);

        // if(empty($req['sale_time'])) {
        //     $req['sale_time'] = null;
        // }
       /*  if(!empty(array_filter($req['give']))){
            $req['give'] = json_encode(array_filter($req['give']));
        }else{
            $req['give'] = 0;
        } */
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
        }
        ProjectItem::where('project_id', $req['id'])->delete();
        ProjectItem::insertAll($arr);

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
