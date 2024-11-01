<?php
/*
Plugin Name: WP Random Ads
Plugin URI: http://withoutnoisetheme.com/
Description: WP Random Ads Plugin allows you to manage advertising on the post (single post) randomly.
Version: 1.0
Author: Onnay Okheng
Author URI: http://onnayokheng.com/
Text Domain: wnt
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

/**
 * Define Constants
 */
define('WNT_SHORTNAME', 'wnt'); 
define('WNT_PAGE_BASENAME', 'wnt-settings'); 
define('WNT_ADSENSE_IMG', WP_PLUGIN_URL.'/wp-random-ads/images');

function wnt_check_layout($data){
    global $wnt_option;
    
    foreach ($wnt_option[$data] as $value) {
        if($value == 'true')
            return true;
    }
    
    return false;
}


// add filter if find function the_content, usually on single page
add_filter('the_content', 'wnt_adsense_random');
function wnt_adsense_random($content){
        global $wnt_option;
        
        $output = '';
        
        if(is_single()){
        
                // random ads script
                $adstop     = (rand(1, 2) == 1)? 300:336;
                $adsmiddle  = (rand(1, 2) == 1)? 300:336;
                $adsbottom  = (rand(1, 2) == 1)? 300:336;

                // looping for random top.
                if(wnt_check_layout('wnt_adsense_top')){
                    do{
                        $top    = rand(1, 3);
                        if($top == 1)
                            $styletop   = 'float: left; margin-right: 10px;';
                        elseif($top == 2)
                            $styletop   = 'text-align: center; margin-bottom: 10px;';
                        elseif($top == 3)
                            $styletop   = 'float: right; margin-left: 10px;';
                        else
                            $styletop   = null;
                    }while($wnt_option['wnt_adsense_top'][$top] == 'false' );

                    if(isset ($styletop))
                        $output = '<div style="'.$styletop.'">'.  stripslashes($wnt_option['wnt_adsense_'.$adstop]).'</div>';
                }
                
                // display the content
                if(wnt_check_layout('wnt_adsense_middle')){
                    $arraypost  = explode("</p>", $content);        
                    $counts     = 1; 
                    $t          = 0;
                    foreach($arraypost as $item) { // looping for display array post
                        if(preg_match('/<p> /', $item) == 0 && $counts == $wnt_option['wnt_place_middle'] && $t == 0) {

                            do{ // looping for random middle.
                                $middle = rand(1, 3);
                                if($middle == 1)
                                    $stylemiddle   = 'float: left; margin-right: 10px;';
                                elseif($middle == 2)
                                    $stylemiddle   = 'text-align: center; margin-bottom: 10px; margin-top:5px;';
                                elseif($middle == 3)
                                    $stylemiddle   = 'float: right; margin-left: 10px;';
                                else
                                    $stylemiddle   = null;
                            }while($wnt_option['wnt_adsense_middle'][$middle] == 'false' );

                            if(isset ($stylemiddle))
                                $output .= '<div style="'.$stylemiddle.'">'.  stripslashes($wnt_option['wnt_adsense_'.$adsmiddle]).'</div>';
                            $t = 1;

                        }

                        $output .= $item; 
                        $output .= "</p>";

                        $counts++;

                    }
                }else{
                    $output .= $content;
                }


                // looping for random top.
                if(wnt_check_layout('wnt_adsense_bottom')){
                    do{
                        $bottom = rand(1, 3);
                        if($bottom == 1)
                            $stylebottom   = 'left';
                        elseif($bottom == 2)
                            $stylebottom   = 'center';
                        elseif($bottom == 3)
                            $stylebottom   = 'right';
                        else
                            $stylebottom   = null;
                    }while($wnt_option['wnt_adsense_bottom'][$bottom] == 'false' );

                    if(isset ($stylebottom))
                        $output .= '<div align="'.$stylebottom.'">'.  stripslashes($wnt_option['wnt_adsense_'.$adsbottom]).'</div>';
                }
        
        }else{
                $output = $content;
        }
    
return $output;
}


//require only in admin!
if(is_admin()){	
	require_once('lib/wnt-plugin-settings-advanced.php');
}

/**
 * Collects our theme options
 *
 * @return array
 */
function wnt_get_global_options(){
	
	$wnt_option = array();

	// collect option names as declared in wnt_get_settings()
	$wnt_option_names = array (
		'wnt_options'
	);

	// loop for get_option
	foreach ($wnt_option_names as $wnt_option_name) {
		if (get_option($wnt_option_name)!= FALSE) {
			$option 	= get_option($wnt_option_name);
			
			// now merge in main $wnt_option array!
			$wnt_option = array_merge($wnt_option, $option);
		}
	}	
	
return $wnt_option;
}

/**
 * Call the function and collect in variable
 *
 * Should be used in template files like this:
 * <?php echo $wnt_option['wnt_txt_input']; ?>
 *
 * Note: Should you notice that the variable ($wnt_option) is empty when used in certain templates such as header.php, sidebar.php and footer.php
 * you will need to call the function (copy the line below and paste it) at the top of those documents (within php tags)!
 */ 
$wnt_option = wnt_get_global_options();

?>
