<?php
/**
 * 2018 Payson AB
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 *
 *  @author    Payson AB <integration@payson.se>
 *  @copyright 2018 Payson AB
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class PaysonCheckout1 extends PaymentModule
{
    public $moduleVersion;
    
    public function __construct()
    {
        $this->name = 'paysoncheckout1';
        $this->tab = 'payments_gateways';
        $this->version = '2.0.13';
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->author = 'Payson AB';
        $this->module_key = '';
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Payson Checkout 1.0');
        $this->description = $this->l('Offer a secure payment option with Payson. Invoice, Card payments and Bank payments.');

        $this->moduleVersion = sprintf('payson_checkout1_prestashop16|%s|%s', $this->version, _PS_VERSION_);

        if (!count(Currency::checkPaymentCurrencies($this->id))) {
            $this->warning = $this->l('No currency has been set for this module.');
        }

        if (!defined('_PCO_LOG_')) {
            define('_PCO_LOG_', Configuration::get('PAYSONCHECKOUT1_LOG'));
        }
    }

    public function install()
    {
        if (parent::install() == false ||
                !$this->registerHook('payment') ||
                !$this->registerHook('actionOrderStatusUpdate') ||
                !$this->registerHook('header') ||
                !$this->registerHook('paymentReturn') ||
                !$this->registerHook('shoppingCart')
            ) {
            return false;
        }
        
        $this->adminDefaultSettings();

        $orderStates = OrderState::getOrderStates(Configuration::get('PS_LANG_DEFAULT'));
        $name = $this->l('Betald med Payson Checkout 1.0');
        $config_name = 'PAYSONCHECKOUT1_ORDER_STATE_PAID';
        $this->createPaysonOrderStates($name, $orderStates, $config_name, true);

        return true;
    }
    
    public function hookShoppingCart()
    {
        // Message on One Page Checkout
        if ($this->context->controller->php_self == 'order-opc'  && isset($this->context->cookie->payson_order_error) && $this->context->cookie->payson_order_error != null) {
            $mess = $this->context->cookie->payson_order_error;
            // Delete old messages
            $this->context->cookie->__set('payson_order_error', null);
            return '<div class="alert alert-warning">' . $mess . '</div>';
        }
    }
    
    public function hookHeader()
    {
        if ($this->context->controller->php_self == 'order-opc' || $this->context->controller->php_self == 'order') {
            $this->context->controller->addCSS(_MODULE_DIR_ . 'paysoncheckout1/views/css/payson_checkout1.css', 'all');
        }
    }
    
    public function uninstall()
    {
        if (parent::uninstall() == false ||
                Configuration::deleteByName('PAYSONCHECKOUT1_AGENTID') == false ||
                Configuration::deleteByName('PAYSONCHECKOUT1_APIKEY') == false ||
                Configuration::deleteByName('PAYSONCHECKOUT1_MODE') == false ||
                Configuration::deleteByName('PAYSONCHECKOUT1_LOG') == false ||
                Configuration::deleteByName('PAYSONCHECKOUT1_RECEIPT') == false ||
                //Configuration::deleteByName('PAYSONCHECKOUT1_GUARANTEE') == false ||
                Configuration::deleteByName('PAYSONCHECKOUT1_ORDER_STATE_SHIPPED') == false ||
                Configuration::deleteByName('PAYSONCHECKOUT1_ORDER_STATE_CANCEL') == false ||
                Configuration::deleteByName('PAYSONCHECKOUT1_ORDER_STATE_CREDIT') == false ||
                Configuration::deleteByName('PAYSONCHECKOUT1_INVOICE_ENABLED') == false ||
                Configuration::deleteByName('PAYSONCHECKOUT1_PAYMENT_METHODS') == false ||
                Configuration::deleteByName('PAYSONCHECKOUT1_ACCOUNT_INFO') == false ||
                Configuration::deleteByName('PAYSONCHECKOUT1_INVOICE_FEE') == false ||
                Configuration::deleteByName('PAYSONCHECKOUT1_AGENT_EMAIL') == false
        ) {
            return false;
        }

        return true;
    }
    
    public function hookPayment($params)
    {
        if (!$this->active || !$this->checkCurrency($params['cart'])) {
            return;
        }

        return $this->display(__FILE__, '/views/templates/front/payment_infos.tpl');
    }
    
    public function hookPaymentReturn($params)
    {
        if (!$this->active) {
            return;
        }

        $this->smarty->assign(array(
            'total_to_pay' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
            'status' => 'ok',
            'id_order' => $params['objOrder']->id
        ));
        if (isset($params['objOrder']->reference) && !empty($params['objOrder']->reference)) {
            $this->smarty->assign('reference', $params['objOrder']->reference);
        }

        return $this->display(__FILE__, 'payment_return.tpl');
    }

    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }
        return false;
    }

    public function adminDefaultSettings()
    {
        $invoiceProduct = $this->getProductFromRef('PS_FA');
        if ($invoiceProduct == false) {
            $invoiceProduct = $this->addInvoiceFeeProduct();
        }
        
        Configuration::updateValue('PAYSONCHECKOUT1_MODE', 1);
        Configuration::updateValue('PAYSONCHECKOUT1_AGENTID', '4');
        Configuration::updateValue('PAYSONCHECKOUT1_APIKEY', '2acab30d-fe50-426f-90d7-8c60a7eb31d4');
        Configuration::updateValue('PAYSONCHECKOUT1_PAYMENT_METHODS', 1);
        Configuration::updateValue('PAYSONCHECKOUT1_INVOICE_ENABLED', 0);
        Configuration::updateValue('PAYSONCHECKOUT1_ACCOUNT_INFO', $this->l('Unable to get account information.'));
        Configuration::updateValue('PAYSONCHECKOUT1_INVOICE_FEE', 0);
        $this->updateInvoiceFeeProductPrice(0);
        //Configuration::updateValue('PAYSONCHECKOUT1_GUARANTEE', 0);
        Configuration::updateValue('PAYSONCHECKOUT1_RECEIPT', 0);
        $accountInfo = $this->validateAccount('4', '2acab30d-fe50-426f-90d7-8c60a7eb31d4', 1);
        if ($accountInfo->getAccountEmail() != '' || $accountInfo->getAccountEmail() != null) {
            Configuration::updateValue('PAYSONCHECKOUT1_AGENT_EMAIL', $accountInfo->getAccountEmail());
            if ($accountInfo->getEnabledForInvoice() == true) {
                Configuration::updateValue('PAYSONCHECKOUT1_INVOICE_ENABLED', 1);
                Configuration::updateValue('PAYSONCHECKOUT1_ACCOUNT_INFO', $accountInfo->getAccountEmail() . ', ' . $this->l('Inovice') . ': ' . $this->l('Yes'));
            }
        } else {
            $this::paysonAddLog('Unable to get account information.');
        }
        Configuration::updateValue('PAYSONCHECKOUT1_RECEIPT', 0);
        Configuration::updateValue('PAYSONCHECKOUT1_LOG', 0);
    }
    
    public function adminValidateSettings()
    {
        if (trim(Tools::getValue('PAYSONCHECKOUT1_AGENTID')) == '' || trim(Tools::getValue('PAYSONCHECKOUT1_APIKEY') == '')) {
            return $this->l('Agent ID and API Key are required.');
        }
            
        $this::paysonAddLog(print_r((int) Tools::getValue('PAYSONCHECKOUT1_MODE'), true));
        $accountInfo = $this->validateAccount(trim(Tools::getValue('PAYSONCHECKOUT1_AGENTID')), trim(Tools::getValue('PAYSONCHECKOUT1_APIKEY')), (int) Tools::getValue('PAYSONCHECKOUT1_MODE'));
        $this::paysonAddLog(print_r($accountInfo, true));
        if ($accountInfo->getAccountEmail() == '' || $accountInfo->getAccountEmail() == null) {
            return $this->l('Unable to get account information. Check Agent ID and API Key and turn off Test mode when using credentials for an PaysonAccount.');
        }

        Configuration::updateValue('PAYSONCHECKOUT1_AGENT_EMAIL', $accountInfo->getAccountEmail());

        if ($accountInfo->getEnabledForInvoice() == true) {
            Configuration::updateValue('PAYSONCHECKOUT1_INVOICE_ENABLED', 1);
            if (Tools::getValue('PAYSONCHECKOUT1_INVOICE_FEE') && (Tools::getValue('PAYSONCHECKOUT1_INVOICE_FEE') > 40 || !is_numeric(Tools::getValue('PAYSONCHECKOUT1_INVOICE_FEE')))) {
                return $this->l('Invoice Fee must be between 0-40');
            }
        } else {
            Configuration::updateValue('PAYSONCHECKOUT1_INVOICE_ENABLED', 0);
        }

        $invActive = $accountInfo->getEnabledForInvoice() == true ? $this->l('Yes') : $this->l('No');
        Configuration::updateValue('PAYSONCHECKOUT1_ACCOUNT_INFO', $accountInfo->getAccountEmail() . ', ' . $this->l('Inovice') . ': ' . $invActive);

        return '';
    }
    
    public function getContent()
    {
        $saved = false;
        $errors = '';
        if (Tools::isSubmit('btnSettingsSubmit')) {
            $errors = $this->adminValidateSettings();
            if ($errors === '') {
                if (Tools::getValue('PAYSONCHECKOUT1_INVOICE_FEE') && Tools::getValue('PAYSONCHECKOUT1_INVOICE_FEE') != Configuration::get('PAYSONCHECKOUT1_INVOICE_FEE')) {
                    Configuration::updateValue('PAYSONCHECKOUT1_INVOICE_FEE', Tools::getValue('PAYSONCHECKOUT1_INVOICE_FEE'));
                    $this->updateInvoiceFeeProductPrice(Configuration::get('PAYSONCHECKOUT1_INVOICE_FEE'));
                }
                Configuration::updateValue('PAYSONCHECKOUT1_AGENTID', trim(Tools::getValue('PAYSONCHECKOUT1_AGENTID')));
                Configuration::updateValue('PAYSONCHECKOUT1_APIKEY', trim(Tools::getValue('PAYSONCHECKOUT1_APIKEY')));
                Configuration::updateValue('PAYSONCHECKOUT1_MODE', (int) Tools::getValue('PAYSONCHECKOUT1_MODE'));
                Configuration::updateValue('PAYSONCHECKOUT1_LOG', (int) Tools::getValue('PAYSONCHECKOUT1_LOG'));
                Configuration::updateValue('PAYSONCHECKOUT1_RECEIPT', (int) Tools::getValue('PAYSONCHECKOUT1_RECEIPT'));
                //Configuration::updateValue('PAYSONCHECKOUT1_GUARANTEE', Tools::getValue('PAYSONCHECKOUT1_GUARANTEE'));
                //Configuration::updateValue('PAYSONCHECKOUT1_ORDER_STATE_SHIPPED', (int) Tools::getValue('PAYSONCHECKOUT1_ORDER_STATE_SHIPPED'));
                //Configuration::updateValue('PAYSONCHECKOUT1_ORDER_STATE_CANCEL', (int) Tools::getValue('PAYSONCHECKOUT1_ORDER_STATE_CANCEL'));
                //Configuration::updateValue('PAYSONCHECKOUT1_ORDER_STATE_CREDIT', (int) Tools::getValue('PAYSONCHECKOUT1_ORDER_STATE_CREDIT'));
                if (Configuration::get('PAYSONCHECKOUT1_INVOICE_ENABLED') != 1 && in_array((int) Tools::getValue('PAYSONCHECKOUT1_PAYMENT_METHODS'), array(1, 2, 3, 5))) {
                    Configuration::updateValue('PAYSONCHECKOUT1_PAYMENT_METHODS', 4);
                } else {
                    Configuration::updateValue('PAYSONCHECKOUT1_PAYMENT_METHODS', (int) Tools::getValue('PAYSONCHECKOUT1_PAYMENT_METHODS'));
                }
                $saved = true;
            }
        }

        $this->context->smarty->assign(array(
            'errorMSG' => $errors,
            'isSaved' => $saved,
            'commonform' => $this->createSettingsForm(),
        ));

        return $this->display(__FILE__, 'views/templates/admin/payson_admin.tpl');
    }

    public function createSettingsForm()
    {
        $orderStates = OrderState::getOrderStates((int) $this->context->cookie->id_lang);
        array_unshift($orderStates, array('id_order_state' => '-1', 'name' => $this->l('Deactivated')));
        
        $warnMess = '';

        $paymentMethods = array();
        if (Configuration::get('PAYSONCHECKOUT1_INVOICE_ENABLED') == 1) {
            $paymentMethods = array(
                array(
                    'value' => 1,
                    'label' => $this->l('Invoice/Card/Bank'),),
                array(
                    'value' => 2,
                    'label' => $this->l('Invoice/Card'),),
                array(
                    'value' => 3,
                    'label' => $this->l('Invoice/Bank'),),
                array(
                    'value' => 4,
                    'label' => $this->l('Card/Bank'),),
                array(
                    'value' => 5,
                    'label' => $this->l('Invoice'),),
                array(
                    'value' => 6,
                    'label' => $this->l('Card'),),
                array(
                    'value' => 7,
                    'label' => $this->l('Bank'),),
            );
        } else {
            $paymentMethods = array(
                array(
                    'value' => 4,
                    'label' => $this->l('Card/Bank'),),
                array(
                    'value' => 6,
                    'label' => $this->l('Card'),),
                array(
                    'value' => 7,
                    'label' => $this->l('Bank'),),
            );
        }
        
        $fields_form = array();
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $warnMess,
                'icon' => '',
            ),
            'input' => array(
                array(
                    'type' => 'switch',
                    'label' => $this->l('Test mode'),
                    'name' => 'PAYSONCHECKOUT1_MODE',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'testmode_on',
                            'value' => 1,
                            'label' => $this->l('Yes'),),
                        array(
                            'id' => 'testmode_off',
                            'value' => 0,
                            'label' => $this->l('No'),),
                    ),
                    'desc' => $this->l('Use an Agent ID and API Key from an TestAccount when Test mode is set to Yes.'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Agent ID'),
                    'name' => 'PAYSONCHECKOUT1_AGENTID',
                    'class' => 'fixed-width-lg',
                    'required' => true,
                    'desc' => $this->l('Enter Agent ID.'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('API Key'),
                    'name' => 'PAYSONCHECKOUT1_APIKEY',
                    'class' => 'fixed-width-lg',
                    'required' => true,
                    'desc' => $this->l('Enter API Key.'),
                ),
                array(
                    'type' => 'free',
                    'label' => $this->l('Account'),
                    'name' => 'PAYSONCHECKOUT1_ACCOUNT_INFO',
                    'class' => 'fixed-width-lg',
                    'required' => false,
                    'desc' => $this->l('Account email and invoice activation status.'),
                ),
//                array(
//                    'type' => 'select',
//                    'label' => $this->l('Shipped order status'),
//                    'name' => 'PAYSONCHECKOUT1_ORDER_STATE_SHIPPED',
//                    'desc' => $this->l('Order status Shipped will be sent to Payson when this order status is set.'),
//                    'options' => array(
//                        'query' => $orderStates,
//                        'id' => 'id_order_state',
//                        'name' => 'name',
//                    ),
//                ),
//                array(
//                    'type' => 'select',
//                    'label' => $this->l('Canceled order status'),
//                    'name' => 'PAYSONCHECKOUT1_ORDER_STATE_CANCEL',
//                    'desc' => $this->l('Order status Canceled will be sent to Payson when this order status is set.'),
//                    'options' => array(
//                        'query' => $orderStates,
//                        'id' => 'id_order_state',
//                        'name' => 'name',
//                    ),
//                ),
//                array(
//                    'type' => 'select',
//                    'label' => $this->l('Refunded order status'),
//                    'name' => 'PAYSONCHECKOUT1_ORDER_STATE_CREDIT',
//                    'desc' => $this->l('Payson order will be refunded when this order status is set.'),
//                    'options' => array(
//                        'query' => $orderStates,
//                        'id' => 'id_order_state',
//                        'name' => 'name',
//                    ),
//                ),
//                array(
//                    'type' => 'select',
//                    'label' => $this->l('PaysonGuarantee'),
//                    'name' => 'PAYSONCHECKOUT1_GUARANTEE',
//                    'desc' => $this->l('Setting for PaysonGuarantee.'),
//                    'options' => array(
//                        'query' => array(
//                            array(
//                                'value' => 'NO',
//                                'label' => $this->l('No'),),
//                            array(
//                                'value' => 'OPTIONAL',
//                                'label' => $this->l('Optional'),),
//                            array(
//                                'value' => 'REQUIRED',
//                                'label' => $this->l('Required'),),
//                        ),
//                        'id' => 'value',
//                        'name' => 'label',
//                    ),
//                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Payment methods'),
                    'name' => 'PAYSONCHECKOUT1_PAYMENT_METHODS',
                    'desc' => $this->l('Select which payment methods that will be available.'),
                    'options' => array(
                        'query' => $paymentMethods,
                        'id' => 'value',
                        'name' => 'label',
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Payson reciept page'),
                    'name' => 'PAYSONCHECKOUT1_RECEIPT',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'receipt_on',
                            'value' => 1,
                            'label' => $this->l('Yes'),),
                        array(
                            'id' => 'reciept_off',
                            'value' => 0,
                            'label' => $this->l('No'),),
                    ),
                    'desc' => $this->l('Select Yes to show Payson receipt page after completed purchase.'),
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );
        
        if (Configuration::get('PAYSONCHECKOUT1_INVOICE_ENABLED') == 1) {
            $fields_form[0]['form']['input'][] = array(
                'type' => 'text',
                'label' => $this->l('Invoice fee'),
                'name' => 'PAYSONCHECKOUT1_INVOICE_FEE',
                'class' => 'fixed-width-lg',
                'required' => false,
                'desc' => $this->l('Invoice fee in SEK. Valid amounts 0-40.'),
            );
        }
        
        $fields_form[0]['form']['input'][] = array(
            'type' => 'switch',
            'label' => $this->l('Log'),
            'name' => 'PAYSONCHECKOUT1_LOG',
            'is_bool' => true,
            'values' => array(
                array(
                    'id' => 'log_on',
                    'value' => 1,
                    'label' => $this->l('Yes'),),
                array(
                    'id' => 'log_off',
                    'value' => 0,
                    'label' => $this->l('No'),),
            ),
            'desc' => $this->l('Select Yes to log messages from Payson Checkout. Avoid logging in production enviroments.'),
        );
        
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        if (Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG')) {
            $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        } else {
            $helper->allow_employee_form_lang = 0;
        }

        $helper->submit_action = 'btnSettingsSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) .
                '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm($fields_form);
    }

    // Get values for module configuration
    public function getConfigFieldsValues()
    {
        return array(
            'PAYSONCHECKOUT1_MODE' => Tools::getValue('PAYSONCHECKOUT1_MODE', Configuration::get('PAYSONCHECKOUT1_MODE')),
            //'PAYSONCHECKOUT1_GUARANTEE' => Tools::getValue('PAYSONCHECKOUT1_GUARANTEE', Configuration::get('PAYSONCHECKOUT1_GUARANTEE')),
            'PAYSONCHECKOUT1_AGENTID' => Tools::getValue('PAYSONCHECKOUT1_AGENTID', Configuration::get('PAYSONCHECKOUT1_AGENTID')),
            'PAYSONCHECKOUT1_APIKEY' => Tools::getValue('PAYSONCHECKOUT1_APIKEY', Configuration::get('PAYSONCHECKOUT1_APIKEY')),
            'PAYSONCHECKOUT1_RECEIPT' => Tools::getValue('PAYSONCHECKOUT1_RECEIPT', Configuration::get('PAYSONCHECKOUT1_RECEIPT')),
            'PAYSONCHECKOUT1_LOG' => Tools::getValue('PAYSONCHECKOUT1_LOG', Configuration::get('PAYSONCHECKOUT1_LOG')),
            //'PAYSONCHECKOUT1_ORDER_STATE_CANCEL' => Tools::getValue('PAYSONCHECKOUT1_ORDER_STATE_CANCEL', Configuration::get('PAYSONCHECKOUT1_ORDER_STATE_CANCEL')),
            //'PAYSONCHECKOUT1_ORDER_STATE_SHIPPED' => Tools::getValue('PAYSONCHECKOUT1_ORDER_STATE_SHIPPED', Configuration::get('PAYSONCHECKOUT1_ORDER_STATE_SHIPPED')),
            //'PAYSONCHECKOUT1_ORDER_STATE_CREDIT' => Tools::getValue('PAYSONCHECKOUT1_ORDER_STATE_CREDIT', Configuration::get('PAYSONCHECKOUT1_ORDER_STATE_CREDIT')),
            'PAYSONCHECKOUT1_PAYMENT_METHODS' => Tools::getValue('PAYSONCHECKOUT1_PAYMENT_METHODS', Configuration::get('PAYSONCHECKOUT1_PAYMENT_METHODS')),
            'PAYSONCHECKOUT1_ACCOUNT_INFO' => Tools::getValue('PAYSONCHECKOUT1_ACCOUNT_INFO', Configuration::get('PAYSONCHECKOUT1_ACCOUNT_INFO')),
            'PAYSONCHECKOUT1_INVOICE_FEE' => Tools::getValue('PAYSONCHECKOUT1_INVOICE_FEE', Configuration::get('PAYSONCHECKOUT1_INVOICE_FEE')),
        );
    }

    private function createPaysonOrderStates($name, $orderStates, $config_name, $paid)
    {
        $exists = false;
        foreach ($orderStates as $state) {
            if ($state['name'] == $name) {
                $exists = true;
                Configuration::updateValue($config_name, $state['id_order_state']);

                return;
            }
        }

        $names = array();
        if ($exists == false) {
            $orderstate = new OrderState();
            foreach (Language::getLanguages(false) as $language) {
                $names[$language['id_lang']] = $name;
            }
            $orderstate->name = $names;
            $orderstate->send_email = false;
            $orderstate->invoice = true;
            $orderstate->color = '#448102';
            $orderstate->unremovable = true;
            $orderstate->module_name = 'paysoncheckout1';
            $orderstate->delivery = false;
            $orderstate->shipped = false;
            $orderstate->deleted = false;
            $orderstate->hidden = true;
            $orderstate->logable = true;
            $orderstate->paid = $paid;
            $orderstate->save();

            Configuration::updateValue($config_name, $orderstate->id);

            if (!copy(dirname(__FILE__) . '/views/img/payson_os.gif', _PS_IMG_DIR_ . 'os/' . $orderstate->id . '.gif')) {
                return false;
            }
        }
    }

    public function redirectToPayson()
    {
        try {
            PaysonCheckout1::paysonAddLog('* ' . __FILE__ . ' -> ' . __METHOD__ . ' *');
            $trackingId = time();

            $paysonApi = $this->getPaysonApiInstance();

            if (!isset($this->context->cart) || !$this->context->cart->nbProducts() > 0) {
                throw new Exception($this->l('Your cart is empty.'));
            }

            $cart = $this->context->cart;

            $currency = new Currency($cart->id_currency);
            // Check cart currency
            if (!$this->validPaysonCurrency($currency->iso_code)) {
                throw new Exception($this->l('Unsupported currency. Please use SEK or EUR for payment with Payson.'));
            }
            PaysonCheckout1::paysonAddLog('Cart Currency: ' . $currency->iso_code);
            
            $id_lang = $this->context->language->id;

            $shopInfo = array(
                'shopName' => Configuration::get('PS_SHOP_NAME'),
                'localeCode' => Language::getIsoById($id_lang),
                'currencyCode' => $currency->iso_code
            );

            $cancelUri = $this->context->link->getPageLink('order.php', null, null, array('action' => 'show', 'step' => '3'));
            //Tools::redirect('order.php?step=3');
            $returnUri = $this->context->link->getModuleLink('paysoncheckout1', 'confirmation', array('trackingId' => $trackingId, 'id_cart' => $cart->id, 'call' => 'confirmation'));
            $notificationUri = $this->context->link->getModuleLink('paysoncheckout1', 'notifications', array('trackingId' => $trackingId, 'id_cart' => $cart->id, 'call' => 'notification'));

            $constraints = $this->getConstraints(Configuration::get('PAYSONCHECKOUT1_PAYMENT_METHODS'));
			
			if (in_array(FundingConstraint::INVOICE, $constraints) && Tools::strtolower($currency->iso_code) != 'sek') {
                // Can only use invoice with SEK
                if (($key = array_search(FundingConstraint::INVOICE, $constraints)) !== false) {
                    unset($constraints[$key]);
                }
                $constraints = array_values($constraints);
            }

            $invoiceFee = null;

            if (in_array(FundingConstraint::INVOICE, $constraints)) {
                $invoiceProduct = $this->getProductFromRef('PS_FA');
                PaysonCheckout1::paysonAddLog('Invoice fee product ID: ' . $invoiceProduct->id);
                if ($invoiceProduct != false && Product::getPriceStatic($invoiceProduct->id) > 0) {
                    $invoiceFee = (float) Product::getPriceStatic($invoiceProduct->id);
                    PaysonCheckout1::paysonAddLog('Invoice fee: ' . $invoiceFee);
                }
            }

            $cartTotal = (float) $cart->getOrderTotal(true, Cart::BOTH);
            PaysonCheckout1::paysonAddLog('Cart total: ' . $cartTotal);
            if ($invoiceFee != null) {
                $orderTotal = ($cartTotal + $invoiceFee);
            } else {
                $orderTotal = $cartTotal;
            }
            PaysonCheckout1::paysonAddLog('Order total: ' . $orderTotal);

            $customer = new Customer((int) $cart->id_customer);

            $sender = new Sender(trim($customer->email), trim($customer->firstname), trim($customer->lastname));

            $receiver = array(new Receiver(Configuration::get('PAYSONCHECKOUT1_AGENT_EMAIL'), $orderTotal));

            $payData = new PayData($returnUri, $cancelUri, $notificationUri, $shopInfo['shopName'], $sender, $receiver);
            $payData->setInvoiceFee($invoiceFee);
            $payData->setFundingConstraints($constraints);
            $payData->setOrderItems($this->orderItemsList($cart, $this, $currency));
            //$payData->setGuaranteeOffered(Configuration::get('PAYSONCHECKOUT1_GUARANTEE'));
            $payData->setGuaranteeOffered('NO');
            $payData->setShowReceiptPage(Configuration::get('PAYSONCHECKOUT1_RECEIPT'));
            $payData->SetCurrencyCode($currency->iso_code);
            $payData->setLocaleCode($this->getPaysonLanguageIso(Language::getIsoById($id_lang)));

            //PaysonCheckout1::paysonAddLog('Paydata: ' . print_r($payData, true));
            
            $payResponse = $paysonApi->pay($payData);
            
            //PaysonCheckout1::paysonAddLog('Payresponse: ' . print_r($payResponse, true));

            if ($payResponse->getResponseEnvelope()->wasSuccessful()) {
                // Redirect to Payson
                Tools::redirect($paysonApi->getForwardPayUrl($payResponse));
                //header("Location: " . $paysonApi->getForwardPayUrl($payResponse));
            } else {
                $errMess =  '';
                $apiErrors = $payResponse->getResponseEnvelope()->getErrors();
                foreach ($apiErrors as $error) {
                    $errMess .= $error->getMessage() . ' ';
                }
                throw new Exception($errMess);
            }
        } catch (Exception $ex) {
            // Log error message
            PaysonCheckout1::paysonAddLog('Checkout error: ' . $ex->getMessage(), 2);

            // Set message to display
            $this->context->cookie->__set('payson_order_error', $ex->getMessage());
            
            // Redirect to cart
            //Tools::redirect($this->context->link->getPageLink('cart', null, null, array('action' => 'show')));
            Tools::redirect('order.php?step=3');
        }
    }
    
    public function getPaysonLanguageIso($isoCode)
    {
        switch (Tools::strtoupper($isoCode)) {
            case 'SE':
            case 'SV':
                return 'SV';
            case 'FI':
                return 'FI';
            case 'DA':
            case 'DK':
                return 'DK';
            case 'NO':
            case 'NB':
                return 'NO';
            default:
                return 'EN';
        }
    }
    
    public function getConstraints($id)
    {
        switch ((int) $id) {
            case 1:
                return array(FundingConstraint::INVOICE, FundingConstraint::CREDITCARD, FundingConstraint::BANK);
            case 2:
                return array(FundingConstraint::INVOICE, FundingConstraint::CREDITCARD);
            case 3:
                return array(FundingConstraint::INVOICE, FundingConstraint::BANK);
            case 4:
                return array(FundingConstraint::CREDITCARD, FundingConstraint::BANK);
            case 5:
                return array(FundingConstraint::INVOICE);
            case 6:
                return array(FundingConstraint::CREDITCARD);
            case 7:
                return array(FundingConstraint::BANK);
            default:
                return array(FundingConstraint::CREDITCARD, FundingConstraint::BANK);
        }
    }

    /**
     * Create PS order
     *
     * @return int order ID|boolean
     *
     */
    public function createOrderPS($cart_id, $paymentDetails)
    {
        if (!isset($this->context)) {
            $this->context = Context::getContext();
        }

        PaysonCheckout1::paysonAddLog('Start create order.');

        // Load cart
        $cart = new Cart((int) $cart_id);

        PaysonCheckout1::paysonAddLog('Cart ID: ' . $cart_id);
        PaysonCheckout1::paysonAddLog('Purchase ID: ' . $paymentDetails->getPurchaseId());

        try {
            // Check if order exists
            if ($cart->OrderExists() == false) {
                $currency = new Currency($cart->id_currency);
                
                $customer = new Customer($cart->id_customer);

                $cart->secure_key = $customer->secure_key;
                $cart->id_customer = $customer->id;
                $cart->save();

                $cache_id = 'objectmodel_cart_' . $cart->id . '*';
                Cache::clean($cache_id);
                $cart = new Cart($cart->id);

                $comment = $this->l('Purchase ID:') . ' ' . $paymentDetails->getPurchaseId() . "\n";
                $comment .= $this->l('Checkout Status:') . ' ' . $paymentDetails->getStatus() . "\n";
                $comment .= $this->l('Cart ID:') . ' ' . $cart->id . "\n";
                $comment .= '<br />';

                // Payson order total
                $receivers = $paymentDetails->getReceivers();
                $total = $receivers[0]->getAmount();
                PaysonCheckout1::paysonAddLog('Payson order total: ' . $total);

                // Create order
                $this->validateOrder((int) $cart->id, Configuration::get("PAYSONCHECKOUT1_ORDER_STATE_PAID"), $total, $this->displayName, $comment, array(), (int) $currency->id, false, $customer->secure_key);

                // Get new order ID
                $order = Order::getOrderByCartId((int) ($cart->id));

                return $order;
            } else {
                PaysonCheckout1::paysonAddLog('PS order already exits.');
            }
        } catch (Exception $ex) {
            PaysonCheckout1::paysonAddLog('PS failed to create order: ' . $ex->getMessage());
        }
        return false;
    }

    public function validPaysonCurrency($currency)
    {
        switch (Tools::strtoupper($currency)) {
            case 'SEK':
            case 'EUR':
                return true;
            default:
                return false;
        }
    }
    
    /*
     * @return the object of PaysonApi
     * 
     */
    public function getPaysonApiInstance($agentId = false, $apiKey = false, $testMode = false)
    {
        require_once(_PS_MODULE_DIR_ . 'paysoncheckout1/lib/paysonapi.php');
        if ($agentId === false) {
            $agentId = trim(Configuration::get('PAYSONCHECKOUT1_AGENTID'));
        }
        if ($apiKey === false) {
            $apiKey = trim(Configuration::get('PAYSONCHECKOUT1_APIKEY'));
        }
        if ($testMode === false) {
            $testMode = (int) Configuration::get('PAYSONCHECKOUT1_MODE');
        }
        if ((int) $testMode == 1) {
            $testMode = true;
//            if (Tools::strlen($agentId) < 1 && Tools::strlen($apiKey) < 1) {
//                $agentId = '4';
//                $apiKey = '2acab30d-fe50-426f-90d7-8c60a7eb31d4';
//            }
        }
        //$credentials = new PaysonCredentials($agentId, $apiKey, null, $this->MODULE_VERSION);
        return new PaysonApi(new PaysonCredentials($agentId, $apiKey, null, $this->version), $testMode);
    }

    public function validateAccount($agentId = false, $apiKey = false, $testMode = false)
    {
        $paysonApi = $this->getPaysonApiInstance($agentId, $apiKey, $testMode);
        $response = $paysonApi->accountDetails();
        $accountDetails = $response->getAccountDetails();
        return $accountDetails;
    }

    public function orderItemsList($cart, $payson, $currency = null)
    {
        require_once(_PS_MODULE_DIR_ . 'paysoncheckout1/lib/orderitem.php');
        $lastrate = "notset";
        $has_different_rates = false;

        $orderitemslist = array();
        $totalCartValue = 0;
        $cur = $currency->decimals;
        foreach ($cart->getProducts() as $cartProduct) {
            PaysonCheckout1::paysonAddLog(print_r($cartProduct, true));
            if ($lastrate == "notset") {
                $lastrate = $cartProduct['rate'];
            } elseif ($lastrate != $cartProduct['rate']) {
                $has_different_rates = true;
            }

            $price = Tools::ps_round($cartProduct['price_wt'], 2);
            $totalCartValue += ($price * (int) ($cartProduct['cart_quantity']));

            if (isset($cartProduct['quantity_discount_applies']) && $cartProduct['quantity_discount_applies'] == 1) {
                $payson->discountApplies = 1;
            }

            $my_taxrate = $cartProduct['rate'] / 100;
            
            if ($cartProduct['reference'] == 'PS_FA') {
                $my_taxrate = 0.25;
                $cartProduct['price'] = ($cartProduct['price'] * 0.8);
            }

            //$product_price = Tools::ps_round($cartProduct['price_wt'], $cur * _PS_PRICE_DISPLAY_PRECISION_);
            $product_price = Tools::ps_round($cartProduct['price'], $cur * _PS_PRICE_DISPLAY_PRECISION_);
            $attributes_small = isset($cartProduct['attributes_small']) ? $cartProduct['attributes_small'] : '';
            //$orderitemslist[] = new PaysonEmbedded\OrderItem($cartProduct['name'] . ' ' . $attributes_small, $product_price, $cartProduct['cart_quantity'], number_format($my_taxrate, 3, '.', ''), $cartProduct['id_product']);
            $orderitemslist[] = new OrderItem($cartProduct['name'] . '  ' . $attributes_small, number_format($product_price, 2, '.', ''), $cartProduct['cart_quantity'], number_format($my_taxrate, 3, '.', ''), $cartProduct['id_product']);
        }

        $cartDiscounts = $cart->getDiscounts();

        $total_shipping_wt = Tools::ps_round($cart->getTotalShippingCost(), $cur * _PS_PRICE_DISPLAY_PRECISION_);
        $total_shipping_wot = 0;
        $carrier = new Carrier($cart->id_carrier, $cart->id_lang);

        $shippingToSubtractFromDiscount = 0;
        if ($total_shipping_wt > 0) {
            $carriertax = Tax::getCarrierTaxRate((int) $carrier->id, $cart->id_address_invoice);
            $carriertax_rate = $carriertax / 100;
            $forward_vat = 1 + $carriertax_rate;
            $total_shipping_wot = $total_shipping_wt / $forward_vat;

            if (!empty($cartDiscounts) && (!empty($cartDiscounts[0]['obj'])) && $cartDiscounts[0]['obj']->free_shipping) {
                $shippingToSubtractFromDiscount = $total_shipping_wot;
            } else {
                //$orderitemslist[] = new PaysonEmbedded\OrderItem(isset($carrier->name) ? $carrier->name : $this->l('Shipping'), $total_shipping_wt, 1, number_format($carriertax_rate, 2, '.', ''), $this->l('Shipping'), PaysonEmbedded\OrderItemType::SERVICE);
                $orderitemslist[] = new OrderItem(isset($carrier->name) ? $carrier->name : $this->l('Shipping'), $total_shipping_wot, 1, number_format($carriertax_rate, 2, '.', ''), $this->l('Shipping'));
            }
        }

        $tax_rate_discount = 0;
        $taxDiscount = Cart::getTaxesAverageUsed((int) ($cart->id));

        if (isset($taxDiscount) && $taxDiscount != 1) {
            $tax_rate_discount = $taxDiscount * 0.01;
        }

        $total_discounts = 0;
        foreach ($cart->getCartRules(CartRule::FILTER_ACTION_ALL) as $cart_rule) {
            $value_real = $cart_rule["value_real"];
            $value_tax_exc = $cart_rule["value_tax_exc"];

            if ($has_different_rates == false) {
                $discount_tax_rate = Tools::ps_round($lastrate, $cur * _PS_PRICE_DISPLAY_PRECISION_);
            } else {
                $discount_tax_rate = (($value_real / $value_tax_exc) - 1) * 100;

                $discount_tax_rate = Tools::ps_round($discount_tax_rate, $cur * _PS_PRICE_DISPLAY_PRECISION_);
            }

            if ($totalCartValue <= $total_discounts) {
                $value_real = 0;
            }

            //$orderitemslist[] = new PaysonEmbedded\OrderItem($cart_rule["name"], -(Tools::ps_round(($value_real - $shippingToSubtractFromDiscount), $cur * _PS_PRICE_DISPLAY_PRECISION_)), 1, number_format(($discount_tax_rate * 0.01), 4, '.', ''), $this->l('Discount'), PaysonEmbedded\OrderItemType::DISCOUNT);
            $orderitemslist[] = new OrderItem($cart_rule["name"], -(Tools::ps_round(($value_tax_exc - $shippingToSubtractFromDiscount), $cur * _PS_PRICE_DISPLAY_PRECISION_)), 1, number_format(($discount_tax_rate * 0.01), 4, '.', ''), $this->l('Discount'));
            $total_discounts += $value_real;
        }

        if ($cart->gift) {
            $wrappingTemp = number_format(Tools::convertPrice((float) $cart->getGiftWrappingPrice(false), Currency::getCurrencyInstance((int) $cart->id_currency)), Configuration::get('PS_PRICE_DISPLAY_PRECISION'), '.', '') * number_format((((($cart->getOrderTotal(true, Cart::ONLY_WRAPPING) * 100) / $cart->getOrderTotal(false, Cart::ONLY_WRAPPING))) / 100), 2, '.', '');
            //$orderitemslist[] = new PaysonEmbedded\OrderItem($this->l('Gift Wrapping'), $wrappingTemp, 1, number_format((((($cart->getOrderTotal(true, Cart::ONLY_WRAPPING) * 100) / $cart->getOrderTotal(false, Cart::ONLY_WRAPPING)) - 100) / 100), 2, '.', ''), 'wrapping', PaysonEmbedded\OrderItemType::SERVICE);
            $orderitemslist[] = new OrderItem($this->l('Gift Wrapping'), $wrappingTemp, 1, number_format((((($cart->getOrderTotal(true, Cart::ONLY_WRAPPING) * 100) / $cart->getOrderTotal(false, Cart::ONLY_WRAPPING)) - 100) / 100), 4, '.', ''), 9999);
        }

        return $orderitemslist;
    }
    
    public static function paysonAddLog($message, $severity = 1, $errorCode = null, $objectType = null, $objectId = null, $allowDuplicate = true, $idEmployee = null)
    {
        if (_PCO_LOG_ || $severity > 1) {
            Logger::addLog($message, $severity, $errorCode, $objectType, $objectId, $allowDuplicate, $idEmployee);
        }
    }
    
    public function addInvoiceFeeProduct()
    {
        try {
            $invoiceProduct = new Product();
            $languages = $this->context->controller->getLanguages();
            foreach ($languages as $lang) {
                $invoiceProduct->name[(int) $lang['id_lang']] = $this->l('Invoice fee');
                $invoiceProduct->description[(int) $lang['id_lang']] = $this->l('Invoice fee for Payson Checkout 1.0');
                $invoiceProduct->link_rewrite[(int) $lang['id_lang']] = 'payson-checkout1-invoice-fee';
                $invoiceProduct->description_short[(int) $lang['id_lang']] = $this->l('Invoice fee for Payson Checkout 1.0');
            }
            $invoiceProduct->price = 0;
            $invoiceProduct->wholesale_price = 0;
            $invoiceProduct->reference = 'PS_FA';
            $invoiceProduct->id_tax_rules_group = 0;
            $invoiceProduct->minimal_quantity = 1;
            $invoiceProduct->additional_delivery_times = 0;
            $invoiceProduct->depends_on_stock = 0;
            $invoiceProduct->advanced_stock_management = 0;
            $invoiceProduct->online_only = 1;
            $invoiceProduct->redirect_type = '404';
            $invoiceProduct->id_type_redirected = 0;
            $invoiceProduct->visibility = 'none';
            //$invoiceProduct->quantity = '100';
            //$invoiceProduct->out_of_stock = 1;
            $invoiceProduct->add();
            
            StockAvailable::setQuantity($invoiceProduct->id, null, 100000);

            return $invoiceProduct;
        } catch (Exception $ex) {
            // Log error message
            PaysonCheckout1::paysonAddLog('Add invoice fee product error: ' . $ex->getMessage(), 3);
            return false;
        }
    }
    
    public function getProductFromRef($productRef)
    {
        $result = Db::getInstance()->getRow('SELECT id_product FROM `' . _DB_PREFIX_ . 'product` WHERE reference="' . pSQL($productRef) . '"');
        if (isset($result['id_product']) and (int) ($result['id_product']) > 0) {
            $invoiceProduct = new Product((int) ($result['id_product']), true);
            return $invoiceProduct;
        } else {
            return false;
        }
    }
    
    public function updateInvoiceFeeProductPrice($newFee)
    {
        $invoiceProduct = $this->getProductFromRef('PS_FA');
        if ($invoiceProduct != false) {
            $taxRate = $this->getProductTaxRate($invoiceProduct->id);
            if ($taxRate > 0 && $newFee > 0) {
                $priceWot = ($newFee / (1 + ($taxRate / 100)));
                $vatAmount = $newFee - $priceWot;
                $newFee = number_format($newFee - $vatAmount, 6);
            }
            $invoiceProduct->price = $newFee;
            $invoiceProduct->save();
        }
    }
    
    public function getProductTaxRate($prodcut_id)
    {
        return Tax::getProductTaxRate((int) $prodcut_id);
    }
    
    /*
     * Update Payson order status, ship, cancel or refund
     */

    public function hookActionOrderStatusUpdate($params)
    {
//        $order = new Order((int) $params['id_order']);
//        
//        if ($order->module == 'paysoncheckout1') {
//            $newOrderStatus = $params['newOrderStatus'];
//            
//            $paidName = '';
//            $shippedName = '';
//            $canceledName = '';
//            $refundName = '';
//            $orderStates = OrderState::getOrderStates(Configuration::get('PS_LANG_DEFAULT'));
//            foreach ($orderStates as $state) {
//                if ($state['module_name'] == 'paysoncheckout1' || $state['paid'] == 1) {
//                    $paidName = $state['name'];
//                }
//                if ($state['id_order_state'] == Configuration::get('PAYSONCHECKOUT1_ORDER_STATE_SHIPPED', null, null, $order->id_shop)) {
//                    $shippedName = $state['name'];
//                }
//                if ($state['id_order_state'] == Configuration::get('PAYSONCHECKOUT1_ORDER_STATE_CANCEL', null, null, $order->id_shop)) {
//                    $canceledName = $state['name'];
//                }
//                if ($state['id_order_state'] == Configuration::get('PAYSONCHECKOUT1_ORDER_STATE_CREDIT', null, null, $order->id_shop)) {
//                    $refundName = $state['name'];
//                }
//            }
//            
//            PaysonCheckout1::paysonAddLog('PS order status changed to ' . $newOrderStatus->name . ' for order: ' . $params['id_order']);
//            PaysonCheckout1::paysonAddLog('PS order status to send shipped to Payson: ' . $shippedName);
//            PaysonCheckout1::paysonAddLog('PS order status to send canceled to Payson: ' . $canceledName);
//            PaysonCheckout1::paysonAddLog('PS order status to send refund to Payson: ' . $refundName);
//
//            if ($newOrderStatus->id == Configuration::get('PAYSONCHECKOUT1_ORDER_STATE_SHIPPED', null, null, $order->id_shop) || $newOrderStatus->id == Configuration::get('PAYSONCHECKOUT1_ORDER_STATE_CANCEL', null, null, $order->id_shop) || $newOrderStatus->id == Configuration::get('PAYSONCHECKOUT1_ORDER_STATE_CREDIT', null, null, $order->id_shop)) {
//                $checkout_id = $this->getPaysonOrderEventId($order->id_cart);
//
//                if (isset($checkout_id) && $checkout_id !== null) {
//                    try {
//                        $paysonApi = $this->getPaysonApiInstance();
//                        $checkout = $paysonApi->GetCheckout($checkout_id);
//                        PaysonCheckout1::paysonAddLog('Payson order current status is: ' . $checkout->status);
//                    } catch (Exception $e) {
//                        $this->adminDisplayWarning($this->l('Unable to get Payson order.'));
//                        Logger::addLog('Unable to get Payson order.', 3, null, null, null, true);
//                        return false;
//                    }
//                    if ($newOrderStatus->id == Configuration::get('PAYSONCHECKOUT1_ORDER_STATE_SHIPPED', null, null, $order->id_shop)) {
//                        if ($checkout->status == 'readyToShip') {
//                            try {
//                                PaysonCheckout1::paysonAddLog('Updating Payson order ststus to shipped.', 1, null, null, null, true);
//                                
//                                $checkout->status = 'shipped';
//                                $updatedCheckout = $paysonApi->UpdateCheckout($checkout);
//
//                                $this->updatePaysonOrderEvent($updatedCheckout, $order->id_cart);
//                                PaysonCheckout1::paysonAddLog('Updated Payson order status is: ' . $updatedCheckout->status);
//                            } catch (Exception $e) {
//                                $this->adminDisplayWarning($this->l('Failed to send updated order stauts to Payson. Please log in to your PaysonAccount to manually edit order.'));
//                                Logger::addLog('Order update fail: ' . $e->getMessage(), 3, null, null, null, true);
//                            }
//                        } else {
//                            $this->adminDisplayWarning($this->l('Payson order must have status Waiting for send before it can be set to Shipped. Please log in to your PaysonAccount to manually edit order.'));
//                            Logger::addLog('Failed to update Payson order status to Shipped. Payson order has wrong status: ' . $checkout->status, 3, null, null, null, true);
//                        }
//                    }
//
//                    if ($newOrderStatus->id == Configuration::get('PAYSONCHECKOUT1_ORDER_STATE_CANCEL', null, null, $order->id_shop)) {
//                        if ($checkout->status == 'readyToShip') {
//                            try {
//                                PaysonCheckout1::paysonAddLog('Updating Payson order status to canceled.', 1, null, null, null, true);
//
//                                $checkout->status = 'canceled';
//                                $updatedCheckout = $paysonApi->UpdateCheckout($checkout);
//
//                                $this->updatePaysonOrderEvent($updatedCheckout, $order->id_cart);
//                                PaysonCheckout1::paysonAddLog('Updated Payson order status is: ' . $updatedCheckout->status);
//                            } catch (Exception $e) {
//                                $this->adminDisplayWarning($this->l('Failed to send updated order stauts to Payson. Please log in to your PaysonAccount to manually edit order.'));
//                                Logger::addLog('Order update fail: ' . $e->getMessage(), 3, null, null, null, true);
//                            }
//                        } else {
//                            $this->adminDisplayWarning($this->l('Payson order must have status Waiting for send before it can be set to Canceled. Please log in to your PaysonAccount to manually edit order.'));
//                            Logger::addLog('Failed to update Payson order status to Canceled. Payson order has wrong status: ' . $checkout->status, 3, null, null, null, true);
//                        }
//                    }
//                    
//                    if ($newOrderStatus->id == Configuration::get('PAYSONCHECKOUT1_ORDER_STATE_CREDIT', null, null, $order->id_shop)) {
//                        if ($checkout->status == 'shipped') {
//                            try {
//                                PaysonCheckout1::paysonAddLog('Updating Payson order status to credited.');
//
//                                foreach ($checkout->payData->items as $item) {
//                                    $item->creditedAmount = ($item->unitPrice*$item->quantity);
//                                }
//
//                                $updatedCheckout = $paysonApi->UpdateCheckout($checkout);
//                                
//                                $this->updatePaysonOrderEvent($updatedCheckout, $order->id_cart);
//                                PaysonCheckout1::paysonAddLog('Updated Payson order status is: ' . $updatedCheckout->status);
//                            } catch (Exception $e) {
//                                $this->adminDisplayWarning($this->l('Failed to send updated order stauts to Payson. Please log in to your PaysonAccount to manually edit order.'));
//                                Logger::addLog('Order update fail: ' . $e->getMessage(), 3, null, null, null, true);
//                            }
//                        } else {
//                            $this->adminDisplayWarning($this->l('Payson order must have status Shipped before it can be set to Credited. Please log in to your PaysonAccount to manually edit order.'));
//                            Logger::addLog('Failed to update Payson order status to Credited. Payson order has wrong status: ' . $checkout->status, 3, null, null, null, true);
//                        }
//                    }
//                } else {
//                    $this->adminDisplayWarning($this->l('Failed to send updated order stauts to Payson. Please log in to your PaysonAccount to manually edit order.'));
//                    Logger::addLog('Failed to send updated order stauts to Payson. Unable to get checkout ID.', 3, null, null, null, true);
//                }
//            }
//        }
    }
}
