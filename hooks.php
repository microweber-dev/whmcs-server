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

    $service_id = (int)$service['service']->id;
    $uid = $_SESSION['uid'];

    $api_key = false;
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

    $js_confirm = '';
    $api_key_message = '';
    if (!$api_key) {
        $api_key_message = 'Click to generate';
    } else {
        $js_confirm = 'onsubmit="return confirm(\'Do you really want to generate a new api key?\');"';
    }

    $panel = '
        <script>
            function toggleWhitelabelSettings() {
                $(".js-whitelabel-panel-settings").toggle();
            }
        </script>
		<div class="panel panel-default">
			   <div class="panel-heading">
				   <h3 class="panel-title">Microweber</h3>
			   </div>
			   <div class="panel-body">
						 <div class="row">
						   <div class="col-md-5 col-xs-6 text-right">
							   <strong>Api Key</strong>
							   <br>
								' . $api_key . '
								' . $api_key_message . '
						   </div>
						   <div class="col-md-7 col-xs-6 text-left">
                           <form action="index.php?m=microweber_server&action=generate_api_keys" method="post" ' . $js_confirm . ' class="form-horizontal">
                               <input type="hidden" value="' . $service_id . '" name="service_id">
                               <button type="submit" class="btn btn-success">
                                    <i class="fa fa-key"></i> Generate Key
                                </button>
                            </form>
                            <br />
                            <button type="button" class="btn btn-info" onclick="toggleWhitelabelSettings();"><i class="fa fa-cog"></i> Whitelabel Settings</button>
							</div>
					   </div>
				</div>
		   </div>';

    $panel .= '
		<div class="panel panel-info js-whitelabel-panel-settings" style="display: none;">
			   <div class="panel-heading">
				   <h3 class="panel-title">Microweber Whitelabel Settings</h3>
			   </div>
			   <div class="panel-body">
			   
                <form class="form-horizontal" method="post" action="index.php?m=microweber_server&action=save_whitelabel">
                  <div class="form-group">
                    <label for="text1" class="control-label col-xs-4">Brand Name</label> 
                    <div class="col-xs-8">
                      <input id="text1" name="wl_brand_name" placeholder="Enter the name of your company." type="text" class="form-control">
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="text2" class="control-label col-xs-4">Admin login - White Label URL?</label> 
                    <div class="col-xs-8">
                      <input id="text2" name="wl_admin_login_url" placeholder="Enter website url of your company." type="text" class="form-control">
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="text3" class="control-label col-xs-4">Enable support links?</label> 
                    <div class="col-xs-8">
                      <input id="text3" name="wl_contact_page" placeholder="Enter url of your contact page" type="text" class="form-control">
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="checkbox" class="control-label col-xs-4">Enable support links</label> 
                    <div class="col-xs-8">
                      <label class="checkbox-inline">
                        <input type="checkbox" name="wl_enable_support_links" value="1">
                              Yes
                      </label>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="textarea" class="control-label col-xs-4">Enter "Powered by" text</label> 
                    <div class="col-xs-8">
                      <textarea id="textarea" name="wl_powered_by_link" cols="40" rows="5" class="form-control"></textarea>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="checkbox1" class="control-label col-xs-4">Hide "Powered by" link</label> 
                    <div class="col-xs-8">
                      <label class="checkbox-inline">
                        <input type="checkbox" name="wl_hide_powered_by_link" value="1">
                              Yes
                      </label>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="text4" class="control-label col-xs-4">Logo for Admin panel (size: 180x35px)</label> 
                    <div class="col-xs-8">
                      <input id="text4" name="wl_logo_admin_panel" type="text" class="form-control">
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="text5" class="control-label col-xs-4">Logo for Live-Edit toolbar (size: 50x50px)</label> 
                    <div class="col-xs-8">
                      <input id="text5" name="wl_logo_live_edit_toolbar" type="text" class="form-control">
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="text6" class="control-label col-xs-4">Logo for Login screen (max width: 290px)</label> 
                    <div class="col-xs-8">
                      <input id="text6" name="wl_logo_login_screen" type="text" class="form-control">
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="checkbox2" class="control-label col-xs-4">Disable Microweber Marketplace</label> 
                    <div class="col-xs-8">
                      <label class="checkbox-inline">
                        <input type="checkbox" name="wl_disable_microweber_marketplace" value="1">
                              Yes
                      </label>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="text7" class="control-label col-xs-4">External Login Server Button Text</label> 
                    <div class="col-xs-8">
                      <input id="text7" name="wl_external_login_server_button_text" type="text" placeholder="Login with Microweber Account" class="form-control">
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="checkbox3" class="control-label col-xs-4">External Login Server Enable</label> 
                    <div class="col-xs-8">
                      <label class="checkbox-inline">
                        <input type="checkbox" name="wl_external_login_server_enable" value="1">
                              Yes
                      </label>
                    </div>
                  </div> 
                  <div class="form-group row">
                    <div class="col-xs-offset-4 col-xs-8">
                      <input type="hidden" value="' . $service_id . '" name="service_id">
                      <button name="submit" type="submit" class="btn btn-primary">Save Settings</button>
                    </div>
                  </div>
                </form>
						   
				</div>
		   </div>';

    return $panel;

});