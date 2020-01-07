<?php

use WHMCS\Database\Capsule;
use WHMCS\View\Menu\Item as MenuItem;

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

    if (isset($_GET['action'])) {
        $controller = new \MicroweberServer\ClientAreaController();
        $method = false;
        if (isset($_GET['action'])) {
            $method = $_GET['action'];
        }
        if (method_exists($controller, $method)) {
            $response = $controller->$method($params);
        }
        if ($response) {
            return $response;
        }
        echo 'Action not found.';
        exit;
    }

    $controller = new \MicroweberServer\ApiController();
    $method = false;
    if (isset($_GET['function'])) {
        $method = $_GET['function'];
    }
    if (method_exists($controller, $method)) {
        $response = $controller->$method($params);
    }

    if ($response) {
        header('Content-Type: application/json');
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
    // Usage report table
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

    // Cloud Connect Api Keys
    try {
        if (!Capsule::schema()->hasTable('mod_microweber_cloudconnect_api_keys')) {
            Capsule::schema()->create(
                'mod_microweber_cloudconnect_api_keys',
                function ($table) {
                    $table->increments('id');
                    $table->integer('client_id');
                    $table->text('api_key');
                    $table->string('api_key_type');
                    $table->string('expiration_date')->nullable();
                    $table->timestamps();
                }
            );
        }
    } catch (\Exception $e) {
        echo "Unable to create mod_microweber_cloudconnect_api_keys: {$e->getMessage()}";
    }
}

function microweber_server_deactivate()
{
    try {
        Capsule::schema()->dropIfExists('mod_microweber_usage_reports');
    } catch (\Exception $e) {
        echo "Unable to drop table mod_microweber_usage_reports: {$e->getMessage()}";
    }

    try {
        Capsule::schema()->dropIfExists('mod_microweber_cloudconnect_api_keys');
    } catch (\Exception $e) {
        echo "Unable to drop table mod_microweber_cloudconnect_api_keys: {$e->getMessage()}";
    }
}