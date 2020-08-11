<?php
/**
 * Plugin Name:       WPSeccure
 * Plugin URI:        https://wpseccure.com/
 * Description:       WordPress security plugin focused on Self-Hosted Monitoring and Integrity for WordPress.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Hafiz Syed Osama Bin Masood
 * Author URI:        https://usamaMasood.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-seccure
 * Domain Path:       /languages
 */

// REMOVE SETTINGS AT UNINSTALL
register_uninstall_hook(__FILE__, 'wpseccure_plugin_uninstall');

// ACTIVATION HOOK
register_activation_hook( __FILE__, 'wpseccure_setup_main_variables' );

// CONSTANTS AND VARIABLES
define('WPSECCURE_DIR', plugin_dir_path( __FILE__ ));
define('CURRENT_SITE_URL', get_site_url());


// CALLING MAIN CLASS
require WPSECCURE_DIR . '/assets/class-wpseccure.php';
use function WPSeccurePlugin\{writeString, generateHashfromURL, get_website_ip_address, get_website_nameservers, get_headers_with_keys, get_current_security_headers, get_hash_from_url};


// Add a menu for our options page
add_action('admin_menu', 'wpseccure_plugin_add_settings_menu');

function wpseccure_plugin_add_settings_menu(){
	add_options_page('WP SECCURE Plugin Settings', 'WPSeccure Settings', 'manage_options', 'wpseccure_plugin', 'wpseccure_plugin_option_page');

}

// Create the option page
function wpseccure_plugin_option_page() {
	?>
		<h1 style="margin: 30px 0;"><span class="dashicons dashicons-shield" style="font-size: 30px; margin: -4px 12px;"></span>WP Seccure</h1>
		<div class="wrap">
			<form action="options.php" method="post">
				<?php
					settings_fields('wpseccure_plugin_options');
					do_settings_sections('wpseccure_plugin');

					wpseccure_plugin_show_existing_details();
					submit_button('Save Changes', 'primary');
				?>
			</form>
		</div>
	<?php
}

// Register and define the settings
add_action('admin_init', 'wpseccure_plugin_admin_init');

function wpseccure_plugin_admin_init(){

	// Define the settings array
	$args = array(
		'type' => 'string',
		'sanitize_callback' => 'wpseccure_plugin_validate_options',
		'default' => NULL
	);

	// Register main settings
	register_setting(
		'wpseccure_plugin_options', // $option_group
		'wpseccure_plugin_options', // $option_name
		$args // arguments array
	);

	// Add Monitoring section
	add_settings_section(
		'wpseccure_plugin_options', // $id
		'Configure Settings', // $title
		'wpseccure_monitoring_section_text', //$callback function
		'wpseccure_plugin' // $page
	);

	// Create "Enable Monitoring" field
	add_settings_field(
		'wpseccure_plugin_monitoring', //$id
		'Enable Monitoring', //$title
		'wpseccure_plugin_setting_enable_monitoring', //$callback
		'wpseccure_plugin', //$page
		'wpseccure_plugin_options' // (string) (Optional) The slug-name of the section of the settings page in which to show the box. Default value: 'default'
	);
	// Create "Monitor DNS" field
	add_settings_field(
		'wpseccure_plugin_dns', //$id
		'Monitor DNS', //$title
		'wpseccure_plugin_setting_monitor_dns', //$callback
		'wpseccure_plugin', //$page
		'wpseccure_plugin_options' // (string) (Optional) The slug-name of the section of the settings page in which to show the box. Default value: 'default'
	);
	// Create "Monitor Headers" field
	add_settings_field(
		'wpseccure_plugin_headers', //$id
		'Monitor Security Headers', //$title
		'wpseccure_plugin_setting_monitor_headers', //$callback
		'wpseccure_plugin', //$page
		'wpseccure_plugin_options' // (string) (Optional) The slug-name of the section of the settings page in which to show the box. Default value: 'default'
	);


	// Create "Enable SRI" field
	add_settings_field(
		'wpseccure_plugin_enable_sri', //$id
		'Enable Subresource Integrity Attribute?', //$title
		'wpseccure_plugin_setting_enable_sri', //$callback
		'wpseccure_plugin', //$page
		'wpseccure_plugin_options' // (string) (Optional) The slug-name of the section of the settings page in which to show the box. Default value: 'default'
	);

}

