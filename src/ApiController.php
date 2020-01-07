<?php

namespace MicroweberServer;

use WHMCS\Database\Capsule;

class ApiController
{

    public function create_account()
    {

        if (!isset($_GET['api_key']) && !isset($_GET['domain']) && !isset($_GET['username']) && !isset($_GET['password'])) {
            return false;
        }

        if (empty($_GET['api_key']) || empty($_GET['domain']) || empty($_GET['username']) || empty($_GET['password'])) {
            return false;
        }

        $domain = $_GET['domain'];
        $api_key = $_GET['api_key'];
        $get_api_key = Capsule::table('mod_microweber_cloudconnect_api_keys')
            ->where('api_key_type', 'default')
            ->where('api_key', $api_key)->first();

        $product_id = 1;
        $client_id = $get_api_key->client_id;

        $orderData = array(
            'clientid' => $client_id,
            'pid' => array($product_id),
            'domain' => array($domain),
            'billingcycle' => array('monthly'),
           // 'configoptions' => array(base64_encode(serialize(array("1" => 999))), base64_encode(serialize(array("1" => 999)))),
            //'domaintype' => array('register', 'register'),
            'regperiod' => array(1, 2),
            'noinvoiceemail'=>true,
            'noemail' => true,
            'paymentmethod' => 'mailin',
            'dnsmanagement' => array(0 => false, 1 => true),
        );
        $addOrder = localAPI('AddOrder', $orderData);
        if (isset($addOrder['orderid'])) {

            $acceptOrderData = array(
                'orderid' => $addOrder['orderid'],
                'autosetup' => true,
                'sendemail' => false,
            );

            var_dump($addOrder);

            $acceptOrder = localAPI('AcceptOrder', $acceptOrderData);

            print_r($acceptOrder);

        }
    }

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