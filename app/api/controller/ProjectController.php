<?php

namespace app\api\controller;

use app\model\PaymentConfig;
use app\model\Project;
use app\model\ProjectItem;

class ProjectController extends AuthController
{
    public function projectsList()
    {
        $builder = Project::where('status', 1);
        if (isset(request()['type']) && request()['type'] > 0) {
            $fa = config('map.project.type');
            $data = [];
            foreach ($fa as $key => $value) {
                $typeArr = [];
                $typeArr['type'] = $key;
                $typeArr['typeName'] = $value;
                $typeArr['children'] = $builder->where('type', $key)->order('sort', 'asc')->select()->each(function($item) {
                    $item['list'] = ProjectItem::where('project_id', $item['id'])->order('price', 'asc')->select()->toArray();
                    return $item;
                });
                array_push($data, $typeArr);
            }
        } else {
            $data = $builder->order('sort', 'asc')->select()->each(function($item) {
                $item['list'] = ProjectItem::where('project_id', $item['id'])->order('price', 'asc')->select()->toArray();
                return $item;
            });
        }
        return out($data);
    }
}
