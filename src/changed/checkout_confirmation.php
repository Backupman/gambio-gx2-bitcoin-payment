<?php
/* --------------------------------------------------------------
   checkout_confirmation.php 2012-07-06 gm
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2012 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------


   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(checkout_confirmation.php,v 1.137 2003/05/07); www.oscommerce.com 
   (c) 2003	 nextcommerce (checkout_confirmation.php,v 1.21 2003/08/17); www.nextcommerce.org
   (c) 2003 XT-Commerce - community made shopping http://www.xt-commerce.com ($Id: checkout_confirmation.php 1277 2005-10-01 17:02:59Z mz $)

   Released under the GNU General Public License 
   -----------------------------------------------------------------------------------------
   Third Party contributions:
   agree_conditions_1.01        	Autor:	Thomas Ploenkers (webmaster@oscommerce.at)

   Customers Status v3.x  (c) 2002-2003 Copyright Elari elari@free.fr | www.unlockgsm.com/dload-osc/ | CVS : http://cvs.sourceforge.net/cgi-bin/viewcvs.cgi/elari/?sortby=date#dirlist

   Credit Class/Gift Vouchers/Discount Coupons (Version 5.10)
   http://www.oscommerce.com/community/contributions,282
   Copyright (c) Strider | Strider@oscworks.com
   Copyright (c  Nick Stanko of UkiDev.com, nick@ukidev.com
   Copyright (c) Andre ambidex@gmx.net
   Copyright (c) 2001,2002 Ian C Wilson http://www.phesis.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');

$breadcrumb->add(NAVBAR_TITLE_1_CHECKOUT_CONFIRMATION, xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
$breadcrumb->add(NAVBAR_TITLE_2_CHECKOUT_CONFIRMATION);

// create smarty elements
$smarty = new Smarty;
// include boxes
require (DIR_FS_CATALOG . 'templates/' . CURRENT_TEMPLATE . '/source/boxes.php');
// include needed functions
require_once (DIR_FS_INC . 'xtc_calculate_tax.inc.php');
require_once (DIR_FS_INC . 'xtc_check_stock.inc.php');
require_once (DIR_FS_INC . 'xtc_display_tax_value.inc.php');
require_once(DIR_FS_INC . 'get_products_vpe_array.inc.php');

// if the customer is not logged on, redirect them to the login page

if (!isset ($_SESSION['customer_id']))
	xtc_redirect(xtc_href_link(FILENAME_LOGIN, '', 'SSL'));

// if there is nothing in the customers cart, redirect them to the shopping cart page
if ($_SESSION['cart']->count_contents() <= 0)
	xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART));

// avoid hack attempts during the checkout procedure by checking the internal cartID
if (isset ($_SESSION['cart']->cartID) && isset ($_SESSION['cartID'])) {
	if ($_SESSION['cart']->cartID != $_SESSION['cartID'])
		xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
}

// if no shipping method has been selected, redirect the customer to the shipping method selection page
if (!isset ($_SESSION['shipping']))
	xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));

//GM_MOD moneybookers:
if (isset($_SESSION['tmp_oID'])) unset($_SESSION['tmp_oID']);

// mediafinanz
include_once(DIR_FS_CATALOG . 'includes/modules/mediafinanz/include_checkout_confirmation.php');

//check if display conditions on checkout page is true

if (isset ($_POST['payment']))
	$_SESSION['payment'] = xtc_db_prepare_input($_POST['payment']);

if ($_POST['comments_added'] != '')
	$_SESSION['comments'] = xtc_db_prepare_input($_POST['comments']);

//-- TheMedia Begin check if display conditions on checkout page is true
if (isset ($_POST['cot_gv']))
	$_SESSION['cot_gv'] = true;
// if conditions are not accepted, redirect the customer to the payment method selection page

// BOF GM_MOD:
if (gm_get_conf('GM_CHECK_CONDITIONS') == 1) {
	if ($_REQUEST['conditions'] == false) $error .= str_replace('\n', '<br />', ERROR_CONDITIONS_NOT_ACCEPTED_AGB);
}
if (gm_get_conf('GM_CHECK_WITHDRAWAL') == 1) {
	if ($_POST['withdrawal'] == false) $error .= str_replace('\n', '<br />', ERROR_CONDITIONS_NOT_ACCEPTED_WITHDRAWAL);
}

// check if country of selected payment address is not allowed
$t_country_check_sql = "SELECT a.address_book_id
						FROM
							" . TABLE_ADDRESS_BOOK . " a,
							" . TABLE_COUNTRIES . " c
						WHERE
							a.address_book_id = '" . (int)$_SESSION['billto'] . "' AND
							a.entry_country_id = c.countries_id AND
							c.status = 1";
$t_country_check_result = xtc_db_query($t_country_check_sql);
if(xtc_db_num_rows($t_country_check_result) == 0)
{
	xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
}

if(!isset($_SESSION['conditions']) || !isset($_SESSION['withdrawal']))
{
if((($_POST['conditions'] == false && gm_get_conf('GM_CHECK_CONDITIONS') == 1) || ($_POST['withdrawal'] == false && gm_get_conf('GM_CHECK_WITHDRAWAL') == 1)) && $_POST['iclearRedirect'] != 1 ){
	//xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode($error), 'SSL', true, false));
	$_SESSION['gm_error_message'] = urlencode($error);
	xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
}
else
{
	if($_POST['conditions'] == true) $_SESSION['conditions']='true';
	if($_POST['withdrawal'] == true) $_SESSION['withdrawal']='true';
}
}

if(isset($_GET['payment_error'])) $smarty->assign('ERROR', htmlentities_wrapper($_GET['ret_errormsg']));

// GV Code ICW ADDED FOR CREDIT CLASS SYSTEM
require (DIR_WS_CLASSES . 'order_total.php');
$order = new order();

// GV Code Start
$order_total_modules = new order_total();
$order_total_modules->collect_posts();
$order_total_modules->pre_confirmation_check();
// GV Code End

// load the selected payment module
require (DIR_WS_CLASSES . 'payment.php');
if (isset ($_SESSION['credit_covers']))
	$_SESSION['payment'] = 'no_payment'; // GV Code Start/End ICW added for CREDIT CLASS
unset($order);
$payment_modules = new payment($_SESSION['payment']);

$order = new order();

// GV Code line changed
// BOF GM_MOD:
if ((is_array($payment_modules->modules) && (sizeof($payment_modules->selection()) > 1) && (!is_object($$_SESSION['payment'])) && (!isset ($_SESSION['credit_covers']))) || (is_object($$_SESSION['payment']) && ($$_SESSION['payment']->enabled == false))) {
//	xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(ERROR_NO_PAYMENT_MODULE_SELECTED), 'SSL'));
	$_SESSION['gm_error_message'] = urlencode(ERROR_NO_PAYMENT_MODULE_SELECTED);
	xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
}

// BOF GM_MOD saferpay
if(is_array($payment_modules->modules) && strpos($_SESSION['payment'], 'saferpaygw') === false)
{
	$payment_modules->pre_confirmation_check();
}
// EOF GM_MOD saferpay

// load the selected shipping module
$shipping_modules = new shipping($_SESSION['shipping']);

// Stock Check
if(STOCK_ALLOW_CHECKOUT != 'true'){
    $products = $_SESSION['cart']->get_products();
    $any_out_of_stock = 0;
    
    for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {        
        $coo_properties = MainFactory::create_object('PropertiesControl');
        $t_combis_id = $coo_properties->extract_combis_id($order->products[$i]['id']);
        
        if($t_combis_id == ''){
            // product without properties
            if(STOCK_CHECK == 'true')
            {
                if (xtc_check_stock($order->products[$i]['id'], $order->products[$i]['qty'])){
                    $any_out_of_stock = 1;
                }
            }
        }else{
            // product with properties            
            $t_use_combis_quantity = $coo_properties->get_use_properties_combis_quantity($order->products[$i]['id']);

            if($t_use_combis_quantity != 3){
                if(($t_use_combis_quantity == 0 && STOCK_CHECK == 'true' && ATTRIBUTE_STOCK_CHECK == 'true') || $t_use_combis_quantity == 2){
                    // check combi quantity
                    $t_combis_quantity = $coo_properties->get_properties_combis_quantity($t_combis_id);
                    
                    if($t_combis_quantity < $order->products[$i]['qty']){
                        $any_out_of_stock = 1;
                    }
                }else if($t_use_combis_quantity == 1){
                    // check article quantity
                    if (xtc_check_stock($order->products[$i]['id'], $order->products[$i]['qty'])){
                        $any_out_of_stock = 1;
                    }
                }
            }
        }    
    }
    
    if ($any_out_of_stock == 1){
        xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART));
    }
}

// BOF GM_MOD saferpay
if(strpos($_SESSION['payment'], 'saferpaygw') !== false)
{
	$total_block = '<table id="total_block_table">';
	if(MODULE_ORDER_TOTAL_INSTALLED)
	{
		$order_total_modules->process();
		$total_block .= $order_total_modules->output();
		$t_total_block_array = $order_total_modules->output_array();
	}
	$total_block .= '</table>';
	if(is_array($payment_modules->modules))
	{
		$payment_modules->pre_confirmation_check();
	}
}
// EOF GM_MOD saferpay

require (DIR_WS_INCLUDES . 'header.php');

/* bof gm*/

