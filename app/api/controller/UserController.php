<?php

namespace app\api\controller;

use app\model\Apply;
use app\model\AssetOrder;
use app\model\EquityYuanRecord;
use app\model\LevelConfig;
use app\model\Order;
use app\model\PaymentConfig;
use app\model\Project;
use app\model\User;
use app\model\UserBalanceLog;
use app\model\UserRelation;
use app\model\KlineChartNew;
use app\model\Capital;
use app\model\Certificate;
use app\model\Payment;
use app\model\UserDelivery;
use app\model\WalletAddress;
use think\facade\Db;
use Exception;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use think\facade\App;

class UserController extends AuthController
{
    public function userInfo()
    {
        $user = $this->user;

        //$user = User::where('id', $user['id'])->append(['equity', 'digital_yuan', 'my_bonus', 'total_bonus', 'profiting_bonus', 'exchange_equity', 'exchange_digital_yuan', 'passive_total_income', 'passive_receive_income', 'passive_wait_income', 'subsidy_total_income', 'team_user_num', 'team_performance', 'can_withdraw_balance'])->find()->toArray();
        $user = User::where('id', $user['id'])->field('id,phone,realname,up_user_id,is_active,invite_code,ic_number,level,balance,topup_balance,poverty_subsidy_amount,digital_yuan_amount,can_open_digital,team_bonus_balance,created_at,qq,avatar')->find()->toArray();
    
        $user['is_set_pay_password'] = !empty($user['pay_password']) ? 1 : 0;
        $user['address'] = '';
        $user['wallet_address'] = '';
        unset($user['password'], $user['pay_password']);
        $delivery=UserDelivery::where('user_id', $user['id'])->find();
        if($delivery){
            $user['address']=$delivery['address'];
        }
        $wallet_address = WalletAddress::where('user_id', $user['id'])->find();
        if($wallet_address){
            $user['wallet_address']=$wallet_address['address'];
        }

        $user['cash'] = $user['topup_balance']+$user['team_bonus_balance'];
        $user['total_balance'] = $user['cash']+$user['digital_yuan_amount']+ $user['poverty_subsidy_amount'];
        $asset = AssetOrder::where('user_id',$user['id'])->where('status',2)->find();
        $user['is_asset'] = $asset?1:0;
       // $user['sum'] = round($user['balance'] + $user['my_bonus'] + $user['passive_wait_income'] + $user['subsidy_total_income']+$user['digital_yuan'],2);
        //$todayPrice = KlineChartNew::getTodayPrice();

        //$user['today_equity_price'] = $todayPrice;
        //$umodel = new User();
        //$user['invite_bonus'] = $umodel->getInviteBonus(0,$user);
        //检测用户升级 投资金额达到 或者  直属下级激活人数达到
        //$user = User::where('id', $user['id'])->find();
        // $new_level = LevelConfig::where('min_topup_amount', '<=', $user['invest_amount'])->order('min_topup_amount', 'desc')->value('level');
        // $zhishuCount = UserRelation::where('user_id',$user['id'])->where('is_active',1)->where('level',1)->count();

        // $zhishu_level = LevelConfig::where('min_direct_sub_active_num', '<=', $zhishuCount)->order('min_direct_sub_active_num', 'desc')->value('level');

        // if ($user['level'] < $new_level) {
        //     User::where('id', $user['id'])->update(['level' => $new_level]);
        // }elseif($user['level'] < $zhishu_level){
        //     User::where('id', $user['id'])->update(['level' => $zhishu_level]);
        // }
/*         $upUserId = $user['up_user_id'];
        $user['up_users'] = [];
        for($i=0;$i<3;$i++){
           if($upUserId==0){
                break;
           }
           $upUser = User::where('id',$upUserId)->field('id,phone,up_user_id')->find();
           if($upUser){
                $upUserId = $upUser['up_user_id'];
                unset($upUser['up_user_id']);
                $user['up_users'][] = $upUser;
           }else{
                break;
           }
           
        }  */
        
        //$subCount = UserRelation::where('user_id',$user['id'])->where('is_active',1)->count();
/*         $medal = Apply::where('user_id',$user['id'])->where('type',1)->find();
        $house = Apply::where('user_id',$user['id'])->where('type',2)->find();
        $car = Apply::where('user_id',$user['id'])->where('type',3)->find();
        $cardAuth = Apply::where('user_id',$user['id'])->where('type',5)->find();
        $user['is_sub_medal'] = $subCount>=500?1:0;
        $user['is_apply_medal'] = $medal?1:0;
        $user['is_apply_house'] = $house?1:0;
        $user['is_apply_car'] = $car?1:0;
        $user['is_three_stage'] = User::isThreeStage($user['id']);
        $user['is_card_order'] = User::isCardOrder($user['id']);
        $user['no_withdraw'] = $user['digital_yuan_amount']+$user['income_balance'];
        $user['is_card_auth'] = $cardAuth?1:0;
        $user['withdraw_sum'] = User::cardWithdrawSum($user['id']); */
/*         $user['up_users'] = [
            ['id'=>'12345','name'=>'13312341234'],
            ['id'=>'12346','name'=>'13312341235'],
            ['id'=>'12347','name'=>'13312341236'],
        ]; */
        return out($user);
    }

