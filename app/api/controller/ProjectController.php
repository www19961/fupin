<?php

namespace app\api\controller;

use app\model\PaymentConfig;
use app\model\Project;

class ProjectController extends AuthController
{
    public function projectList()
    {
        $req = $this->validate(request(), [
            'project_group_id' => 'number'
        ]);
        $data = Project::field('id, name, intro, cover_img, details_img, single_amount, sum_amount, period, support_pay_methods, virtually_progress')
                ->where('status', 1)
                ->where('class',1)
                ->where('project_group_id',$req['project_group_id'] ?? 1)
                ->order(['sort' => 'asc', 'id' => 'desc'])
                ->append(['daily_bonus'])
                ->paginate();
        foreach($data as $item){
            //$item['intro']="";
            //$item['project_income']=$item['sum_amount'];
            $item['project_end_time'] = date("m月d日", strtotime("+{$item['period']} day", strtotime($item['created_at'])));
        }
        return out($data);
    }
    
    public function projectsList()
    {
        $data = Project::where('status', 1)->where('class',2)->order(['sort' => 'asc', 'id' => 'desc'])->append(['total_amount', 'daily_bonus', 'passive_income', 'progress','day_amount'])->paginate();
        foreach($data as $item){
            //$item['intro']="";
            $item['cover_img']=get_img_api($item['cover_img']);
            $item['details_img']=get_img_api($item['details_img']);
        }
        return out($data);
    }

    public function projectDetail()
    {
        $req = $this->validate(request(), [
            'project_id' => 'require|number'
        ]);
        $user = $this->user;

        $data = Project::where('id', $req['project_id'])->where('status', 1)->append(['total_amount', 'daily_bonus', 'passive_income', 'progress','day_amount'])->find()->toArray();

        // 排除不存在支付渠道的支付方式
        foreach ($data['support_pay_methods'] as $k => $v) {
            if (in_array($v, [2,3,4,6])) {
                $type = $v - 1;
                if ($v == 6) {
                    $type = 4;
                }
                if (!PaymentConfig::where('type', $type)->where('status', 1)->where('start_topup_limit', '<=', $user['total_payment_amount'])->count()) {
                    unset($data['support_pay_methods'][$k]);
                }
            }
        }

        return out($data);
    }
    
    public function projectGroupList()
    {
        $req = $this->validate(request(), [
            'project_group_id' => 'require|number'
        ]);
        $user = $this->user;

        $data = Project::where('project_group_id', $req['project_group_id'])->where('status', 1)->append(['total_amount', 'daily_bonus', 'passive_income', 'progress','day_amount'])->select()->toArray();
        $withdrawSum = \app\model\User::cardWithdrawSum($user['id']);
        $recommendId = \app\model\User::cardRecommend($withdrawSum);

        foreach($data as &$item){
            //$item['intro']="";
            $item['card_recommend']=0;
            $item['cover_img']=get_img_api($item['cover_img']);
            $item['details_img']=get_img_api($item['details_img']);
            if($item['project_group_id']==5){
                if($recommendId == $item['id']){
                    $item['card_recommend']=1;
                }
            }
        }
        if($req['project_group_id']==5){
            array_multisort(array_column($data, 'card_recommend'), SORT_DESC, $data);
        }

        return out($data);
    }

    public function groupName(){
        $data = config('map.project.groupName');

        return out($data);
    }


    
        public function PaymentType(){
//        array(
//            1 => '微信',
//            2 => '支付宝',
//            3 => '线上银联',
//            4 => '线下银联',
//        ),
        $wechat_status = PaymentConfig::where('type', 1)->where('status', 1)->find();
        if($wechat_status){
            $wechat = 1;
        }else{
            $wechat = 0;
        }
        $alipay_status = PaymentConfig::where('type', 2)->where('status', 1)->find();
        if($alipay_status){
            $alipay = 1;
        }else{
            $alipay = 0;
        }
        $yinlian_status = PaymentConfig::where('type', 3)->where('status', 1)->find();
        if($yinlian_status){
            $yinlian = 1;
        }else{
            $yinlian = 0;
        }
        $yinlian2_status = PaymentConfig::where('type', 4)->where('status', 1)->find();
        if($yinlian2_status){
            $yinlian2 = 1;
        }else{
            $yinlian2 = 0;
        }
        $yunshan_status = PaymentConfig::where('type', 5)->where('status', 1)->find();
        if($yunshan_status){
            $yunshan = 1;
        }else{
            $yunshan = 0;
        }
        $data = array(['name'=>'微信','id'=>1,'status'=>$wechat],
            ['name'=>'支付宝','id'=>2,'status'=>$alipay],
            ['name'=>'线上银联','id'=>3,'status'=>$yinlian],
            ['name'=>'银行卡','id'=>4,'status'=>$yinlian2],
            ['name'=>'云闪付','id'=>5,'status'=>$yunshan]);

        return out($data);
    }
}
