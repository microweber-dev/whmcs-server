<?php
$products = array();
$results = localAPI('GetProducts', array());
if (isset($results['products']['product'])) {
    foreach ($results['products']['product'] as $product) {
        $products[] = $product;
    }
}

$html = "<h2>Mark plans wich is a Whitelabel Enterprise</h2>";

$html .= '<form method="post">';

foreach ($products as $product) {

    $html .= '<label style="width:300px;padding: 5px;border: 1px solid #00000021;margin-right: 5px;">';
    $html .= '<input type="checkbox" value="'.$product['pid'].'" name="product_ids[]">';
    $html .= ' ' . $product['name'];
    $html .= '</label>';
    $html .= '<br />';

}

$html .= "<button type='submit' class='btn btn-success'>Save</button>";
$html .= '<hr>';
$html .= '</form>';

echo $html;