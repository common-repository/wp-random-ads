<?php

/**
 * Specify Hooks/Filters
 */
add_action( 'admin_menu', 'wnt_add_menu' );
add_action( 'admin_init', 'wnt_register_settings' );

/**
 * Include the required files
 */
// helper functions
require_once('wnt-helper-functions.php');
// page settings sections & fields as well as the contextual help text.
require_once('wnt-plugin-options.php');

 /**
 * Helper function for defining variables according to current page content
 *
 * @return array
 */
function wnt_get_settings() {
	
	$output = array();
	
	/*PAGES*/
	// get current page
	$page = wnt_get_admin_page();
	
	/*TABS*/
	// get current tab
	$tab = wnt_get_the_tab();
	
	/*DEFINE VARS*/
	// define variables according to registered admin menu page: wnt_add_menu()
	switch ($page) {
		case WNT_PAGE_BASENAME:
			$wnt_option_name 			= 'wnt_options';
			$wnt_settings_page_title    = __( 'WNT Random Ads Settings','wnt');	
			$wnt_page_sections 			= wnt_options_page_sections();
			$wnt_page_fields 			= wnt_options_page_fields();
			$wnt_contextual_help        = wnt_options_page_contextual_help();
			$wnt_page_tabs				= '';
		break;
	}
	
	// put together the output array 
	$output['wnt_option_name'] 		= $wnt_option_name;
	$output['wnt_page_title'] 		= $wnt_settings_page_title;
	$output['wnt_page_tabs']		= $wnt_page_tabs;
	$output['wnt_page_sections'] 	= $wnt_page_sections;
	$output['wnt_page_fields'] 		= $wnt_page_fields;
	$output['wnt_contextual_help'] 	= $wnt_contextual_help;
	
return $output;
}

/**
 * Helper function for registering our form field settings
 *
 * src: http://alisothegeek.com/2011/01/wordpress-settings-api-tutorial-1/
 * @param (array) $args The array of arguments to be used in creating the field
 * @return function call
 */
function wnt_create_settings_field( $args = array() ) {
	// default array to overwrite when calling the function
	$defaults = array(
		'id'      => 'default_field', 			// the ID of the setting in our options array, and the ID of the HTML form element
		'title'   => 'Default Field', 			// the label for the HTML form element
		'desc'    => 'This is a default description.', 	// the description displayed under the HTML form element
		'std'     => '', 				// the default value for this setting
		'type'    => 'text', 				// the HTML form element to use
		'section' => 'main_section', 			// the section this setting belongs to must match the array key of a section in wnt_options_page_sections()
		'choices' => array(), 				// (optional): the values in radio buttons or a drop-down menu
		'class'   => '' 				// the HTML form element class. Is used for validation purposes and may be also use for styling if needed.
	);
	
	// "extract" to be able to use the array keys as variables in our function output below
	extract( wp_parse_args( $args, $defaults ) );
	
	// additional arguments for use in form field output in the function wnt_form_field_fn!
	$field_args = array(
		'type'      => $type,
		'id'        => $id,
		'desc'      => $desc,
		'std'       => $std,
		'choices'   => $choices,
		'label_for' => $id,
		'class'     => $class
	);

	add_settings_field( $id, $title, 'wnt_form_field_fn', __FILE__, $section, $field_args );

}

/**
 * Register our setting, settings sections and settings fields
 */
function wnt_register_settings(){
	
	// get the settings sections array
	$settings_output 	= wnt_get_settings();
	$wnt_option_name = $settings_output['wnt_option_name'];
	
	//setting
	register_setting($wnt_option_name, $wnt_option_name, 'wnt_validate_options' );
	
	//sections
	if(!empty($settings_output['wnt_page_sections'])){
		// call the "add_settings_section" for each!
		foreach ( $settings_output['wnt_page_sections'] as $id => $title ) {
			add_settings_section( $id, $title, 'wnt_section_fn', __FILE__);
		}
	}
		
	//fields
	if(!empty($settings_output['wnt_page_fields'])){
		// call the "add_settings_field" for each!
		foreach ($settings_output['wnt_page_fields'] as $option) {
			wnt_create_settings_field($option);
		}
	}
}

/**
 * Group scripts (js & css)
 */
