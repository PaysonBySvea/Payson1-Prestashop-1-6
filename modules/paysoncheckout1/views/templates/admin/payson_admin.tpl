{*
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
*}
{if $isSaved}	
	<div class="alert alert-success">
		{l s='Settings updated' mod='paysoncheckout1'}
	</div>
{/if}
{if $errorMSG!=''}	
	<div class="alert alert-danger">
		 {$errorMSG|escape:'htmlall':'UTF-8'}
	</div>
{/if}

<div class="row">
    <div class="col-xs-12">
            <div class="panel">
                    <div class="panel-heading"><i class="icon-home"></i> {l s='Payson Checkout 1.0' mod='paysoncheckout1'}</div>
                    <div class="row">
                            <p>{l s='Offer a secure payment option with Payson. Invoice, Card payments and Bank payments.' mod='paysoncheckout1'}</p>
                    </div>
            </div>
    </div>
    <div class="col-xs-4">
        <div class="panel">
                <div class="panel-heading"><i class="icon-question"></i> {l s='Documentation' mod='paysoncheckout1'}</div>
                <div class="row">
                        <p>
                                <a href="{$module_dir|escape:'htmlall':'UTF-8'}doc/readme_sv.pdf" target="_blank" id="documentation-en" class="btn btn-default" title="{l s='Documentation' mod='paysoncheckout1'}">
                                        <i class="icon-file-text"></i> {l s='Documentation' mod='paysoncheckout1'} SV
                                </a>
                        
                                <a href="{$module_dir|escape:'htmlall':'UTF-8'}doc/readme_en.pdf" target="_blank" id="documentation-en" class="btn btn-default" title="{l s='Documentation' mod='paysoncheckout1'}">
                                        <i class="icon-file-text"></i> {l s='Documentation' mod='paysoncheckout1'} EN
                                </a>
                        </p>
                        
                </div>
        </div>
    </div>
    <div class="col-xs-4">
            <div class="panel">
                    <div class="panel-heading"><i class="icon-info"></i> {l s='TestAccount' mod='paysoncheckout1'}</div>
                    <div class="row">
                            <p>
                                    <a href="http://test-www.payson.se/testaccount/create/" target="_blank" id="test_agent" class="btn btn-default" title="{l s='Create TestAccount' mod='paysoncheckout1'}">
                                            <i class="icon-user"></i> {l s='Create TestAccount' mod='paysoncheckout1'}
                                    </a>
                            </p>
                    </div>
            </div>
    </div>
    <div class="col-xs-4">
            <div class="panel">
                    <div class="panel-heading"><i class="icon-info"></i> {l s='PaysonAccount' mod='paysoncheckout1'}</div>
                    <div class="row">
                            <p>
                                    <a href="https://account.payson.se/account/create/?type=b" target="_blank" id="payson_account" class="btn btn-default" title="{l s='Create PaysonAccount' mod='paysoncheckout1'}">
                                            <i class="icon-user"></i> {l s='Create PaysonAccount' mod='paysoncheckout1'}
                                    </a>
                            </p>
                    </div>
            </div>
    </div>
</div>

<div class="tabbable">
	<ul class="nav nav-tabs">
		<li class="active"><a href="#pane1" data-toggle="tab"><i class="icon-cogs"></i> {l s='Payson' mod='paysoncheckout1'}</a></li>
	</ul>
	<div class="panel">
            <div class="tab-content">

                    <div id="pane1" class="tab-pane active">
                            <div class="tabbable row payson-admin">
                                    <div class="col-lg-12 tab-content">
                                            <div class="sidebar col-lg-2" style="display: none;">
                                                    <ul class="nav nav-tabs">
                                                            <li class="nav-item"><a href="javascript:;" title="{l s='General settings' mod='paysoncheckout1'}" data-panel="3" data-fieldset="0"><i class="icon-AdminAdmin"></i>{l s='General settings' mod='paysoncheckout1'}</a></li>
                                                    </ul>
                                            </div>
                                            <div id="payson-admin" class="col-lg-12">
                                                    {html_entity_decode($commonform|escape:'htmlall':'UTF-8')}
                                            </div>
                                    </div>
                            </div>
                    </div>

            </div>
	</div>
</div>