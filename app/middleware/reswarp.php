<?php

namespace app\middleware;
use app\model\Setting;

class ResWarp
{
    public function handle($request, \Closure $next)
    {
        
		$response = $next($request);
        $setting=Setting::getSetting();
        if($setting['is_req_encypt']==1){
            // 添加中间件执行代码
            $key=config('config.req_aes_key');
            $iv=config('config.req_aes_iv');

            $json = $response->getContent();
            $jsonData = encryptAES($json,$key,$iv);
            $cryptData = json_encode($jsonData);
            $data ='{"c":"'.$cryptData.'"}';
            //$data=json_encode(['c'=>$cryptData]);
            $response->content($data);
        }
        return $response;
    }
}