if (gm_get_conf("GM_LOG_IP") == '1') {

	$smarty->assign('GM_LOG_IP', '1');
	if ($_SERVER["HTTP_X_FORWARDED_FOR"]) {
		$customers_ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
	} else {
		$customers_ip = $_SERVER["REMOTE_ADDR"];
	}

	$smarty->assign('CUSTOMERS_IP', $customers_ip);
	
	if (gm_get_conf("GM_CONFIRM_IP") == '1') {
		$smarty->assign('GM_CONFIRM_IP', '1');
		$smarty->assign('GM_CONFIRM_IP_CHECK', '<input type="checkbox" value="save" name="gm_log_ip" />');
	} elseif(gm_get_conf("GM_SHOW_IP") == '1') {
		$smarty->assign('GM_SHOW_IP', '1');
	}
}
/* eof gm */
$smarty->assign('DELIVERY_LABEL', xtc_address_format($order->delivery['format_id'], $order->delivery, 1, ' ', '<br />'));
if ($_SESSION['credit_covers'] != '1') {
	$smarty->assign('BILLING_LABEL', xtc_address_format($order->billing['format_id'], $order->billing, 1, ' ', '<br />'));
}
$smarty->assign('PRODUCTS_EDIT', xtc_href_link(FILENAME_SHOPPING_CART, '', 'SSL'));
$smarty->assign('SHIPPING_ADDRESS_EDIT', xtc_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL'));
$smarty->assign('BILLING_ADDRESS_EDIT', xtc_href_link(FILENAME_CHECKOUT_PAYMENT_ADDRESS, '', 'SSL'));

if ($_SESSION['sendto'] != false) {

	if ($order->info['shipping_method']) {
		$smarty->assign('SHIPPING_METHOD', $order->info['shipping_method']);
		$smarty->assign('SHIPPING_EDIT', xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));

	}

}