    public function applyMedal(){
        $req = $this->validate(request(), [
            'address|详细地址' => 'require',
        ]);
        $user = $this->user;


        UserDelivery::updateAddress($user,$req);
        $subCount = UserRelation::where('user_id',$user['id'])->where('is_active',1)->count();
        if($subCount<500){
            return out(null,10002,'激活人数不足500人');
        }
        $msg = Apply::add($user['id'],1);
        if($msg==""){
            return out();
        }else{
            return out(null,10003,$msg);
        }

    }
    public function applyHouse(){
        $user = $this->user;
        $is_three_stage = User::isThreeStage($user['id']);
        if(!$is_three_stage){
            return out(null,10001,'暂未满足条件');
        }
        $msg = Apply::add($user['id'],2);
        if($msg==""){
            return out();
        }else{
            return out(null,10002,"预约看房申请已提交，请耐心等待，留意好您的手机。");
        }
    }
    public function applyCar(){
        $user = $this->user;
        $count = UserRelation::where('user_id',$user['id'])->where('is_active',1)->count();
        if($count<1000){
            $projectIds = [53,54,55,56,57];
            foreach($projectIds as $v){
                $order = Order::where('user_id',$user['id'])->where('project_group_id',4)->where('project_id',$v)->where('status','>=',2)->find();
                if(!$order){
                    return out(null,10001,'暂未满足条件');
                }
            }
       }
        $msg = Apply::add($user['id'],3);
        if($msg==""){
            return out();
        }else{
            return out(null,10002,"预约提车申请已提交，请耐心等待，留意好您的手机。");
        }
    }

    public function myHouse(){
        $user = $this->user;
        $data = User::myHouse($user['id']);
        if($data['msg']!=''){
            return out(null,10001,$data['msg']);
        }
        $house = $data['house'];
        $coverImg = Project::where('id',$house['project_id'])->value('cover_img');
        $houseFee = \app\model\HouseFee::where('user_id',$user['id'])->find();
        $data = [
            'name'=>$house['project_name'],
            'cover_img'=>$coverImg,
            'is_house_fee'=>$houseFee?1:0,
        ];
        
        return out($data);
    }

    public function cardAuth(){
        $user = $this->user;
        $order = Order::where('user_id',$user['id'])->where('project_group_id',5)->where('status','>=',2)->find();
        if(!$order){
            return out(null,10001,'请先购买办卡项目');
        }
        $req= $this->validate(request(), [
            'realname|真实姓名' => 'require',
            'ic_number|身份证号' => 'require',
        ]);
        if($user['realname']=='' || $user['ic_number']==''){
            return out(null,10002,'请先完成实名认证');
        }
        if($user['realname']!=$req['realname'] || $user['ic_number']!=$req['ic_number']){
            return out(null,10003,'与实名认证信息不一致');
        }
        $msg = Apply::add($user['id'],5);
        if($msg==""){
            return out();
        }else if($msg=="已经申请过了"){
            return out();
        }else{
            return out(null,10004,$msg);
        }
    }

    public function cardProgress(){
        $user = $this->user;
        $apply = Apply::where('user_id',$user['id'])->where('type',5)->find();
        if(!$apply){
            return out(null,10001,'请先开户认证');
        }
        $order = Order::where('user_id',$user['id'])->where('project_group_id',5)->where('status','>=',2)->select();
        $data = [];
        //$ids = [];
        foreach($order as $v){
            // if(isset($ids[$v['project_id']])){
            //     continue;
            // }
            $data[] = [
                'name'=>$v['project_name'],
                'cover_img'=>get_img_api($v['cover_img']),
            ];
            //$ids[$v['project_id']] = 1;
        }
        
        return out($data);
    }


