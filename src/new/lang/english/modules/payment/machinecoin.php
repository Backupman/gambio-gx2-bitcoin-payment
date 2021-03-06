<?php
/**
 * Machinecoin Payment for Gambio Shopsoftware
 *
 * NOTICE OF LICENSE
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; version 2 of the License
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @copyright   Copyright (c)   2013 CommerceCoding (http://www.commerce-coding.de)
 *                              2014 Machinecoinn   (http://www.machinecoin.org)
 * @author      Alexander Diebler
 *              Jürgen Scholz
 * @license     http://opensource.org/licenses/GPL-2.0  GNU General Public License, version 2 (GPL-2.0)
 */

// Backend Information
define('MODULE_PAYMENT_MACHINECOIN_TEXT_TITLE', 'Machinecoin');
define('MODULE_PAYMENT_MACHINECOIN_TEXT_DESCRIPTION', 'Allow your customers to pay orders with Machinecoins.');

// Configuration Titles & Descriptions
define('MODULE_PAYMENT_MACHINECOIN_STATUS_TITLE', 'Enable Machinecoin Module');
define('MODULE_PAYMENT_MACHINECOIN_STATUS_DESC', 'Would you like to accept payments via Machinecoin?');
define('MODULE_PAYMENT_MACHINECOIN_ALLOWED_TITLE', 'Allowed Zones');
define('MODULE_PAYMENT_MACHINECOIN_ALLOWED_DESC', 'Please enter the zones <b>individually</b> that should be allowed to use this module (e.g. US, UK (leave blank to allow all zones))');
define('MODULE_PAYMENT_MACHINECOIN_UNITS_TITLE', 'Units');
define('MODULE_PAYMENT_MACHINECOIN_UNITS_DESC', 'In which units should Machinecoin prices be displayed?');
define('MODULE_PAYMENT_MACHINECOIN_SOURCE_TITLE', 'Source for Machinecoin exchange rates');
define('MODULE_PAYMENT_MACHINECOIN_SOURCE_DESC', 'About which provider exchange rate information should be queried?');
define('MODULE_PAYMENT_MACHINECOIN_MACEUR_TITLE', 'Euro / Machinecoin');
define('MODULE_PAYMENT_MACHINECOIN_MACEUR_DESC', 'At what price Euro will be converted into Machinecoin?');
define('MODULE_PAYMENT_MACHINECOIN_API_ADDRESS_TITLE', 'Target Address');
define('MODULE_PAYMENT_MACHINECOIN_API_ADDRESS_DESC', 'All payments will be forwarded to this address.');
define('MODULE_PAYMENT_MACHINECOIN_API_CONFIRMS_TITLE', 'Required Confirmations');
define('MODULE_PAYMENT_MACHINECOIN_API_CONFIRMS_DESC', 'Minimum of needed confirmations to accept a payment.');
define('MODULE_PAYMENT_MACHINECOIN_API_SHARED_TITLE', 'Shared');
define('MODULE_PAYMENT_MACHINECOIN_API_SHARED_DESC', 'The transactions are send through a shared wallet to give you greater privacy. (Fee: 0.5%) More information: http://blockchain.info/wallet/send-shared');
define('MODULE_PAYMENT_MACHINECOIN_NEW_STATUS_TITLE', 'Status for unpaid orders');
define('MODULE_PAYMENT_MACHINECOIN_NEW_STATUS_DESC', 'Specify the status that unpaid orders should be assigned.');
define('MODULE_PAYMENT_MACHINECOIN_PAID_STATUS_TITLE', 'Status for paid order');
define('MODULE_PAYMENT_MACHINECOIN_PAID_STATUS_DESC', 'Specify the status, which paid orders should be assigned.');
define('MODULE_PAYMENT_MACHINECOIN_SORT_ORDER_TITLE', 'Display Sort Order');
define('MODULE_PAYMENT_MACHINECOIN_SORT_ORDER_DESC', 'Display sort order. The lowest value is displayed first.');

// Frontend Texts
define('MODULE_PAYMENT_MACHINECOIN_TEXT_FRONTEND_DESCRIPTION', 'Pay your order with Machinecoins. The amount due will be shown again in the order summary. After the completion of your order you will be shown the recipient address. The goods are shipped after receipt of payment.');
define('MODULE_PAYMENT_MACHINECOIN_TEXT_ERROR', 'Payment Error');
define('MODULE_PAYMENT_MACHINECOIN_TEXT_PAYMENT_ERROR', 'There was an error when creating a payment address. Please try again or change the method of payment.');
define('MODULE_PAYMENT_MACHINECOIN_NEW_COMMENT', 'Machinecoin Address: %s | Amount: %s');
define('MODULE_PAYMENT_MACHINECOIN_PAID_COMMENT', 'The Machinecoin amount has been received and confirmed by the specified address.');