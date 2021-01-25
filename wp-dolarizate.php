<?php
/**
 * Plugin Name: WP Dolarizate
 * Plugin URI: https://github.com/PecceG2/wp-dolarizate/
 * Description: WP Dolarizate is a WordPress plugin for automatically currency exchange and display with Visual Composer.
 * Version: 0.1
 * Author: Giuliano Peccetto
 * Author URI: https://www.pecceg2.com/
 */
 
 
// Main files
require_once dirname( __FILE__ ) .'/config-page.php';


add_action('admin_menu', 'create_settings_page');

function create_settings_page() {
	add_menu_page('WP Dolarizate', 'WP Dolarizate', 'administrator', __FILE__, 'settings_page' , plugins_url('/icon.png', __FILE__) );
	add_action( 'admin_init', 'register_settings' );
}

add_action('admin_post_wpd_update_config', 'save_settings_page');
function save_settings_page() {
	update_option('api_dolar', $_POST['api_dolar']);
	update_option('update_time', $_POST['update_time']);
	update_option('dolar_type', $_POST['dolar_type']);
	update_option('dolar_type_cv', $_POST['dolar_type_cv']);
	switch($_POST['api_dolar']){
		case 0:
		//DolarSI
		currencyUpdate_DolarSI();
		break;
		case 1:
		//BCRA
		break;
		case 2:
		//BNA
		break;
		case 3:
		//XEC
		break;
	}
    wp_redirect($_SERVER["HTTP_REFERER"], 302, 'WordPress');
} 

function register_settings() {
	register_setting('wp-dolarizate-settings', 'api_dolar');
	register_setting('wp-dolarizate-settings', 'update_time');
	register_setting('wp-dolarizate-settings', 'dolar_type');
	register_setting('wp-dolarizate-settings', 'dolar_type_cv');
	register_setting('wp-dolarizate-settings', 'dolar_value');
}

/*########################################################################*/
//		  						CRON JOBS								  //
/*########################################################################*/

function currencyUpdate_DolarSI(){
	$content = getCURL("https://www.dolarsi.com/api/api.php?type=valoresprincipales", true);
	switch(get_option("dolar_type_cv")){
		case "dolarCompra":
			$dolVal = $content[get_option("dolar_type")]->casa->compra;
		break;
		case "dolarVenta":
			$dolVal = $content[get_option("dolar_type")]->casa->venta;
		break;
		case "dolarAgencia":
			$dolVal = $content[get_option("dolar_type")]->casa->agencia;
		break;
		default:
			$dolVal = 0;
		break;
	}
	
	
	update_option('dolar_value', $dolVal);
	return($content);
}

function currencyUpdate_EstadisticasBCRA(){

}

function currencyUpdate_BNAOficial(){

}

function currencyUpdate_XE(){

}

/*########################################################################*/
//		  					   END CRON JOBS							  //
/*########################################################################*/


function getCURL($url, $isJSON){
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
	if($isJSON){
		$content = json_decode(curl_exec($ch));
	}else{
		$content = curl_exec($ch);
	}
	curl_close($ch);
	return($content);
}


/*########################################################################*/
//		  						SHORTCODES								  //
/*########################################################################*/

function sc_dolarizate_print($atts) {
    $default = array(
        'valor' => '0',
    );
	
    $attr = shortcode_atts($default, $atts);
    
	return floatval($attr['valor'])*floatval(get_option('dolar_value'));
}
add_shortcode('wp-dolarizate', 'sc_dolarizate_print'); 

/*########################################################################*/
//		  					   END SHORTCODES							  //
/*########################################################################*/
?>