<?php
/**
 * Created by PhpStorm.
 * User: Bojidar
 * Date: 1/7/2020
 * Time: 1:57 PM
*/


use WHMCS\View\Menu\Item as MenuItem;
use WHMCS\Database\Capsule;


/*
add_hook('ClientAreaSecondarySidebar', 1, function (MenuItem $secondarySidebar)
{
    $secondarySidebar->addChild('microweber-panel', array(
        'label' => 'Microweber',
        'uri' => '#',
      //  'icon' => 'fas fa-thumbs-up',
    ));

    $microweberPanel = $secondarySidebar->getChild('microweber-panel');
    $microweberPanel->moveToBack()
        // ->setBodyHtml('Your HTML output goes here...')
    ;

    $microweberPanel->addChild('api-key-generate-link', array(
        'uri' => 'index.php?m=microweber_server&action=api_keys',
        'label' => 'Api Keys',
        'order' => 1,
        'icon' => 'fab fa-lock',
    ));
});
*/

add_hook('ClientAreaProductDetailsOutput', 1, function ($service) {

    if (!isset($_SESSION['uid'])) {
        return;
    }

    $service_id = (int) $service['service']->id;
    $uid = $_SESSION['uid'];

    $api_key = 'Click to generate';
    //$api_key_expiration_date = 'Never expire';

    $get_api_key = Capsule::table('mod_microweber_cloudconnect_api_keys')
        ->where('api_key_type', 'default')
        ->where('client_id', $uid)
        ->where('service_id', $service_id)
        ->first();

    if (is_object($get_api_key) && isset($get_api_key->api_key) && !empty($get_api_key->api_key)) {
        $api_key = $get_api_key->api_key;
        if ($get_api_key->expiration_date) {
           // $api_key_expiration_date = $get_api_key->expiration_date;
        }
    }

    $panel = '
		<div class="panel panel-default" id="mwPanelConfigurableOptionsPanel">
			   <div class="panel-heading">
				   <h3 class="panel-title">Microweber</h3>
			   </div>
			   <div class="panel-body">
								   <div class="row">
						   <div class="col-md-5 col-xs-6 text-right">
							   <strong>Api Key</strong>
							   <br>
								'.$api_key.'
						   </div>
						   <div class="col-md-7 col-xs-6 text-left">
                           <form action="index.php?m=microweber_server&action=generate_api_keys" method="post" id="" class="form-horizontal">
                               <input type="hidden" value="'.$service_id.'" name="service_id">
                               <button type="submit" class="btn btn-success">
                                    Generate Key
                                </button>
                            </form>
							</div>
					   </div>
				</div>
		   </div>';

    return $panel;

});