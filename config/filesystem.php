<?php

return [
    // 默认磁盘
    'default' => env('filesystem.driver', 'public'),
    // 磁盘列表
    'disks'   => [
        'local'  => [
            'type' => 'local',
            'root' => app()->getRuntimePath() . 'storage',
        ],
        'public' => [
            // 磁盘类型
            'type'       => 'local',
            // 磁盘路径
            'root'       => app()->getRootPath() . 'public/storage',
            // 磁盘路径对应的外部URL路径
            'url'        => '/storage',
            // 可见性
            'visibility' => 'public',
        ],
        
        // 更多的磁盘配置信息
        'qiniu' =>[									//完全可以自定义的名称
            'type'=>'qiniu',						//可以自定义,实际上是类名小写
            'accessKey' =>'ubBNVF0xaV2EypwaHsQSd_zeg1E0NhAVOsFaP0TJ',		//七牛云的配置,accessKey
            'secretKey'=>'ESiOl6vG2GC0wN3M0vkIGOOJ_-Cp7YYaW-DQeSYS',//七牛云的配置,secretKey
            'bucket'=>'channel0',					//七牛云的配置,bucket空间名
            'domain'=>'dfs.tghsmsp.com',				//七牛云的配置,domain,域名
        ],
    ],
];
