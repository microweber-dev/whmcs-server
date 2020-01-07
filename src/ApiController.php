<?php

namespace MicroweberServer;

use WHMCS\Database\Capsule;

class ApiController
{

    public function validate_api_key()
    {

        if (isset($_GET['api_key'])) {
            $api_key = $_GET['api_key'];

            $get_api_key = Capsule::table('mod_microweber_cloudconnect_api_keys')
                ->where('api_key_type', 'default')
                ->where('api_key', $api_key)->first();

            if ($get_api_key) {
                return array('is_correct'=>true);
            }
        }

        return array('is_correct'=>false);
    }

    public function save_usage_report()
    {
        if (isset($_POST['total_clients']) && isset($_POST['whmcs_domain'])) {

            $totalClients = (int)$_POST['total_clients'];
            $whmcsDomain = $_POST['whmcs_domain'];
            $serverIp = $_POST['server_ip'];
            $licenseKey = $_POST['license_key'];

            $reportCheck = Capsule::table('mod_microweber_usage_reports')->where(['domain' => $whmcsDomain])->first();
            if ($reportCheck) {
                // Update
                Capsule::table('mod_microweber_usage_reports')
                    ->where('id', $reportCheck->id)
                    ->update(
                        [
                            'server_ip' => $serverIp,
                            'license_key'=> $licenseKey,
                            'total_clients' => $totalClients,
                            'updated_at' => date("Y-m-d H:i:s")
                        ]
                    );
            } else {
                // Save
                Capsule::table('mod_microweber_usage_reports')->insert([
                    'server_ip' => $serverIp,
                    'license_key'=> $licenseKey,
                    'domain' => $whmcsDomain,
                    'total_clients' => $totalClients,
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s")
                ]);
            }

        }

    }
}