    public function invite(){
        $user = $this->user;
        $host = env('app.host', '');
        $frontHost = env('app.front_host', 'https://h5.zdrxm.com');
       
        $url = "$frontHost/#/pages/system-page/gf_register?invite_code={$user['invite_code']}";
        $img = $user['invite_img'];
        if($img==''){
            $qrCode = QrCode::create($url)
            // 内容编码
            ->setEncoding(new Encoding('UTF-8'))
            // 内容区域大小
            ->setSize(200)
            // 内容区域外边距
            ->setMargin(10);
            // 生成二维码数据对象
            $result = (new PngWriter)->write($qrCode);
            // 直接输出在浏览器中
            // ob_end_clean(); //处理在TP框架中显示乱码问题
            // header('Content-Type: ' . $result->getMimeType());
            // echo $result->getString();
            // 将二维码图片保存到本地服务器
            $today = date("Y-m-d");
            $basePath = App::getRootPath()."public/";
            $path =  "storage/qrcode/$today";
            if(!is_dir($basePath.$path)){
                mkdir($basePath.$path);
            }   
            $name = "{$user['id']}.png";
            $filePath = $basePath.$path.'/'.$name;
            $result->saveToFile($filePath);
            $img = $path.'/'.$name;
            User::where('id',$user['id'])->update(['invite_img'=>$img]);
        }else{
        }
        $img = $host.'/'.$img;
        // 返回 base64 格式的图片
        //$dataUri = $result->getDataUri();
        //echo "<img src='{$dataUri}'>";
        $data=[
            'url'=>$url,
            'img'=>$img,
        ];
        return out($data);
    }
    
    public function hongbao(){
        $user = $this->user;
        $zg = UserRelation::where('user_id',$user['id'])->where('level',1)->where('is_active',1)->select();
        $data = [];
        if(count($zg) >= 10){
            $data['zg'] = 1;
        }else{
            $data['zg'] = 0;
        }
        $sql = 'select user_id from mp_user_relation where is_active=1 and level=1 GROUP BY user_id having count(user_id)>=10';
        $u = Db::query($sql);
        // $data['amount'] = round(100000000 / count($u),2);
        $d = [
            '20221205'=>'225891.28',
            '20221206'=>'236156.19',
            '20221207'=>'278912.01',
            '20221208'=>'300007.22',
            '20221209'=>'326517.59',
            '20221210'=>'353027.95',
            '20221211'=>'379538.32',
            '20221212'=>'406048.68',
            '20221213'=>'432559.05',
            '20221214'=>'459069.41',
            '20221215'=>'485579.78',
            '20221216'=>'512090.14',
            '20221217'=>'538600.51',
            '20221218'=>'565110.87',
            '20221219'=>'591621.24',
            '20221220'=>'618131.60',
            '20221221'=>'644641.97',
            '20221222'=>'671152.33',
            '20221223'=>'697662.70',
            '20221224'=>'724173.06',
            '20221225'=>'750683.43',
            '20221226'=>'777193.79',
            '20221227'=>'803704.16',
            '20221228'=>'830214.52',
            '20221229'=>'856724.89',
            '20221230'=>'883235.25',
            '20221231'=>'909745.62',
            '20220101'=>'936255.98',
            '20220102'=>'962766.35',
            '20220103'=>'989276.71',
            '20220104'=>'1015787.08',
            '20220105'=>'1042297.44',
            '20220106'=>'1068807.81',
            '20220107'=>'1095318.17',
            '20220108'=>'1121828.54',
            '20220109'=>'1148338.90',
            '20220110'=>'1174849.27',
            '20220111'=>'1201359.63',
            '20220112'=>'1227870.00',
            '20220113'=>'1254380.36',
            '20220114'=>'1280890.73',
            '20220115'=>'1307401.09',
            '20220116'=>'1333911.46',
            '20220117'=>'1360421.82',
            '20220118'=>'1386932.19',
            '20220119'=>'1413442.55',
            '20220120'=>'1439952.92',
            '20220121'=>'1466463.28',
            '20220122'=>'1492973.65',
            '20220123'=>'1519484.01'];
        $data['amount'] = $d[date('Ymd')];
        if(!empty($u)){
            $uid = [];
            foreach($u as $v){
                $uid[] = $v['user_id'];
            }
           $phone = User::whereIn('id',$uid)->field('phone,realname')->select();
           foreach($phone as $v){
                $q = substr($v['phone'],0,3);
                $h = substr($v['phone'],7,10);
                $qq = mb_substr($v['realname'],0,1);
                $hh = mb_substr($v['realname'],2);
                $data['list'][] = $q .'****' . $h .'  '.$qq.'*'.$hh;
           }
        }
        return out($data);

    }