if (sizeof($order->info['tax_groups']) > 1) {

	if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1) {

	}

} else {

}
// BOF GM_MOD
$coo_properties_control = MainFactory::create_object('PropertiesControl');
$coo_properties_view = MainFactory::create_object('PropertiesView');

$t_products_array = array();

$data_products = '<table id="table_products_data" border="0" cellspacing="0" cellpadding="0">';
for ($i = 0, $n = sizeof($order->products); $i < $n; $i++)
{
	$coo_product_item = new product(xtc_get_prid($order->products[$i]['id']));
	
	$t_options_values_array = array();
	$t_attr_weight = 0;
	$t_attr_model_array = array();
	if(isset($order->products[$i]['attributes']) && is_array($order->products[$i]['attributes']))
	{
		foreach($order->products[$i]['attributes'] AS $t_attributes_data_array)
		{
			$t_options_values_array[$t_attributes_data_array['option_id']] = $t_attributes_data_array['value_id'];
		}
				
		// calculate attributes weight and get attributes model
		foreach($t_options_values_array AS $t_option_id => $t_value_id)
		{
			$t_attr_sql = "SELECT
								options_values_weight AS weight,
								weight_prefix AS prefix,
								attributes_model
							FROM
								products_attributes
							WHERE
								products_id				= '" . (int)xtc_get_prid($order->products[$i]['id']) . "' AND
								options_id				= '" . (int)$t_option_id . "' AND
								options_values_id		= '" . (int)$t_value_id	 . "'
							LIMIT 1";
			$t_attr_result = xtc_db_query($t_attr_sql);
			if(xtc_db_num_rows($t_attr_result) == 1)
			{
				$t_attr_result_array = xtc_db_fetch_array($t_attr_result);
				
				if(trim($t_attr_result_array['attributes_model']) != '')
				{
					$t_attr_model_array[] = $t_attr_result_array['attributes_model'];
				}
				
				if($t_attr_result_array['prefix'] == '-')
				{
					$t_attr_weight -= (double)$t_attr_result_array['weight'];
				} 
				else
				{
					$t_attr_weight += (double)$t_attr_result_array['weight'];
				}
			}			
		}
	}
	
	$t_shipping_time = '';
	if(ACTIVATE_SHIPPING_STATUS == 'true')
	{
		$t_shipping_time = $order->products[$i]['shipping_time'];
	}
	
	
	$t_products_weight = '';	
	if(!empty($coo_product_item->data['gm_show_weight']))
	{
		// already contains products properties weight
		$t_products_weight = gm_prepare_number((double)$order->products[$i]['weight'] + $t_attr_weight, $xtPrice->currencies[$xtPrice->actualCurr]['decimal_point']);
	}
	
	$t_products_model = $order->products[$i]['model'];
	if($t_products_model != '' && isset($t_attr_model_array[0]))
	{
		$t_products_model .= '-' . implode('-', $t_attr_model_array);
	}
	else
	{
		$t_products_model .= implode('-', $t_attr_model_array);
	}
	
	#properties
	$t_properties = '';
	$t_combis_id = '';
	$t_properties_array = array();
	if(strpos($order->products[$i]['id'], 'x') !== false)
	{
		$t_combis_id = (int)substr($order->products[$i]['id'], strpos($order->products[$i]['id'], 'x')+1);		
	}	
	if($t_combis_id != '')
	{
		$t_properties = $coo_properties_view->get_order_details_by_combis_id($t_combis_id, 'cart');
		$t_properties_array = $coo_properties_view->v_coo_properties_control->get_properties_combis_details($t_combis_id, $_SESSION['languages_id']);
		
		if(method_exists($coo_properties_control, 'get_properties_combis_model'))
		{
			$t_combi_model = $coo_properties_control->get_properties_combis_model($t_combis_id);

			if(APPEND_PROPERTIES_MODEL == "true") {
				// Artikelnummer (Kombi) an Artikelnummer (Artikel) anh�ngen
				if($t_products_model != '' && $t_combi_model != ''){
					$t_products_model = $t_products_model .'-'. $t_combi_model;
				}else if($t_combi_model != ''){
					$t_products_model = $t_combi_model;
				}
			}else{
				// Artikelnummer (Artikel) durch Artikelnummer (Kombi) ersetzen
				if($t_combi_model != ''){
					$t_products_model = $t_combi_model;
				}
			}

			if($coo_product_item->data['use_properties_combis_shipping_time'] == 1 && ACTIVATE_SHIPPING_STATUS == 'true'){
				$t_shipping_time = $coo_properties_control->get_properties_combis_shipping_time($t_combis_id);
			}
		}
	}
	
	$t_products_item = array(
		'products_name'		=> '',
		'quantity'			=> '',
		'price'				=> $xtPrice->xtcFormat($order->products[$i]['price'], true),
		'final_price'		=> '',
		'shipping_status'	=> '',
		'attributes'		=> '',
		'flag_last_item'	=> false,
		'PROPERTIES'		=> $t_properties,
		'properties_array'	=> $t_properties_array,
		'products_image'	=> (!empty($coo_product_item->data['gm_show_image']) && !empty($coo_product_item->data['products_image'])) ? DIR_WS_THUMBNAIL_IMAGES . $coo_product_item->data['products_image'] : '',
		'products_vpe_array' => get_products_vpe_array($order->products[$i]['id'], $order->products[$i]['price'], $t_options_values_array),
		'products_alt'		=> (!empty($coo_product_item->data['gm_alt_text'])) ? $coo_product_item->data['gm_alt_text'] : $order->products[$i]['name'],
		'checkout_information' => $coo_product_item->data['checkout_information'],
		'products_url'		=> xtc_href_link('request_port.php', 'module=ProductDetails&id=' . $order->products[$i]['id'], 'SSL'),	
		'products_model'	=> $t_products_model,
		'products_weight'	=> $t_products_weight,
		'shipping_time'		=> $t_shipping_time,
		'DATA_ARRAY'		=> $coo_product_item->data
	);
	$t_products_attributes = array();


	if (ACTIVATE_SHIPPING_STATUS == 'true') {
		$t_products_item['shipping_status'] = SHIPPING_TIME . $order->products[$i]['shipping_time'];
		$gm_shipping_status = ' <nobr><span class="shipping_time">(' . $t_products_item['shipping_status'] . ')</span><nobr>';
	}
	else $gm_shipping_status = '';

	$t_products_item['quantity'] = gm_convert_qty($order->products[$i]['qty'], false);
	$t_products_item['products_name'] = $order->products[$i]['name'];
	$t_products_item['final_price'] = $xtPrice->xtcFormat($order->products[$i]['final_price'], true);
	$t_products_item['unit'] = $order->products[$i]['unit_name'];
	
	$data_products .= '<tr>' . "\n" . '            <td align="left" valign="top">' . $t_products_item['quantity'] . ' x ' . $t_products_item['products_name'] . $gm_shipping_status . '</td>' . "\n" . '                <td align="right" valign="top">' . $t_products_item['final_price'] . '</td></tr>' . "\n";
	if ((isset ($order->products[$i]['attributes'])) && (sizeof($order->products[$i]['attributes']) > 0)) {
		for ($j = 0, $n2 = sizeof($order->products[$i]['attributes']); $j < $n2; $j++) 
		{
			$t_products_attributes_item = array(
												'option' => $order->products[$i]['attributes'][$j]['option'],
												'value'  => $order->products[$i]['attributes'][$j]['value']
											);
			$data_products .= '<tr>
								<td align="left" valign="top">
								<nobr>&nbsp; - ' . $t_products_attributes_item['option'] . ': ' . $t_products_attributes_item['value'] . '
								<nobr></td>
								<td align="right" valign="top">&nbsp;</td></tr>';
			$t_products_attributes[] = $t_products_attributes_item;
		}
		// BOF GM_MOD GX-Customizer:
		include(DIR_FS_CATALOG . 'gm/modules/gm_gprint_checkout_confirmation.php');

		$t_products_item['attributes'] = $t_products_attributes;
	}

	$data_products .= '' . "\n";

	if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1) {
		if (sizeof($order->info['tax_groups']) > 1)
			$data_products .= '            <td valign="top" align="right">' . xtc_display_tax_value($order->products[$i]['tax']) . '%</td>' . "\n";
	}
	$data_products .= '</tr><tr><td class="table_products_space"></td></tr>' . "\n";
	$t_products_array[] = $t_products_item;
}
$t_products_array[sizeof($t_products_array)-1]['flag_last_item'] = true;