// Draw the section header
function wpseccure_monitoring_section_text(){
	echo '<p>Enable or Disable Monitoring for each category here:</p>';
}

// ENABLE MONITORING FIELD
function wpseccure_plugin_setting_enable_monitoring(){

	// Get option 'enable_monitoring' value from the database
	// Set to 'disabled' as default if the option value does not exit
	$options = get_option( 'wpseccure_plugin_options', ['enable_monitoring' => 'disabled'] );
	$enable_monitoring = $options['enable_monitoring'];

	// Define the radio button option
	$items = array('enabled', 'disabled');	

	foreach ($items as $item) {
		// Loop through the two radio button options if set in the option value
		echo "<label><input " . checked($enable_monitoring, $item, false) . " value='" . esc_attr($item) . "' name='wpseccure_plugin_options[enable_monitoring]' type='radio' />" . esc_attr( $item ) . "</label><br/>";
	}

}

// DISPLAY ENABLE SUBRESOURCE INTEGRITY FIELD
function wpseccure_plugin_setting_enable_sri(){

	// Get option 'enable_monitoring' value from the database
	// Set to 'disabled' as default if the option value does not exit
	$options = get_option( 'wpseccure_plugin_options', ['enable_sri' => 'disabled'] );
	$enable_sri = $options['enable_sri'];

	// Define the radio button option
	$items = array('enabled', 'disabled');	

	foreach ($items as $item) {
		// Loop through the two radio button options if set in the option value
		echo "<label><input " . checked($enable_sri, $item, false) . " value='" . esc_attr($item) . "' name='wpseccure_plugin_options[enable_sri]' type='radio' />" . esc_attr( $item ) . "</label><br/>";
	}

}

// Display and fill the Name text form field
function wpseccure_plugin_setting_monitor_dns(){

	// Get option 'monitor_dns' value from the database
	// Set to 'disabled' as default if the option value does not exit
	$options = get_option( 'wpseccure_plugin_options', ['monitor_dns' => 'disabled'] );
	$monitor_dns = $options['monitor_dns'];

	// Define the radio button option
	$items = array('enabled', 'disabled');

	foreach ($items as $item) {
		// Loop through the two radio button options if set in the option value
		echo "<label><input " . checked($monitor_dns, $item, false) . " value='" . esc_attr($item) . "' name='wpseccure_plugin_options[monitor_dns]' type='radio' />" . esc_attr( $item ) . "</label><br/>";
	}

}

// Display and fill the Name text form field
function wpseccure_plugin_setting_monitor_headers(){

	$options = get_option( 'wpseccure_plugin_options', ['monitor_headers' => 'disabled'] );
	$monitor_headers = $options['monitor_headers'];

	// Define the radio button option
	$items = array('enabled', 'disabled');

	foreach ($items as $item) {
		// Loop through the two radio button options if set in the option value
		echo "<label><input " . checked($monitor_headers, $item, false) . " value='" . esc_attr($item) . "' name='wpseccure_plugin_options[monitor_headers]' type='radio' />" . esc_attr( $item ) . "</label><br/>";
	}

}

