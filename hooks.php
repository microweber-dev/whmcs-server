<?php
/**
 * Created by PhpStorm.
 * User: Bojidar
 * Date: 1/7/2020
 * Time: 1:57 PM
*/


use WHMCS\View\Menu\Item as MenuItem;

add_hook('ClientAreaSecondarySidebar', 1, function (MenuItem $secondarySidebar)
{
    $secondarySidebar->addChild('microweber-panel', array(
        'label' => 'Microweber',
        'uri' => '#',
      //  'icon' => 'fas fa-thumbs-up',
    ));

    $microweberPanel = $secondarySidebar->getChild('microweber-panel');
    $microweberPanel->moveToBack()
        // ->setBodyHtml('Your HTML output goes here...')
    ;

    $microweberPanel->addChild('api-key-generate-link', array(
        'uri' => 'index.php?m=microweber_server&action=api_keys',
        'label' => 'Api Keys',
        'order' => 1,
        'icon' => 'fab fa-lock',
    ));
});