$data_products .= '</table>';
$smarty->assign('PRODUCTS_BLOCK', $data_products);

$coo_content_master = MainFactory::create_object('ContentMaster');	
$t_confirmation_info_array = $coo_content_master->get_content(198);
$smarty->assign('CONFIRMATION_INFO', $t_confirmation_info_array['content_text']);

# products table part
$coo_content_view = new ContentView();
$coo_content_view->set_content_template('module/checkout_confirmation_products.html');
$coo_content_view->set_content_data('products_data', $t_products_array);
$t_products_table_part = $coo_content_view->get_html();
$smarty->assign('PRODUCTS_TABLE_PART', $t_products_table_part);

// EOF GM_MOD

if ($order->info['payment_method'] != 'no_payment' && $order->info['payment_method'] != '') {
	include (DIR_WS_LANGUAGES . '/' . $_SESSION['language'] . '/modules/payment/' . $order->info['payment_method'] . '.php');
	$smarty->assign('PAYMENT_METHOD', constant(MODULE_PAYMENT_ . strtoupper($order->info['payment_method']) . _TEXT_TITLE));
	if (isset($_GET['payment_error']) && is_object(${$_GET['payment_error']}) && ($error = ${$_GET['payment_error']}->get_error())){
		$smarty->assign('error', $error['title'].'<br />'.htmlspecialchars_wrapper($error['error']));
	}
}
$smarty->assign('PAYMENT_EDIT', xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));

