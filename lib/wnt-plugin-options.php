<?php
/**
 * Define our settings sections
 *
 * array key=$id, array value=$title in: add_settings_section( $id, $title, $callback, $page );
 * @return array
 */
function wnt_options_page_sections() {
	
	$sections = array();
	// $sections[$id] 		= __($title, 'wnt');
	$sections['general_section'] 	= __('General Settings', 'wnt');
	$sections['adsense_section'] 	= __('Ads Script', 'wnt');
	
	return $sections;	
} 

/**
 * Define our form fields (settings) 
 *
 * @return array
 */
function wnt_options_page_fields() {
    
	
	// Checkbox Form Fields section
	$options[] = array(
		"section" => "general_section",
		"id"      => WNT_SHORTNAME . "_adsense_top",
		"title"   => __( 'Select layout ads before content', 'wnt' ),
		"desc"    => __( 'Choose the layout. If more than 1, it will be random.', 'wnt' ),
		"type"    => "image-checkbox",
		"std"     => '',
		"choices" => array( __(WNT_ADSENSE_IMG.'/top-left.png','wnt') . "|1", __(WNT_ADSENSE_IMG.'/top-center.png','wnt') . "|2", __(WNT_ADSENSE_IMG.'/top-right.png','wnt') . "|3")
	);
        
	$options[] = array(
		"section" => "general_section",
		"id"      => WNT_SHORTNAME . "_adsense_middle",
		"title"   => __( 'Select layout ads middle content', 'wnt' ),
		"desc"    => __( 'Choose the layout. If more than 1, it will be random.', 'wnt' ),
		"type"    => "image-checkbox",
		"std"     => '',
		"choices" => array( __(WNT_ADSENSE_IMG.'/middle-left.png','wnt') . "|1", __(WNT_ADSENSE_IMG.'/middle-center.png','wnt') . "|2", __(WNT_ADSENSE_IMG.'/middle-right.png','wnt') . "|3")
	);
        
	$options[] = array(
		"section" => "general_section",
		"id"      => WNT_SHORTNAME . "_adsense_bottom",
		"title"   => __( 'Select layout ads after content', 'wnt' ),
		"desc"    => __( 'Choose the layout. If more than 1, it will be random.', 'wnt' ),
		"type"    => "image-checkbox",
		"std"     => '',
		"choices" => array( __(WNT_ADSENSE_IMG.'/bottom-left.png','wnt') . "|1", __(WNT_ADSENSE_IMG.'/bottom-center.png','wnt') . "|2", __(WNT_ADSENSE_IMG.'/bottom-right.png','wnt') . "|3")
	);
        
	$options[] = array(
		"section" => "general_section",
		"id"      => WNT_SHORTNAME . "_place_middle",
		"title"   => __( 'Select number of paragraph for middle ads', 'wnt' ),
		"desc"    => __( 'Select the paragraphs to how to place ads.', 'wnt' ),
                "type"    => "select",
                "std"     => "3",
                "choices" => array( "2", "3", "4", "5")
	);
	
	// Textarea Form Fields section
	$options[] = array(
		"section" => "adsense_section",
		"id"      => WNT_SHORTNAME . "_adsense_300",
		"title"   => __( 'Ads 300 x 250', 'wnt' ),
		"desc"    => __( 'Ads script size 300 x 250.', 'wnt' ),
		"type"    => "textarea",
		"class"   => "script",
		"std"     => ''
	);
        
	$options[] = array(
		"section" => "adsense_section",
		"id"      => WNT_SHORTNAME . "_adsense_336",
		"title"   => __( 'Ads 336 x 280', 'wnt' ),
		"desc"    => __( 'Ads script size 336 x 280.', 'wnt' ),
		"type"    => "textarea",
		"class"   => "script",
		"std"     => ''
	);
	
	return $options;	
}

/**
 * Contextual Help
 */
function wnt_options_page_contextual_help() {
	
	$text 	= "<h3>" . __('WNT Ads Settings - Help','wnt') . "</h3>";
	$text 	.= "<p>" . __("This is BETA version, we don't done yet the documentation.",'wnt') . "</p>";
	
	// must return text! NOT echo
	return $text;
} ?>