    public function wallet(){
        $user = $this->user;
        $umodel = new User();
        //$user['invite_bonus'] = $umodel->getInviteBonus(0,$user);
        $user['total_balance'] = bcadd($user['topup_balance'],$user['balance'],2);
        $map = config('map.user_balance_log')['type_map'];
        $list = UserBalanceLog::where('user_id',$user['id'])
        ->where('log_type',1)->whereIn('type',[1,2,3,13,18,19])
        ->order('created_at','desc')
        ->paginate(10)
        ->each(function($item,$key) use ($map){
            $typeText = $map[$item['type']];
            $item['type_text'] = $typeText;
            if($item['type']==3){
                $projectName = Order::where('id',$item['relation_id'])->value('project_name');
                $item['type_text']=$typeText.$projectName;
            }
            
            return $item;
        });
        $u=[
            'topup_balance'=>$user['topup_balance'],
            'total_balance'=>$user['total_balance'],
            'balance'=>$user['balance'],
        ];
        $data['wallet']=$u;
        $data['list'] = $list;
        return out($data);


    }

    //数字人民币转账
    public function transferAccounts(){
        $req = $this->validate(request(), [
            'type' => 'require|in:1,2,3',//1数字人民币,2 现金充值余额 3 可提现余额
            'realname|对方姓名' => 'max:20',
            'account|对方账号' => 'require',//虚拟币钱包地址
            'money|转账金额' => 'require|number|between:100,100000',
            'pay_password|支付密码' => 'require',
        ]);//type 1 数字人民币，realname 对方姓名，account 对方账号，money 转账金额，pay_password 支付密码
        $user = $this->user;

        if (empty($user['ic_number'])) {
            return out(null, 10001, '请先完成实名认证');
        }
        if (empty($user['pay_password'])) {
            return out(null, 801, '请先设置支付密码');
        }
        if (!empty($req['pay_password']) && $user['pay_password'] !== sha1(md5($req['pay_password']))) {
            return out(null, 10001, '支付密码错误');
        }
        if (!in_array($req['type'], [1,2,3])) {
            return out(null, 10001, '不支持该支付方式');
        }
        if ($user['phone'] == $req['account'] && $req['type']==2) {
            return out(null, 10001, '不能转帐给自己');
        }

        Db::startTrans();
        try {
            //1可用余额（可提现金额） 2 转账余额（充值金额加他人转账的金额）
            //topup_balance充值余额 can_withdraw_balance可提现余额  balance总余额
            $user = User::where('id', $user['id'])->lock(true)->find();//转账人
            if($req['type']==1){
                $wallet =WalletAddress::where('address',$req['account'])->where('user_id','>',0)->find();
                if(!$wallet){
                    exit_out(null, 10002, '目标地址不存在');
                }
                $take = User::where('id', $wallet['user_id'])->lock(true)->find();//收款人
            }else{
                if(!isset($req['realname'])){
                    return out(null, 10001, '请输入对方姓名');
                }
                $take = User::where('phone',$req['account'])->where('realname',$req['realname'])->lock(true)->find();//收款人

            }

            if (!$take) {
                exit_out(null, 10002, '用户不存在');
            }
            if (empty($take['ic_number'])) {
                exit_out(null, 10002, '请收款用户先完成实名认证');
            }
            
            if($req['type'] ==1){
                $field = 'digital_yuan_amount';
                $fieldText = '数字人民币';
                $logType=2;
            } elseif($req['type'] ==2){
                $field = 'topup_balance';
                $fieldText = '现金余额';
                $logType = 1;
             }else{
                 $field = 'team_bonus_balance';
                 $fieldText = '可提现余额';
                 $logType = 1;
             }


            if ($req['money'] > $user[$field]) {
                exit_out(null, 10002, '转账余额不足');
            }
            //转出金额  扣金额 可用金额 转账金额
            $change_balance = 0 - $req['money'];
            

            //2 转账余额（充值金额加他人转账的金额）
            //User::where('id', $user['id'])->inc('balance', $change_balance)->inc($field, $change_balance)->update();
            User::where('id', $user['id'])->inc($field, $change_balance)->update();
            //User::changeBalance($user['id'], $change_balance, 18, 0, 1,'转账余额转账给'.$take['realname']);
            //增加资金明细
            UserBalanceLog::create([
                'user_id' => $user['id'],
                'type' => 18,
                'log_type' => $logType,
                'relation_id' => $take['id'],
                'before_balance' => $user[$field],
                'change_balance' => $change_balance,
                'after_balance' =>  $user[$field]-$req['money'],
                'remark' => '转账'.$fieldText.'转账给'.$take['realname'],
                'admin_user_id' => 0,
                'status' => 2,
                'project_name' => ''
            ]);

            //收到金额  加金额 转账金额
            //User::where('id', $take['id'])->inc('balance', $req['money'])->inc('topup_balance', $req['money'])->update();
            if(in_array($req['type'],[2,3])){
                $field2 = 'topup_balance';
            }else if($req['type'] ==1){
                $field2 = 'digital_yuan_amount';
            }
            User::where('id', $take['id'])->inc($field2, $req['money'])->update();
            //User::changeBalance($take['id'], $req['money'], 18, 0, 1,'接收转账来自'.$user['realname']);
            UserBalanceLog::create([
                'user_id' => $take['id'],
                'type' => 19,
                'log_type' => $logType,
                'relation_id' => $user['id'],
                'before_balance' => $take[$field2],
                'change_balance' => $req['money'],
                'after_balance' =>  $take[$field2]+$req['money'],
                'remark' => '接收'.$fieldText.'来自'.$user['realname'],
                'admin_user_id' => 0,
                'status' => 2,
                'project_name' => ''
            ]);
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            throw $e;
        }
        return out();
    }

    
    //转账2
    public function transferAccounts2(){
        $req = $this->validate(request(), [
            'type' => 'require|in:1,2,3',//1推荐给奖励,2 转账余额（充值金额）3 可提现余额
            //'realname|对方姓名' => 'require|max:20',
            'account|对方账号' => 'require',//虚拟币钱包地址
            'money|转账金额' => 'require|number|between:100,100000',
            'pay_password|支付密码' => 'require',
        ]);//type 1 数字人民币，，realname 对方姓名，account 对方账号，money 转账金额，pay_password 支付密码
        $user = $this->user;

        if (empty($user['ic_number'])) {
            return out(null, 10001, '请先完成实名认证');
        }
        if (empty($user['pay_password'])) {
            return out(null, 801, '请先设置支付密码');
        }
        if (!empty($req['pay_password']) && $user['pay_password'] !== sha1(md5($req['pay_password']))) {
            return out(null, 10001, '支付密码错误');
        }
        if (!in_array($req['type'], [1,2,3])) {
            return out(null, 10001, '不支持该支付方式');
        }
        if ($user['phone'] == $req['account'] && $req['type']==2) {
            return out(null, 10001, '不能转帐给自己');
        }

        Db::startTrans();
        try {
            //1可用余额（可提现金额） 2 转账余额（充值金额加他人转账的金额）
            //topup_balance充值余额 can_withdraw_balance可提现余额  balance总余额
            $user = User::where('id', $user['id'])->lock(true)->find();//转账人
            $wallet =WalletAddress::where('address',$req['account'])->where('user_id','>',0)->find();
            if(!$wallet){
                exit_out(null, 10002, '目标地址不存在');
            }
            $take = User::where('id', $wallet['user_id'])->lock(true)->find();//收款人
            if (!$take) {
                exit_out(null, 10002, '用户不存在');
            }
            if (empty($take['ic_number'])) {
                exit_out(null, 10002, '请收款用户先完成实名认证');
            }
            
            if($req['type'] ==1){
                $field = 'digital_yuan_amount';
                $fieldText = '数字人民币';
                $logType=2;
            }/* elseif($req['type'] ==2){
                $field = 'balance';
                $fieldText = '充值余额';
                $logType = 1;
            } */
            // }else{
            //     $field = 'balance';
            //     $fieldText = '可提现余额';
            // }


            if ($req['money'] > $user[$field]) {
                exit_out(null, 10002, '转账余额不足');
            }
            //转出金额  扣金额 可用金额 转账金额
            $change_balance = 0 - $req['money'];
            

            //2 转账余额（充值金额加他人转账的金额）
            //User::where('id', $user['id'])->inc('balance', $change_balance)->inc($field, $change_balance)->update();
            User::where('id', $user['id'])->inc($field, $change_balance)->update();
            //User::changeBalance($user['id'], $change_balance, 18, 0, 1,'转账余额转账给'.$take['realname']);
            //增加资金明细
            UserBalanceLog::create([
                'user_id' => $user['id'],
                'type' => 18,
                'log_type' => $logType,
                'relation_id' => $take['id'],
                'before_balance' => $user[$field],
                'change_balance' => $change_balance,
                'after_balance' =>  $user[$field]-$req['money'],
                'remark' => '转账'.$fieldText.'转账给'.$take['realname'],
                'admin_user_id' => 0,
                'status' => 2,
                'project_name' => ''
            ]);

            //收到金额  加金额 转账金额
            //User::where('id', $take['id'])->inc('balance', $req['money'])->inc('topup_balance', $req['money'])->update();
            User::where('id', $take['id'])->inc('balance', $req['money'])->update();
            //User::changeBalance($take['id'], $req['money'], 18, 0, 1,'接收转账来自'.$user['realname']);
            UserBalanceLog::create([
                'user_id' => $take['id'],
                'type' => 19,
                'log_type' => 1,
                'relation_id' => $user['id'],
                'before_balance' => $take[$field],
                'change_balance' => $req['money'],
                'after_balance' =>  $take[$field]+$req['money'],
                'remark' => '接收'.$fieldText.'来自'.$user['realname'],
                'admin_user_id' => 0,
                'status' => 2,
                'project_name' => ''
            ]);
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            throw $e;
        }
        return out();
    }