function wnt_settings_scripts(){
	wp_enqueue_style('wnt_theme_settings_css', WP_PLUGIN_URL . '/wp-random-ads/lib/css/wnt_theme_settings.css');
	wp_enqueue_script( 'wnt_theme_settings_js', WP_PLUGIN_URL . '/wp-random-ads/lib/js/wnt_theme_settings.js', array('jquery'), '1', true );
}

/**
 * The admin menu pages
 */
function wnt_add_menu(){
	
	$settings_output 		= wnt_get_settings();
	// collect our contextual help text
	$wnt_contextual_help = $settings_output['wnt_contextual_help'];
	
	// As a "top level" menu
	add_menu_page( __('WNT Random Ads Settings'), __('WNT Settings','wnt'), 'manage_options', WNT_PAGE_BASENAME, 'wnt_settings_page_fn');
	
	// page one
	$wnt_settings_page = add_submenu_page(WNT_PAGE_BASENAME, __('Wnt Settings | Options'), __('WNT Ads Random','wnt'), 'manage_options', WNT_PAGE_BASENAME, 'wnt_settings_page_fn');
		// contextual help
		if ($wnt_settings_page) {
			add_contextual_help( $wnt_settings_page, $wnt_contextual_help );
		}
		// css & js
		add_action( 'load-'. $wnt_settings_page, 'wnt_settings_scripts' );
	
}

// ************************************************************************************************************

// Callback functions

/*
 * Section HTML, displayed before the first option
 * @return echoes output
 */
function  wnt_section_fn($desc) {
	echo '<p>' . __('Settings for this section','wnt') . '</p>';
}

/**
 * Form Fields HTML
 * All form field types share the same function!!
 * @return echoes output
 */
function wnt_form_field_fn($args = array()) {
	
	extract( $args );
	
	// get the settings sections array
	$settings_output 	= wnt_get_settings();
	
	$wnt_option_name    = $settings_output['wnt_option_name'];
	$options 			= get_option($wnt_option_name);
	
	// pass the standard value if the option is not yet set in the database
	if ( !isset( $options[$id] ) && 'type' != 'checkbox' ) {
		$options[$id] = $std;
	}
	
	// additional field class. output only if the class is defined in the create_setting arguments
	$field_class = ($class != '') ? ' ' . $class : '';
	
	// switch html display based on the setting type.	
	switch ( $type ) {
		case 'text':
			$options[$id] = stripslashes($options[$id]);
			$options[$id] = esc_attr( $options[$id]);
			echo "<input class='regular-text$field_class' type='text' id='$id' name='" . $wnt_option_name . "[$id]' value='$options[$id]' />";
			echo ($desc != '') ? "<br /><span class='description'>$desc</span>" : "";
		break;
		
		case "multi-text":
			foreach($choices as $item) {
				$item = explode("|",$item); // cat_name|cat_slug
				$item[0] = esc_html__($item[0], 'wnt');
				if (!empty($options[$id])) {
					foreach ($options[$id] as $option_key => $option_val){
						if ($item[1] == $option_key) {
							$value = $option_val;
						}
					}
				} else {
					$value = '';
				}
				echo "<span>$item[0]:</span> <input class='$field_class' type='text' id='$id|$item[1]' name='" . $wnt_option_name . "[$id|$item[1]]' value='$value' /><br/>";
			}
			echo ($desc != '') ? "<span class='description'>$desc</span>" : "";
		break;
		
		case 'textarea':
			$options[$id] = stripslashes($options[$id]);
			$options[$id] = esc_html( $options[$id]);
			echo "<textarea class='textarea$field_class' type='text' id='$id' name='" . $wnt_option_name . "[$id]' rows='5' cols='30'>$options[$id]</textarea>";
			echo ($desc != '') ? "<br /><span class='description'>$desc</span>" : ""; 		
		break;
		
		case 'select':
			echo "<select id='$id' class='select$field_class' name='" . $wnt_option_name . "[$id]'>";
				foreach($choices as $item) {
					$value 	= esc_attr($item, 'wnt');
					$item 	= esc_html($item, 'wnt');
					
					$selected = ($options[$id]==$value) ? 'selected="selected"' : '';
					echo "<option value='$value' $selected>$item</option>";
				}
			echo "</select>";
			echo ($desc != '') ? "<br /><span class='description'>$desc</span>" : ""; 
		break;
		
		case 'select2':
			echo "<select id='$id' class='select$field_class' name='" . $wnt_option_name . "[$id]'>";
			foreach($choices as $item) {
				
				$item = explode("|",$item);
				$item[0] = esc_html($item[0], 'wnt');
				
				$selected = ($options[$id]==$item[1]) ? 'selected="selected"' : '';
				echo "<option value='$item[1]' $selected>$item[0]</option>";
			}
			echo "</select>";
			echo ($desc != '') ? "<br /><span class='description'>$desc</span>" : "";
		break;
		
		case 'checkbox':
			echo "<input class='checkbox$field_class' type='checkbox' id='$id' name='" . $wnt_option_name . "[$id]' value='1' " . checked( $options[$id], 1, false ) . " />";
			echo ($desc != '') ? "<br /><span class='description'>$desc</span>" : "";
		break;
		
		case "multi-checkbox":
			foreach($choices as $item) {
				
				$item = explode("|",$item);
				$item[0] = esc_html($item[0], 'wnt');
				
				$checked = '';
				
			    if ( isset($options[$id][$item[1]]) ) {
					if ( $options[$id][$item[1]] == 'true') {
			   			$checked = 'checked="checked"';
					}
				}
				
				echo "<input class='checkbox$field_class' type='checkbox' id='$id|$item[1]' name='" . $wnt_option_name . "[$id|$item[1]]' value='1' $checked /><label for='$id|$item[1]'> $item[0]</label> <br/>";
			}
			echo ($desc != '') ? "<br /><span class='description'>$desc</span>" : "";
		break;
		
		case "image-checkbox":
			foreach($choices as $item) {
				
				$item = explode("|",$item);
				$item[0] = esc_html($item[0], 'wnt');
				
				$checked = '';
				
			    if ( isset($options[$id][$item[1]]) ) {
					if ( $options[$id][$item[1]] == 'true') {
			   			$checked = 'checked="checked"';
					}
				}
				
				echo "<label for='$id|$item[1]' style='float: left;'><input class='checkbox$field_class' type='checkbox' id='$id|$item[1]' name='" . $wnt_option_name . "[$id|$item[1]]' value='1' $checked /><img src='$item[0]' /></label>";
			}
                        echo "<div class='clearfix'></div>";
			echo ($desc != '') ? "<span class='description'>$desc</span>" : "";
		break;
	}
}

