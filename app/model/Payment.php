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
            'notifyurl' => $conf['pay_notifyurl'],
            'returnurl' => $conf['pay_callbackurl'],
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
                exit_out(null, 10001, $data['msg'] ?? '支付异常，请稍后重试', ['请求参数' => $req, '返回数据' => $resp]);
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

    public static function requestPayment4($trade_sn, $pay_bankcode, $pay_amount)
    {
        $conf = config('config.payment_conf4');
        $req = [
            'mchKey' => $conf['pay_memberid'],
            'mchOrderNo' => $trade_sn,
            'product' => $pay_bankcode,
            'amount' => $pay_amount * 100, //以分为单位
            'notifyUrl' => $conf['pay_notifyurl'],
            'returnUrl' => $conf['pay_callbackurl'],
            'timestamp' => self::getMillisecond(),
            'nonce' => rand(10000000, 99999999999999999),
            //'userIp' => date('Y-m-d H:i:s'),
        ];
        $req['sign'] = self::builderSign4($req);
        $client = new Client(['verify' => false, 'headers' => ['Content-Type' => 'application/json']]);
        try {
            $ret = $client->post($conf['payment_url'], [
                'body' => json_encode($req),
            ]);
            $resp = $ret->getBody()->getContents();
            $data = json_decode($resp, true);
            if (empty($data['data']['payStatus']) || $data['data']['payStatus'] != 'PROCESSING') {
                exit_out(null, 10001, $data['msg'] ?? '支付异常，请稍后重试', ['请求参数' => $req, '返回数据' => $resp]);
            }
        } catch (Exception $e) {
            throw $e;
        }
        return [
            'data' => $data['data']['url']['payUrl'],
        ];
    }

    public static function requestPayment5($trade_sn, $pay_bankcode, $pay_amount)
    {
        $conf = config('config.payment_conf5');
        $req = [
            'mid' => $conf['pay_memberid'],
            'orderid' => $trade_sn,
            'paytype' => $pay_bankcode,
            'amount' => $pay_amount,
            'notifyurl' => $conf['pay_notifyurl'],
            'returnurl' => $conf['pay_callbackurl'],
            'version' => 3,
            'note' => 'note',
            'ip' => request()->ip(),
            //'userIp' => date('Y-m-d H:i:s'),
        ];
        $req['sign'] = self::builderSign5($req);
        $client = new Client(['verify' => false]);
        try {
            $ret = $client->post($conf['payment_url'], [
                'form_params' => $req,
            ]);
            $resp = $ret->getBody()->getContents();
            $data = json_decode($resp, true);
            if (empty($data['status']) || $data['status'] != 1) {
                exit_out(null, 10001, $data['msg'] ?? '支付异常，请稍后重试', ['请求参数' => $req, '返回数据' => $resp]);
            }
        } catch (Exception $e) {
            throw $e;
        }
        return [
            'data' => $data['api_jump_url'],
        ];
    }

    public static function requestPayment6($trade_sn, $pay_bankcode, $pay_amount)
    {
        $conf = config('config.payment_conf6');
        $req = [
            'mchId' => $conf['pay_memberid'],
            //'appId'=>0,
            'productId' => $pay_bankcode,
            'mchOrderNo' => $trade_sn,
            'amount' => $pay_amount*100,
            'currency' => 'cny',
            'notifyUrl' => $conf['pay_notifyurl'],
            'returnurl' => $conf['pay_callbackurl'],
            'subject' => 'subject',
            'body' => 'body',
            'version' => '1.0',
            'reqTime' => date('YmdHis'),
            //'userIp' => date('Y-m-d H:i:s'),
        ];
        $req['sign'] = self::builderSign6($req);
        $client = new Client(['verify' => false]);
        try {
            $ret = $client->post($conf['payment_url'], [
                'form_params' => $req,
            ]);
            $resp = $ret->getBody()->getContents();
            $data = json_decode($resp, true);
            if (!isset($data['retCode']) || $data['retCode']!=0) {
                exit_out(null, 10001, $data['retMsg'] ?? '支付异常，请稍后重试', ['请求参数' => $req, '返回数据' => $resp]);
            }
        } catch (Exception $e) {
            throw $e;
        }
        return [
            'data' => $data['payUrl'],
        ];
    }

    public static function requestPayment7($trade_sn, $pay_bankcode, $pay_amount)
    {
        $conf = config('config.payment_conf7');
        $req = [
            'mchId' => $conf['pay_memberid'],
            //'appId'=>0,
            'productId' => $pay_bankcode,
            'mchOrderNo' => $trade_sn,
            'amount' => $pay_amount*100,
            'currency' => 'cny',
            'notifyUrl' => $conf['pay_notifyurl'],
            'returnUrl' => $conf['pay_callbackurl'],
            'subject' => 'subject',
            'body' => 'body',
            'version' => '1.0',
            'reqTime' => date('YmdHis'),
            //'userIp' => date('Y-m-d H:i:s'),
        ];
        $req['sign'] = self::builderSign7($req);
        $client = new Client(['verify' => false]);
        try {
            $ret = $client->post($conf['payment_url'], [
                'form_params' => $req,
            ]);
            $resp = $ret->getBody()->getContents();
            $data = json_decode($resp, true);
            if (!isset($data['retCode']) || $data['retCode']!=0) {
                exit_out(null, 10001, $data['retMsg'] ?? '支付异常，请稍后重试', ['请求参数' => $req, '返回数据' => $resp]);
            }
        } catch (Exception $e) {
            throw $e;
        }
        return [
            'data' => $data['payUrl'],
        ];
    }

    public static function requestPayment8($trade_sn, $pay_bankcode, $pay_amount)
    {
        $conf = config('config.payment_conf8');
        $req = [
            'account_id' => $conf['pay_memberid'],
            //'appId'=>0,
            'content_type' => 'json',
            'thoroughfare' => $pay_bankcode,
            'out_trade_no' => $trade_sn,
            'amount' => "$pay_amount.00",
            'callback_url' => $conf['pay_notifyurl'],
            'success_url' => $conf['pay_callbackurl'],
            'error_url'=>$conf['pay_callbackurl'],
            'timestamp' => strtotime(date("Y-m-d H:i:s")),
            'ip'=>request()->ip(),
            'deviceos'=>sysType(),
            'payer_ip'=>'123456789',
            //'userIp' => date('Y-m-d H:i:s'),
        ];
        $req['sign'] = self::builderSign8($req);
        $client = new Client(['verify' => false]);
        try {
            $ret = $client->post($conf['payment_url'], [
                'form_params' => $req,
            ]);
            $resp = $ret->getBody()->getContents();
            $data = json_decode($resp, true);
            if (!isset($data['code']) || $data['code']!=200) {
                exit_out(null, 10001, $data['msg'] ?? '支付异常，请稍后重试', ['请求参数' => $req, '返回数据' => $resp]);
            }
        } catch (Exception $e) {
            throw $e;
        }
        return [
            'data' => $data['data']['pay_url'],
        ];
    }

    public static function requestPayment9($trade_sn, $pay_bankcode, $pay_amount)
    {
        $conf = config('config.payment_conf9');
        $req = [
            'customerNumber' => $conf['pay_memberid'],
            'orderNumber' => $trade_sn,
            'amount' => "$pay_amount",
            'callBackUrl' => $conf['pay_notifyurl'],
            'payType' => $pay_bankcode,
            'playUserIp'=>request()->ip(),
        ];
        $req['sign'] = self::builderSign9($req);
        $client = new Client(['verify' => false]);
        try {
            $ret = $client->post($conf['payment_url'], [
                'form_params' => $req,
            ]);
            $resp = $ret->getBody()->getContents();
            $data = json_decode($resp, true);
            if (!isset($data['code']) || $data['code']!=10000) {
                exit_out(null, 10001, $data['message'] ?? '支付异常，请稍后重试', ['请求参数' => $req, '返回数据' => $resp]);
            }
        } catch (Exception $e) {
            throw $e;
        }
        return [
            'data' => $data['payUrl'],
        ];
    }

    public static function requestPayment10($trade_sn, $pay_bankcode, $pay_amount)
    {
        $conf = config('config.payment_conf10');
        $req = [
            'pay_memberid' => $conf['pay_memberid'],
            'pay_orderid' => $trade_sn,
            'pay_applydate' => date('Y-m-d H:i:s'),
            'pay_bankcode' => $pay_bankcode,
            'pay_notifyurl' => $conf['pay_notifyurl'],
            'pay_callbackurl' => $conf['pay_callbackurl'],
            'pay_amount' => "$pay_amount",
            'pay_productname' => 'pay_productname',
        ];
        $req['pay_md5sign'] = self::builderSign10($req);
        $client = new Client(['verify' => false]);
        try {
            $ret = $client->post($conf['payment_url'], [
                'form_params' => $req,
            ]);
            $resp = $ret->getBody()->getContents();
            $data = json_decode($resp, true);
            if (!isset($data['code']) || $data['code']!=200) {
                exit_out(null, 10001, $data['msg'] ?? '支付异常，请稍后重试', ['请求参数' => $req, '返回数据' => $resp]);
            }
        } catch (Exception $e) {
            throw $e;
        }
        return [
            'data' => $data['data'],
        ];
    }


    public static function requestPayment11($trade_sn, $pay_bankcode, $pay_amount)
    {
        $conf = config('config.payment_conf11');
        $req = [
            'merchantId' => $conf['pay_memberid'],
            'orderId' => $trade_sn,
            'notifyUrl' => $conf['pay_notifyurl'],
            'orderAmount' => "$pay_amount",
            'channelType'=>$pay_bankcode,
        ];
        $req['sign'] = self::builderSign11($req);
        $client = new Client(['verify' => false]);
        try {
            $ret = $client->post($conf['payment_url'], [
                'form_params' => $req,
            ]);
            $resp = $ret->getBody()->getContents();
            $data = json_decode($resp, true);
            if (!isset($data['code']) || $data['code']!=200) {
                exit_out(null, 10001, $data['msg'] ?? '支付异常，请稍后重试', ['请求参数' => $req, '返回数据' => $resp]);
            }
        } catch (Exception $e) {
            throw $e;
        }
        return [
            'data' => $data['data']['payUrl'],
        ];
    }

    public static function requestPayment12($trade_sn, $pay_bankcode, $pay_amount)
    {
        $conf = config('config.payment_conf12');
        $req = [
            'account_id' => $conf['pay_memberid'],
            //'appId'=>0,
            'content_type' => 'json',
            'thoroughfare' => $pay_bankcode,
            'out_trade_no' => $trade_sn,
            'amount' => "$pay_amount.00",
            'callback_url' => $conf['pay_notifyurl'],
            'success_url' => $conf['pay_callbackurl'],
            'error_url'=>$conf['pay_callbackurl'],
            'timestamp' => strtotime(date("Y-m-d H:i:s")),
            'ip'=>request()->ip(),
            'deviceos'=>sysType(),
            'payer_ip'=>'123456789',
            //'userIp' => date('Y-m-d H:i:s'),
        ];
        $req['sign'] = self::builderSign12($req);
        $client = new Client(['verify' => false]);
        try {
            $ret = $client->post($conf['payment_url'], [
                'form_params' => $req,
            ]);
            $resp = $ret->getBody()->getContents();
            $data = json_decode($resp, true);
            if (!isset($data['code']) || $data['code']!=200) {
                exit_out(null, 10001, $data['msg'] ?? '支付异常，请稍后重试', ['请求参数' => $req, '返回数据' => $resp]);
            }
        } catch (Exception $e) {
            throw $e;
        }
        return [
            'data' => $data['data']['pay_url'],
        ];
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
        $str = "{$req['amount']}{$req['code']}{$req['notifyurl']}{$req['orderno']}{$req['returnurl']}" . config('config.payment_conf2')['key'];
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

    public static function builderSign4($req)
    {
        ksort($req);
        $buff = '';
        foreach ($req as $k => $v) {
            $buff .= $k . '=' . $v . '&';
        }
        $buff = trim($buff, '&');

        $str = $buff . config('config.payment_conf4')['key'];
        //echo $str;
        $sign = md5($str);
        return $sign;
    }

    public static function builderSign5($req)
    {
/*         ksort($req);
        $buff = '';
        foreach ($req as $k => $v) {
            $buff .= $k . '=' . $v . '&';
        }
        $buff = trim($buff, '&'); */
        $str = "mid={$req['mid']}&orderid={$req['orderid']}&amount={$req['amount']}&note={$req['note']}&paytype={$req['paytype']}&notifyurl={$req['notifyurl']}&returnurl={$req['returnurl']}&";
        $str = $str . config('config.payment_conf5')['key'];
        //echo $str;
        $sign = md5($str);
        return $sign;
    }

    public static function builderSign5Notify($req)
    {
/*         ksort($req);
        $buff = '';
        foreach ($req as $k => $v) {
            $buff .= $k . '=' . $v . '&';
        }
        $buff = trim($buff, '&'); */
        $str = "mid={$req['mid']}&status=1&id={$req['id']}&orderid={$req['orderid']}&orderamount={$req['orderamount']}&";
        $str = $str . config('config.payment_conf5')['key'];
        //echo $str;
        $sign = md5($str);
        return $sign;
    }

    public static function builderSign6($req)
    {
        ksort($req);
        $buff = '';
        foreach ($req as $k => $v) {
            if($v!=''){
            $buff .= $k . '=' . $v . '&';
            }
        }
        $str = $buff . "key=" . config('config.payment_conf6')['key'];
        $sign = strtoupper(md5($str));
        return $sign;
    }
    public static function builderSign7($req)
    {
        ksort($req);
        $buff = '';
        foreach ($req as $k => $v) {
            if($v!=''){
            $buff .= $k . '=' . $v . '&';
            }
        }
        $str = $buff . "key=" . config('config.payment_conf7')['key'];
        $sign = strtoupper(md5($str));
        return $sign;
    }

    public static function builderSign8($req)
    {
        ksort($req);
        $buff = '';
        foreach ($req as $k => $v) {
            if($v!=''){
            $buff .= $k . '=' . $v . '&';
            }
        }
        $str = $buff . "key=" . config('config.payment_conf8')['key'];
        $sign = md5($str);
        return $sign;
    }

    public static function builderSign9($req)
    {
        ksort($req);
        $buff = '';
        foreach ($req as $k => $v) {
            if($v!=''){
            $buff .= $k . '=' . $v . '&';
            }
        }
        $str = $buff . "key=" . config('config.payment_conf9')['key'];
        $sign = strtoupper(md5($str));
        return $sign;
    }

    public static function builderSign10($req)
    {
        unset($req['pay_productname']);
        ksort($req);
        $buff = '';
        foreach ($req as $k => $v) {
            if($v!=''){
            $buff .= $k . '=' . $v . '&';
            }
        }
        $str = $buff . "key=" . config('config.payment_conf10')['key'];
        $sign = strtoupper(md5($str));
        return $sign;
    }
    public static function builderSign11($req)
    {
        ksort($req);
        $buff = '';
        foreach ($req as $k => $v) {
            if($v!=''){
            $buff .= $k . '=' . $v . '&';
            }
        }
        $str = $buff . "key=" . config('config.payment_conf11')['key'];
        $sign = md5($str);
        return $sign;
    }

    public static function builderSign12($req)
    {
        ksort($req);
        $buff = '';
        foreach ($req as $k => $v) {
            if($v!=''){
            $buff .= $k . '=' . $v . '&';
            }
        }
        $str = $buff . "key=" . config('config.payment_conf12')['key'];
        $sign = md5($str);
        return $sign;
    }

    public  static function getMillisecond()
    {
        list($s1, $s2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }
}
