<?php

namespace app\api\controller;

use app\model\PaymentConfig;
use app\model\Project;

class ProjectController extends AuthController
{
    public function projectsList()
    {
        $data = Project::where('status', 1)->select();
        return out($data);
    }
}
