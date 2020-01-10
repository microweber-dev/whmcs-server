<?php

namespace MicroweberServer;

use WHMCS\Database\Capsule;

class AdminController
{
    public function index($params)
    {
        global $CONFIG;

        $license_key_plans = $this->_get_license_key_plans();
        $hosting_plans = $this->_get_hosting_plans();
        $mapping = Capsule::table('mod_microweber_cloudconnect_license_keys_mapping')->get();

        $view_file = dirname(__DIR__) . '/views/index.php';
        $view = new View($view_file);
        $view->assign('params', $params);
        $view->assign('config', $CONFIG);
        $view->assign('license_key_plans', $license_key_plans);
        $view->assign('hosting_plans', $hosting_plans);
        $view->assign('mapping', $mapping);

        return $view->display();
    }

    public function save_mapping($params)
    {
        if (isset($_POST['license_plan']) && is_array($_POST['license_plan'])) {
            foreach ($_POST['license_plan'] as $license_plan_id => $product_plan_id) {

                $checkMapping = Capsule::table('mod_microweber_cloudconnect_license_keys_mapping')
                    ->where('license_plan_id', $license_plan_id)
                    ->first();
                if ($checkMapping) {
                    // Update
                    Capsule::table('mod_microweber_cloudconnect_license_keys_mapping')
                        ->where('license_plan_id', $checkMapping->license_plan_id)
                        ->update([
                            'product_plan_id'=>$product_plan_id
                         ]);
                } else {
                    // Insert
                    Capsule::table('mod_microweber_cloudconnect_license_keys_mapping')->insert([
                        'license_plan_id'=>$license_plan_id,
                        'product_plan_id'=>$product_plan_id
                    ]);
                }

            }
        }

        header('Location: addonmodules.php?module=microweber_server');
    }

    private function _get_license_key_plans()
    {
        return Capsule::table('tblproducts')->where('servertype', 'licensing')->get();
    }

    private function _get_hosting_plans()
    {
        return Capsule::table('tblproducts')->where('servertype', '!=', 'licensing')->get();
    }
}