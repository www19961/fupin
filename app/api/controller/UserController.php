<?php

namespace app\api\controller;

use app\model\EquityYuanRecord;
use app\model\LevelConfig;
use app\model\Order;
use app\model\PaymentConfig;
use app\model\Project;
use app\model\User;
use app\model\UserBalanceLog;
use app\model\UserRelation;
use app\model\KlineChartNew;

use think\facade\Db;
use Exception;


class UserController extends AuthController
{
    public function userInfo()
    {
        $user = $this->user;

        $user = User::where('id', $user['id'])->append(['equity', 'digital_yuan', 'my_bonus', 'total_bonus', 'profiting_bonus', 'exchange_equity', 'exchange_digital_yuan', 'passive_total_income', 'passive_receive_income', 'passive_wait_income', 'subsidy_total_income', 'team_user_num', 'team_performance', 'can_withdraw_balance'])->find()->toArray();

        $user['is_set_pay_password'] = !empty($user['pay_password']) ? 1 : 0;
        unset($user['password'], $user['pay_password']);
        $user['sum'] = round($user['balance'] + $user['my_bonus'] + $user['passive_wait_income'] + $user['subsidy_total_income']+$user['digital_yuan'],2);
        
        //检测用户升级 投资金额达到 或者  直属下级激活人数达到
        //$user = User::where('id', $user['id'])->find();
        $new_level = LevelConfig::where('min_topup_amount', '<=', $user['invest_amount'])->order('min_topup_amount', 'desc')->value('level');
        $zhishuCount = UserRelation::where('user_id',$user['id'])->where('is_active',1)->where('level',1)->count();

        $zhishu_level = LevelConfig::where('min_direct_sub_active_num', '<=', $zhishuCount)->order('min_direct_sub_active_num', 'desc')->value('level');

        if ($user['level'] < $new_level) {
            User::where('id', $user['id'])->update(['level' => $new_level]);
        }elseif($user['level'] < $zhishu_level){
            User::where('id', $user['id'])->update(['level' => $zhishu_level]);
        }
        return out($user);
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

    //转账
    public function transferAccounts(){
        $req = $this->validate(request(), [
            'type' => 'require|in:2',//2 转账余额（充值金额）
            'realname|对方姓名' => 'require',
            'account|对方账号' => 'require',
            'money|转账金额' => 'require|number',
            'pay_password|支付密码' => 'requireIf:pay_method,1|requireIf:pay_method,5',
        ]);//type 1 可用余额，2 转账余额，realname 对方姓名，account 对方账号，money 转账金额，pay_password 支付密码
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
        if (!in_array($req['type'], [1,2])) {
            return out(null, 10001, '不支持该支付方式');
        }

        Db::startTrans();
        try {
            //1可用余额（可提现金额） 2 转账余额（充值金额加他人转账的金额）
            //topup_balance充值余额 can_withdraw_balance可提现余额  balance总余额
            $user = User::where('id', $user['id'])->lock(true)->find();//转账人
            $take = User::where('phone', $req['account'])->where('realname',$req['realname'])->lock(true)->find();//收款人
            if (!$take) {
                exit_out(null, 10002, '用户不存在');
            }
            if (empty($take['ic_number'])) {
                exit_out(null, 10002, '请收款用户先完成实名认证');
            }

            if ($req['type'] == 2 && $req['money'] > $user['topup_balance']) {
                exit_out(null, 10002, '转账余额不足');
            }
            //转出金额  扣金额 可用金额 转账金额
            $change_balance = 0 - $req['money'];
            if($req['type']==2){

                //2 转账余额（充值金额加他人转账的金额）
                User::where('id', $user['id'])->inc('balance', $change_balance)->inc('topup_balance', $change_balance)->update();
                //User::changeBalance($user['id'], $change_balance, 18, 0, 1,'转账余额转账给'.$take['realname']);
                //增加资金明细
                UserBalanceLog::create([
                    'user_id' => $user['id'],
                    'type' => 18,
                    'log_type' => 1,
                    'relation_id' => 0,
                    'before_balance' => $user['topup_balance'],
                    'change_balance' => $change_balance,
                    'after_balance' =>  $user['topup_balance']-$req['money'],
                    'remark' => '转账余额转账给'.$take['realname'],
                    'admin_user_id' => 0,
                    'status' => 2,
                    'project_name' => ''
                ]);
            }

            //收到金额  加金额 转账金额
            User::where('id', $take['id'])->inc('balance', $req['money'])->inc('topup_balance', $req['money'])->update();
            //User::changeBalance($take['id'], $req['money'], 18, 0, 1,'接收转账来自'.$user['realname']);
            UserBalanceLog::create([
                'user_id' => $take['id'],
                'type' => 18,
                'log_type' => 1,
                'relation_id' => 0,
                'before_balance' => $take['topup_balance'],
                'change_balance' => $req['money'],
                'after_balance' =>  $take['topup_balance']+$req['money'],
                'remark' => '接收转账来自'.$user['realname'],
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
            'status' => 'number',
            'search_type' => 'number',
        ]);
        $user = $this->user;

        $builder = UserBalanceLog::where('user_id', $user['id'])->where('type', 18)->select();
            //->paginate(15,false,['query'=>request()->param()]);

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

        // 给直属上级额外奖励
        if (!empty($user['up_user_id'])) {
            User::changeBalance($user['up_user_id'], dbconfig('direct_recommend_reward_amount'), 7, $user['id']);
        }

        // 把注册赠送的股权给用户
        EquityYuanRecord::where('user_id', $user['id'])->where('type', 1)->where('status', 1)->where('relation_type', 2)->update(['status' => 2, 'give_time' => time()]);

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

    public function userBalanceLog()
    {
        $req = $this->validate(request(), [
            'log_type' => 'require|in:1,2',
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

    public function teamRankList()
    {
        $req = $this->validate(request(), [
            'level' => 'require|in:1,2,3',
        ]);
        $user = $this->user;

        $total_num = UserRelation::where('user_id', $user['id'])->where('level', $req['level'])->count();
        $active_num = UserRelation::where('user_id', $user['id'])->where('level', $req['level'])->where('is_active', 1)->count();

        $sub_user_ids = UserRelation::where('user_id', $user['id'])->where('level', $req['level'])->column('sub_user_id');
        $list = User::field('id,avatar,phone,invest_amount,level,is_active,created_at')->whereIn('id', $sub_user_ids)->order('invest_amount', 'desc')->paginate();

        return out([
            'total_num' => $total_num,
            'active_num' => $active_num,
            'list' => $list,
        ]);
    }

    public function payChannelList()
    {
        $req = $this->validate(request(), [
            'type' => 'require|number|in:1,2,3,4,5',
        ]);
        $user = $this->user;

        $data = [];
        foreach (config('map.payment_config.channel_map') as $k => $v) {
            $paymentConfig = PaymentConfig::where('type', $req['type'])->where('status', 1)->where('channel', $k)->where('start_topup_limit', '<=', $user['total_payment_amount'])->order('start_topup_limit', 'desc')->find();
            if (!empty($paymentConfig)) {
                $confs = PaymentConfig::where('type', $req['type'])->where('status', 1)->where('channel', $k)->where('start_topup_limit', $paymentConfig['start_topup_limit'])->select()->toArray();
                $data = array_merge($data, $confs);
            }
        }

        return out($data);
    }
    public function klineTotal()
    {
        $k = KlineChartNew::where('date',date("Y-m-d",strtotime("-1 day")))->field('price25')->order('id desc')->find();
        $data['klineTotal'] = $k['price25'];
        return out($data);
    }
}
