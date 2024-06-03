<?php

namespace app\api\controller;

use app\model\AssetOrder;
use app\model\ProjectItem;
use app\model\Order;
use app\model\Payment;
use app\model\PaymentConfig;
use app\model\Project;
use app\model\PassiveIncomeRecord;
use app\model\User;
use app\model\UserRelation;
use app\model\UserSignin;
use think\facade\Cache;
use Exception;
use think\facade\Db;

class OrderController extends AuthController
{

    public function placeOrder()
    {
        $req = $this->validate(request(), [
            'project_id' => 'require|number',
            //'price' => 'require|number',
            'pay_password' => 'require'
        ]);

        $user = $this->user;

        $clickRepeatName = 'placeOrder-' . $user->id;
        if (Cache::get($clickRepeatName)) {
            return out(null, 10001, '操作频繁，请稍后再试');
        }
        Cache::set($clickRepeatName, 1, 5);

        if (empty($user['pay_password'])) {
            return out(null, 801, '请先设置支付密码');
        }
        if (!empty($req['pay_password']) && $user['pay_password'] !== sha1(md5($req['pay_password']))) {
            return out(null, 10001, '支付密码错误');
        }

        $projectItem = ProjectItem::where('id', $req['project_id'])->find();
        if(!$projectItem){
            return out(null, 10001, '项目不存在');
        } else {
            $project = Project::find($projectItem['project_id']);
            if (!$project || $project['status'] == 0) {
                return out(null, 10001, '项目不存在');
            }

            if ($project['rate'] == 100) {
                return out(null, 10001, '当日份额已经售罄');
            }

            if ($project['is_gift']) {
                $isBought = Order::where('user_id', $user['id'])->where('is_gift', 1)->find();
                if ($isBought) {
                    return out(null, 10001, '赠送基金仅能购买一次');
                }
            }

            if ($project['name'] == '新用户体验基金') {
            //     $projectItemIdsTemp = ProjectItem::where('project_id', $projectItem['project_id'])->column('id');
                $isBuyThisTypeProduct = Order::where('user_id', $user['id'])->where('project_name', '新用户体验基金')->find();
                if ($isBuyThisTypeProduct) {
                    return out(null, 10001, '该产品限购一次');
                }
            }

            $projectItemIds = ProjectItem::where('project_id', $projectItem['project_id'])->column('id');
            // $isBuyThisTypeProduct = Order::where('user_id', $user['id'])->whereIn('project_id', $projectItemIds)->find();
            // if ($isBuyThisTypeProduct) {
            //     return out(null, 10001, '每个项目只能购买一份');
            // }
            $isBuyThisTypeProduct = Order::where('user_id', $user['id'])->find();
            // if ($isBuyThisTypeProduct) {
            //     return out(null, 10001, '只能购买一次');
            // }

            $buyLimit = Order::where('user_id', $user['id'])->where('type', 4)->count();
            if ($buyLimit >= 2) {
                return out(null, 10001, '只能购买两次');
            }
        }

        Db::startTrans();
        try {
            $user = User::where('id', $user['id'])->lock(true)->find();

            $pay_amount = $projectItem['price'];

            if ($pay_amount > $user['balance'] + $user['topup_balance']) {
                exit_out(null, 10090, '余额不足');
            }

            $order_sn = 'FP'.build_order_sn($user['id']);

            $order['project_id'] = $req['project_id'];
            $order['user_id'] = $user['id'];
            $order['up_user_id'] = $user['up_user_id'];
            $order['order_sn'] = $order_sn;
            $order['buy_num'] = 1;
            $order['price'] = $pay_amount;
            $order['buy_amount'] = $pay_amount;
            $order['start_time'] = time();
            $order['project_name'] = $project['name'];
            $order['type'] = $project['type'];
            $order['days'] = $projectItem['days'];
            $order['reward'] = $projectItem['reward'];
            $order['end_time'] = time() + 86400 * $projectItem['days'];
            $order['fupin_reward'] = $projectItem['fupin_reward'];
            $order['is_gift'] = $project['is_gift'];
            $order['is_circle'] = $project['is_circle'];
            $order['multiple'] = $project['multiple'];

            $orderRes = Order::create($order);

            //国家扶贫金
            if ($project['is_circle']) {
                $fupinReward = bcmul($order['fupin_reward'], $order['multiple']);
                User::changeInc($order['user_id'], $fupinReward, 'specific_fupin_balance', 37, $orderRes->getData('id'), 3);
            }

            if ($project['is_gift'] == 0) {
                // 扣余额
                User::changeBalance($user['id'],-$pay_amount,3,$orderRes->getData('id'),1);

                $userRelation = UserRelation::where('sub_user_id', $user['id'])->select();
                $map = [1 => 'first_team_reward_ratio', 2 => 'second_team_reward_ratio', 3 => 'third_team_reward_ratio'];
                foreach ($userRelation as $value) {
                    $rate = round(dbconfig($map[$value['level']])/100, 2);
                    $reward = bcmul($pay_amount, $rate, 2);
                    if ($reward > 0) {
                        User::changeInc($value['user_id'],$reward,'balance',8,$user['id'],1,'团队奖励',0,1,$pay_amount);
                    }
                }

                //激活
                User::where('id', $user['id'])->update(['is_active' => 1]);
                UserRelation::where('sub_user_id', $user['id'])->update(['is_active' => 1]);
            }

            //赠送同项目下同天数低价产品
            // if ($project['type'] == 3) {
            //     $freeProjectItem = ProjectItem::where('project_id', $projectItem['project_id'])->where('days', 90)->where('price', '<', $projectItem['price'])->find();
            //     if (!empty($freeProjectItem)) {
            //         $order_sn = 'FP'.build_order_sn($user['id']);
            //         $order['project_id'] = $freeProjectItem['id'];
            //         $order['user_id'] = $user['id'];
            //         $order['up_user_id'] = $user['up_user_id'];
            //         $order['order_sn'] = $order_sn;
            //         $order['buy_num'] = 1;
            //         $order['price'] = $freeProjectItem['price'];
            //         $order['buy_amount'] = $freeProjectItem['price'];
            //         $order['start_time'] = time();
            //         $order['project_name'] = $project['name'];
            //         $order['type'] = $project['type'];
            //         $order['days'] = $freeProjectItem['days'];
            //         $order['reward'] = $freeProjectItem['reward'];
            //         $order['end_time'] = time() + 86400 * $freeProjectItem['days'];
            //         $order['fupin_reward'] = $freeProjectItem['fupin_reward'];
            //         $order['is_gift'] = $project['is_gift'];
            //         $order['is_circle'] = $project['is_circle'];
            //         $orderRes = Order::create($order);
            //         if ($project['is_circle']) {
            //             User::changeInc($order['user_id'], $order['fupin_reward'], 'specific_fupin_balance', 37, $orderRes->getData('id'), 3);
            //         }
            //     }
            // }

            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            throw $e;
        }

        return out(['order_id' => $orderRes->getData('id')]);

    }