// BOF GM_MOD saferpay
if(strpos($_SESSION['payment'], 'saferpaygw') === false)
{
	$total_block = '<table id="total_block_table">';
	if (MODULE_ORDER_TOTAL_INSTALLED) {
		$order_total_modules->process();
		$total_block .= $order_total_modules->output();
		$t_total_block_array = $order_total_modules->output_array();
	}
	$total_block .= '</table>';
}
// EOF GM_MOD saferpay
$smarty->assign('TOTAL_BLOCK', $total_block);
$smarty->assign('total_block_data', $t_total_block_array);

//GM_PATCH 0000318
$payment_modules->update_status();

if (is_array($payment_modules->modules)) {
	if ($confirmation = $payment_modules->confirmation()) {

		$payment_info = $confirmation['title'];
		for ($i = 0, $n = sizeof($confirmation['fields']); $i < $n; $i++) {

			$payment_info .= '<table>
								<tr>
						                <td>' . xtc_draw_separator('pixel_trans.gif', '10', '1') . '</td>
						                <td class="main">' . $confirmation['fields'][$i]['title'] . '</td>
						                <td>' . xtc_draw_separator('pixel_trans.gif', '10', '1') . '</td>
						                <td class="main">' . stripslashes($confirmation['fields'][$i]['field']) . '</td>
						              </tr></table>';

		}
		$smarty->assign('PAYMENT_INFORMATION', $payment_info);

	}
}

