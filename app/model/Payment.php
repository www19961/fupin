<?php

namespace app\model;

use Exception;
use GuzzleHttp\Client;
use think\Model;

class Payment extends Model
{
    public function getProductTypeTextAttr($value, $data)
    {
        $map = config('map.payment')['product_type_map'];
        return $map[$data['product_type']];
    }

    public function getStatusTextAttr($value, $data)
    {
        $map = config('map.payment')['status_map'];
        return $map[$data['status']];
    }

    public function getChannelTextAttr($value, $data)
    {
        $map = config('map.payment_config')['channel_map'];
        return $map[$data['channel']];
    }

    public function getCardInfoAttr($value)
    {
        return json_decode($value, true);
    }

    public static function requestPayment($trade_sn, $pay_bankcode, $pay_amount)
    {
        $conf = config('config.payment_conf');
        $req = [
            'pay_memberid' => $conf['pay_memberid'],
            'pay_orderid' => $trade_sn,
            'pay_bankcode' => $pay_bankcode,
            'pay_amount' => $pay_amount,
            'pay_notifyurl' => $conf['pay_notifyurl'],
            'pay_callbackurl' => $conf['pay_callbackurl'],
        ];
        $req['pay_md5sign'] = self::builderSign($req);
        $client = new Client(['verify' => false]);
        try {
            $ret = $client->post($conf['payment_url'], [
                'headers' => [
                    'Accept' => 'application/json',
                    'content-type' => 'application/json',
                ],
                'json' => $req,
            ]);
            $resp = $ret->getBody()->getContents();
            $data = json_decode($resp, true);
            if (empty($data['status']) || $data['status'] != 200) {
                exit_out(null, 10001, '支付异常，请稍后重试', ['请求参数' => $req, '返回数据' => $resp]);
            }
        } catch (Exception $e) {
            throw $e;
        }

        return $data;
    }

    public static function requestPayment2($trade_sn, $ppdID, $pay_amount)
    {
        $conf = config('config.payment_conf2');
        $req = [
            'code' => $conf['account_id'],
            'orderno' => $trade_sn,
            'amount' => $pay_amount,
            'returnurl' => $conf['callback_url'],
            'notifyurl' => $conf['success_url'],
            'ppID' => $ppdID,
        ];
        $req['sign'] = self::builderSign2($req);
        $client = new Client(['verify' => false]);
        try {
            $ret = $client->post($conf['payment_url'], [
                'headers' => [
                    'Accept' => 'application/json',
                    'content-type' => 'application/x-www-form-urlencoded',
                ],
                'form_params' => $req,
            ]);
            $resp = $ret->getBody()->getContents();
            $data = json_decode($resp, true);
            if (empty($data['responseCode']) || $data['responseCode'] != 200) {
                exit_out(null, 10001, $data['msg']??'支付异常，请稍后重试', ['请求参数' => $req, '返回数据' => $resp]);
            }
        } catch (Exception $e) {
            throw $e;
        }

        return ['type' => 'url', 'data' => $data['url'] ?? ''];
    }

    public static function requestPayment3($trade_sn, $pay_bankcode, $pay_amount)
    {
        $conf = config('config.payment_conf3');
        $req = [
            'pay_memberid' => $conf['pay_memberid'],
            'pay_orderid' => $trade_sn,
            'pay_bankcode' => $pay_bankcode,
            'pay_amount' => $pay_amount,
            'pay_notifyurl' => $conf['pay_notifyurl'],
            'pay_callbackurl' => $conf['pay_callbackurl'],
            'pay_applydate' => date('Y-m-d H:i:s'),
        ];
        $req['pay_md5sign'] = self::builderSign3($req);
        $client = new Client(['verify' => false]);
        try {
            $ret = $client->post($conf['payment_url'], [
                'form_params' => $req,
            ]);
            $resp = $ret->getBody()->getContents();
            $data = json_decode($resp, true);
            if (empty($data['status']) || $data['status'] != 200) {
                exit_out(null, 10001, $data['msg'] ?? '支付异常，请稍后重试', ['请求参数' => $req, '返回数据' => $resp]);
            }
        } catch (Exception $e) {
            throw $e;
        }

        return $data;
    }

    public static function builderSign($req)
    {
        ksort($req);
        $buff = '';
        foreach ($req as $k => $v) {
            $buff .= $k . '=' . $v . '&';
        }
        $str = $buff . "key=" . config('config.payment_conf')['key'];
        $sign = strtoupper(md5($str));
        return $sign;
    }

    public static function builderSign2($req)
    {
/*         ksort($req);
        $buff = '';
        foreach ($req as $k => $v) {
            $buff .= $k . '=' . $v . '&';
        } */
       // $str = $buff . "key=" . config('config.payment_conf2')['key'];
       $str="{$req['amount']}{$req['code']}{$req['notifyurl']}{$req['orderno']}{$req['returnurl']}".config('config.payment_conf2')['key'];
        return md5($str);
    }

    public static function builderSign3($req)
    {
        ksort($req);
        $buff = '';
        foreach ($req as $k => $v) {
            $buff .= $k . '=' . $v . '&';
        }
        $str = $buff . "key=" . config('config.payment_conf3')['key'];
        $sign = strtoupper(md5($str));
        return $sign;
    }
}
