<?php
/**
 * 2019 Payson AB
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 *
 *  @author    Payson AB <integration@payson.se>
 *  @copyright 2019 Payson AB
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class PaysonCheckout1RedirectModuleFrontController extends ModuleFrontController
{

    public $ssl = false;
    
    public function __construct()
    {
        parent::__construct();

        if (Configuration::get('PS_SSL_ENABLED')) {
            $this->ssl = true;
        }
    }

    public function postProcess()
    {
        require_once(_PS_MODULE_DIR_ . 'paysoncheckout1/paysoncheckout1.php');
        $payson = new PaysonCheckout1();
        $payson->redirectToPayson();
        
        // Should never be displayed
        //$this->context->smarty->assign(array('payson_message' => $this->module->l('Redirection to Payson failed. Please try again.', 'redirect')));
        //$this->setTemplate('module:paysoncheckout1/views/templates/front/payment.tpl');
    }
}
