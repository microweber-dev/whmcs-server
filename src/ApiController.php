<?php

namespace MicroweberServer;

use WHMCS\Database\Capsule;

class ApiController
{

    public function suspend_account()
    {
        $validate = $this->_validate_account_params();
        if ($validate['success'] == false) {
            return $validate;
        }

        $domain = $_GET['domain'];
        $api_key = $_GET['api_key'];
        $get_api_key = Capsule::table('mod_microweber_cloudconnect_api_keys')
            ->where('api_key_type', 'default')
            ->where('api_key', $api_key)->first();

        if (!$get_api_key) {
            return array('success'=>false, 'message'=>'Wrong api key.');
        }

        $service = $this->_get_product_service_by_domain($domain, $get_api_key->client_id);
        if (!$service) {
            return array('success'=>false, 'message'=>'Service not found');
        }

        $suspendData = array(
            'serviceid' => $service->id,
            'suspendreason' => 'Suspended by admin.',
        );
        $moduleSuspend = localAPI('ModuleSuspend', $suspendData);

        return array('success'=>true, 'message'=>'Module is suspended.');
    }

    public function create_account()
    {
        $validate = $this->_validate_account_params();
        if ($validate['success'] == false) {
            return $validate;
        }

        echo 1;
        die();
        $username = $_GET['username'];
        $passowrd = $_GET['password'];
        $domain = $_GET['domain'];
        $api_key = $_GET['api_key'];
        $get_api_key = Capsule::table('mod_microweber_cloudconnect_api_keys')
            ->where('api_key_type', 'default')
            ->where('api_key', $api_key)->first();

        if (!$get_api_key) {
            return array('success'=>false, 'message'=>'Wrong api key.');
        }


        var_dump($_GET);
        die();

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

            // Accept order
            $acceptOrderData = array(
                'orderid' => $addOrder['orderid'],
                'autosetup' => true,
                'sendemail' => false,
            );

            $acceptOrder = localAPI('AcceptOrder', $acceptOrderData);

            // Update Client Product
            $clientProductData = array(
                'serviceid' => $addOrder['serviceids'],
                'serviceusername' => $username,
                'servicepassword' => $passowrd
            );
            $updateClientProduct = localAPI('UpdateClientProduct', $clientProductData);

            return array('success'=>true);
        }

        return array('success'=>false);
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


    private function _validate_account_params()
    {
        if (!isset($_GET['api_key']) && !isset($_GET['domain']) && !isset($_GET['username']) && !isset($_GET['password'])) {
            return array('success'=>false, 'message'=>'Wrong parameters.');
        }

        if (empty($_GET['api_key']) || empty($_GET['domain']) || empty($_GET['username']) || empty($_GET['password'])) {
            return array('success'=>false, 'message'=>'Empty parameters.');
        }

        return array('success'=>true);
    }

    private function _get_product_service_by_domain($domain, $client_id) {
        return Capsule::table('tblhosting')->where(['domain' => $domain, 'userid'=>$client_id])->first();
    }
}