    public function transferList()
    {
        $req = $this->validate(request(), [
            //'status' => 'number',
            //'search_type' => 'number',
        ]);
        $user = $this->user;

        $builder = UserBalanceLog::where('user_id', $user['id'])->whereIn('type', [18,19])->order('created_at','desc')
                    ->paginate(10,false,['query'=>request()->param()]);
        if($builder){
            foreach($builder as $k => $v){
                $builder[$k]['phone'] = User::where('id', $v['relation_id'])->value('phone');
            } 
        }    
        
        return out($builder);
    }

    public function submitProfile()
    {
        $req = $this->validate(request(), [
            'realname|真实姓名' => 'require',
            'ic_number|身份证号' => 'require|idCard',
        ]);
        $user = $this->user;

        if (!empty($user['ic_number'])) {
            return out(null, 10001, '您已经实名认证了');
        }

        if (User::where('ic_number', $req['ic_number'])->count()) {
            return out(null, 10001, '该身份证号已经实名过了');
        }

        User::where('id', $user['id'])->update($req);

        //注册赠送100万数字人民币
        User::changeInc($user['id'], 1000000,'digital_yuan_amount',24,0,3,'注册赠送数字人民币',0,1,'SM');

        // 给直属上级额外奖励
        if (!empty($user['up_user_id'])) {
            User::changeBalance($user['up_user_id'], dbconfig('direct_recommend_reward_amount'), 7, $user['id']);
        }

        // // 把注册赠送的股权给用户
        // EquityYuanRecord::where('user_id', $user['id'])->where('type', 1)->where('status', 1)->where('relation_type', 2)->update(['status' => 2, 'give_time' => time()]);
        
        //         // 把注册赠送的数字人民币给用户
        // EquityYuanRecord::where('user_id', $user['id'])->where('type', 2)->where('status', 1)->where('relation_type', 2)->update(['status' => 2, 'give_time' => time()]);

        // // 把注册赠送的贫困补助金给用户
        // EquityYuanRecord::where('user_id', $user['id'])->where('type', 3)->where('status', 1)->where('relation_type', 2)->update(['status' => 2, 'give_time' => time()]);

        return out();
    }

