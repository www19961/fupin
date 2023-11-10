<?php

namespace app\middleware;
use app\model\Setting;

class ResWarp
{
    protected $noArr = [
        //'common/uploadFile2',
        'common/paynotify',
        'common/payNotify',
        'common/paynotify2',
        'common/payNotify2',
        'common/paynotify3',
        'common/payNotify3',
        'common/paynotify4',
        'common/payNotify4',
    ];

    public function handle($request, \Closure $next)
    {
        
		$response = $next($request);
        $pathInfo = $request->pathinfo();
        if(in_array($pathInfo,$this->noArr)){
            return $response;
        }
        $setting=Setting::getSetting();
        if($setting['is_req_encypt']==1){
            // 添加中间件执行代码
            $key=config('config.req_aes_key');
            $iv=config('config.req_aes_iv');

            $json = $response->getContent();
            //$jsonData = encryptAES($json,$key,$iv);
            $cryptData = encryptAES($json,$key,$iv);
            $data ='{"c":"'.$cryptData.'"}';
            //$data=json_encode(['c'=>$cryptData]);
            $response->content($data);
        }
        return $response;
    }
}