/*
 * Admin Settings Page HTML
 * 
 * @return echoes output
 */
function wnt_settings_page_fn() {
	// get the settings sections array
	$settings_output = wnt_get_settings();
?>
	<div class="wrap">
		<?php 
		// dislays the page title and tabs (if needed)
		wnt_settings_page_header(); 
		?>
		
		<form action="options.php" method="post" style="float: left; width: 760px;">
			<?php 
			// http://codex.wordpress.org/Function_Reference/settings_fields
			settings_fields($settings_output['wnt_option_name']); 
			// http://codex.wordpress.org/Function_Reference/do_settings_sections
			do_settings_sections(__FILE__); 
			?>
			
			<p class="submit">
				<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes','wnt'); ?>" />
			</p>
			
		</form>
		
		<div id="wnt_side">

			<div class="postbox-container">
				<div class="metabox-holder">	
					<div class="meta-box-sortables ui-sortable">
					
						<div id="wnt-support" class="postbox">
							<h3 class="hndle"><span><?php _e('WNT Support Tiket', 'wnt'); ?></span></h3>
							<div class="inside">
								<p><?php _e('Need a support ? Visit this page', 'wnt'); ?> <a href="http://support.withoutnoisetheme.com/" target="_blank"><?php _e('wnt support', 'wnt'); ?></a></p>
							</div>
						</div>
						
					</div>
				</div>
			</div>

		</div>
	</div><!-- wrap -->
<?php }

/**
 * Validate input
 * 
 * @return array
 */