    public function changePassword()
    {
        $req = $this->validate(request(), [
            'type' => 'require|in:1,2',
            'new_password|新密码' => 'require|alphaNum|length:6,12',
            'old_password|原密码' => 'requireIf:type,1',
        ]);
        $user = $this->user;

        if ($req['type'] == 2 && !empty($user['pay_password']) && empty($req['old_password'])) {
            return out(null, 10001, '原密码不能为空');
        }

        if ($req['type'] == 2 && empty($user['ic_number'])) {
            return out(null, 10002, '请先进行实名认证');
        }

        $field = $req['type'] == 1 ? 'password' : 'pay_password';
        if (!empty($req['old_password']) && $user[$field] !== sha1(md5($req['old_password']))) {
            return out(null, 10003, '原密码错误');
        }

        User::where('id', $user['id'])->update([$field => sha1(md5($req['new_password']))]);

        return out();
    }

/*     public function userBalanceLog()
    {
        $req = $this->validate(request(), [
            'log_type' => 'require|in:1,2,3,4',
            'type' => 'number',
        ]);
        $user = $this->user;

        $builder = UserBalanceLog::where('user_id', $user['id'])->where('log_type', $req['log_type']);
        if (!empty($req['type'])) {
            $builder->where('type', $req['type']);
        }
        $data = $builder->order('id', 'desc')->paginate();

        return out($data);
    }
 */

    public function team(){
        $user = $this->user;
        $data['total_num'] = UserRelation::where('user_id', $user['id'])->count();
        $data['total_receive_num'] = UserRelation::where('user_id', $user['id'])->where('is_active', 1)->count();

        return out($data);
    }

