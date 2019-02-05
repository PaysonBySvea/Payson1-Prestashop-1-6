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

{if $status == 'ok'}
	<p>{l s='Your order on %s is complete.' sprintf=$shop_name mod='paysoncheckout1'}
		<br /><br />- {l s='Order amount:' mod='paysoncheckout1'} <span class="price"><strong>{$total_to_pay|escape:'html'}</strong></span>
		{if !isset($reference)}
			<br /><br />- {l s='Order number: #%d' sprintf=$id_order mod='paysoncheckout1'}
                {else}
			<br /><br />- {l s='Order reference: %s' sprintf=$reference mod='paysoncheckout1'}
		{/if}
		<br /><br />{l s='An email has been sent to you with this information.' mod='paysoncheckout1'}
		<br /><br />{l s='For any questions or for further information, please contact our' mod='paysoncheckout1'} <a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='customer service department.' mod='paysoncheckout1'}</a>.
	</p>
{else}
	<p class="warning">
		{l s='We have noticed that there is a problem with your order. If you think this is an error, you can contact our' mod='paysoncheckout1'} 
		<a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='customer service department.' mod='paysoncheckout1'}</a>.
	</p>
{/if}
