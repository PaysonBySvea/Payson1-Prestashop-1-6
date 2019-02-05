{*
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
*}

<div class="row">
	<div class="col-xs-12">
        <p class="payment_module paysoncheckout1">
            <a class="payment-payson-checkout1" href="{$link->getModuleLink('paysoncheckout1', 'redirect')|escape:'html'}" title="{l s='Secure payment with Payson. Invoice, Bank payment or Card payment.' mod='paysoncheckout1'}">
                <img src="{$module_template_dir|escape:'htmlall':'UTF-8'}views/img/p_payment_payson.png" alt="{l s='Pay with Payson Checkout 1.0' mod='paysoncheckout1'}"/>
                {l s='Secure payment with Payson. Invoice, Bank payment or Card payment.' mod='paysoncheckout1'}
            </a>
        </p>
    </div>
</div>
