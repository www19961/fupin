<?php

namespace app\api\controller;

use app\model\PaymentConfig;
use app\model\Project;
use app\model\ProjectItem;

class ProjectController extends AuthController
{
    public function projectsList()
    {
        $reg = '/(https):\/\/([^\/]+)/i';
        if (isset(request()['type']) && request()['type'] > 0) {
            $fa = config('map.project.type');
            $data = [];
            foreach ($fa as $key => $value) {
                $typeArr = [];
                $typeArr['type'] = $key;
                $typeArr['typeName'] = $value;
                $typeArr['children'] = Project::where('status', 1)->where('type', $key)->order('sort', 'asc')->select()->toArray();
                foreach($typeArr['children'] as $k => $item) {
                    $typeArr['children'][$k]['cover_img'] = preg_replace($reg, env('app.host'), $typeArr['children'][$k]['cover_img']);
                    $typeArr['children'][$k]['details_img'] = preg_replace($reg, env('app.host'), $typeArr['children'][$k]['details_img']);
                    if ($item['is_circle'] == 0) {
                        $typeArr['children'][$k]['list'] = ProjectItem::where('project_id', $item['id'])->order('price', 'asc')->select()->toArray();
                    } else {
                        $typeArr['children'][$k]['list'] = array();
                        $tempPirceArr = ProjectItem::where('project_id', $item['id'])->group('price')->column('price');
                        foreach ($tempPirceArr as $k1 => $v) {
                            $arr = ProjectItem::where('project_id', $item['id'])->where('price', $v)->order('id', 'asc')->select()->toArray();
                            // $typeArr['children'][$k]['list'][$v] = $arr;
                            $typeArr['children'][$k]['list'][$k1] = ['price' => $v, 'list' => $arr];
                        }
                    }
                };
                array_push($data, $typeArr);
            }
        } else {
            $data = Project::where('status', 1)->order('sort', 'asc')->select()->each(function($item) {
                $item['list'] = ProjectItem::where('project_id', $item['id'])->order('price', 'asc')->select()->toArray();
                return $item;
            });
        }
        return out($data);
    }
}
