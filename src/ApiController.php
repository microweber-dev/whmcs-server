<?php

namespace MicroweberServer;

use MicroweberServer\Traits\ApiAccountMethods;
use WHMCS\Database\Capsule;

class ApiController
{
    use ApiAccountMethods;

    public function get_whitelabel_settings()
    {
        if (!isset($_GET['domain'])) {
            return array('success' => false, 'error' => 'Domain parameter is required');
        }

        $domain = $_GET['domain'];

        $hosting = Capsule::table('tblhosting')->where(['domain' => $domain])->first();
        if ($hosting) {
            $hostingDetails = Capsule::table('mod_microweber_cloudconnect_hosting_details')
                ->where(['hosting_id' => $hosting->id, 'client_id' => $hosting->userid])
                ->first();

            if ($hostingDetails) {
                $apiKey = Capsule::table('mod_microweber_cloudconnect_api_keys')
                    ->where(['client_id' => $hosting->userid, 'id' => $hostingDetails->api_key_id])
                    ->first();

                $checkSettings = Capsule::table('mod_microweber_cloudconnect_whitelabel_settings')
                    ->where([
                        'service_id' => $apiKey->service_id,
                        'client_id' => $apiKey->client_id
                    ])->first();
                if ($checkSettings) {
                    return array('success' => false, 'settings' => $checkSettings);
                }
            }
        }

        return array('success' => false, 'error' => 'No whitelabel settings');
    }

    public function single_signon()
    {
        $validation = $this->_validate_api_key_is_active();
        if ($validation['success'] == false) {
            return $validation;
        }

        $domain = $_GET['domain'];

        $service = $this->_get_product_service_by_domain($domain, $validation['api_key']->client_id);
        if (!$service) {
            return array('success' => false, 'error' => 'Service not found');
        }

        $decryptPasswordData = array(
            'password2' => $service->password,
        );

        $decryptPassword = localAPI('DecryptPassword', $decryptPasswordData);
        if (isset($decryptPassword['result']) && $decryptPassword['result'] == 'success') {

            $redirectUrl = "http://" . $service->domain . "/api/user_login";

            $redirectUrl .= "?username_encoded=" . base64_encode($service->username);
            $redirectUrl .= "&password_encoded=" . base64_encode($decryptPassword['password']);

            $redirectUrl .= "&redirect=" . "http://" . $service->domain . "/?editmode=y";

            return array('success' => true, 'redirect_url' => $redirectUrl);
        }

        return array('success' => false, 'error' => 'Wrong api details');
    }

    public function validate_api_key()
    {
        if (isset($_GET['api_key'])) {
            $api_key = $_GET['api_key'];

            $get_api_key = Capsule::table('mod_microweber_cloudconnect_api_keys')
                ->where('api_key_type', 'default')
                ->where('api_key', $api_key)->first();

            if ($get_api_key) {
                return array('success' => 'Api key is correct');
            }
        }

        return array('success' => false, 'error' => 'Api key is not valid');
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
                            'license_key' => $licenseKey,
                            'total_clients' => $totalClients,
                            'updated_at' => date("Y-m-d H:i:s")
                        ]
                    );
            } else {
                // Save
                Capsule::table('mod_microweber_usage_reports')->insert([
                    'server_ip' => $serverIp,
                    'license_key' => $licenseKey,
                    'domain' => $whmcsDomain,
                    'total_clients' => $totalClients,
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s")
                ]);
            }

        }

    }

    private function _get_template_by_name($templateName)
    {
        $configOption = Capsule::table('tblproductconfiggroups')->where(['name' => 'Template'])->first();
        if ($configOption) {
            $productConfigOption = Capsule::table('tblproductconfigoptions')->where(['gid' => $configOption->id, 'optionname' => 'Template'])->first();
            if ($productConfigOption) {
                $productConfigOptionSub = Capsule::table('tblproductconfigoptionssub')->where(['configid' => $productConfigOption->gid, 'optionname' => $templateName])->first();
                if ($productConfigOptionSub) {
                    return array('template_id' => $productConfigOptionSub->id, 'config_option_id' => $productConfigOption->gid);
                }
            }

        }

        return false;
    }

    private function _validate_api_key_is_active()
    {
        $validation = $this->_validate_required_params();
        if ($validation['success'] == false) {
            return $validation;
        }

        $apiKey = $_GET['api_key'];
        $getApiKey = Capsule::table('mod_microweber_cloudconnect_api_keys')
            ->where('api_key_type', 'default')
            ->where('api_key', $apiKey)->first();

        if (!$getApiKey) {
            return array('success' => false, 'error' => 'Wrong api key');
        }

        $checkServiceIsValid = Capsule::table('tblhosting')->where(['id' => $getApiKey->service_id, 'userid' => $getApiKey->client_id])->first();
        if (!$checkServiceIsValid) {
            return array('success' => false, 'error' => 'Api key is expired');
        }

        if ($checkServiceIsValid) {
            $error = '';
            $getProduct = Capsule::table('tblproducts')->where(['id' => $checkServiceIsValid->packageid])->first();

            if ($checkServiceIsValid->domainstatus !== 'Active') {
                $error .= 'The api key for ' . $getProduct->name . ' is in status ' . $checkServiceIsValid->domainstatus . ' and must be on status Active';
                return array('success' => false, 'error' => $error);
            }
        }

        return array('success'=>true, 'api_key'=>$getApiKey);
    }

    private function _validate_required_params()
    {
        if (!isset($_GET['api_key']) && !isset($_GET['domain'])) {
            return array('success' => false, 'error' => 'Wrong parameters');
        }

        if (empty($_GET['api_key']) || empty($_GET['domain'])) {
            return array('success' => false, 'error' => 'Empty parameters');
        }

        return array('success' => true);
    }

    private function _get_product_service_by_domain($domain, $client_id)
    {
        return Capsule::table('tblhosting')->where(['domain' => $domain, 'userid' => $client_id])->first();
    }
}