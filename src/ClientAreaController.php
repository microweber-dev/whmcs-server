<?php

namespace MicroweberServer;

use WHMCS\Database\Capsule;

class ClientAreaController
{
    public function api_keys()
    {
        if (!isset($_SESSION['uid'])) {
            return;
        }

        $uid = $_SESSION['uid'];

        $api_key = '';
        $api_key_expiration_date = 'Never expire';

        $get_api_key = Capsule::table('mod_microweber_cloudconnect_api_keys')
            ->where('api_key_type', 'default')
            ->where('client_id', $uid)->first();

        if (is_object($get_api_key) && isset($get_api_key->api_key) && !empty($get_api_key->api_key)) {
            $api_key = $get_api_key->api_key;
            if ($get_api_key->expiration_date) {
                $api_key_expiration_date = $get_api_key->expiration_date;
            }
        }

        return array(
            'pagetitle' => 'Microweber Api Keys',
            'breadcrumb' => array('index.php?m=microweber_server' => 'Microweber Api Keys'),
            'templatefile' => 'views/api_keys',
            'requirelogin' => true,
            'vars'=> array(
                'api_key'=> $api_key,
                'api_key_expiration_date'=>$api_key_expiration_date
            )
        );
    }


    public function generate_api_keys()
    {

        if (!isset($_SESSION['uid'])) {
            return;
        }

        $uid = $_SESSION['uid'];
        $api_key = $this->_generate_api_key();

        $check = Capsule::table('mod_microweber_cloudconnect_api_keys')
            ->where('api_key_type', 'default')
            ->where('client_id', $uid)->first();
        if (!$check) {
            // Save new api key
            Capsule::table('mod_microweber_cloudconnect_api_keys')
                ->insert(
                    [
                        'api_key' => $api_key,
                        'api_key_type' => 'default',
                        'client_id' => $uid,
                        'expiration_date'=> null,
                        'created_at'=>date('Y-m-d H:i:s'),
                        'updated_at'=>date('Y-m-d H:i:s'),
                    ]
                );
        } else {
            // Update api key
            Capsule::table('mod_microweber_cloudconnect_api_keys')
                ->where('client_id', $uid)
                ->update(
                    [
                        'api_key' => $api_key,
                        'expiration_date'=> null,
                        'updated_at'=>date('Y-m-d H:i:s'),
                    ]
                );
        }

        header('Location: index.php?m=microweber_server&action=api_keys');
    }

    private function _generate_api_key()
    {
        return md5(time() . uniqid());
    }
}