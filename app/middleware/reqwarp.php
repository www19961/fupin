<?php
declare (strict_types = 1);

namespace app\middleware;

class Reqwarp
{
    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure       $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
        $d = $request->param('d');
       
    }
}