if (xtc_not_null($order->info['comments'])) {
	$smarty->assign('ORDER_COMMENTS', nl2br(htmlspecialchars_wrapper($order->info['comments'])) . xtc_draw_hidden_field('comments', $order->info['comments']));

}

// Call Refresh Hook
$payment_modules->refresh();
if (isset ($$_SESSION['payment']->form_action_url) && !$$_SESSION['payment']->tmpOrders && $_SESSION['payment'] != 'no_payment') {

	$form_action_url = $$_SESSION['payment']->form_action_url;

} else {
	$form_action_url = xtc_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL');
}

// BOF GM_MOD saferpay
// we need a source for our js to be loaded befor form
$sPreForm = '';
if(method_exists($$_SESSION['payment'], 'confirm_pre_form'))
{
	$sPreForm = $$_SESSION['payment']->confirm_pre_form();
}
$smarty->assign('CHECKOUT_FORM', $sPreForm . xtc_draw_form('checkout_confirmation', $form_action_url, 'post'));
$smarty->assign('CHECKOUT_FORM_PREFORM', $sPreForm);
$smarty->assign('CHECKOUT_FORM_ACTION_URL', $form_action_url);
// EOF GM_MOD saferpay

$payment_button = '';
if (is_array($payment_modules->modules)) {
	$payment_button .= $payment_modules->process_button();
}
$smarty->assign('MODULE_BUTTONS', $payment_button);
// BOF GM_MOD
$smarty->assign('BUTTON_BACK', '<a href="javascript:history.back()"><img src="templates/' . CURRENT_TEMPLATE . '/buttons/' . $_SESSION['language'] . '/backgr.gif" /></a>');
$coo_text_mgr = MainFactory::create_object('LanguageTextManager', array('checkout_confirmation', $_SESSION['languages_id']) );
$t_button_alt_text = $coo_text_mgr->get_text('text_confirm_send');
$smarty->assign('CHECKOUT_BUTTON', xtc_image_submit('bestellung.gif', $t_button_alt_text) . '</form>' . "\n");
// EOF GM_MOD

