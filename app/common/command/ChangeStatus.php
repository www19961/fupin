<?php

namespace app\common\command;

use think\facade\Db;
use app\model\User;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Cache;
use Exception;

class ChangeStatus extends Command
{
    /**
     * 1 0 * * * cd /www/wwwroot/mip_sys && php think YuanMengChangeStatus
     */
    protected function configure()
    {
        $this->setName('ChangeStatus')->setDescription('修改审核状态');
    }

    protected function execute(Input $input, Output $output)
    {
        $hour1 = dbconfig('loading1_hour');
        $hour2 = dbconfig('loading2_hour');
        $hour3 = dbconfig('loading3_hour');
        $hour4 = dbconfig('loading4_hour');
        $hour5 = dbconfig('loading5_hour');
        $now = time();
        Db::name('specific_fupin_capital')->where('loading5_status', '<', 2)->order('id', 'asc')->chunk(500, function($orderList) use($hour1, $hour2, $hour3, $hour4, $hour5, $now) {
            foreach ($orderList as $key => $order) {
                $statusSum = $order['loading1_status'] + $order['loading2_status'] + $order['loading3_status'] + $order['loading4_status'] + $order['loading5_status'];
                switch ($statusSum) {
                    case 1:
                        $endTime = $order['loading1_start_time'] + bcmul($hour1, 3600, 2);
                        if ($endTime <= $now) {
                            Db::name('specific_fupin_capital')->where('id', $order['id'])->update([
                                'loading1_status' => 2,
                                'loading2_status' => 1,
                                'loading2_start_time' => $now,
                            ]);
                            echo $statusSum;
                        }
                        break;
                    case 3:
                        $endTime = $order['loading2_start_time'] + bcmul($hour2, 3600, 2);
                        if ($endTime <= $now) {
                            Db::name('specific_fupin_capital')->where('id', $order['id'])->update([
                                'loading2_status' => 2,
                                'loading3_status' => 1,
                                'loading3_start_time' => $now,
                            ]);
                            echo $statusSum;
                        }
                        break;
                    case 5:
                        $endTime = $order['loading3_start_time'] + bcmul($hour3, 3600, 2);
                        if ($endTime <= $now) {
                            Db::name('specific_fupin_capital')->where('id', $order['id'])->update([
                                'loading3_status' => 2,
                                'loading4_status' => 1,
                                'loading4_start_time' => $now,
                            ]);
                            echo $statusSum;
                        }
                        break;
                    case 7:
                        $endTime = $order['loading4_start_time'] + bcmul($hour4, 3600, 2);
                        if ($endTime <= $now) {
                            Db::name('specific_fupin_capital')->where('id', $order['id'])->update([
                                'loading4_status' => 2,
                                'loading5_status' => 1,
                                'loading5_start_time' => $now,
                            ]);
                            echo $statusSum;
                        }
                        break;
                    case 9:
                        $endTime = $order['loading5_start_time'] + bcmul($hour5, 3600, 2);
                        if ($endTime <= $now) {
                            Db::name('specific_fupin_capital')->where('id', $order['id'])->update([
                                'loading5_status' => 2,
                                //'order_status' => 2,
                            ]);
                            echo $statusSum;
                        }
                        break;
                }
            }
        });
    }
}