function wpseccure_plugin_show_existing_details(){
	
	// GET SAVED VALUES FROM DATABASE
	$monitoring_options = get_option('wpseccure_monitoring_options');

	$security_header_array = $monitoring_options['current_security_headers'];
	$current_ip_address = $monitoring_options['current_ip'];
	$current_nameservers_array = $monitoring_options['current_nameservers'];

	// print_r($monitoring_options);

	?>
	<hr>
	<h3>Current Server Status</h3>
	<table class="form-table">
		<tr valign="top">
			<th scope="row">Current IP Address</th>
			<td><strong><?php echo $current_ip_address; ?></strong></td>
		</tr>
		<tr valign="top">
			<th scope="row">NameServers</th>
			<td><strong><?php
				if(is_array($current_nameservers_array)){
					foreach ($current_nameservers_array as $server) {
						echo $server . "<br>";
					}            		
				} else {
					echo "<span style='color:red'>Cannot get Name Servers</span>";
				}
			?></strong></td>
		</tr>
		<tr valign="top">
			<th scope="row">Current Security Headers</th>
			<td><strong><?php
				if(is_array($security_header_array)){
					foreach ($security_header_array as $header) {
						echo $header . "<br>";
					}            		
				} else {
					echo "<span style='color:red'>Cannot get Security Headers</span>";
				}
			?></strong></td>
		</tr>
		<tr valign="top">
			<th scope="row"><h2>Subresource Integrity</h2><p>Existing Saved Hashes</p></th>
			<td></td>
			<table class="widefat">
			    <thead>
			        <tr>
			            <th>URL</th>
			            <th>SHA-384 Hash</th>
			        </tr>
			    </thead>
			    <tfoot>
			        <tr>
			            <th>URL</th>
			            <th>SHA-384 Hash</th>
			        </tr>
			    </tfoot>
			    <tbody>
			        <?php
			            $options = get_option( 'wpseccure_sri_options', false );
			            $hashes = $options['hashes'];
			            foreach ($hashes as $item) {
			                echo "<tr>
			                <td>{$item['src']}</td>
			                <td>{$item['hash']}</td>
			                </tr>";
			            }
			        ?>
			        <tr></tr>
			    </tbody>
			</table>
		</tr>
		<tr valign="top">
			<th scope="row"><h2>Content-Security-Policy</h2><p>Current List of Domains in Content-Security-Policy</p></th>
			<td><strong><?php
			            $options = get_option( 'wpseccure_sri_options', false );
			            $domains = $options['domains'];
			            foreach ($domains as $domain) {
			                echo "{$domain}<br>";
			            }
			?></strong></td>
		</tr>
	</table>
	<?php
}

// Validate user input for all three optionn
function wpseccure_plugin_validate_options($input){

	// Only allow letters and spaces for name
	$valid['name'] = preg_replace(
		'/[^a-zA-Z\s]/',
		'',
		$input['name']
	);

	// Sanitize the data we are receiving
	$valid['enable_monitoring'] = sanitize_text_field( $input['enable_monitoring'] );
	$valid['monitor_dns'] = sanitize_text_field( $input['monitor_dns'] );
	$valid['monitor_headers'] = sanitize_text_field( $input['monitor_headers'] );
	$valid['enable_sri'] = sanitize_text_field( $input['enable_sri'] );

	return $valid;

}

// Register basic settings
function wpseccure_setup_main_variables(){

	// GENERATE AND SAVE SRI
	save_sri_to_db();

	$current_nameservers_array = get_website_nameservers(CURRENT_SITE_URL);

	$current_ip_address = get_website_ip_address(CURRENT_SITE_URL);
	$current_security_headers = get_current_security_headers(CURRENT_SITE_URL);

	// UPDATE OPTIONS
	$all_options = array(
		'current_nameservers' => $current_nameservers_array,
		'current_ip'		  => $current_ip_address,
		'current_security_headers' => $current_security_headers 
	);

	// SAVE ALL OPTIONS AGAIN ALONG WITH NEW OPTIONS
	update_option( 'wpseccure_monitoring_options', $all_options );

	echo "<span style='color:red'>DONE</span>";
}

// Deregister our settings group and delete all option
function wpseccure_plugin_uninstall(){

	// Clean de-registration of registered settings
	unregister_setting('wpseccure_plugin_options', 'wpseccure_plugin_options');
	// unregister_setting( $option_group, $option_name );

	// Remove saved options from the database
	delete_option('wpseccure_plugin_options');
	delete_option('wpseccure_monitoring_options');
	delete_option('wpseccure_sri_options');
}