// BOF GM_MOD Heidelpay Bugfixes
$_SESSION['gm_heidelpay'] = $order->info['total'];
$_SESSION['gm_heidelpay_currency'] = $order->info['currency'];
$_SESSION['gm_heidelpay_firstname'] = $order->billing['firstname'];
$_SESSION['gm_heidelpay_lastname'] = $order->billing['lastname'];
$_SESSION['gm_heidelpay_gender'] = $order->customer['gender'];
$_SESSION['gm_heidelpay_street_address'] = $order->billing['street_address'];
$_SESSION['gm_heidelpay_postcode'] = $order->billing['postcode'];
$_SESSION['gm_heidelpay_city'] = $order->billing['city'];
$_SESSION['gm_heidelpay_state'] = $order->billing['state'];
$_SESSION['gm_heidelpay_city'] = $order->billing['city'];
$_SESSION['gm_heidelpay_state'] = $order->billing['state'];
$_SESSION['gm_heidelpay_iso_code_2'] = $order->billing['country']['iso_code_2'];
$_SESSION['gm_heidelpay_email_address'] = $order->customer['email_address'];
// EOF GM_MOD Heidelpay Bugfixes

 // Heidelpay: ueberschreiben von CHECKOUT_FORM, MODULE_BUTTONS und CHECKOUT_BUTTON
 if (substr($payment_modules->selected_module,0,9) == 'heidelpay' && $payment_modules->selected_module != 'heidelpaypp') {
   $HEIDELPAY_CALL_FORM = true;
   $smarty->assign('CHECKOUT_FORM', '');
   $payment_button = $payment_modules->process_button();
   $smarty->assign('MODULE_BUTTONS', $payment_button);
   $smarty->assign('CHECKOUT_BUTTON', '');
 }
 // /Heidelpay

// BOF GM_MOD
if(gm_get_env_info('TEMPLATE_VERSION') < FIRST_GX2_TEMPLATE_VERSION)
{
	if(gm_get_conf('GM_SHOW_PRIVACY_CONFIRMATION') == 1){
		$smarty->assign('PRIVACY_CONFIRMATION', '<a href="' . xtc_href_link('shop_content.php', 'coID=2', 'SSL') . '" target="_blank" class="conditions_info_link">' . GM_CONFIRMATION_PRIVACY . '</a>');
		$smarty->assign('PRIVACY_CONFIRMATION_TEXT', GM_CONFIRMATION_PRIVACY);
		$smarty->assign('PRIVACY_CONFIRMATION_URL', xtc_href_link('shop_content.php', 'coID=2', 'SSL'));
	}

	if(gm_get_conf('GM_SHOW_CONDITIONS_CONFIRMATION') == 1){
		$smarty->assign('CONDITIONS_CONFIRMATION', '<a href="' . xtc_href_link('shop_content.php', 'coID=3', 'SSL') . '" target="_blank" class="conditions_info_link">' . GM_CONFIRMATION_CONDITIONS . '</a>');
		$smarty->assign('CONDITIONS_CONFIRMATION_TEXT', GM_CONFIRMATION_CONDITIONS);
		$smarty->assign('CONDITIONS_CONFIRMATION_URL', xtc_href_link('shop_content.php', 'coID=3', 'SSL') );
	}

	if(gm_get_conf('GM_SHOW_WITHDRAWAL_CONFIRMATION') == 1){
		$smarty->assign('WITHDRAWAL_CONFIRMATION', '<a href="' . xtc_href_link('shop_content.php', 'coID='.gm_get_conf('GM_WITHDRAWAL_CONTENT_ID'), 'SSL') . '" target="_blank" class="conditions_info_link">' . GM_CONFIRMATION_WITHDRAWAL . '</a>');
		$smarty->assign('WITHDRAWAL_CONFIRMATION_TEXT', GM_CONFIRMATION_WITHDRAWAL);
		$smarty->assign('WITHDRAWAL_CONFIRMATION_URL', xtc_href_link('shop_content.php', 'coID='.gm_get_conf('GM_WITHDRAWAL_CONTENT_ID'), 'SSL'));
	}
}
else
{
	if(gm_get_conf('GM_SHOW_PRIVACY_CONFIRMATION') == 1){
		$smarty->assign('PRIVACY_CONFIRMATION', '<a href="' . xtc_href_link('shop_content.php', 'coID=2&lightbox_mode=1', 'SSL') . '" target="_blank" class="conditions_info_link lightbox_iframe">' . GM_CONFIRMATION_PRIVACY . '</a>');
		$smarty->assign('PRIVACY_CONFIRMATION_TEXT', GM_CONFIRMATION_PRIVACY);
		$smarty->assign('PRIVACY_CONFIRMATION_URL', xtc_href_link('popup_content.php', 'coID=2&lightbox_mode=1', 'SSL'));
	}

	if(gm_get_conf('GM_SHOW_CONDITIONS_CONFIRMATION') == 1){
		$smarty->assign('CONDITIONS_CONFIRMATION', '<a href="' . xtc_href_link('shop_content.php', 'coID=3&lightbox_mode=1', 'SSL') . '" target="_blank" class="conditions_info_link lightbox_iframe">' . GM_CONFIRMATION_CONDITIONS . '</a>');
		$smarty->assign('CONDITIONS_CONFIRMATION_TEXT', GM_CONFIRMATION_CONDITIONS);
		$smarty->assign('CONDITIONS_CONFIRMATION_URL', xtc_href_link('popup_content.php', 'coID=3&lightbox_mode=1', 'SSL') );
	}

	if(gm_get_conf('GM_SHOW_WITHDRAWAL_CONFIRMATION') == 1){
		$smarty->assign('WITHDRAWAL_CONFIRMATION', '<a href="' . xtc_href_link('shop_content.php', 'coID='.gm_get_conf('GM_WITHDRAWAL_CONTENT_ID') . '&lightbox_mode=1', 'SSL') . '" target="_blank" class="conditions_info_link lightbox_iframe">' . GM_CONFIRMATION_WITHDRAWAL . '</a>');
		$smarty->assign('WITHDRAWAL_CONFIRMATION_TEXT', GM_CONFIRMATION_WITHDRAWAL);
		$smarty->assign('WITHDRAWAL_CONFIRMATION_URL', xtc_href_link('popup_content.php', 'coID='.gm_get_conf('GM_WITHDRAWAL_CONTENT_ID') . '&lightbox_mode=1', 'SSL'));
	}
}



