<?php

namespace app\api\controller;

use app\model\PaymentConfig;
use app\model\Project;

class ProjectController extends AuthController
{
    public function projectList()
    {
        $data = Project::where('status', 1)->where('class',1)->order(['sort' => 'asc', 'id' => 'desc'])->append(['total_amount', 'daily_bonus', 'passive_income', 'progress'])->paginate();
        return out($data);
    }
    
    public function projectsList()
    {
        $data = Project::where('status', 1)->where('class',2)->order(['sort' => 'asc', 'id' => 'desc'])->append(['total_amount', 'daily_bonus', 'passive_income', 'progress','day_amount'])->paginate();
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
