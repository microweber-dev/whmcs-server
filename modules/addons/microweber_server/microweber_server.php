<?php
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

define("CLIENTAREA", true);
include "includes/clientfunctions.php";

function microweber_server_config()
{
    $default_redirect_url = 'https://' . $_SERVER['HTTP_HOST'] . '/index.php?m=custom_oauth2';
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
    $html = "<h3>Mark plans wich is a Whitelabel Enterprise</h3>";
    $html .= "<br>";


    $html .= "<button type='button' class='btn btn-success'>Save</button>";
    $html .= '<hr>';

    echo $html;
}

function microweber_server_activate()
{
}

function microweber_server_deactivate()
{
}