// EOF GM_MOD

// Machinecoin Payment - Commerce Coding - BEGIN
if($_SESSION['payment'] == 'machinecoin') {
    $multiplier = 1;
    $digits = 8;
    switch (MODULE_PAYMENT_MACHINECOIN_UNITS) {
        case 'uMAC':
            $multiplier *= 1000;
            $digits -= 3;
        case 'mMAC':
            $multiplier *= 1000;
            $digits -= 3;
        case 'MAC':
            $macPrice = number_format($order->info['total'] / MODULE_PAYMENT_MACHINECOIN_MACEUR * $multiplier, $digits, '.', '');
            $smarty->assign('MACHINECOIN_PRICE', $macPrice . ' ' . MODULE_PAYMENT_MACHINECOIN_UNITS);
    }
}
// Machinecoin Payment - Commerce Coding - END

$coo_ts_excellence = MainFactory::create_object('TrustedShopsExcellenceContentView');
$t_view_html = $coo_ts_excellence->get_html($order);
$smarty->assign('MODULE_ts_excellence', $t_view_html);

// BOF GM_MOD:
$smarty->assign('LIGHTBOX', gm_get_conf('GM_LIGHTBOX_CHECKOUT'));	
$smarty->assign('LIGHTBOX_CLOSE', xtc_href_link(FILENAME_DEFAULT, '', 'NONSSL'));

$smarty->assign('language', $_SESSION['language']);
$smarty->assign('PAYMENT_BLOCK', $payment_block);
$smarty->caching = 0;
$main_content = $smarty->fetch(CURRENT_TEMPLATE . '/module/checkout_confirmation.html');

$smarty->assign('tpl_path', 'templates/'.CURRENT_TEMPLATE.'/');
$smarty->assign('language', $_SESSION['language']);
$smarty->assign('main_content', $main_content);
$smarty->caching = 0;
if (!defined(RM))
	$smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE . '/index.html');
include ('includes/application_bottom.php');
?>