// Function for checking monitoring to be used with Cron (WP-Cron or real Cron)
function check_monitoring(){
	// $test_url = 'https://troyhunt.com/';

	// GET NAMESERVERS, IP ADDRESS AND HEADERS AGAIN
	$current_nameservers_array 			= get_website_nameservers(CURRENT_SITE_URL);
	sort($current_nameservers_array);

	$current_security_headers_array 	= get_current_security_headers(CURRENT_SITE_URL);
	sort($current_security_headers_array);

	$current_ip_address 				= get_website_ip_address(CURRENT_SITE_URL);

	$stored_options = get_option('wpseccure_monitoring_options');

	// Get Individual Values
	$stored_nameservers_array 			= $stored_options['current_nameservers'];
	sort($stored_nameservers_array);

	$stored_security_headers_array 		= $stored_options['current_security_headers'];
	sort($stored_security_headers_array);

	$stored_ip_address 					= $stored_options['current_ip'];

	$email_message = '';

	if( $current_nameservers_array == $stored_nameservers_array && $current_ip_address == $stored_ip_address &&  $current_security_headers_array == $stored_security_headers_array ){
		// echo "<h2> All values same </h2>";
		return;
	}


	if( $current_nameservers_array != $stored_nameservers_array ){
		$email_message .= "<br> ALERT: one or more Name Servers have been altered! <br>\n";
	}


	if($current_ip_address != $stored_ip_address){
		$email_message .= "<br> ALERT: IP address has been changed! <br>\n";
	}


	if($current_security_headers_array != $stored_security_headers_array){
		$email_message .= "<br> ALERT: One or more security headers are missing! <br>\n";
	}

	// echo "MAIL MESSGAGE: " . $email_message;

	$admin_email = get_option('admin_email', false);
	$email_subject = 'ALERT! Settings on ' . CURRENT_SITE_URL . 'changed!';

	wp_mail(
		sanitize_email( $admin_email ),
		$email_subject,
		$email_message
	);
}

// REGISTER CRON FUNCTION TO CHECK MONITORING ATTRIBUTES HOURLY
register_activation_hook( __FILE__, 'wpseccure_cron_activation' );
function wpseccure_cron_activation(){

	if(!wp_next_scheduled( 'wpseccure_hourly_mail', $args )){
		wp_schedule_event(time(), 'hourly', 'wpseccure_hourly_mail', $args);
	}
}

add_action('wpseccure_hourly_mail', 'check_monitoring');


$all_script_array = array();
function get_all_scripts_with_hashes($tag, $handle, $src){

    global $all_script_array;
    $all_script_array[$handle] = array(
        'src' => $src,
        'hash' => get_hash_from_url($src)
    );

    return $tag;

}
add_filter('script_loader_tag', 'get_all_scripts_with_hashes', 10, 3);

function save_sri_to_db(){
    global $all_script_array;
    $all_domains = array();
    echo "URLS here";
    foreach ($all_urls as $url) {
    	$all_domains[] = parse_url($url['src'], PHP_URL_HOST);
    }

    // REMOVE DUPLICATES
    $all_domains = array_unique($all_domains);

    $args = array(
    	'hashes' => $all_script_array,
    	'domains' => $all_domains
    );
    update_option( 'wpseccure_sri_options', $args );

}
add_action('wp_footer', 'save_sri_to_db', 20);

function add_sri_in_script_tags($tag, $handle, $src){

	// GET SAVED HASHES FROM DATABASE
	$all_options = get_option('wpseccure_sri_options')['hashes'];

	// CHECK WHETHER SRI IS ENABLED OR NOT
	$enable_sri = get_option('wpseccure_plugin_options')['enable_sri'];

	if(array_key_exists($handle, $all_options) && $enable_sri == 'enabled') {
	    $tag = str_replace(' src', ' integrity="sha384-' . $all_options[$handle]['hash'] . '" crossorigin="anonymous" src', $tag);
	}

    return $tag;

}
add_filter('script_loader_tag', 'add_sri_in_script_tags', 99, 3);

function insert_CSP_header(){
	$all_options = get_option('wpseccure_sri_options');
	$domains = $all_options['domains'];
	$domain_string = '';

	foreach ($domains as $domain) {
		$domain_string .= ' ' . $domain;
	}
	$domain_string = "Header set Content-Security-Policy \"script-src 'self' 'unsafe-inline'" . $domain_string . ";\"";
	$home_path = function_exists('get_home_path') ? get_home_path() : ABSPATH;
	$htaccess = $home_path . ".htaccess";
	 
	$lines = array();
	$lines[] = "<IfModule mod_headers.c>";
	$lines[] = $domain_string;
	$lines[] = "</IfModule>";
	 
	insert_with_markers($htaccess, "ADDED BY WPSECCURE PLUGIN - DO NOT REMOVE", $lines);
}

add_action('admin_init', 'insert_CSP_header');

?>