function wnt_validate_options($input) {
	
	// for enhanced security, create a new empty array
	$valid_input = array();
	
	// collect only the values we expect and fill the new $valid_input array i.e. whitelist our option IDs
	
		// get the settings sections array
		$settings_output = wnt_get_settings();
		
		$options = $settings_output['wnt_page_fields'];
		
		// run a foreach and switch on option type
		foreach ($options as $option) {
		
			switch ( $option['type'] ) {
				case 'text':
					//switch validation based on the class!
					switch ( $option['class'] ) {
						//for numeric 
						case 'numeric':
							//accept the input only when numeric!
							$input[$option['id']] 		= trim($input[$option['id']]); // trim whitespace
							$valid_input[$option['id']] = (is_numeric($input[$option['id']])) ? $input[$option['id']] : 'Expecting a Numeric value!';
							
							// register error
							if(is_numeric($input[$option['id']]) == FALSE) {
								add_settings_error(
									$option['id'], // setting title
									WNT_SHORTNAME . '_txt_numeric_error', // error ID
									__('Expecting a Numeric value! Please fix.','wnt'), // error message
									'error' // type of message
								);
							}
						break;
						
						//for multi-numeric values (separated by a comma)
						case 'multinumeric':
							//accept the input only when the numeric values are comma separated
							$input[$option['id']] 		= trim($input[$option['id']]); // trim whitespace
							
							if($input[$option['id']] !=''){
								// /^-?\d+(?:,\s?-?\d+)*$/ matches: -1 | 1 | -12,-23 | 12,23 | -123, -234 | 123, 234  | etc.
								$valid_input[$option['id']] = (preg_match('/^-?\d+(?:,\s?-?\d+)*$/', $input[$option['id']]) == 1) ? $input[$option['id']] : __('Expecting comma separated numeric values','wnt');
							}else{
								$valid_input[$option['id']] = $input[$option['id']];
							}
							
							// register error
							if($input[$option['id']] !='' && preg_match('/^-?\d+(?:,\s?-?\d+)*$/', $input[$option['id']]) != 1) {
								add_settings_error(
									$option['id'], // setting title
									WNT_SHORTNAME . '_txt_multinumeric_error', // error ID
									__('Expecting comma separated numeric values! Please fix.','wnt'), // error message
									'error' // type of message
								);
							}
						break;
						
						//for no html
						case 'nohtml':
							//accept the input only after stripping out all html, extra white space etc!
							$input[$option['id']] 		= sanitize_text_field($input[$option['id']]); // need to add slashes still before sending to the database
							$valid_input[$option['id']] = addslashes($input[$option['id']]);
						break;
						
						//for url
						case 'url':
							//accept the input only when the url has been sanited for database usage with esc_url_raw()
							$input[$option['id']] 		= trim($input[$option['id']]); // trim whitespace
							$valid_input[$option['id']] = esc_url_raw($input[$option['id']]);
						break;
						
						//for email
						case 'email':
							//accept the input only after the email has been validated
							$input[$option['id']] 		= trim($input[$option['id']]); // trim whitespace
							if($input[$option['id']] != ''){
								$valid_input[$option['id']] = (is_email($input[$option['id']])!== FALSE) ? $input[$option['id']] : __('Invalid email! Please re-enter!','wnt');
							}elseif($input[$option['id']] == ''){
								$valid_input[$option['id']] = __('This setting field cannot be empty! Please enter a valid email address.','wnt');
							}
							
							// register error
							if(is_email($input[$option['id']])== FALSE || $input[$option['id']] == '') {
								add_settings_error(
									$option['id'], // setting title
									WNT_SHORTNAME . '_txt_email_error', // error ID
									__('Please enter a valid email address.','wnt'), // error message
									'error' // type of message
								);
							}
						break;
						
						// a "cover-all" fall-back when the class argument is not set
						default:
							// accept only a few inline html elements
							$allowed_html = array(
								'a' => array('href' => array (),'title' => array ()),
								'b' => array(),
								'em' => array (), 
								'i' => array (),
								'strong' => array()
							);
							
							$input[$option['id']] 		= trim($input[$option['id']]); // trim whitespace
							$input[$option['id']] 		= force_balance_tags($input[$option['id']]); // find incorrectly nested or missing closing tags and fix markup
							$input[$option['id']] 		= wp_kses( $input[$option['id']], $allowed_html); // need to add slashes still before sending to the database
							$valid_input[$option['id']] = addslashes($input[$option['id']]); 
						break;
					}
				break;
				
				case "multi-text":
					// this will hold the text values as an array of 'key' => 'value'
					unset($textarray);
					
					$text_values = array();
					foreach ($option['choices'] as $k => $v ) {
						// explode the connective
						$pieces = explode("|", $v);
						
						$text_values[] = $pieces[1];
					}
					
					foreach ($text_values as $v ) {		
						
						// Check that the option isn't empty
						if (!empty($input[$option['id'] . '|' . $v])) {
							// If it's not null, make sure it's sanitized, add it to an array
							switch ($option['class']) {
								// different sanitation actions based on the class create you own cases as you need them
								
								//for numeric input
								case 'numeric':
									//accept the input only if is numberic!
									$input[$option['id'] . '|' . $v]= trim($input[$option['id'] . '|' . $v]); // trim whitespace
									$input[$option['id'] . '|' . $v]= (is_numeric($input[$option['id'] . '|' . $v])) ? $input[$option['id'] . '|' . $v] : '';
								break;
								
								// a "cover-all" fall-back when the class argument is not set
								default:
									// strip all html tags and white-space.
									$input[$option['id'] . '|' . $v]= sanitize_text_field($input[$option['id'] . '|' . $v]); // need to add slashes still before sending to the database
									$input[$option['id'] . '|' . $v]= addslashes($input[$option['id'] . '|' . $v]);
								break;
							}
							// pass the sanitized user input to our $textarray array
							$textarray[$v] = $input[$option['id'] . '|' . $v];
						
						} else {
							$textarray[$v] = '';
						}
					}
					// pass the non-empty $textarray to our $valid_input array
					if (!empty($textarray)) {
						$valid_input[$option['id']] = $textarray;
					}
				break;
				
				case 'textarea':
					//switch validation based on the class!
					switch ( $option['class'] ) {
						//for only inline html
						case 'inlinehtml':
							// accept only inline html
							$input[$option['id']] 		= trim($input[$option['id']]); // trim whitespace
							$input[$option['id']] 		= force_balance_tags($input[$option['id']]); // find incorrectly nested or missing closing tags and fix markup
							$input[$option['id']] 		= addslashes($input[$option['id']]); //wp_filter_kses expects content to be escaped!
							$valid_input[$option['id']]     = wp_filter_kses($input[$option['id']]); //calls stripslashes then addslashes
						break;
						
						//for no html
						case 'nohtml':
							//accept the input only after stripping out all html, extra white space etc!
							$input[$option['id']] 		= sanitize_text_field($input[$option['id']]); // need to add slashes still before sending to the database
							$valid_input[$option['id']]     = addslashes($input[$option['id']]);
						break;
						
						//for script
						case 'script':
							//accept the input only after stripping out all html, extra white space etc!
							$input[$option['id']] 		= stripslashes($input[$option['id']]); // need to add slashes still before sending to the database
							$valid_input[$option['id']]     = addslashes($input[$option['id']]);
						break;
						
						//for allowlinebreaks
						case 'allowlinebreaks':
							//accept the input only after stripping out all html, extra white space etc!
							$input[$option['id']] 		= wp_strip_all_tags($input[$option['id']]); // need to add slashes still before sending to the database
							$valid_input[$option['id']]     = addslashes($input[$option['id']]);
						break;
						
						// a "cover-all" fall-back when the class argument is not set
						default:
							// accept only limited html
							//my allowed html
							$allowed_html = array(
								'a' 			=> array('href' => array (),'title' => array ()),
								'b' 			=> array(),
								'blockquote'            => array('cite' => array ()),
								'br' 			=> array(),
								'dd' 			=> array(),
								'dl' 			=> array(),
								'dt' 			=> array(),
								'em' 			=> array (), 
								'i' 			=> array (),
								'li' 			=> array(),
								'ol' 			=> array(),
								'p' 			=> array(),
								'q' 			=> array('cite' => array ()),
								'strong' 		=> array(),
								'ul' 			=> array(),
								'h1' 			=> array('align' => array (),'class' => array (),'id' => array (), 'style' => array ()),
								'h2' 			=> array('align' => array (),'class' => array (),'id' => array (), 'style' => array ()),
								'h3' 			=> array('align' => array (),'class' => array (),'id' => array (), 'style' => array ()),
								'h4' 			=> array('align' => array (),'class' => array (),'id' => array (), 'style' => array ()),
								'h5' 			=> array('align' => array (),'class' => array (),'id' => array (), 'style' => array ()),
								'h6' 			=> array('align' => array (),'class' => array (),'id' => array (), 'style' => array ())
							);
							
							$input[$option['id']] 		= trim($input[$option['id']]); // trim whitespace
							$input[$option['id']] 		= force_balance_tags($input[$option['id']]); // find incorrectly nested or missing closing tags and fix markup
							$input[$option['id']] 		= wp_kses( $input[$option['id']], $allowed_html); // need to add slashes still before sending to the database
							$valid_input[$option['id']] = addslashes($input[$option['id']]);							
						break;
					}
				break;
				
				case 'select':
					// check to see if the selected value is in our approved array of values!
					$valid_input[$option['id']] = (in_array( $input[$option['id']], $option['choices']) ? $input[$option['id']] : '' );
				break;
				
				case 'select2':
					// process $select_values
						$select_values = array();
						foreach ($option['choices'] as $k => $v) {
							// explode the connective
							$pieces = explode("|", $v);
							
							$select_values[] = $pieces[1];
						}
					// check to see if selected value is in our approved array of values!
					$valid_input[$option['id']] = (in_array( $input[$option['id']], $select_values) ? $input[$option['id']] : '' );
				break;
				
				case 'checkbox':
					// if it's not set, default to null!
					if (!isset($input[$option['id']])) {
						$input[$option['id']] = null;
					}
					// Our checkbox value is either 0 or 1
					$valid_input[$option['id']] = ( $input[$option['id']] == 1 ? 1 : 0 );
				break;
				
				case 'multi-checkbox':
					unset($checkboxarray);
					$check_values = array();
					foreach ($option['choices'] as $k => $v ) {
						// explode the connective
						$pieces = explode("|", $v);
						
						$check_values[] = $pieces[1];
					}
					
					foreach ($check_values as $v ) {		
						
						// Check that the option isn't null
						if (!empty($input[$option['id'] . '|' . $v])) {
							// If it's not null, make sure it's true, add it to an array
							$checkboxarray[$v] = 'true';
						}
						else {
							$checkboxarray[$v] = 'false';
						}
					}
					// Take all the items that were checked, and set them as the main option
					if (!empty($checkboxarray)) {
						$valid_input[$option['id']] = $checkboxarray;
					}
				break;
				
				case 'image-checkbox':
					unset($checkboxarray);
					$check_values = array();
					foreach ($option['choices'] as $k => $v ) {
						// explode the connective
						$pieces = explode("|", $v);
						
						$check_values[] = $pieces[1];
					}
					
					foreach ($check_values as $v ) {		
						
						// Check that the option isn't null
						if (!empty($input[$option['id'] . '|' . $v])) {
							// If it's not null, make sure it's true, add it to an array
							$checkboxarray[$v] = 'true';
						}
						else {
							$checkboxarray[$v] = 'false';
						}
					}
					// Take all the items that were checked, and set them as the main option
					if (!empty($checkboxarray)) {
						$valid_input[$option['id']] = $checkboxarray;
					}
				break;
				
			}
		}
return $valid_input; // return validated input
}

