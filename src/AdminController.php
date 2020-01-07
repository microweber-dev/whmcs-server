<?php

namespace MicroweberServer;

use WHMCS\Database\Capsule;

class AdminController
{
    public function index($params)
    {
       /* global $CONFIG;

        $view_file = __DIR__ . '/views/index.php';

        $view = new View($view_file);
        $view->assign('params', $params);
        $view->assign('config', $CONFIG);

        return $view->display();*/
       echo 'it works!';
    }

    public function save($params)
    {

    }
}