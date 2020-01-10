<?php

namespace MicroweberServer;

use WHMCS\Database\Capsule;

class ClientAreaController
{
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