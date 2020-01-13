<?php

namespace MicroweberServer\Traits;

trait ApiAccountMethods
{
    public function create_account()
    {
        $validation = $this->_validate_api_key_is_active();
        if ($validation['success'] == false) {
            return $validation;
        }

        $get_license = Capsule::table('tblhosting')
            ->where('id', $validation['api_key']->service_id)
            ->first();
        if (!$get_license) {
            return array('success' => false, 'error' => 'License key not found');
        }

        $get_license_key_mapping = Capsule::table('mod_microweber_cloudconnect_license_keys_mapping')
            ->where('license_plan_id', $get_license->packageid)
            ->first();
        if (!$get_license_key_mapping) {
            return array('success' => false, 'error' => 'License key is not mapped to hosting plan');
        }

        if (!$get_license_key_mapping->product_plan_id) {
            return array('success' => false, 'error' => 'License key is not mapped to hosting plan');
        }

        $domain = $_GET['domain'];
        $template = $_GET['template'];

        $get_template = $this->_get_template_by_name($template);

        $get_service = Capsule::table('tblhosting')
            ->where('domain', $domain)
            ->where('userid', $validation['api_key']->client_id)->first();
        if ($get_service) {

            $this->_update_hosting_details($get_service->id, array(
                'client_id'=> $validation['api_key']->client_id,
                'api_key_id'=> $validation['api_key']->id,
                'product_plan_id'=> $get_license_key_mapping->product_plan_id,
                'license_plan_id'=> $get_license_key_mapping->license_plan_id
            ));

            $update_hosting = Capsule::table('tblhosting')
                ->where('id', $get_service->id)
                ->update(
                    [
                        'packageid' => $get_license_key_mapping->product_plan_id,
                    ]
                );

            $update_hosting_config_options = Capsule::table('tblhostingconfigoptions')
                ->where('relid', $get_service->id)
                ->where('configid', $get_template['config_option_id'])
                ->update(
                    [
                        'optionid' => $get_template['template_id'],
                    ]
                );

            $message = '';
            $module_create_data = array(
                'serviceid' => $get_service->id,
            );
            $module_create = localAPI('ModuleCreate', $module_create_data);

            if (isset($module_create['result']) && $module_create['result'] == 'success') {
                return array('success' => 'Service is successfuly created');
            }

            if (isset($module_create['result']) && $module_create['result'] == 'error') {
                if (isset($module_create['message'])) {
                    $message = $module_create['message'];
                }
                return array('success' => false, 'error' => $message);
            }

        }

        $order_data = array(
            'clientid' => $validation['api_key']->client_id,
            'pid' => array($get_license_key_mapping->product_plan_id),
            'domain' => array($domain),
            'billingcycle' => array('monthly'),
            'noinvoiceemail' => true,
            'noemail' => true,
            'paymentmethod' => 'mailin',
        );

        if ($get_template) {
            $order_data['configoptions'] = array(
                base64_encode(
                    serialize(
                        array(
                            $get_template['config_option_id'] => $get_template['template_id']
                        )
                    )
                )
            );
        }

        $add_order = localAPI('AddOrder', $order_data);
        if (isset($add_order['orderid'])) {

            // Accept order
            $accept_order_data = array(
                'orderid' => $add_order['orderid'],
                'autosetup' => true,
                'sendemail' => false,
            );

            $accept_order = localAPI('AcceptOrder', $accept_order_data);

            $get_service = Capsule::table('tblhosting')
                ->where('domain', $domain)
                ->where('userid', $validation['api_key']->client_id)->first();
            if ($get_service) {
                $this->_update_hosting_details($get_service->id, array(
                    'client_id' => $validation['api_key']->client_id,
                    'api_key_id' => $validation['api_key']->id,
                    'product_plan_id' => $get_license_key_mapping->product_plan_id,
                    'license_plan_id' => $get_license_key_mapping->license_plan_id
                ));
            }

            return array('success' => 'Account is created');
        }

        return array('success' => false);
    }

    public function terminate_account()
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

        $suspendData = array(
            'serviceid' => $service->id,
            'suspendreason' => 'Terminated by admin',
        );
        $moduleSuspend = localAPI('ModuleTerminate', $suspendData);

        return array('success' => 'Service Terminated Successfully');
    }

    public function suspend_account()
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

        $suspendData = array(
            'serviceid' => $service->id,
            'suspendreason' => 'Suspended by admin',
        );
        $moduleSuspend = localAPI('ModuleSuspend', $suspendData);

        return array('success' => 'Service Suspended Successfully');
    }

    public function unsuspend_account()
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

        $unsuspendData = array(
            'serviceid' => $service->id,
            'suspendreason' => 'Unsuspend by admin.',
        );
        $moduleUnsuspend = localAPI('ModuleUnsuspend', $unsuspendData);

        return array('success' => 'Service Unsuspended Successfully.');
    }

    private function _update_hosting_details($hosting_id, $details)
    {
        $get_hosting_details = Capsule::table('mod_microweber_cloudconnect_hosting_details')
            ->where('hosting_id', $hosting_id)->first();

        if ($get_hosting_details) {
            // Update
            $update_hosting_details = Capsule::table('mod_microweber_cloudconnect_hosting_details')
                ->where('hosting_id', $hosting_id)
                ->update(
                    [
                        'api_key_id'=>$details['api_key_id'],
                        'product_plan_id' => $details['product_plan_id'],
                        'license_plan_id' => $details['license_plan_id'],
                        'client_id' => $details['client_id'],
                        'updated_at'=> date('Y-m-d H:i:s'),
                    ]
                );
        } else {
            // Insert
            $insert_hosting_details = Capsule::table('mod_microweber_cloudconnect_hosting_details')
                ->insert(
                    [
                        'hosting_id'=>$hosting_id,
                        'api_key_id'=>$details['api_key_id'],
                        'product_plan_id' => $details['product_plan_id'],
                        'license_plan_id' => $details['license_plan_id'],
                        'client_id' => $details['client_id'],
                        'created_at'=> date('Y-m-d H:i:s'),
                        'updated_at'=> date('Y-m-d H:i:s'),
                    ]
                );
        }
    }
}