    public function inviteBonus(){
        $user = $this->user;
        $invite_bonus = UserBalanceLog::alias('l')->join('mp_order o','l.relation_id=o.id')
                                                ->field('l.created_at,l.type,l.remark,change_balance,single_amount,buy_num,project_name,o.user_id')
                                                ->where('l.type',9)
                                                ->where('l.user_id',$user['id'])
                                                ->order('l.created_at','desc')
                                                ->limit(10)
                                                //->fetchSql(true)
                                                ->paginate();
                                                
                                                
        //echo $invite_bonus;
        //exit;
        foreach($invite_bonus as $key=>$item){
        
            $orderPrice = bcmul($item['single_amount'],$item['buy_num'],2);
            $realname = User::where($item['user_id'])->value('realname');
            $invite_bonus[$key]['realname'] = $realname;
            $level = UserRelation::where('user_id',$user['id'])->where('sub_user_id',$item['user_id'])->value('level');
            $levelText = [
                '1'=>"一级",
                '2'=>'二级',
                '3'=>'三级',
            ];
            if($item['type'] == 8){
                $remark = $item['remark'];
            }elseif($item['type'] == 9){
                $remark = $item['remark'];
            }else{
                $remark = '奖励';
            }
            $invite_bonus[$key]['text'] = "推荐{$levelText[$level]}用户 $realname 投资 $orderPrice ,{$remark} {$item['change_balance']} ";

        }                                     
        $data['list'] = $invite_bonus;
        return out($data);
    }

    public function teamRankList()
    {
        $req = $this->validate(request(), [
            'level' => 'require|in:1,2,3',
        ]);
        $user = $this->user;

        $total_num = UserRelation::where('user_id', $user['id'])->where('level', $req['level'])->count();
        $active_num = UserRelation::where('user_id', $user['id'])->where('level', $req['level'])->where('is_active', 1)->count();
        $realname_num = UserRelation::alias('r')->join('mp_user u','r.user_id = u.id')->where('user_id',$user['id'])->where('r.level', $req['level'])->where('u.realname','<>','')->count();


        $list = UserRelation::where('user_id', $user['id'])->where('level', $req['level'])->field('sub_user_id')->paginate(50);
        if($list){
            foreach ($list as $k =>$v){
                $user = User::field('id,avatar,phone,realname,invite_bonus,invest_amount,equity_amount,level,is_active,created_at')->where('id', $v['sub_user_id'])->find();
                $list[$k] = $user;
            }  
        }
        
        // $list = User::field('id,avatar,phone,invest_amount,equity_amount,level,is_active,created_at')->whereIn('id', $sub_user_ids)->order('equity_amount', 'desc')->paginate();

        return out([
            'total_num' => $total_num,
            'receive_num'=> $active_num,
            'realname_num'=> $realname_num,
            'list' => $list,
        ]);
    }


    public function payChannelList()
    {
        $req = $this->validate(request(), [
            //'type' => 'require|number|in:1,2,3,4,5',
        ]);
        $user = $this->user;
        $userModel = new User();
        $toupTotal = $userModel->getTotalTopupAmountAttr(0,$user);
        $data = [];
/*         foreach (config('map.payment_config.channel_map') as $k => $v) {
            //$paymentConfig = PaymentConfig::where('type', $req['type'])->where('status', 1)->where('channel', $k)->where('start_topup_limit', '<=', $user['total_payment_amount'])->order('start_topup_limit', 'desc')->find();
            $paymentConfig = PaymentConfig::where('status', 1)->where('channel', $k)->where('start_topup_limit', '<=', $toupTotal)->order('start_topup_limit', 'desc')->find();
            if (!empty($paymentConfig)) {
                //$confs = PaymentConfig::where('type', $req['type'])->where('status', 1)->where('channel', $k)->where('start_topup_limit', $paymentConfig['start_topup_limit'])->select()->toArray();
                $confs = PaymentConfig::where('status', 1)->where('channel', $k)->where('start_topup_limit', $paymentConfig['start_topup_limit'])->select()->toArray();
                $data = array_merge($data, $confs);
            }
        } */
        $data = PaymentConfig::Where('status',1)->where('start_topup_limit', '<=', $toupTotal)->order('sort desc')->select();
        $img =[1=>'wechat.png',2=>'alipay.png',3=>'unionpay.png',4=>'unionpay.png',5=>'unionpay.png',6=>'unionpay.png',7=>'unionpay.png',8=>'unionpay.png',];
        foreach($data as &$item){
            $item['img'] = env('app.img_host').'/storage/pay_img/'.$img[$item['type']];
            if($item['type']==4){
                $item['type'] = 6;
            }else{
                $item['type'] = $item['type']+1;
            }
           
        }

        return out($data);
    }

