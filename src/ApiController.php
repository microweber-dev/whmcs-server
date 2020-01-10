<?php

namespace MicroweberServer;

use WHMCS\Database\Capsule;

class ApiController
{
    public function single_signon()
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
            return array('success' => false, 'error' => 'Wrong api key');
        }

        $service = $this->_get_product_service_by_domain($domain, $get_api_key->client_id);
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
            return array('success' => false, 'error' => 'Wrong api key');
        }

        $service = $this->_get_product_service_by_domain($domain, $get_api_key->client_id);
        if (!$service) {
            return array('success' => false, 'error' => 'Service not found');
        }

        $suspendData = array(
            'serviceid' => $service->id,
            'suspendreason' => 'Suspended by admin',
        );
        $moduleSuspend = localAPI('ModuleSuspend', $suspendData);

        return array('success' => 'Service Suspended Successfully');
    }

    public function unsuspend_account()
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
            return array('success' => false, 'error' => 'Wrong api key');
        }

        $service = $this->_get_product_service_by_domain($domain, $get_api_key->client_id);
        if (!$service) {
            return array('success' => false, 'error' => 'Service not found');
        }

        $unsuspendData = array(
            'serviceid' => $service->id,
            'suspendreason' => 'Unsuspend by admin.',
        );
        $moduleUnsuspend = localAPI('ModuleUnsuspend', $unsuspendData);

        return array('success' => 'Service Unsuspended Successfully.');
    }

    public function create_account()
    {
        $validate = $this->_validate_account_params();
        if ($validate['success'] == false) {
            return $validate;
        }

        $template = $_GET['template'];
        $domain = $_GET['domain'];
        $api_key = $_GET['api_key'];
        $get_api_key = Capsule::table('mod_microweber_cloudconnect_api_keys')
            ->where('api_key_type', 'default')
            ->where('api_key', $api_key)->first();

        if (!$get_api_key) {
            return array('success' => false, 'error' => 'Wrong api key');
        }

        $get_template = $this->_get_template_by_name($template);

        $get_service = Capsule::table('tblhosting')
            ->where('domain', $domain)
            ->where('userid', $get_api_key->client_id)->first();
        if ($get_service) {

            $update = Capsule::table('tblhostingconfigoptions')
                ->where('relid', $get_service->id)
                ->where('configid', $get_template['config_option_id'])
                ->update(
                    [
                        'optionid' => $get_template['template_id'],
                    ]
                );

            $message = '';
            $moduleCreateData = array(
                'serviceid' => $get_service->id,
            );
            $moduleCreate = localAPI('ModuleCreate', $moduleCreateData);
            if (isset($moduleCreate['result']) && $moduleCreate['result'] == 'success') {
                return array('success' => 'Service is successfuly created');
            }
            if (isset($moduleCreate['result']) && $moduleCreate['result'] == 'error') {
                if (isset($moduleCreate['message'])) {
                    $message = $moduleCreate['message'];
                }
                return array('success' => false, 'error' => $message);
            }
        }

        $product_id = 1;

        $orderData = array(
            'clientid' => $get_api_key->client_id,
            'pid' => array($product_id),
            'domain' => array($domain),
            'billingcycle' => array('monthly'),
            'noinvoiceemail' => true,
            'noemail' => true,
            'paymentmethod' => 'mailin',
        );

        if ($get_template) {
            $orderData['configoptions'] = array(
                base64_encode(
                    serialize(
                        array(
                            $get_template['config_option_id'] => $get_template['template_id']
                        )
                    )
                )
            );
        }

        $addOrder = localAPI('AddOrder', $orderData);
        if (isset($addOrder['orderid'])) {

            // Accept order
            $acceptOrderData = array(
                'orderid' => $addOrder['orderid'],
                'autosetup' => true,
                'sendemail' => false,
            );

            $acceptOrder = localAPI('AcceptOrder', $acceptOrderData);

            return array('success' => 'Account is created');
        }

        return array('success' => false);
    }

    public function terminate_account()
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
            return array('success' => false, 'error' => 'Wrong api key');
        }

        $service = $this->_get_product_service_by_domain($domain, $get_api_key->client_id);
        if (!$service) {
            return array('success' => false, 'error' => 'Service not found');
        }

        $suspendData = array(
            'serviceid' => $service->id,
            'suspendreason' => 'Terminated by admin',
        );
        $moduleSuspend = localAPI('ModuleTerminate', $suspendData);

        return array('success' => 'Service Terminated Successfully');
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

    private function _validate_account_params()
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