/**
 * Helper function for creating admin messages
 * src: http://www.wprecipes.com/how-to-show-an-urgent-message-in-the-wordpress-admin-area
 *
 * @param (string) $message The message to echo
 * @param (string) $msgclass The message class
 * @return echoes the message
 */
function wnt_show_msg($message, $msgclass = 'info') {
	echo "<div id='message' class='$msgclass'>$message</div>";
}

/**
 * Callback function for displaying admin messages
 *
 * @return calls wnt_show_msg()
 */
function wnt_admin_msgs() {
	
	// check for our settings page - need this in conditional further down
	$wnt_settings_pg = strpos($_GET['page'], WNT_PAGE_BASENAME);
	// collect setting errors/notices: //http://codex.wordpress.org/Function_Reference/get_settings_errors
	$set_errors = get_settings_errors(); 
	
	//display admin message only for the admin to see, only on our settings page and only when setting errors/notices are returned!	
	if(current_user_can ('manage_options') && $wnt_settings_pg !== FALSE && !empty($set_errors)){

		// have our settings succesfully been updated? 
		if($set_errors[0]['code'] == 'settings_updated' && isset($_GET['settings-updated'])){
			wnt_show_msg("<p>" . $set_errors[0]['message'] . "</p>", 'updated');
		
		// have errors been found?
		}else{
			// there maybe more than one so run a foreach loop.
			foreach($set_errors as $set_error){
				// set the title attribute to match the error "setting title" - need this in js file
				wnt_show_msg("<p class='setting-error-message' title='" . $set_error['setting'] . "'>" . $set_error['message'] . "</p>", 'error');
			}
		}
	}
}

// admin messages hook!
add_action('admin_notices', 'wnt_admin_msgs');
?>