    public function orderList()
    {
        $req = $this->validate(request(), [
            'status' => 'number',
            // 'project_group_id' => 'number',
        ]);
        $user = $this->user;

        $builder = Order::where('user_id', $user['id']);
        
        if (!empty($req['status'])) {
            $builder->where('status', $req['status']);
        }

        $builder->where('type', '<>', 4);

        // if (!empty($req['project_group_id'])) {
        //     $builder->where('project_group_id', $req['project_group_id']);
        // }
        $data = $builder->order('id', 'desc')->field(['id', 'order_sn', 'status', 'created_at', 'price', 'days', 'reward', 'project_name', 'is_transfer', 'type', 'multiple'])->paginate(10,false,['query'=>request()->param()]);

        return out($data);
    }

    public function orderListType4()
    {
        $req = $this->validate(request(), [
            'status' => 'number',
            // 'project_group_id' => 'number',
        ]);
        $user = $this->user;

        $builder = Order::where('user_id', $user['id']);
        
        if (!empty($req['status'])) {
            $builder->where('status', $req['status']);
        }

        $builder->where('type', 4);

        // if (!empty($req['project_group_id'])) {
        //     $builder->where('project_group_id', $req['project_group_id']);
        // }
        $data = $builder->order('id', 'desc')->field(['id', 'order_sn', 'status', 'created_at', 'price', 'days', 'reward', 'project_name', 'is_transfer', 'type', 'multiple'])->paginate(10,false,['query'=>request()->param()]);

        return out($data);
    }


    //已发放收益 提现到余额
    public function specificBalance2Balance()
    {
        $user = $this->user;
        $clickRepeatName = 'specificBalance2Balance-' . $user->id;
        if (Cache::get($clickRepeatName)) {
            return out(null, 10001, '操作频繁，请稍后再试');
        }
        Cache::set($clickRepeatName, 1, 5);

        // $resCount = Order::where('user_id', $user['id'])->where('status', 2)->where('is_transfer', 0)->sum('reward');
        // if ($resCount == 0) {
        if ($user['specific_balance'] <= 0) {
            return out(null, 10001, '暂无可提现余额');
        }

        Db::startTrans();
        try {   

            // $list = Order::where('user_id', $user['id'])->where('status', 2)->where('is_transfer', 0)->select()->toArray();
            // foreach ($list as $key => $value) {
            //     Order::where('id', $value['id'])->update(['is_transfer' => 1]);
            //     User::changeInc($user['id'],-$value['reward'],'specific_balance',33,$value['id'],1,'',0,1,'TRS');
            //     User::changeInc($user['id'],$value['reward'],'balance',34,$value['id'],1,'',0,1,'TRR');
            // }

            User::changeInc($user['id'],-$user['specific_balance'],'specific_balance',33,$user['id'],1,'',0,1,'TRS');
            User::changeInc($user['id'],$user['specific_balance'],'balance',34,$user['id'],1,'',0,1,'TRR');

            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            throw $e;
        }

        return out();
    }
}
