<?php
add_action( 'admin_menu', 'wcmyap_add_admin_menu' );
add_action( 'admin_init', 'wcmyap_settings_init' );


function wcmyap_add_admin_menu(  ) { 

	add_options_page( 'MYAP Configuration', 'MYAP Config', 'manage_options', 'wcmyap_plugin', 'wcmyap_options_page' );

}


function wcmyap_settings_init(  ) { 

	register_setting(
		'pluginPage',
		'wcmyap_settings',
		'wcmyap_settings_validate_and_sanitize'
	);

	add_settings_section(
		'wcmyap_pluginPage_section', 
		__( 'Program Settings', 'wordpress' ), 
		'wcmyap_settings_section_callback', 
		'pluginPage'
	);

	add_settings_field( 
		'wcmyap_text_program_domain', 
		__( 'Program Domain', 'wordpress' ), 
		'wcmyap_text_program_domain_render', 
		'pluginPage', 
		'wcmyap_pluginPage_section' 
	);

	add_settings_field( 
		'wcmyap_checkbox_program_has_ssl', 
		__( 'Program Has SSL', 'wordpress' ), 
		'wcmyap_checkbox_program_has_ssl_render', 
		'pluginPage', 
		'wcmyap_pluginPage_section' 
	);

	add_settings_field( 
		'wcmyap_checkbox_track_products', 
		__( 'Record Products', 'wordpress' ), 
		'wcmyap_checkbox_track_products_render', 
		'pluginPage', 
		'wcmyap_pluginPage_section' 
	);

	add_settings_field( 
		'wcmyap_text_merchant_action_id', 
		__( 'Commissionable Action ID', 'wordpress' ), 
		'wcmyap_text_merchant_action_id_render', 
		'pluginPage', 
		'wcmyap_pluginPage_section' 
	);
	
	add_settings_field( 
		'wcmyap_new_to_file', 
		__( 'New to File', 'wordpress' ), 
		'wcmyap_new_to_file_render', 
		'pluginPage', 
		'wcmyap_pluginPage_section' 
	);

	
	
	add_settings_field( 
		'wcmyap_select_lifetime_id', 
		__( 'Lifetime ID', 'wordpress' ), 
		'wcmyap_select_lifetime_id_render', 
		'pluginPage', 
		'wcmyap_pluginPage_section' 
	);

	add_settings_field( 
		'wcmyap_checkbox_enable_itn', 
		__( 'Instant Transaction Notification (ITN)', 'wordpress' ), 
		'wcmyap_checkbox_enable_itn_render', 
		'pluginPage', 
		'wcmyap_pluginPage_section' 
	);


}


function wcmyap_text_program_domain_render(  ) { 

	$options = get_option( 'wcmyap_settings' );
	?>
	<input type='text' name='wcmyap_settings[wcmyap_text_program_domain]' value='<?php echo $options['wcmyap_text_program_domain']; ?>'>
	<small>example: affiliates.mydomain.com</small>
	<?php

}


function wcmyap_checkbox_program_has_ssl_render(  ) { 

	$options = get_option( 'wcmyap_settings' );
	?>
	<input type='checkbox' name='wcmyap_settings[wcmyap_checkbox_program_has_ssl]' <?php checked( $options['wcmyap_checkbox_program_has_ssl'], 1 ); ?> value='1'>
	<small>My MYAP has a SSL certificate</small>
	<?php

}

function wcmyap_checkbox_enable_itn_render(  ) { 

	$options = get_option( 'wcmyap_settings' );
	?>	<input type='checkbox' name='wcmyap_settings[wcmyap_checkbox_enable_itn]' <?php checked( $options['wcmyap_checkbox_enable_itn'], 1 ); ?> value='1'>
	<small>Enable Instant Transaction Notification support</small>
	<p><br/>When enabled, this plugin will support MYAP's Instant Transaction Notification (ITN) feature which allows you to update your WooCommerces orders with the ID of the affiliate who received credit for the sale. After you have enabled this feature, please login to your MYAP program, go to Settings =&gt; Actions and enable ITN on your commissionable action. Use the following URL for your ITN endpoint:<br/><br/><strong><?php echo get_site_url(); ?>/myap_itn.php</strong></p>
	<p><br/>If you are using a plugin that manipulates order numbers, such as <a href="https://wordpress.org/plugins/woocommerce-sequential-order-numbers/" target="_blank">WooCommerce Sequential Order Numbers</a> or similar, you will need to add a Transaction Extra Field by logging in to your MYAP and going to Transactions =&gt; Extra Fields and creating a new field with a name/slug of wc_order_id and use a field type of Integer.</p>
	<?php

}