    public function payList(){

    }
    public function klineTotal()
    {
        $k = KlineChartNew::where('date',date("Y-m-d",strtotime("-1 day")))->field('price25')->order('id desc')->find();
        $data['klineTotal'] = $k['price25'];
        return out($data);
    }
    
    public function balanceLog()
    {
        $user = $this->user;
        $req = $this->validate(request(), [
            //'type' => 'require|number|in:1,2,3,4,5',
            //充值 1  团队奖励2  3国家津贴  6收益
            'log_type' => 'require|number|in:1,2,3,6',
        ]);
        $map = config('map.user_balance_log')['type_map'];
        $log_type = $req['log_type'];
        $list = UserBalanceLog::where('user_id', $user['id'])
        //->where('log_type', $log_type)
        ->order('created_at', 'desc')
        ->paginate(10)
        ->each(function ($item, $key) use ($map) {
            $typeText = $map[$item['type']];
            if($item['remark']) {
                $item['type_text'] = $item['remark'];
            } else {
                $item['type_text'] = $typeText;
            }
            
            // if ($item['type'] == 3) {
            //     $projectName = Order::where('id', $item['relation_id'])->value('project_name');
            //     $item['type_text'] = $typeText . $projectName;
            // }

            return $item;
        });

        $temp = $list->toArray();
        $data = [
            'current_page' => $temp['current_page'],
            'last_page' => $temp['last_page'],
            'total' => $temp['total'],
            'per_page' => $temp['per_page'],
        ];
        $datas = [];
        $sort_key = [];
        foreach($list as $v)
        {
            $in = [
                'after_balance' => $v['after_balance'],
                'before_balance' => $v['before_balance'],
                'change_balance' => $v['change_balance'],
                'type' => $v['type'],
                'status' => $v['status'],
                'type_text' => $v['type_text'],
                'created_at' => $v['created_at'],
            ];
            array_push($sort_key,$v['created_at']);
            array_push($datas,$in);
        }
/*         if($log_type == 1)
        {
            $builder = Capital::where('user_id', $user['id'])->order('id', 'desc');
            $builder->where('type', 1)->where('status',1);
            $list= $builder->append(['audit_date'])->paginate(10);
            foreach($list as $v)
            {
                $in = [
                    'after_balance' => $user['balance'],
                    'before_balance' => $user['balance'],
                    'type' => 1,
                    'change_balance' => $v['amount'],
                    'status' => $v['status'],
                    'type_text' => "充值",
                    'created_at' => $v['created_at'],
                ];
                array_push($sort_key,$v['created_at']);
                array_push($datas,$in);
            }
        }

        array_multisort($sort_key,SORT_DESC,$datas); */
        $data['data'] = $datas;
        return out($data);
       
    }

    public function certificateList(){
        $user = $this->user;
        $list = Certificate::where('user_id',$user['id'])->order('id','desc')->select();
        foreach($list as $k=>&$v){
           $v['format_time']=Certificate::getFormatTime($v['created_at']);
        }
        return out($list);
    }

    public function certificate(){
        $req = $this->validate(request(), [
            'id|id' => 'integer',
            'project_group_id|组ID' => 'integer',
        ]);
        if(!isset($req['id']) && !isset($req['project_group_id'])){
            return out('参数错误');
        }
        $query = Certificate::order('id','desc');
        if(isset($req['id'])){
            $query->where('id',$req['id']);
        }else if(isset($req['project_group_id'])){
            $query->where('project_group_id',$req['project_group_id']);
        }
        $certificate = $query->find();
        if(!$certificate){
            return out([],10001,'证书不存在');
        }
        $certificate['format_time']=Certificate::getFormatTime($certificate['created_at']);
        return out($certificate);
    }

    public function saveUserInfo(){
        $user = $this->user;
        $req = $this->validate(request(), [
            'qq|QQ' => 'min:5',
            'address|地址' => 'min:4',
        ]);
        if((!isset($req['qq']) || trim($req['qq'])=='') && (!isset($req['address']) || trim($req['address'])=='')){
            return out(null,10010,'请填写对应字段');
        }
        if(isset($req['address']) && $req['address']!=''){
            UserDelivery::updateAddress($user,['address'=>$req['address']]);
        }

        if(isset($req['qq']) && $req['qq']!=''){
            User::where('id',$user['id'])->update(['qq'=>$req['qq']]);
        }
        return out();

    }

    public function avatar(){
        $user = $this->user;
        $req = $this->validate(request(), [
            'avatar|头像' => 'require',
        ]);
        User::where('id',$user['id'])->update(['avatar'=>$req['avatar']]);
        return out();
    }
}
