<?php

namespace MicroweberServer;

use WHMCS\Database\Capsule;

class ClientAreaController
{

    public function save_whitelabel()
    {

        if (!isset($_SESSION['uid'])) {
            return;
        }

        $service_id = (int) $_POST['service_id'];
        $uid = $_SESSION['uid'];

        $checkIsOwner = Capsule::table('tblhosting')
            ->where('id', $service_id)
            ->where('userid', $uid)->first();

        if (!$checkIsOwner) {
            header('Location: clientarea.php?action=productdetails&id=' . $service_id);
            return;
        }

        $wl_brand_name = $_POST['wl_brand_name'];
        $wl_admin_login_url = $_POST['wl_admin_login_url'];
        $wl_contact_page = $_POST['wl_contact_page'];
        $wl_enable_support_links = $_POST['wl_enable_support_links'];
        $wl_powered_by_link = $_POST['wl_powered_by_link'];
        $wl_hide_powered_by_link = $_POST['wl_hide_powered_by_link'];
        $wl_logo_admin_panel = $_POST['wl_logo_admin_panel'];
        $wl_logo_live_edit_toolbar = $_POST['wl_logo_live_edit_toolbar'];
        $wl_logo_login_screen = $_POST['wl_logo_login_screen'];
        $wl_disable_microweber_marketplace = $_POST['wl_disable_microweber_marketplace'];
        $wl_external_login_server_button_text = $_POST['wl_external_login_server_button_text'];
        $wl_external_login_server_enable = $_POST['wl_external_login_server_enable'];

        $wl_settings = [
            'wl_brand_name'=> $wl_brand_name,
            'wl_admin_login_url'=> $wl_admin_login_url,
            'wl_contact_page'=> $wl_contact_page,
            'wl_enable_support_links'=> $wl_enable_support_links,
            'wl_powered_by_link'=> $wl_powered_by_link,
            'wl_hide_powered_by_link'=> $wl_hide_powered_by_link,
            'wl_logo_admin_panel'=> $wl_logo_admin_panel,
            'wl_logo_live_edit_toolbar'=> $wl_logo_live_edit_toolbar,
            'wl_logo_login_screen'=> $wl_logo_login_screen,
            'wl_disable_microweber_marketplace'=> $wl_disable_microweber_marketplace,
            'wl_external_login_server_button_text'=> $wl_external_login_server_button_text,
            'wl_external_login_server_enable'=> $wl_external_login_server_enable
        ];


        $check = Capsule::table('mod_microweber_cloudconnect_whitelabel_settings')
            ->where('client_id', $uid)
            ->where('service_id', $service_id)
            ->first();
        if (!$check) {

            $insert_table = [
                'service_id' => $service_id,
                'client_id' => $uid,

            ];
            $insert_table = array_merge($insert_table, $wl_settings);

            Capsule::table('mod_microweber_cloudconnect_whitelabel_settings')
                ->insert($insert_table);
        } else {

            Capsule::table('mod_microweber_cloudconnect_whitelabel_settings')
                ->where('id', $check->id)
                ->update($wl_settings);
        }

        //header('Location: clientarea.php?action=productdetails&id=' . $service_id);
    }

    public function generate_api_keys()
    {

        if (!isset($_SESSION['uid'])) {
            return;
        }

        $service_id = (int) $_POST['service_id'];
        $uid = $_SESSION['uid'];

        $checkIsOwner = Capsule::table('tblhosting')
            ->where('id', $service_id)
            ->where('userid', $uid)->first();

        if (!$checkIsOwner) {
            header('Location: clientarea.php?action=productdetails&id=' . $service_id);
            return;
        }

        $api_key = $this->_generate_api_key();

        $check = Capsule::table('mod_microweber_cloudconnect_api_keys')
            ->where('api_key_type', 'default')
            ->where('client_id', $uid)
            ->where('service_id', $service_id)
            ->first();
        if (!$check) {
            // Save new api key
            Capsule::table('mod_microweber_cloudconnect_api_keys')
                ->insert(
                    [
                        'api_key' => $api_key,
                        'api_key_type' => 'default',
                        'client_id' => $uid,
                        'service_id'=> $service_id,
                        'expiration_date'=> null,
                        'created_at'=>date('Y-m-d H:i:s'),
                        'updated_at'=>date('Y-m-d H:i:s'),
                    ]
                );
        } else {
            // Update api key
            Capsule::table('mod_microweber_cloudconnect_api_keys')
                ->where('client_id', $uid)
                ->where('service_id', $service_id)
                ->update(
                    [
                        'api_key' => $api_key,
                        'expiration_date'=> null,
                        'updated_at'=>date('Y-m-d H:i:s'),
                    ]
                );
        }

        header('Location: clientarea.php?action=productdetails&id=' . $service_id);
    }

    private function _generate_api_key()
    {
        return md5(time() . uniqid());
    }
}