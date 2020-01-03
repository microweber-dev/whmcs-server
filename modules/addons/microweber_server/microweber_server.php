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
    $products = array();
    $results = localAPI('GetProducts', array());
    if (isset($results['products']['product'])) {
        foreach ($results['products']['product'] as $product) {
            $products[] = $product;
        }
    }

    $html = "<h3>Mark plans wich is a Whitelabel Enterprise</h3>";

    $html = '<form method="post">';

    foreach ($products as $product) {

        $html .= '<label style="padding: 5px;border: 1px solid #00000021;margin-right: 5px;">';
        $html .= '<input type="checkbox" value="'.$product['pid'].'" name="product_ids[]">';
        $html .= ' ' . $product['name'];
        $html .= '</label>';
        $html .= '<br />';

    }

    $html .= "<button type='submit' class='btn btn-success'>Save</button>";
    $html .= '<hr>';
    $html .= '</form>';

    echo $html;
}

function microweber_server_activate()
{
}

function microweber_server_deactivate()
{
}