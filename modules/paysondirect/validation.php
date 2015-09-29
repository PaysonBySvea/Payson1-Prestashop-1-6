<?php
include_once(dirname(__FILE__) . '/../../config/config.inc.php');
include_once(dirname(__FILE__) . '/paysondirect.php');
include_once(dirname(__FILE__) . '/../../header.php');

if (version_compare(_PS_VERSION_, '1.5.0.0 ', '>=')) {
    $context = Context::getContext();
    $cart = $context->cart;
}

$token = $_GET["TOKEN"];
$cart_id = intval($_GET["id_cart"]);

$payson = new Paysondirect();

// Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
$authorized = false;
foreach (Module::getPaymentModules() as $module)
    if ($module['name'] == 'paysondirect') {
        $authorized = true;
        break;
    }
if (!$authorized)
    die(Tools::displayError('This payment method Payson direct is not available.'));

$payson->CreateOrder($cart_id, $token, NULL);
?>