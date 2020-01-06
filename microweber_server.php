<?php

use WHMCS\Database\Capsule;

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
    $params = array();

    if ($_GET) {
        $params = array_merge($params, $_GET);
    }

    if ($_POST) {
        $params = array_merge($params, $_POST);
    }

    $response = $vars;

    $controller = new \MicroweberServer\ApiController();
    $method = false;
    if (isset($_GET['function'])) {
        $method = $_GET['function'];
    }
    if (method_exists($controller, $method)) {
        $response = $controller->$method($params);
    }

    if ($response) {
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }
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
    try {
        if (!Capsule::schema()->hasTable('mod_microweber_usage_reports')) {
            Capsule::schema()->create(
                'mod_microweber_usage_reports',
                function ($table) {
                    $table->increments('id');
                    $table->string('domain');
                    $table->string('server_ip');
                    $table->string('license_key');
                    $table->integer('total_clients');
                    $table->timestamps();
                }
            );
        }
    } catch (\Exception $e) {
        echo "Unable to create mod_microweber_usage_reports: {$e->getMessage()}";
    }
}

function microweber_server_deactivate()
{
    try {
        Capsule::schema()->dropIfExists('mod_microweber_usage_reports');
    } catch (\Exception $e) {
        echo "Unable to drop table mod_microweber_usage_reports: {$e->getMessage()}";
    }
}