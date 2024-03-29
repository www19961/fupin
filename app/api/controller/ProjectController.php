<?php

namespace app\api\controller;

use app\model\PaymentConfig;
use app\model\Project;
use app\model\ProjectItem;

class ProjectController extends AuthController
{
    public function projectsList()
    {
        $data = Project::where('status', 1)->order('sort', 'asc')->select()->each(function($item) {
            $item['list'] = ProjectItem::where('project_id', $item['id'])->order('price', 'asc')->select()->toArray();
            return $item;
        });
        return out($data);
    }
}
