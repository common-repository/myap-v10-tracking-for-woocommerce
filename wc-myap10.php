<?php
/**
 * Plugin Name: MYAP v10 Tracking for WooCommerce
 * Plugin URI: http://www.myaffiliateprogram.com
 * Description: This plugin integrates MYAP v10 with your WooCommerce store
 * Version: 10.1.43.1023
 * Author: Inuvo, Inc.
 * Author URI: http://www.inuvo.com
 * License: GPL2
 * WC requires at least: 2.2
 * WC tested up to: 3.7
 */
 
 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


include_once( 'wc-myap10-settings.php' );


//Instruct WordPress to include a JavaScript in the HEAD of the site
add_action( 'wp_head', 'wcmyap_script' );
function wcmyap_script() {
   $wcmyapConfig = get_option( 'wcmyap_settings' );
   if(isset($wcmyapConfig) && isset($wcmyapConfig['wcmyap_text_program_domain']) && !empty($wcmyapConfig['wcmyap_text_program_domain']))
   {
	wp_enqueue_script( 'wcmyap_script_js',  'http'.($wcmyapConfig['wcmyap_checkbox_program_has_ssl'] === '1'? 's':'').'://'.$wcmyapConfig['wcmyap_text_program_domain'].'/js/myap10.1.js', array('jquery') );
   }
}

//MYAP 10 Conversion Tracking
add_action( 'woocommerce_thankyou', 'wcmyap_trackorder' );

function wcmyap_trackorder( $order_id ) {
   $wcmyapConfig = get_option( 'wcmyap_settings' );
   if(isset($wcmyapConfig) && isset($wcmyapConfig['wcmyap_text_program_domain']) && !empty($wcmyapConfig['wcmyap_text_program_domain']))
   {
	$action_id = $wcmyapConfig['wcmyap_text_merchant_action_id'];
	$track_products = ($wcmyapConfig['wcmyap_checkbox_track_products'] === '1'? true:false);
	$lifetime_id_setting = $wcmyapConfig['wcmyap_select_lifetime_id'];	
	$ntf_length = $wcmyapConfig['wcmyap_new_to_file_length'];
	$ntf_type = $wcmyapConfig['wcmyap_new_to_file_type'];
	
	if(!isset($ntf_length) || empty($ntf_length))
		$ntf_length = 1;
		
	if(!isset($ntf_type) || empty($ntf_type))
		$ntf_type = 'Hour';
	
	
	$order = new WC_Order( $order_id );
	$order_id = $order->get_id();
	$order_number = $order->get_order_number();
	$order_meta = get_post_meta($order_id);	
	$cart_value = number_format( (float) $order->get_total() - $order->get_total_tax() - $order->get_total_shipping() , wc_get_price_decimals(), '.', '' );
	$couponCode = '';
	$couponIndex = 1;
	
	foreach( $order->get_used_coupons() as $coupon) {
		$couponCode .= $coupon;
		if( $couponIndex  < $coupons_count )
			$couponCode .= ', ';
		$couponIndex ++;
	}
	

	
	$user_id = get_post_meta( $order_id, '_customer_user', true );
	$user_data = get_userdata($user_id);
	$registered_date = $user_data->user_registered;	
	$lifetime_id_value = isset($lifetime_id_setting) ? ($lifetime_id_setting == 'Coupon'? $couponCode:($lifetime_id_setting == 'UserID' ? $user_id : ($lifetime_id_setting == 'Username' ? ($user_data->user_login) : ''))) : '';
	
	//Any user account created within 1 hour before the sale shale be deemed NEW TO FILE
	$newToFile = (strtotime($registered_date) > strtotime('-' . $ntf_length . ' ' . ($ntf_type == 'Day'? 'day' : ($ntf_type == 'Minute'? 'minute' : 'hour'))))? 'true':'false';

 ?>
<script type="text/javascript">
	jQuery(document).ready(function() {
		MYAP.trackAction({
			actionID: <?php echo $action_id; ?>,
			orderID: '<?php echo $order_number; ?>',
			amount: <?php echo $cart_value; ?>,
			shippingAmount: <?php echo $order->get_total_shipping(); ?>,
			taxAmount: <?php echo $order->get_total_tax(); ?>,
			discountAmount: <?php echo $order->get_total_discount();  ?>,
			couponUsed: <?php echo empty($couponCode)? 'false':'true'; ?>,
			couponCode: '<?php echo $couponCode; ?>',
			newToFile: <?php echo $newToFile; ?>,<?php if(isset($lifetime_id_value) && !empty($lifetime_id_value)) {?>
			
			lifetimeID: '<?php echo $lifetime_id_value; ?>',<?php
			}
			?>			
			extraFields:
			{
				'wc_order_id': '<?php echo $order_id ?>'
			}<?php

			if($track_products)
			{
 ?>,
			products:
			[
<?php
				$items = $order->get_items();
				foreach ( $items as $item_id => $item ) {
					//print_r($item);
					$item_product_id = $item['product_id']; 

					$data = get_post_meta( $item_product_id, '_tmcartepo_data'); 

					$product = $order->get_product_from_item( $item );
					if ( ! $product ) { continue; }
					$sku = $product->get_sku();

					if(empty($sku))
						$sku = 'WCOIID_'.$item_id;

					echo '				{ sku: "' . $sku . '", amount: ' . $item['line_subtotal'] .' , quantity: ' . $item['qty'] . ', itemAmount: ' .($item['qty']> 0? $item['line_subtotal'] / $item['qty'] : 0) .', taxAmount: ' .  $item['line_tax'] . ' },';
				}
?>

			]
<?php
			}			
			?>
	 	});
	});
</script>
<?php
   }
}



add_action('woocommerce_admin_order_data_after_order_details', 'wcmyap_admin_order_data_after_billing_address', 10, 1);
function wcmyap_admin_order_data_after_billing_address($order) {
	$affiliate_id = get_post_meta($order->get_id(), '_myap_order_affiliate_id', true);
	echo '<p class="form-field form-field-wide"><label for="myap_order_affiliate_id">' . __('MYAP Affiliate ID') . '</label>' . (!isset($affiliate_id) || trim($affiliate_id) === ''? '(None)': $affiliate_id ) . '</p>';
}


add_action('parse_request', 'wcmyap_parse_request');
function wcmyap_parse_request() {
	if(0 === strpos($_SERVER["REQUEST_URI"],'/myap_itn.php'))
	{		
	
		$wcmyapConfig = get_option( 'wcmyap_settings' );
		$itn_enabled = (isset($wcmyapConfig) && $wcmyapConfig['wcmyap_checkbox_enable_itn'] === '1'? true:false);
		
		/* If the merchant has not enabled ITN, ignore these requests */
		if(!$itn_enabled )
		{
			header('HTTP/1.0 404 File Not Found');
			echo 'File Not Found';
			exit();
		} 
		
	
		$order_id = trim(str_replace( '#', '', esc_attr($_POST['orderid'])));
		$wc_order_id = esc_attr($_POST['wc_order_id']);
		$source_id = esc_attr($_POST['sourceid']);
		$order = null;
		
		if( isset($wc_order_id) && !empty($wc_order_id))
			$order = new WC_Order($wc_order_id);
		else if( isset($order_id) && !empty($order_id))
			$order = new WC_Order($order_id);		
		
		if(!isset($order) || empty($order) || trim(str_replace( '#', '', $order->get_order_number())) != $order_id)
		{
			header('HTTP/1.0 403 Forbidden');
			echo 'You are forbidden!';
			exit();
		} 
		
		update_post_meta($order->get_id(), '_myap_order_affiliate_id', $source_id);		
		exit();
	}
}