function wcmyap_checkbox_track_products_render(  ) { 

	$options = get_option( 'wcmyap_settings' );
	?>
	<input type='checkbox' name='wcmyap_settings[wcmyap_checkbox_track_products]' <?php checked( $options['wcmyap_checkbox_track_products'], 1 ); ?> value='1'>
	<small>Pass order product information to MYAP</small>
	<?php

}


function wcmyap_text_merchant_action_id_render(  ) { 

	$options = get_option( 'wcmyap_settings' );
	?>
	<input type='text' name='wcmyap_settings[wcmyap_text_merchant_action_id]' value='<?php echo $options['wcmyap_text_merchant_action_id']; ?>'>
	<small>Numerical</small>
	<?php

}

function wcmyap_select_lifetime_id_render(){
	$options = get_option( 'wcmyap_settings' );
	$option_value = $options['wcmyap_select_lifetime_id'];	
	?>
	<select name="wcmyap_settings[wcmyap_select_lifetime_id]">
		<option <?php selected( !isset($option_value) || empty($option_value) ); ?> value="">None</option>
		<option <?php selected( "Coupon" == $option_value ); ?> value="Coupon">Coupon Code</option>
		<option <?php selected( "Username" == $option_value ); ?> value="Username">User Name</option>
		<option <?php selected( "UserID" == $option_value ); ?> value="UserID">User ID</option>
	</select>
	<p><br/>Lifetime IDs enable you to track sales without cookies. This is accomplished by establishing a link between an Affiliate and a Lifetime ID value. Future sales that occur can be attributed to an Affiliate if the same Lifetime ID is passed.</p><?php
}

function wcmyap_new_to_file_render(){
	$options = get_option( 'wcmyap_settings' );
	$ntf_length = $options['wcmyap_new_to_file_length'];
	$ntf_type = $options['wcmyap_new_to_file_type'];
	
	if(!isset($ntf_length) || empty($ntf_length))
		$ntf_length = 1;
		
	if(!isset($ntf_type) || empty($ntf_type))
		$ntf_type = 'Hour';

	?>
	<input type='text' name='wcmyap_settings[wcmyap_new_to_file_length]' value='<?php echo $ntf_length; ?>'>
	<select name="wcmyap_settings[wcmyap_new_to_file_type]">
		<option <?php selected( "Minute" == $ntf_type ); ?> value="Minute">Minute(s)</option>
		<option <?php selected( "Hour" == $ntf_type ); ?> value="Hour">Hour(s)</option>
		<option <?php selected( "Day" == $ntf_type ); ?> value="Day">Day(s)</option>
	</select>
	<?php
}



function wcmyap_settings_section_callback(  ) { 

	echo __( 'Basic configuration for MYAP', 'wordpress' );

}

function wcmyap_settings_validate_and_sanitize($input){
	
	
	$output = array();	 
    foreach( $input as $key => $value ) {         
        // Check to see if the current option has a value. If so, process it.
        if( isset( $input[$key] ) ) {
			$output[$key] = $input[ $key ];
        }
         
    }
	
	/* Ensure that New To File duration is set and a whole number */
	if(!isset($input['wcmyap_new_to_file_length']) || empty($input['wcmyap_new_to_file_length']) || !is_numeric($input['wcmyap_new_to_file_length']) || intval($input['wcmyap_new_to_file_length']) <= 0 )
		add_settings_error( 'wcmyap_settings', 'wcmyap_new_to_file_length', 'New To File interval must be a number, greater than zero' );
	else
		$output['wcmyap_new_to_file_length'] = intval($input['wcmyap_new_to_file_length']);

	/* Ensure that Commissionable Action ID is set and a whole number */
	if(!isset($input['wcmyap_text_merchant_action_id']) || empty($input['wcmyap_text_merchant_action_id']) || !is_numeric($input['wcmyap_text_merchant_action_id']) || intval($input['wcmyap_text_merchant_action_id']) <= 0 )
		add_settings_error( 'wcmyap_settings', 'wcmyap_text_merchant_action_id', 'Commissionable Action ID must be a number, greater than zero' );
	else
		$output['wcmyap_text_merchant_action_id'] = intval($input['wcmyap_text_merchant_action_id']);
		
		
    // Return the array processing any additional functions filtered by this action
    return apply_filters( 'wcmyap_settings_validate_and_sanitize', $output, $input );

}


function wcmyap_options_page(  ) { 

	?>
	<form action='options.php' method='post'>

		<h2>MYAP v10 Tracking for WooCommerce</h2>

		<?php
		settings_fields( 'pluginPage' );
		do_settings_sections( 'pluginPage' );
		submit_button();
		?>

	</form>
	<?php

}

?>