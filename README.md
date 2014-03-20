# Payson Prestashop Module

## Description

Module for Prestashop implementing Payson
* Support 
Prestashop 1.5.X and 1.6.X
* Requirements: 
Curl

## Installation

You should have a backup of your web store and database 
* Download the module on your PC 
* Go to the folder paysondirect
* Compress the contents of the catalog 'paysondirect' to another directory and name it to paysondirect.zip
* Log into your web shop Administration Panel
* Go to Modules and click ?Add a new module?
* Upload the paysondirect.zip from your computer

### Configuration


Log into your web shop Administration Panel. 
* Go to Modules -> Payment & Geteways -> Payson and click Install.
* Go to Modules -> Payment & Geteways -> Payson and click Configure 
(You do not need to configure the module by sandbox).
* Enter your Email, Agent ID, MD5 Key and payment method. Click save.

#### Payson Faktura:
Enable Payson Invoice (Only if you have a contract for Payson Invoice).

The module retrives the invoice fee as a product from your webshop. You have to
create a product with a specific reference called "PS_FA".

* Go to catalog-> product in your admin.
* Click Add new.
* Enter the name, Reference, price, tax, status (Disabled) and save the product.

Retail price with tax must be in the range 0 to 40 SEK.
Tax must be 25 %

## Upgrade

You should have a backup of your web store and database.
* Log into your web shop Administration Panel. 
* Go to Modules -> Payment & Geteways -> Payson and click Uninstall.
* Go to Modules -> Payment & Geteways -> Payson and click Delete.
* Go to Installation in this document.

## Usage

If you only are interested to use this module in your store, please download it from [our homepage](https://www.payson.se/integration/moduler/prestashop)

## Contributing

Issue pull requests or send feature requests.