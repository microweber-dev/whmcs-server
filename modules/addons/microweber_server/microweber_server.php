<?php
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

define("CLIENTAREA", true);
include "includes/clientfunctions.php";

include_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

function microweber_server_config()
{
    $config = array(
        'name' => 'Microweber Server',
        'description' => 'This module allows manage all whitelabels.',
        'version' => '1.0',
        'author' => 'Microweber',
        'language' => 'english',
        'fields' => [

        ]
    );

    return $config;
}

function microweber_server_clientarea($vars)
{

}

function microweber_server_output($vars)
{
    $response = '';
    $params = array();
    if ($_GET) {
        $params = array_merge($params, $_GET);
    }
    if ($_POST) {
        $params = array_merge($params, $_POST);
    }
    if ($vars) {
        $params = array_merge($params, $vars);
    }
    $controller = new \MicroweberServer\AdminController();
    $method = 'index';
    if (isset($params['function'])) {
        $method = $params['function'];
    }
    if (method_exists($controller, $method)) {
        $response = $controller->$method($params);
    }
    echo $response;
}

function microweber_server_activate()
{

}

function microweber_server_deactivate()
{

}