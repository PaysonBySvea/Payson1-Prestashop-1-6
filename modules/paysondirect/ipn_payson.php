<?php

/**
 * ipn_payson.php callback handler for Payson IPN notifications prestashop
 *
 * @package paymentMethod
 * @copyright Copyright 2012 Payson
 */
include_once(dirname(__FILE__) . '/../../config/config.inc.php');
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
        
        createPaysonOrderEvents($response);
        $details = $response->getPaymentDetails();
        $payson->CreateOrder($cart_id, $token, $details);
    } else {
        if (Configuration::get('PAYSON_LOGS') == 'yes')
            Logger::addLog('<Payson Direct api>The response could not validate.', 1, NULL, NULL, NULL, true);
    }
}

/*
 * @return void
 * @param $ipn_respons
 * @disc The function save the parameters in the database
 */

function createPaysonOrderEvents($response) {
    include_once(_PS_MODULE_DIR_ . 'paysondirect/payson_api/def.payson.php');
    $table_order_events = _DB_PREFIX_ . $paysonDbTableOrderEvents;
    $db = Db::getInstance();

    $paysonDirectInsert = "
        order_id                      = '1', 
	valid                         = '1', 
	added                         = NOW(), 
	updated                       = NOW(), 
	ipn_status                    = '" . $response->getPaymentDetails()->getStatus() . "', 	
	sender_email                  = '" . $response->getPaymentDetails()->getSenderEmail() . "', 
	currency_code                 = '" . $response->getPaymentDetails()->getCurrencyCode() . "',
	tracking_id                   = '" . $response->getPaymentDetails()->getTrackingId() . "',
	type                          = '" . $response->getPaymentDetails()->getType() . "',
	purchase_id                   = '" . $response->getPaymentDetails()->getPurchaseId() . "',					
	customer                      = '" . $response->getPaymentDetails()->getCustom() . "', 		
	token                         =  '" . $response->getPaymentDetails()->getToken() . "'";

    $paysonInvoiceInsert = "";
    if ($response->getPaymentDetails()->getType() == "INVOICE") {
        $paysonInvoiceInsert = ",
                invoice_status                  = '" . $response->getPaymentDetails()->getInvoiceStatus() . "', 
                shippingAddress_name            = '" . $response->getPaymentDetails()->getShippingAddressName() . "', 
		shippingAddress_street_ddress   = '" . $response->getPaymentDetails()->getShippingAddressStreetAddress() . "', 
		shippingAddress_postal_code     = '" . $response->getPaymentDetails()->getShippingAddressPostalCode() . "', 
		shippingAddress_city            = '" . $response->getPaymentDetails()->getShippingAddressCity() . "', 
                shippingAddress_country         = '" . $response->getPaymentDetails()->getShippingAddressCountry() . "'";
    }

    $q = "INSERT INTO " . $table_order_events . " SET " . $paysonDirectInsert . $paysonInvoiceInsert;

    $db->Execute($q);
}

?>