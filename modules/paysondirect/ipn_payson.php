<?php

/**
 * ipn_payson.php callback handler for Payson IPN notifications prestashop
 *
 * @package paymentMethod
 * @copyright Copyright 2012 Payson
 */
include_once(dirname(__FILE__) . '/../../config/config.inc.php');

if (version_compare(_PS_VERSION_, '1.6.1.0 ', '<=')) {
    include_once(dirname(__FILE__) . '/../../header.php');
}
/*
 * @return void
 * @param int $id_cart
 * @disc 
 */
paysonIpn();

function paysonIpn() {
    include_once(_PS_MODULE_DIR_ . 'paysondirect/payson/paysonapi.php');
    include_once(_PS_MODULE_DIR_ . 'paysondirect/paysondirect.php');

    $postData = file_get_contents("php://input");
    $token = "";
    $cart_id = intval($_GET["id_cart"]);
    
    $payson = new Paysondirect();

    $api = $payson->getAPIInstance();

    // Validate the request
    $response = $api->validate($postData);
    
    if ($response->isVerified()) {
        $details = $response->getPaymentDetails();
        $payson->CreateOrder($cart_id, $token, $details);
    } else {
        if (Configuration::get('PAYSON_LOGS') == 'yes')
            Logger::addLog('<Payson Direct api>The response could not validate.', 1, NULL, NULL, NULL, true);
    }
}
?>