<?php

namespace app\admin\controller;

use app\model\Project;

class ProjectController extends AuthController
{
    public function projectList()
    {
        $req = request()->param();

        $builder = Project::order(['sort' => 'desc', 'id' => 'desc'])->where('class',1);
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
        }
        //赠送项目
        $give = Project::select();
        if(!empty($data['give'])){
            $data['give'] = json_decode($data['give'],true);
        }
        $this->assign('give',$give);
        $this->assign('data', $data);

        return $this->fetch();
    }

    public function addProject()
    {
        $req = $this->validate(request(), [
            'project_group_id|项目分组ID' => 'require|integer',
            'name|项目名称' => 'require|max:100',
            'single_amount|单份金额' => 'require|float',
            //'single_integral|单份积分' => 'integer',
            //'total_num|总份数' => 'require|integer',
            //'sham_buy_num|虚拟购买份数' => 'integer',
            //'daily_bonus_ratio|单份日分红金额' => 'require|float',
            'period|周期' => 'require|number|>:0',
            //'single_gift_equity|单份赠送股权' => 'integer',
            'single_gift_digital_yuan|单份赠送国家津贴' => 'integer',
            'is_recommend|是否推荐' => 'require|integer',
            //'give|赠送项目' => 'max:100',
            'support_pay_methods|支付方式' => 'require|max:100',
            'sort|排序号' => 'integer',
            'sum_amount|总补贴金额' => 'require|float',
        ]);

        $methods = explode(',', $req['support_pay_methods']);
/*         if (in_array(5, $methods) && empty($req['single_integral'])) {
            return out(null, 10001, '支付方式包含积分兑换，单份积分必填');
        } */
        $req['support_pay_methods'] = json_encode($methods);
/*         if(!empty(array_filter($req['give']))){
            $req['give'] = json_encode(array_filter($req['give']));
        }else{
            $req['give'] = 0;
        } */
        $req['cover_img'] = upload_file('cover_img');
        $req['details_img'] = upload_file('details_img');
        Project::create($req);

        return out();
    }

    public function editProject()
    {
        $req = $this->validate(request(), [
            'id' => 'require|number',
            'project_group_id|项目分组ID' => 'require|integer',
            'name|项目名称' => 'require|max:100',
            'single_amount|单份金额' => 'require|float',
            //'single_integral|单份积分' => 'integer',
            //'total_num|总份数' => 'require|integer',
            //'sham_buy_num|虚拟购买份数' => 'integer',
            //'daily_bonus_ratio|单份日分红金额' => 'require|float',
            'period|周期' => 'require|number|>:0',
            //'single_gift_equity|单份赠送股权' => 'integer',
            'single_gift_digital_yuan|单份赠送国家津贴' => 'integer',
            'is_recommend|是否推荐' => 'require|integer',
            //'give|赠送项目' => 'max:100',
            'support_pay_methods|支持的支付方式' => 'require|max:100',
            'sort|排序号' => 'integer',
            'sum_amount|总补贴金额' => 'require|float',
            //'bonus_multiple|奖励倍数' => 'require|>=:0',
        ]);

        $methods = explode(',', $req['support_pay_methods']);
/*         if (in_array(5, $methods) && empty($req['single_integral'])) {
            return out(null, 10001, '支付方式包含积分兑换，单份积分必填');
        } */
        $req['support_pay_methods'] = json_encode($methods);
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
