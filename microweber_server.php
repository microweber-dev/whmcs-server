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
    $response = array();
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

    return $response;
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
                    $table->integer('service_id');
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

    // Cloud Connect License Key To Hosting Products Mapping
    try {
        if (!Capsule::schema()->hasTable('mod_microweber_cloudconnect_license_keys_mapping')) {
            Capsule::schema()->create(
                'mod_microweber_cloudconnect_license_keys_mapping',
                function ($table) {
                    $table->increments('id');
                    $table->integer('license_plan_id');
                    $table->integer('product_plan_id')->nullable();
                }
            );
        }
    } catch (\Exception $e) {
        echo "Unable to create mod_microweber_cloudconnect_license_keys_mapping: {$e->getMessage()}";
    }

    // WhiteLabel Settings
    try {
        if (!Capsule::schema()->hasTable('mod_microweber_cloudconnect_whitelabel_settings')) {
            Capsule::schema()->create(
                'mod_microweber_cloudconnect_whitelabel_settings',
                function ($table) {
                    $table->increments('id');
                    $table->integer('client_id');
                    $table->integer('service_id');
                    $table->string('wl_brand_name')->nullable();
                    $table->string('wl_admin_login_url')->nullable();
                    $table->string('wl_contact_page')->nullable();
                    $table->integer('wl_enable_support_links')->nullable();
                    $table->text('wl_powered_by_link')->nullable();
                    $table->integer('wl_hide_powered_by_link')->nullable();
                    $table->string('wl_logo_admin_panel')->nullable();
                    $table->string('wl_logo_live_edit_toolbar')->nullable();
                    $table->string('wl_logo_login_screen')->nullable();
                    $table->integer('wl_disable_microweber_marketplace')->nullable();
                    $table->string('wl_external_login_server_button_text')->nullable();
                    $table->integer('wl_external_login_server_enable')->nullable();
                }
            );
        }
    } catch (\Exception $e) {
        echo "Unable to create mod_microweber_cloudconnect_whitelabel_settings: {$e->getMessage()}";
    }

    // Hosting details
    try {
        if (!Capsule::schema()->hasTable('mod_microweber_cloudconnect_hosting_details')) {
            Capsule::schema()->create(
                'mod_microweber_cloudconnect_hosting_details',
                function ($table) {
                    $table->increments('id');
                    $table->integer('client_id');
                    $table->integer('hosting_id');
                    $table->integer('api_key_id');
                    $table->integer('license_plan_id');
                    $table->integer('product_plan_id');
                    $table->timestamps();
                }
            );
        }
    } catch (\Exception $e) {
        echo "Unable to create mod_microweber_cloudconnect_hosting_details: {$e->getMessage()}";
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

    try {
        Capsule::schema()->dropIfExists('mod_microweber_cloudconnect_license_keys_mapping');
    } catch (\Exception $e) {
        echo "Unable to drop table mod_microweber_cloudconnect_license_keys_mapping: {$e->getMessage()}";
    }

    try {
        Capsule::schema()->dropIfExists('mod_microweber_cloudconnect_whitelabel_settings');
    } catch (\Exception $e) {
        echo "Unable to drop table mod_microweber_cloudconnect_whitelabel_settings: {$e->getMessage()}";
    }

    try {
        Capsule::schema()->dropIfExists('mod_microweber_cloudconnect_hosting_details');
    } catch (\Exception $e) {
        echo "Unable to drop table mod_microweber_cloudconnect_hosting_details: {$e->getMessage()}";
    }

}