<?php

/**
 * ipn_payson.php callback handler for Payson IPN notifications prestashop
 *
 * @package paymentMethod
 * @copyright Copyright 2012 Payson
 */
include(dirname(__FILE__) . '/../../config/config.inc.php');
/*
 * @return void
 * @param int $id_cart
 * @disc 
 */
paysonIpn();

function paysonIpn() {
    $postData = file_get_contents("php://input");

    if (Configuration::get('PAYSON_MODE') == 'sandbox') {
        include(_PS_MODULE_DIR_ . 'paysondirect/payson/paysonapiTest.php');
        $credentials = new PaysonCredentials(trim(Configuration::get('PAYSON_SANDBOX_AGENTID')), trim(Configuration::get('PAYSON_SANDBOX_MD5KEY')), null);
    } else {
        include(_PS_MODULE_DIR_ . 'paysondirect/payson/paysonapi.php');
        $credentials = new PaysonCredentials(trim(Configuration::get('PAYSON_AGENTID')), trim(Configuration::get('PAYSON_MD5KEY')), null);
    }
    $api = new PaysonApi($credentials);

    // Validate the request
    $response = $api->validate($postData);


    if ($response->isVerified()) {

        // IPN request is verified with Payson				
        $ipn_respons = array(
            'order_id' => 1,
            'valid' => 1,
            'ipn_status' => $response->getPaymentDetails()->getStatus(),
            'sender_email' => $response->getPaymentDetails()->getSenderEmail(),
            'currency_code' => $response->getPaymentDetails()->getCurrencyCode(),
            'tracking_id' => $response->getPaymentDetails()->getTrackingId(),
            'token' => $response->getPaymentDetails()->getToken(),
            'type' => $response->getPaymentDetails()->getType(),
            'purchase_id' => $response->getPaymentDetails()->getPurchaseId(),
            'customer' => $response->getPaymentDetails()->getCustom()
        );

        if ($response->getPaymentDetails()->getType() == "INVOICE") {
            $ipn_invoice_response = array(
                'invoice_status' => $response->getPaymentDetails()->getInvoiceStatus(),
                'shippingAddress_name' => $response->getPaymentDetails()->getShippingAddressName(),
                'shippingAddress_street_ddress' => $response->getPaymentDetails()->getShippingAddressStreetAddress(),
                'shippingAddress_postal_code' => $response->getPaymentDetails()->getShippingAddressPostalCode(),
                'shippingAddress_city' => $response->getPaymentDetails()->getShippingAddressCity(),
                'shippingAddress_country' => $response->getPaymentDetails()->getShippingAddressCountry()
            );

            $ipn_respons = array_merge($ipn_respons, $ipn_invoice_response);
        }



        createPaysonOrderEvents($ipn_respons);

        if ($response->getPaymentDetails()->getType() == "INVOICE") {
            $invoicefee = Db::getInstance()->getRow("SELECT id_product FROM " . _DB_PREFIX_ . "product WHERE reference = 'PS_FA'");

            Db::getInstance()->Execute('INSERT INTO ' . _DB_PREFIX_ . 'cart_product (id_cart, id_product, id_product_attribute, quantity, date_add) VALUES(' . intval($_GET["id_cart"]) . ',' . intval($invoicefee['id_product']) . ',0,1,\'' . pSql(date('Y-m-d h:i:s')) . '\')');
            $cart = new Cart((int) $_GET["id_cart"]);
        }
    } else {
        if (Configuration::get('PAYSON_LOGS') == 'yes')
            Logger::addLog('<Payson Direct api>The response could not validate.', 1, NULL, NULL, NULL, true);
    }
}

/*
 * @return void
 * @param array $ipn_respons
 * @disc The function save the parameters in the database
 */

function createPaysonOrderEvents($ipn_respons) {
    include(_PS_MODULE_DIR_ . 'paysondirect/payson_api/def.payson.php');
    $table_order_events = _DB_PREFIX_ . $paysonDbTableOrderEvents;
    $db3 = Db::getInstance();

    $paysonDirectInsert = "order_id                      = '" . $ipn_respons['order_id'] . "', 
	  						valid                         = '" . $ipn_respons['valid'] . "', 
	  						added                         = NOW(), 
	  						updated                       = NOW(), 
	  						ipn_status                    = '" . $ipn_respons['ipn_status'] . "', 	
	  						sender_email                  = '" . $ipn_respons['sender_email'] . "', 
	  						currency_code                 = '" . $ipn_respons['currency_code'] . "',
	  						tracking_id                   = '" . $ipn_respons['tracking_id'] . "',
	  						type                          = '" . $ipn_respons['type'] . "',
	  						purchase_id                   = '" . $ipn_respons['purchase_id'] . "',					
	  						customer                      = '" . $ipn_respons['customer'] . "', 		
	  						token                         =  '" . $ipn_respons['token'] . "'";
    
    $paysonInvoiceInsert = "";
	if ($ipn_respons['type'] == "INVOICE"){
	    $paysonInvoiceInsert = ",invoice_status = '" . $ipn_respons['invoice_status'] . "', 
		  						shippingAddress_name          = '" . $ipn_respons['shippingAddress_name'] . "', 
		  						shippingAddress_street_ddress = '" . $ipn_respons['shippingAddress_street_ddress'] . "', 
		  						shippingAddress_postal_code   = '" . $ipn_respons['shippingAddress_postal_code'] . "', 
		  						shippingAddress_city          = '" . $ipn_respons['shippingAddress_city'] . "', 
		  						shippingAddress_country       = '" . $ipn_respons['shippingAddress_country'] . "'";

	}

    $q = "INSERT INTO " . $table_order_events . " SET " . $paysonDirectInsert . $paysonInvoiceInsert;

    $db3->Execute($q);
}

function myFile($arg, $arg2 = NULL) {
    $myFile = "testFile.txt";
    if ($myFile == NULL) {
        $myFile = fopen($myFile, "w+");
        fwrite($fh, "\r\n" . date("Y-m-d H:i:s") . "Radera mig n�r du vill");
    }
    $fh = fopen($myFile, 'a') or die("can't open file");
    fwrite($fh, "\r\n" . date("Y-m-d H:i:s") . " **");
    fwrite($fh, $arg . '**');
    fwrite($fh, $arg2);
    fclose($fh);
}

?>